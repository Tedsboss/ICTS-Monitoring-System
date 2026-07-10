<?php

namespace App\Traits;

use App\Models\SystemLog;
use App\Models\Allotment;
use App\Models\Fund;
use App\Models\Center;
use App\Models\Uacs;
use App\Models\AllotmentExpense;
use App\Models\Office;
use App\Models\Acct_op_workflow_log;
use DateTime;
use DatePeriod;
use DateInterval;

trait GenerateLogs
{
  public function addSystemLogs($myActivity, $myUserID, $myUserName, $myIP, $myTable = null, $myID = null, $myCreatedAt = null)
  {
    $systemlog = new SystemLog;
    $systemlog->user_id = $myUserID;
    $systemlog->name = $myUserName;
    $systemlog->reference_table = $myTable;
    $systemlog->reference_id = $myID;
    $systemlog->activity = $myActivity;
    $systemlog->ipaddress = $myIP;
    if ($myCreatedAt != null) {
      $systemlog->created_at = $myCreatedAt;
    }
    $systemlog->save();
    return $systemlog->id;
  }
}
