<?php

namespace App\Http\Controllers;

use App\Http\Requests\InquiryReplyRequest;
use App\Http\Requests\InquiryRequest;
use App\Models\Inquiry;
use App\Models\Parameter;
use App\Models\Question;
use App\Models\RestrictedIp;
use App\Notifications\ReplyInquiryNotification;
use App\Traits\GenerateLogs;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Mews\Purifier\Facades\Purifier;
use Yajra\DataTables\DataTables;

class InquiryController extends Controller
{
  use GenerateLogs;
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    $this->authorize('viewAny', Inquiry::class);
    return view('inquiries.index');
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    $icc_telephone = Parameter::where('id', 27)->first()->value;
    $icc_email = Parameter::where('id', 28)->first()->value;
    $icc_open = DateTime::createFromFormat('H:i', Parameter::where('id', 23)->first()->value)->format('g:i A');
    $icc_close = DateTime::createFromFormat('H:i', Parameter::where('id', 24)->first()->value)->format('g:i A');
    $icc_address = Parameter::where('id', 29)->first()->value;

    return view('inquiries.create', compact('icc_telephone', 'icc_email', 'icc_open', 'icc_close', 'icc_address'));
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(InquiryRequest $request)
  {
    $inquiry = new Inquiry();
    if (auth()->check()) {
      $user = auth()->user();
      $inquiry->firstname = $user->firstname;
      $inquiry->lastname = $user->lastname;
      $inquiry->staff = $user->staff->name . ' (' . $user->division->abbreviation . ')';
      $inquiry->email = $user->email;
      $inquiry->user_id = $user->id;
    } else {
      $inquiry->firstname = $request->firstname;
      $inquiry->lastname = $request->lastname;
      $inquiry->staff = $request->staff;
      $inquiry->email = $request->email;
    }

    /**
     * The convertBase64ImagesToURLs function will create a copy of the image in the application's public folder. 
     * This is necessary when sending an HTML message via email, especially to email clients like Gmail that do not support base64-encoded images.
     * 
     * PROS:
     * 1. Ensures that images will display properly in email clients like Gmail, Outlook
     * 2. Reduced Email Size
     * 3. Cleaner and more readable email HTML
     * 4. Email clients or browsers may cache the images, reducing the load time
     * 
     * CONS:
     * 1. Public folder may accumulate a large number of images, potentially consuming server storage space
     * 2. If the image is deleted or the URL changes, the email will display a broken image.
     * 3. No Offline Viewing
     */
    // $inquiry->html_message = Purifier::clean(convertBase64ImagesToURLs($request->html_message), 'allow_quilljs_element');

    $inquiry->ip = $request->ip();
    $inquiry->html_message = Purifier::clean($request->html_message, 'allow_quilljs_element');
    $inquiry->save();

    $route_link = 'guest.contactus.create';
    $add_remarks = '(as guest)';
    if (auth()->check()) {
      $route_link = 'auth.contactus.create';
      $add_remarks = '';
    }
    $this->addSystemLogs("Created user inquiry: " . $inquiry->email . $add_remarks, auth()->id(), auth()->user()->email, request()->getClientIp(true), 'inquiries', $inquiry->id);
    return redirect()->route($route_link)->with('succes', 'Your message has been sent!. Someone from our support team will contact you shortly.');
  }

