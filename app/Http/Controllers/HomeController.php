<?php

namespace App\Http\Controllers;

use App\Helpers\ComplexSQLHelper;
use App\Models\Parameter;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
  public function index()
  {
    $homeannouncement = Parameter::findorFail(2);
    return view("home", compact('homeannouncement'));
  }
}
