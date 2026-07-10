<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPassword;
use App\Http\Controllers\Auth\ChangePassword;
use App\Http\Controllers\AgencyController;
use App\Http\Controllers\ApiClientController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\FormSubmissionController;
use App\Http\Controllers\SubmissionNotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RestrictedIpController;
use App\Http\Controllers\SystemLogController;
use App\Http\Middleware\AppAzure;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\ParameterController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\UpliftFormBuilderController;
use App\Http\Controllers\UpliftSubmissionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
  return redirect('/login');
});

Route::get('/login', [LoginController::class, 'show'])
  ->middleware(['guest', 'check.restricted.ips'])
  ->name('login');

Route::get('/login/unknownuser', [LoginController::class, 'unknownuser'])
  ->middleware('guest')
  ->name('login.unknownuser');

Route::get('/blocked', [RestrictedIpController::class, 'blocked'])
  ->name('blocked');

Route::group(['middleware' => 'check.restricted.ips'], function () {
  Route::post('/login', [LoginController::class, 'login'])
    ->middleware('login.ip.throttle')
    ->name('login.perform');

  Route::group(['middleware' => 'guest'], function () {
    Route::get('/login/azure', [AppAzure::class, 'azure'])
      ->name('azure.login');

    Route::get('/login/azurecallback', [AppAzure::class, 'azurecallback'])
      ->name('azure.callback');

    Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])
      ->name('auth.google.redirect');

    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])
      ->name('auth.google.callback');

    Route::get('/reset-password', [ResetPassword::class, 'show'])
      ->name('reset');

    Route::post('/reset-password', [ResetPassword::class, 'send'])
      ->middleware('reset.ip.throttle')
      ->name('reset.perform');

    Route::get('/change-password', [ChangePassword::class, 'show'])
      ->middleware('guest')
      ->name('change.password');

    Route::post('/change-password', [ChangePassword::class, 'update'])
      ->middleware('guest')
      ->name('change.perform');

    Route::get('/guest/contact-us', [InquiryController::class, 'create'])
      ->name('guest.contactus.create');

    Route::post('/guest/contact-us', [InquiryController::class, 'store'])
      ->middleware('message.ip.throttle')
      ->name('guest.contactus.store');
  });

  Route::group(['middleware' => 'auth'], function () {
    Route::get('/2fa/challenge', [TwoFactorController::class, 'show'])
      ->name('2fa.challenge.show');

    Route::post('/2fa/verify', [TwoFactorController::class, 'verify'])
      ->name('2fa.verify');

    Route::post('/2fa/resend', [TwoFactorController::class, 'resend'])
      ->name('2fa.resend');

    Route::group(['middleware' => 'two.factor'], function () {
      Route::get('/user-profile', [ProfileController::class, 'show'])
        ->name('user-profile');

      Route::post('/user-profile', [ProfileController::class, 'update'])
        ->name('user-profile.perform');

      Route::get('/user-profile/{trusteddevice}', [ProfileController::class, 'revoke'])
        ->name('user-profile.revoke');

      Route::get('/auth/contact-us', [InquiryController::class, 'create'])
        ->name('auth.contactus.create');

      Route::post('/auth/contact-us', [InquiryController::class, 'store'])
        ->middleware('message.ip.throttle')
        ->name('auth.contactus.store');

      Route::group(['middleware' => 'first.login'], function () {
        Route::get('home', [HomeController::class, 'index'])
          ->name('home');

        /*
        |--------------------------------------------------------------------------
        | Submissions
        |--------------------------------------------------------------------------
        */

        Route::resource('submissions', FormSubmissionController::class, [
          'except' => ['destroy'],
          'parameters' => ['submissions' => 'form_submission'],
        ]);

        Route::post('submissions/{form_submission}/submit', [FormSubmissionController::class, 'submit'])
          ->name('submissions.submit');

        Route::post('submissions/{form_submission}/approve', [FormSubmissionController::class, 'approve'])
          ->name('submissions.approve');

        Route::post('submissions/{form_submission}/return', [FormSubmissionController::class, 'return'])
          ->name('submissions.return');

        Route::post('submissions/{form_submission}/reject', [FormSubmissionController::class, 'reject'])
          ->name('submissions.reject');

        Route::resource('uplift-submissions', UpliftSubmissionController::class, [
          'except' => ['destroy'],
          'parameters' => ['uplift-submissions' => 'uplift_submission'],
        ]);

        Route::post('uplift-submissions/{uplift_submission}/submit', [UpliftSubmissionController::class, 'submit'])
          ->name('uplift-submissions.submit');

        Route::post('uplift-submissions/{uplift_submission}/approve', [UpliftSubmissionController::class, 'approve'])
          ->name('uplift-submissions.approve');

        Route::post('uplift-submissions/{uplift_submission}/return', [UpliftSubmissionController::class, 'return'])
          ->name('uplift-submissions.return');

        Route::post('uplift-submissions/{uplift_submission}/reject', [UpliftSubmissionController::class, 'reject'])
          ->name('uplift-submissions.reject');

        Route::get('notifications/submissions', [SubmissionNotificationController::class, 'index'])
          ->name('notifications.submissions.index');

        Route::post('notifications/submissions/{notification}/read', [SubmissionNotificationController::class, 'read'])
          ->name('notifications.submissions.read');

        /*
        |--------------------------------------------------------------------------
        | Administrator Basic Resources
        |--------------------------------------------------------------------------
        */

        Route::resource('administrator/parameters', ParameterController::class, [
          'except' => ['show', 'create', 'edit', 'destroy'],
        ]);

        Route::resource('administrator/agencies', AgencyController::class, [
          'except' => ['show', 'create', 'store', 'edit', 'destroy'],
        ]);
/*
|--------------------------------------------------------------------------
| Administrator Forms
|--------------------------------------------------------------------------
*/

Route::get('administrator/forms/agencies/search', [FormController::class, 'searchAgencies'])
  ->name('forms.agencies.search');

Route::resource('administrator/forms', FormController::class, [
  'except' => ['show', 'create', 'destroy'],
]);

Route::get('administrator/forms/{form}/preview', [FormController::class, 'preview'])
  ->name('forms.preview');

Route::post('administrator/forms/{form}/duplicate', [FormController::class, 'duplicate'])
  ->name('forms.duplicate');

Route::post('administrator/forms/{form}/fields', [FormController::class, 'storeField'])
  ->name('forms.fields.store');

Route::put('administrator/forms/{form}/fields', [FormController::class, 'updateFields'])
  ->name('forms.fields.update-all');

Route::put('administrator/forms/{form}/fields/{form_field}', [FormController::class, 'updateField'])
  ->name('forms.fields.update');

Route::delete('administrator/forms/{form}/fields/{form_field}', [FormController::class, 'destroyField'])
  ->name('forms.fields.destroy');

        /*
        |--------------------------------------------------------------------------
        | UPLIFT Builder
        |--------------------------------------------------------------------------
        */

        Route::get('administrator/uplift-builder', [UpliftFormBuilderController::class, 'index'])
          ->name('uplift-builder.index');

        Route::post('administrator/uplift-builder/pillars', [UpliftFormBuilderController::class, 'storePillar'])
          ->name('uplift-builder.pillars.store');

        Route::put('administrator/uplift-builder/pillars/{uplift_pillar}', [UpliftFormBuilderController::class, 'updatePillar'])
          ->name('uplift-builder.pillars.update');

        Route::post('administrator/uplift-builder/measures', [UpliftFormBuilderController::class, 'storeMeasure'])
          ->name('uplift-builder.measures.store');

        Route::get('administrator/uplift-builder/measures/{uplift_measure}', [UpliftFormBuilderController::class, 'edit'])
          ->name('uplift-builder.edit');

        Route::get('administrator/uplift-builder/measures/{uplift_measure}/preview', [UpliftFormBuilderController::class, 'preview'])
          ->name('uplift-builder.preview');

        Route::put('administrator/uplift-builder/measures/{uplift_measure}', [UpliftFormBuilderController::class, 'updateMeasure'])
          ->name('uplift-builder.measures.update');

        Route::post('administrator/uplift-builder/measures/{uplift_measure}/duplicate', [UpliftFormBuilderController::class, 'duplicateMeasure'])
          ->name('uplift-builder.measures.duplicate');

        Route::post('administrator/uplift-builder/measures/{uplift_measure}/supporting-agencies', [UpliftFormBuilderController::class, 'storeSupportingAgency'])
          ->name('uplift-builder.supporting-agencies.store');

        Route::delete('administrator/uplift-builder/measures/{uplift_measure}/supporting-agencies/{agency}', [UpliftFormBuilderController::class, 'destroySupportingAgency'])
          ->name('uplift-builder.supporting-agencies.destroy');

        Route::post('administrator/uplift-builder/measures/{uplift_measure}/fields', [UpliftFormBuilderController::class, 'storeField'])
          ->name('uplift-builder.fields.store');

        Route::put('administrator/uplift-builder/measures/{uplift_measure}/fields-order', [UpliftFormBuilderController::class, 'updateFieldsOrder'])
          ->name('uplift-builder.fields.order');

        Route::put('administrator/uplift-builder/measures/{uplift_measure}/fields/{uplift_pillar_field}', [UpliftFormBuilderController::class, 'updateField'])
          ->name('uplift-builder.fields.update');

        Route::delete('administrator/uplift-builder/measures/{uplift_measure}/fields/{uplift_pillar_field}', [UpliftFormBuilderController::class, 'destroyField'])
          ->name('uplift-builder.fields.destroy');

        Route::post('administrator/uplift-builder/measures/{uplift_measure}/fields/{uplift_pillar_field}/indicators', [UpliftFormBuilderController::class, 'storeIndicator'])
          ->name('uplift-builder.indicators.store');

        Route::put('administrator/uplift-builder/measures/{uplift_measure}/fields/{uplift_pillar_field}/indicators/{uplift_indicator}', [UpliftFormBuilderController::class, 'updateIndicator'])
          ->name('uplift-builder.indicators.update');

        Route::delete('administrator/uplift-builder/measures/{uplift_measure}/fields/{uplift_pillar_field}/indicators/{uplift_indicator}', [UpliftFormBuilderController::class, 'destroyIndicator'])
          ->name('uplift-builder.indicators.destroy');

        /*
        |--------------------------------------------------------------------------
        | Administrator Resources
        |--------------------------------------------------------------------------
        */

        Route::resource('administrator/roles', RoleController::class, [
          'except' => ['show', 'create'],
        ]);

        Route::resource('administrator/users', UserController::class, [
          'except' => ['show', 'create', 'edit'],
        ]);

        Route::resource('administrator/systemlogs', SystemLogController::class, [
          'except' => ['create', 'show', 'store', 'edit', 'update', 'destroy'],
        ]);

        Route::resource('administrator/restrictedips', RestrictedIpController::class, [
          'except' => ['show', 'create', 'edit', 'update'],
        ]);

        Route::get('administrator/api-clients', [ApiClientController::class, 'index'])
          ->name('api-clients.index');

        Route::post('administrator/api-clients', [ApiClientController::class, 'store'])
          ->name('api-clients.store');

        Route::post('administrator/api-clients/{api_client}/revoke', [ApiClientController::class, 'revoke'])
          ->name('api-clients.revoke');

        Route::resource('administrator/holidays', HolidayController::class, [
          'except' => ['show', 'create'],
        ]);

        Route::resource('administrator/staffs', StaffController::class, [
          'except' => ['show', 'create', 'store'],
        ]);

        Route::resource('administrator/divisions', DivisionController::class, [
          'except' => ['show', 'create', 'store'],
        ]);

        Route::get('administrator/restrictedips/{restrictedip}/updatestatus', [RestrictedIpController::class, 'updatestatus'])
          ->name('restrictedips.updatestatus');

        Route::get('administrator/agencies/{agency}/switchstatus', [AgencyController::class, 'switchstatus'])
          ->name('agencies.switchstatus');

        Route::get('administrator/divisions/{division}/switchstatus', [DivisionController::class, 'switchstatus'])
          ->name('divisions.switchstatus');

        Route::get('administrator/staffs/{staff}/switchstatus', [StaffController::class, 'switchstatus'])
          ->name('staffs.switchstatus');

        /*
        |--------------------------------------------------------------------------
        | DataTable / AJAX Routes
        |--------------------------------------------------------------------------
        */

        Route::post('administrator/roles/getroles', [RoleController::class, 'getroles'])
          ->name('getroles');

        Route::post('administrator/users/getusers', [UserController::class, 'getusers'])
          ->name('getusers');

        Route::post('administrator/systemlogs/getsystemlogs', [SystemLogController::class, 'getsystemlogs'])
          ->name('getsystemlogs');

        Route::post('administrator/systemlogs/gethistory', [SystemLogController::class, 'gethistory'])
          ->name('gethistory');

        Route::post('administrator/systemlogs/{tablename}/{rowid}', [SystemLogController::class, 'getmodellogs'])
          ->name('getmodellogs');

        Route::post('administrator/rectrictedips/getrectrictedips', [RestrictedIpController::class, 'getrestrictedips'])
          ->name('getrestrictedips');

        Route::post('administrator/parameters/getparameters', [ParameterController::class, 'getparameters'])
          ->name('getparameters');

        Route::post('administrator/agencies/getagencies', [AgencyController::class, 'getagencies'])
          ->name('getagencies');

        Route::post('administrator/forms/getforms', [FormController::class, 'getforms'])
          ->name('getforms');

        Route::post('administrator/inquiries/getinquiries', [InquiryController::class, 'getinquiries'])
          ->name('getinquiries');

        Route::post('administrator/holidays/getholidays', [HolidayController::class, 'getholidays'])
          ->name('getholidays');

        Route::post('administrator/staffs/getstaffs', [StaffController::class, 'getstaffs'])
          ->name('getstaffs');

        Route::post('administrator/divisions/getdivisions', [DivisionController::class, 'getdivisions'])
          ->name('getdivisions');

        /*
        |--------------------------------------------------------------------------
        | Inquiries
        |--------------------------------------------------------------------------
        */

        Route::get('administrator/inquiries', [InquiryController::class, 'index'])
          ->name('inquiries.index');

        Route::put('administrator/inquiries/submit/{inquiry}', [InquiryController::class, 'submit'])
          ->name('inquiries.submit');

        Route::post('administrator/inquiries/updatestatus', [InquiryController::class, 'updatestatus'])
          ->name('inquiries.updatestatus');
      });
    });

    Route::post('logout', [LoginController::class, 'logout'])
      ->name('logout');
  });
});
