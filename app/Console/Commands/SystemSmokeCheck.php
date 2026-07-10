<?php

namespace App\Console\Commands;

use App\Models\Agency;
use App\Models\FormSubmission;
use App\Models\SubmissionApprovalHistory;
use App\Models\UpliftSubmission;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

class SystemSmokeCheck extends Command
{
  protected $signature = 'system:smoke-check
    {--user= : User ID to use for login/logout guard check}
    {--email= : User email to use for login/logout guard check}
    {--url= : Base URL for HTTP checks, defaults to APP_URL}
    {--skip-http : Skip local HTTP checks}
    {--json : Output machine-readable JSON}';

  protected $description = 'Run a non-destructive smoke check for auth, submission approval routing, and protected APIs';

  private array $results = [];

  public function handle(): int
  {
    $this->results = [];

    $user = $this->resolveUser();

    $this->checkRoutes();
    $this->checkAuthGuard($user);
    $this->checkSubmissions();
    $this->checkApprovalRouting();

    if (!$this->option('skip-http')) {
      $this->checkHttpEndpoints();
    }

    $failed = collect($this->results)->contains(fn($result) => $result['status'] === 'fail');

    if ($this->option('json')) {
      $this->line(json_encode($this->results, JSON_PRETTY_PRINT));
    } else {
      $this->renderTable();
    }

    return $failed ? self::FAILURE : self::SUCCESS;
  }

  private function resolveUser(): ?User
  {
    if ($this->option('user')) {
      return User::find($this->option('user'));
    }

    if ($this->option('email')) {
      return User::where('email', $this->option('email'))->first();
    }

    return User::orderBy('id')->first();
  }

  private function checkRoutes(): void
  {
    $routes = [
      'login',
      'logout',
      'submissions.index',
      'uplift-submissions.index',
      'submissions.approve',
      'submissions.return',
      'submissions.reject',
      'uplift-submissions.approve',
      'uplift-submissions.return',
      'uplift-submissions.reject',
    ];

    foreach ($routes as $route) {
      $this->record(
        'route:' . $route,
        Route::has($route),
        Route::has($route) ? 'Registered' : 'Missing'
      );
    }

    $hasApprovedAnswersRoute = collect(Route::getRoutes())
      ->contains(fn($route) => $route->uri() === 'api/v1/approved/indicator-submissions');

    $this->record(
      'route:api.approved.indicator-submissions',
      $hasApprovedAnswersRoute,
      $hasApprovedAnswersRoute ? 'Registered' : 'Missing'
    );
  }

  private function checkAuthGuard(?User $user): void
  {
    if (!$user) {
      $this->record('auth:user', false, 'No user found. Pass --email= or --user=.');
      return;
    }

    Auth::login($user);
    $this->record('auth:login', Auth::check() && Auth::id() === $user->id, 'Logged in as ' . $user->email);

    Auth::logout();
    $this->record('auth:logout', !Auth::check(), 'Logged out guard session');
  }

  private function checkSubmissions(): void
  {
    $formStatuses = FormSubmission::query()
      ->selectRaw('status, count(*) as total')
      ->groupBy('status')
      ->pluck('total', 'status')
      ->all();

    $upliftStatuses = UpliftSubmission::query()
      ->selectRaw('status, count(*) as total')
      ->groupBy('status')
      ->pluck('total', 'status')
      ->all();

    $this->record('submissions:indicator', true, $this->formatCounts($formStatuses));
    $this->record('submissions:uplift', true, $this->formatCounts($upliftStatuses));

    $historyCount = SubmissionApprovalHistory::count();
    $this->record('approval-history', $historyCount > 0, $historyCount . ' row(s)', $historyCount > 0 ? 'pass' : 'warn');
  }

  private function checkApprovalRouting(): void
  {
    $this->checkFormApprovalRouting();
    $this->checkUpliftApprovalRouting();
  }

  private function checkFormApprovalRouting(): void
  {
    $submission = FormSubmission::with('form')
      ->where('status', 'submitted')
      ->whereHas('form', fn($query) => $query->whereNotNull('assigned_sector_id'))
      ->first();

    if (!$submission || !$submission->form) {
      $this->record('approval:indicator:data', true, 'No submitted assigned indicator submission found', 'warn');
      return;
    }

    $this->checkPolicyPair(
      'approval:indicator',
      'approve',
      $submission,
      (int) $submission->form->assigned_sector_id
    );
  }

