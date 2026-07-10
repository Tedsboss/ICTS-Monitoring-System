<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use RootInc\LaravelAzureMiddleware\Azure as Azure;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Exception\ClientException;

use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\NewUser;
use App\Models\Staff;

class AppAzure extends Azure
{


  protected function success(Request $request, $access_token, $refresh_token, $profile)
  {
    $graph = new Graph();
    $graph->setAccessToken($access_token);

    $graph_user = $graph->createRequest("GET", "/me")
      ->setReturnType(Model\User::class)
      ->execute();


    $gu_first_name = substr($graph_user->getGivenName(), -1) == '.' ? implode('-', array_map('ucwords', explode('-', strtolower(substr($graph_user->getGivenName(), 0, -3))))) : $graph_user->getGivenName();
    $gu_middle_name = substr($graph_user->getGivenName(), -1) == '.' ? implode('-', array_map('ucwords', explode('-', strtolower(substr($graph_user->getGivenName(), strlen($graph_user->getGivenName()) - 2, 1))))) : null;
    $gu_last_name = implode('-', array_map('ucwords', explode('-', strtolower($graph_user->getSurname()))));
    $gu_email = strtolower($graph_user->getUserPrincipalName());
    $gu_staff = $graph_user->getOfficeLocation();
    $gu_designation = $graph_user->getJobTitle();
    $gu_phone = count($graph_user->getbusinessPhones()) > 0 ? $graph_user->getbusinessPhones()[0] : null;
    try {
      $gu_photo = $graph->createRequest('GET', '/me/photo/$value')
        ->addHeaders(array("Content-Type" => "image/jpeg"))
        ->execute();

      $gu_photo = $gu_photo->getRawBody();
    } catch (ClientException $e) {
      $gu_photo = null;
    }

    $tmp_staff = Staff::where('name', $gu_staff)->first();
    if ($tmp_staff != null) {
      $gu_staff_id = $tmp_staff->id;
    } else {
      $gu_staff_id = null;
    }

    $unique_id = uniqid();
    $tmp_user = User::where('email', $gu_email)->first();

    if ($tmp_user == null) {
      // $tmp_user = NewUser::where('email', $gu_email)
      // ->where(function ($q) {
      //   $q->where('status', 0)
      //     ->orWhere('status', 1)
      //     ->orWhereNull('status');
      // })->first();

      // if ($tmp_user == null) {
      //   $newuser = new NewUser;
      //   $newuser->firstname = $gu_first_name;
      //   $newuser->middlename = $gu_middle_name;
      //   $newuser->lastname = $gu_last_name;
      //   $newuser->staff_id = $gu_staff_id;
      //   $newuser->email = $gu_email;
      //   $newuser->designation = $gu_designation;
      //   $newuser->phone = $gu_phone;
      //   $newuser->avatar = $unique_id . '.jpg';
      //   $newuser->status = 0;
      //   $newuser->ip = request()->getClientIp(true);
      //   $newuser->save();

      //   Storage::put('public/avatars/' . $unique_id . '.jpg', $gu_photo);
      // }
      // return redirect()->route('pending.notify');


      return redirect()->route('login.unknownuser');
    } else {
      session(['provider_name' => 'Microsoft']);

      $user = $tmp_user;
      if ($gu_photo != null && ($tmp_user->avatar == '' || $tmp_user->avatar == null)) {
        Storage::put('public/avatars/' . $unique_id . '.jpg', $gu_photo);
        $user->avatar = $unique_id . '.jpg';
        $user->save();
      }
      Auth::login($user, true);

      session(['user_settings' => [
        'class_theme' => auth()->user()->enabledark == 'Y' ? 'dark' : '',
        'hide_dashboard_charts' => auth()->user()->autohidecharts ?? 'N',
      ]]);

      return parent::success($request, $access_token, $refresh_token, $profile);
    }
  }

  protected function redirect(Request $request)
  {
    if (Auth::user() !== null) {
      return $this->azure($request);
    } else {
      return parent::redirect($request);
    }
  }
}