  /**
   * Display the specified resource.
   */
  public function show(Inquiry $inquiry)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(Inquiry $inquiry)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, Inquiry $inquiry)
  {
    //
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Inquiry $inquiry)
  {
    //
  }

  public function getinquiries(Request $request)
  {
    $this->authorize('viewAny', Inquiry::class);
    DB::statement("SET SQL_MODE=''");
    $inquiries = Inquiry::select([
      'inquiries.id',
      'inquiries.firstname',
      'inquiries.lastname',
      'inquiries.email',
      'inquiries.staff',
      'inquiries.status',
      'inquiries.user_id',
      'inquiries.html_message',
      'inquiries.html_reply',
      'inquiries.updated_by',
      'inquiries.created_at',
      DB::raw("CONCAT(inquiries.firstname, ' ', inquiries.lastname) as fullname"),
    ])
      // ->whereDoesntHave('user')
      ->with([
        'editor:id,firstname,lastname,middlename',
      ])
      ->groupBy('inquiries.id');

    return DataTables::of($inquiries)
      ->editColumn('created_at', function (Inquiry $inquiry) {
        return $inquiry->created_at->format('Y-m-d H:i:s');
      })
      ->addColumn('actions', function (Inquiry $inquiry) {
        $fa_icons = 'fa-eye text-secondary';
        if ($inquiry->status == 1) {
          $fa_icons = 'fa-pencil text-info';
        }
        $can_block = 0;
        if (auth()->user()->can('block', [Inquiry::class, $inquiry])) {
          $can_block = 1;
        }
        return '<div class="btn-group" role="group"><button data-bs-toggle="tooltip" data-bs-original-title="Show" class="border-0 bg-transparent" onclick=\'showInquiry(' . json_encode($inquiry, JSON_HEX_APOS) . ', "' . config('items.inquiry_statuses')[$inquiry->status] . '", ' . $can_block . ', this)\'><i class="fa ' . $fa_icons . '"></i></button></div>';
      })
      ->filterColumn('fullname', function ($query, $keyword) {
        $query->whereRaw("CONCAT(inquiries.firstname, ' ', inquiries.lastname) like ?", ["%{$keyword}%"]);
      })
      ->filterColumn('status', function ($query, $keyword) {
        $caseStatement = "CASE status";
        foreach (config('items.inquiry_statuses') as $key => $value) {
          if ($key !== 0) {
            $caseStatement .= " WHEN $key THEN '$value'";
          }
        }
        $caseStatement .= " ELSE NULL END";
        $query->whereRaw($caseStatement . ' LIKE ? ', ["%{$keyword}%"]);
      })
      ->editColumn('status', function (Inquiry $inquiry) {
        return ['text' => config('items.inquiry_statuses')[$inquiry->status], 'html' => '<span class="badge bg-' . config('items.inquiry_statuses_color')[$inquiry->status] . '">' .  config('items.inquiry_statuses')[$inquiry->status] . '</span>'];
      })
      ->rawColumns(['actions', 'status.html'])
      ->toJson();
  }

  public function submit(InquiryReplyRequest $request, Inquiry $inquiry)
  {
    if ($request->status == 3) {
      $this->authorize('reply', [Inquiry::class, $inquiry]);
    } elseif ($request->status == 4) {
      $this->authorize('block', [Inquiry::class, $inquiry]);
    }

    DB::transaction(function () use ($request, &$inquiry) {
      DB::table('inquiries')->where('id', $inquiry->id)->lockForUpdate();

      $reply_message = Purifier::clean($request->html_reply, 'allow_quilljs_element');

      $inquiry->updated_by = auth()->id();
      $inquiry->status = $request->status;
      $inquiry->html_reply = $reply_message;
      if ($request->status == 3) {
        // $inquiry->notify(new ReplyInquiryNotification($inquiry, $reply_message));
        Notification::route('mail', [config('mail.email_prefix') . $request->email])->notify(new ReplyInquiryNotification($inquiry, $reply_message));
        $this->addSystemLogs("Replied user inquiry: " . $inquiry->email, auth()->id(), auth()->user()->email, request()->getClientIp(true), 'inquiries', $inquiry->id);
      } elseif ($request->status == 4) {
        $restricted_ip = new RestrictedIp();
        $restricted_ip->ipaddress = $inquiry->ip;
        $restricted_ip->route = 'userinquiry';
        $restricted_ip->status = 1;
        $restricted_ip->updated_by = null;
        $restricted_ip->content = "{'email': '" . $inquiry->email . "', 'reason': '" . $request->reason . "', 'requested_by': '" . auth()->id() . "'}";
        $restricted_ip->save();
        $this->addSystemLogs("Added blocked IP: " . $restricted_ip->ipaddress . "(via inquiry module)", auth()->id(), auth()->user()->email, request()->getClientIp(true), 'restricted_ips', $restricted_ip->id);
      }
      $inquiry->save();
    });
    $action = config('items.inquiry_statuses')[$inquiry->status];
    $this->addSystemLogs(ucfirst($action) . " inquiry from " . $inquiry->agency . ": " . $inquiry->email, auth()->id(), auth()->user()->email, request()->getClientIp(true), 'new_users', $inquiry->id);

    return redirect()->route('inquiries.index')->with('succes', 'Inquiry succesfully ' . strtolower($action));
  }

  public function updatestatus(Request $request)
  {
    $inquiry = Inquiry::find($request->inquiry_id);
    if ($inquiry == null) {
      return response()->json(['error' => 'Invalid Inquiry'], Response::HTTP_UNPROCESSABLE_ENTITY);
    } else {
      if (!auth()->user()->canAny(['reply', 'block'], $inquiry)) {
        return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNPROCESSABLE_ENTITY);
      } else {
        DB::transaction(function () use ($inquiry) {
          DB::table('inquiries')->where('id', $inquiry->id)->lockForUpdate();
          $inquiry->status = 2;
          $inquiry->updated_by = auth()->id();
          $inquiry->save();
          $this->addSystemLogs("Read user inquiry: " . $inquiry->email, auth()->id(), auth()->user()->email, request()->getClientIp(true), 'inquiries', $inquiry->id);
        });
        return response()->json(['data' => 'success'], Response::HTTP_OK);
      }
    }
  }
}