  private function checkUpliftApprovalRouting(): void
  {
    $submission = UpliftSubmission::with('measure')
      ->where('status', 'submitted')
      ->whereHas('measure', fn($query) => $query->whereNotNull('assigned_sector_id'))
      ->first();

    if (!$submission || !$submission->measure) {
      $this->record('approval:uplift:data', true, 'No submitted assigned UPLIFT submission found', 'warn');
      return;
    }

    $this->checkPolicyPair(
      'approval:uplift',
      'approve',
      $submission,
      (int) $submission->measure->assigned_sector_id
    );
  }

  private function checkPolicyPair(string $label, string $ability, mixed $submission, int $staffId): void
  {
    $matchingStaff = User::where('agency_id', Agency::DEPDEV_ID)
      ->where('staff_id', $staffId)
      ->first();

    $nonMatchingStaff = User::where('agency_id', Agency::DEPDEV_ID)
      ->whereNotNull('staff_id')
      ->where('staff_id', '!=', $staffId)
      ->where('role_id', '!=', 1)
      ->first();

    if ($matchingStaff) {
      $allowed = Gate::forUser($matchingStaff)->allows($ability, $submission);
      $this->record($label . ':matching-staff', $allowed, $matchingStaff->email);
    } else {
      $this->record($label . ':matching-staff', true, 'No DEPDev user found for staff_id ' . $staffId, 'warn');
    }

    if ($nonMatchingStaff) {
      $denied = Gate::forUser($nonMatchingStaff)->denies($ability, $submission);
      $this->record($label . ':non-matching-staff', $denied, $nonMatchingStaff->email);
    } else {
      $this->record($label . ':non-matching-staff', true, 'No non-matching DEPDev staff user found', 'warn');
    }
  }

  private function checkHttpEndpoints(): void
  {
    $baseUrl = rtrim($this->option('url') ?: config('app.url'), '/');

    if (!$baseUrl) {
      $this->record('http:base-url', false, 'APP_URL is empty');
      return;
    }

    $this->checkHttpGet('http:login', $baseUrl . '/login', 200);
    $this->checkHttpGet('http:api-missing-token', $baseUrl . '/api/v1/approved/indicator-submissions', 401);

    try {
      $response = Http::withToken('invalid-smoke-token')
        ->timeout(5)
        ->get($baseUrl . '/api/v1/approved/indicator-submissions');

      $this->record(
        'http:api-invalid-token',
        $response->status() === 401,
        'HTTP ' . $response->status() . ' ' . $response->body()
      );
    } catch (\Throwable $exception) {
      $this->record('http:api-invalid-token', false, $exception->getMessage());
    }

    $envToken = (string) config('services.approved_submissions.token');

    if ($envToken !== '') {
      try {
        $response = Http::withToken($envToken)
          ->timeout(5)
          ->get($baseUrl . '/api/v1/approved/indicator-submissions');

        $this->record(
          'http:api-env-token',
          in_array($response->status(), [200, 403], true),
          'HTTP ' . $response->status() . ' ' . $response->body(),
          $response->status() === 403 ? 'warn' : null
        );
      } catch (\Throwable $exception) {
        $this->record('http:api-env-token', false, $exception->getMessage());
      }
    } else {
      $this->record('http:api-env-token', true, 'APPROVED_SUBMISSIONS_API_TOKEN is not set', 'warn');
    }
  }

  private function checkHttpGet(string $label, string $url, int $expectedStatus): void
  {
    try {
      $response = Http::timeout(5)->get($url);
      $this->record(
        $label,
        $response->status() === $expectedStatus,
        'HTTP ' . $response->status() . ' expected ' . $expectedStatus
      );
    } catch (\Throwable $exception) {
      $this->record($label, false, $exception->getMessage());
    }
  }

  private function record(string $check, bool $passed, string $detail, string $status = null): void
  {
    $this->results[] = [
      'check' => $check,
      'status' => $status ?: ($passed ? 'pass' : 'fail'),
      'detail' => $detail,
    ];
  }

  private function formatCounts(array $counts): string
  {
    if (empty($counts)) {
      return 'No rows';
    }

    return collect($counts)
      ->map(fn($total, $status) => $status . '=' . $total)
      ->implode(', ');
  }

  private function renderTable(): void
  {
    $this->table(['Check', 'Status', 'Detail'], $this->results);
  }
}
