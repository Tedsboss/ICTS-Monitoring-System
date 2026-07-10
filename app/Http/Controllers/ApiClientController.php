<?php

namespace App\Http\Controllers;

use App\Models\ApiClient;
use Illuminate\Http\Request;

class ApiClientController extends Controller
{
  private const VIEW_PERMISSION = 129;
  private const CREATE_PERMISSION = 130;
  private const REVOKE_PERMISSION = 131;

  public function index()
  {
    $this->authorizeAccess(self::VIEW_PERMISSION);

    $clients = ApiClient::latest()->get();

    return view('api-clients.index', compact('clients'));
  }

  public function store(Request $request)
  {
    $this->authorizeAccess(self::CREATE_PERMISSION);

    $data = $request->validate([
      'name' => ['required', 'string', 'max:255'],
      'allowed_ips' => ['required', 'string', 'max:5000'],
    ]);

    $token = ApiClient::issueToken();
    $allowedIps = collect(preg_split('/\r\n|\r|\n|,/', $data['allowed_ips']))
      ->map(fn($ip) => trim($ip))
      ->filter()
      ->unique()
      ->values()
      ->all();

    ApiClient::create([
      'name' => $data['name'],
      'token_hash' => ApiClient::hashToken($token),
      'allowed_ips' => $allowedIps,
      'created_by' => auth()->id(),
    ]);

    return redirect()->route('api-clients.index')->with('api_token', $token)->with('succes', 'API client created');
  }

  public function revoke(ApiClient $api_client)
  {
    $this->authorizeAccess(self::REVOKE_PERMISSION);

    $api_client->update(['revoked_at' => now()]);

    return redirect()->route('api-clients.index')->with('succes', 'API client revoked');
  }

  public static function canView(?\App\Models\User $user): bool
  {
    return $user != null && self::hasAccess($user, self::VIEW_PERMISSION);
  }

  private function authorizeAccess(int $permissionId): void
  {
    abort_unless(auth()->check() && self::hasAccess(auth()->user(), $permissionId), 403);
  }

  private static function hasAccess(\App\Models\User $user, int $permissionId): bool
  {
    $allowedEmails = array_filter(array_map('trim', explode(',', (string) config('app.api_client_admin_emails', env('API_CLIENT_ADMIN_EMAILS', '')))));

    return $user->isSuperAdmin()
      || in_array($user->email, $allowedEmails, true)
      || $user->role->permissions->where('id', $permissionId)->count() > 0;
  }
}
