<?php

namespace App\Http\Controllers;

use App\Models\FinancialPlan;
use App\Models\Parameter;
use App\Models\WorkPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class PageController extends Controller
{
  public function home()
  {
    $homeNow = now();
    $homeUser = auth()->user();
    $birthday = $homeUser?->birthday;
    $birthdate = $birthday ? \Carbon\Carbon::parse($birthday) : null;
    $firstName = trim((string) ($homeUser?->firstname ?? '')) ?: 'there';
    $greeting = $homeNow->hour < 12
      ? 'Good morning'
      : ($homeNow->hour < 18 ? 'Good afternoon' : 'Good evening');
    $canViewFinancialPlan = $homeUser && $homeUser->can('viewAny', FinancialPlan::class);
    $canViewWorkPlan = $homeUser && $homeUser->can('viewAny', WorkPlan::class);
    $modules = [];

    if ($canViewFinancialPlan && Route::has('financial-plans.plans')) {
      $modules[] = [
        'title' => 'Financial plans',
        'description' => 'Build budgets, schedule targets, and review allocation availability.',
        'route' => 'financial-plans.plans',
        'icon' => 'fa-money',
        'category' => 'Budget',
        'tone' => 'blue',
        'index' => '01',
      ];
    }

    if ($canViewWorkPlan && Route::has('work-plans.plans')) {
      $modules[] = [
        'title' => 'Work plans',
        'description' => 'Turn planned activities into measurable outputs and delivery months.',
        'route' => 'work-plans.plans',
        'icon' => 'fa-tasks',
        'category' => 'Delivery',
        'tone' => 'emerald',
        'index' => '02',
      ];
    }

    if ($canViewFinancialPlan && $canViewWorkPlan && Route::has('mdp.index')) {
      $modules[] = [
        'title' => 'Monthly disbursement',
        'description' => 'Review monthly cash requirements and export your MDP schedule.',
        'route' => 'mdp.index',
        'icon' => 'fa-calendar',
        'category' => 'Cash flow',
        'tone' => 'violet',
        'index' => '03',
      ];
    }

    if (Route::has('user-profile')) {
      $modules[] = [
        'title' => 'My profile',
        'description' => 'Manage account details, security preferences, and trusted devices.',
        'route' => 'user-profile',
        'icon' => 'fa-user',
        'category' => 'Account',
        'tone' => 'amber',
        'index' => '04',
      ];
    }

    return view('home', [
      'homeNow' => $homeNow,
      'isDark' => session('user_settings.class_theme', '') === 'dark',
      'homeannouncement' => Parameter::find(2),
      'birthdate' => $birthdate,
      'firstName' => $firstName,
      'greeting' => $greeting,
      'modules' => $modules,
      'primaryModule' => $modules[0] ?? null,
      'steps' => [
        ['label' => 'Set foundations', 'detail' => 'Fiscal year and allocation level'],
        ['label' => 'Prepare resources', 'detail' => 'Expense types and allocations'],
        ['label' => 'Build the plan', 'detail' => 'Financial and work plans'],
        ['label' => 'Review delivery', 'detail' => 'Monthly disbursement program'],
      ],
    ]);
  }

  public function dashboards(string $page)
  {
    if (view()->exists("dashboards.{$page}")) {
      return view("dashboards.{$page}");
    }

    return abort(404);
  }

  public function profiles(string $page)
  {
    if (view()->exists("pages.profile.{$page}")) {
      return view("pages.profile.{$page}");
    }

    return abort(404);
  }

  public function users(string $page)
  {
    if (view()->exists("pages.users.{$page}")) {
      return view("pages.users.{$page}");
    }

    return abort(404);
  }

  public function accounts(string $page)
  {
    if (view()->exists("pages.account.{$page}")) {
      return view("pages.account.{$page}");
    }

    return abort(404);
  }

  public function projects(string $page)
  {
    if (view()->exists("pages.projects.{$page}")) {
      return view("pages.projects.{$page}");
    }

    return abort(404);
  }


  public function pages(string $page)
  {
    if (view()->exists("pages.{$page}")) {
      return view("pages.{$page}");
    }

    return abort(404);
  }

  public function applications(string $page)
  {
    if (view()->exists("applications.{$page}")) {
      return view("applications.{$page}");
    }

    return abort(404);
  }

  public function ecommerce(string $page)
  {
    if (view()->exists("ecommerce.{$page}")) {
      return view("ecommerce.{$page}");
    }

    return abort(404);
  }

  public function products(string $page)
  {
    if (view()->exists("ecommerce.products.{$page}")) {
      return view("ecommerce.products.{$page}");
    }

    return abort(404);
  }

  public function orders(string $page)
  {
    if (view()->exists("ecommerce.orders.{$page}")) {
      return view("ecommerce.orders.{$page}");
    }

    return abort(404);
  }

  public function signins(string $page)
  {
    if (view()->exists("authentication.sign-in.{$page}")) {
      return view("authentication.sign-in.{$page}");
    }

    return abort(404);
  }
  public function signups(string $page)
  {
    if (view()->exists("authentication.sign-up.{$page}")) {
      return view("authentication.sign-up.{$page}");
    }

    return abort(404);
  }

  public function resets(string $page)
  {
    if (view()->exists("authentication.reset-password.{$page}")) {
      return view("authentication.reset-password.{$page}");
    }

    return abort(404);
  }

  public function locks(string $page)
  {
    if (view()->exists("authentication.lock.{$page}")) {
      return view("authentication.lock.{$page}");
    }

    return abort(404);
  }

  public function verifications(string $page)
  {
    if (view()->exists("authentication.verification.{$page}")) {
      return view("authentication.verification.{$page}");
    }

    return abort(404);
  }

  public function errors(string $page)
  {
    if (view()->exists("authentication.error.{$page}")) {
      return view("authentication.error.{$page}");
    }

    return abort(404);
  }
}
