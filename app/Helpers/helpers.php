<?php

use App\Models\Comment;
use App\Models\Holiday;
use App\Models\SystemLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

if (!function_exists('addToColumn')) {
  function addToColumn($col, $add)
  {
    $colNumber = 0;
    for ($i = 0; $i < strlen($col); $i++) {
      $colNumber = $colNumber * 26 + (ord($col[$i]) - ord('A') + 1);
    }
    $colNumber += $add;
    $newCol = '';
    while ($colNumber > 0) {
      $colNumber--;
      $newCol = chr($colNumber % 26 + ord('A')) . $newCol;
      $colNumber = floor($colNumber / 26);
    }
    return $newCol;
  }
}

if (!function_exists('minusToColumn')) {
  function minusToColumn($col, $subtract)
  {
    $colNumber = 0;
    for ($i = 0; $i < strlen($col); $i++) {
      $colNumber = $colNumber * 26 + (ord($col[$i]) - ord('A') + 1);
    }
    $colNumber -= $subtract;
    if ($colNumber <= 0) {
      throw new InvalidArgumentException('Resulting column is out of bounds.');
    }
    $newCol = '';
    while ($colNumber > 0) {
      $colNumber--;
      $newCol = chr($colNumber % 26 + ord('A')) . $newCol;
      $colNumber = floor($colNumber / 26);
    }
    return $newCol;
  }
}

if (!function_exists('convertBase64ImagesToURLs')) {
  function convertBase64ImagesToURLs($htmlContent)
  {
    preg_match_all('/<img[^>]*src="data:image\/(.*?);base64,(.*?)"[^>]*>/i', $htmlContent, $matches, PREG_SET_ORDER);

    foreach ($matches as $match) {
      $imageData = base64_decode($match[2]);
      $imageExtension = $match[1];
      $imageName = uniqid() . '.' . $imageExtension;

      Storage::disk('public')->put('uploads/' . $imageName, $imageData);

      $imageUrl = asset('storage/uploads/' . $imageName);
      $htmlContent = str_replace($match[0], '<img src="' . $imageUrl . '">', $htmlContent);
    }
    return $htmlContent;
  }
}

if (!function_exists('addOrdinalSuffix')) {
  function addOrdinalSuffix($number)
  {
    if (!in_array(($number % 100), [11, 12, 13])) {
      switch ($number % 10) {
        case 1:
          return $number . 'st';
        case 2:
          return $number . 'nd';
        case 3:
          return $number . 'rd';
      }
    }
    return $number . 'th';
  }
}

if (!function_exists('safeString')) {
  function safeString($string)
  {
    if ($string == null) {
      return '';
    }
    return str_replace(['"', "'"], ['\x22', '\x27'], $string);
  }
}

if (!function_exists('formatNumberOfHours')) {
  function formatNumberOfHours($myHours, $myType = null)
  {
    $hours = floor($myHours);
    $minutes = floor(($myHours - $hours) * 60);
    $seconds = floor((($myHours - $hours) * 3600) - ($minutes * 60));

    if ($myType == 2) {
      $tmphr = 'hrs';
      if ($hours < 2) {
        $tmphr = 'hr';
      }
      $tmpmin = 'mins';
      if ($minutes < 2) {
        $tmpmin = 'min';
      }
      $tmpsec = 'secs';
      if ($seconds < 2) {
        $tmpsec = 'sec';
      }
      // return $hours . ' ' . $tmphr . ' ' . $minutes . ' ' . $tmpmin . ' ' . $seconds . ' ' . $tmpsec;
      return ($hours > 0 ? $hours . ' ' . $tmphr . ' ' : '') . ($hours > 0 || $minutes > 0 ? $minutes . ' ' . $tmpmin . ' ' : '') . $seconds . ' ' . $tmpsec;
    } else if ($myType == 1) {
      $tmphr = 'hrs';
      if ($hours < 2) {
        $tmphr = 'hr';
      }
      $tmpmin = 'mins';
      if ($minutes < 2) {
        $tmpmin = 'min';
      }
      return $hours . ' ' . $tmphr . ' ' . $minutes . ' ' . $tmpmin;
    } else {
      return [$hours, $minutes, $seconds];
    }
  }
}

if (!function_exists('convertErrorIdElement')) {
  function convertErrorIdElement($myErrorId)
  {
    return str_replace('.', '_', $myErrorId) . '_error_id';
  }
}

if (!function_exists('getWorkingHours')) {
  function getWorkingHours($myStartDate, $myEndDate, array $myconfig)
  {
    $startWH = $myconfig['startWH'];
    $startWM = $myconfig['startWM'];
    $endWH = $myconfig['endWH'];
    $endWM = $myconfig['endWM'];
    $include_weekends = $myconfig['include_weekends'];
    $include_suspensions_holidays = $myconfig['include_suspensions_holidays'];

    $myHours = 0;

    // $myStartDate = '2024-05-11 12:02:00';
    // $myEndDate = '2024-05-13 21:44:47';

    $started = Carbon::parse($myStartDate);
    $completed = Carbon::parse($myEndDate);

    $tmpStarted9AM = $started->copy()->setTime($startWH, $startWM, 0);
    $tmpStarted5PM = $started->copy()->setTime($endWH, $endWM, 0);

    $tmpCompleted9AM = $completed->copy()->setTime($startWH, $startWM, 0);
    $tmpCompleted5PM = $completed->copy()->setTime($endWH, $endWM, 0);

    if ($started->lt($tmpStarted9AM)) {
      $started->setTime($startWH, $startWM, 0);
    } else if ($started->gt($tmpStarted5PM)) {
      $started->setTime($startWH, $startWM, 0)->addDay();
    }

    if ($completed->lt($tmpCompleted9AM)) {
      $completed->setTime($startWH, $startWM, 0);
    } else if ($completed->gt($tmpCompleted5PM)) {
      $completed->setTime($startWH, $startWM, 0)->addDay();
    }
    $currentDate = $started->copy();

    // dump($started);
    // dd($completed);


    $filter_date = $currentDate->copy();

    $suspensions = Holiday::where(function ($query) use ($filter_date) {
      $query->where('repeat_every_year', 'N')
        ->whereDate('start', '>=', $filter_date);
    })->orWhere(function ($query) {
      $query->where('repeat_every_year', 'Y');
    })->get();


    while ($currentDate->lte($completed)) {
      if ($include_weekends || $currentDate->isWeekend() == false) {
        if ($currentDate->isSameDay($completed)) {
          $currentDateEnd = $completed->copy();
        } else {
          $currentDateEnd = $currentDate->copy()->setTime($endWH, $endWM, 0);
        }

        $difference = $currentDateEnd->copy()->diff($currentDate);
        $myHours += $difference->h + ($difference->i / 60) + ($difference->s / 3600);

        // Deduct holidays/suspension
        if ($include_suspensions_holidays == false) {
          $mySuspHours = 0;
          foreach ($suspensions as $suspension) {
            $suspensionyear = $currentDate->copy();
            if ($suspension->repeat_every_year == 'Y') {
              $suspStart = Carbon::parse($suspension->start)->year($suspensionyear->year);
              $suspEnd = Carbon::parse($suspension->end)->year($suspensionyear->year);
            } else {
              $suspStart = Carbon::parse($suspension->start);
              $suspEnd = Carbon::parse($suspension->end);
            }
            if ($currentDate < $suspEnd && $currentDateEnd > $suspStart) {
              $startDate = $currentDate->copy();
              if ($suspStart > $currentDate) {
                $startDate = $suspStart->copy();
              }
              $endDate = $currentDateEnd->copy();
              if ($suspEnd < $currentDateEnd) {
                $endDate = $suspEnd->copy();
              }
              $suspDifference = $endDate->diff($startDate);
              $mySuspHours += $suspDifference->h + ($suspDifference->i / 60) + ($suspDifference->s / 3600);
            }
          }
          $myHours -= $mySuspHours;
        }
      }
      $currentDate = $currentDate->setTime($startWH, $startWM, 0);
      $currentDate->addDay();
    }

    if ($myHours < 0) {
      return 0;
    }
    return $myHours;
  }
}

if (!function_exists('guessDeviceName')) {
  function guessDeviceName($ua)
  {
    // $ua = $ua ?? '';
    // $os = str_contains($ua, 'Windows') ? 'Windows' : (str_contains($ua, 'Mac OS') ? 'macOS' : (str_contains($ua, 'Android') ? 'Android' : (str_contains($ua, 'iPhone') ? 'iOS' : 'Other')));
    // $browser = str_contains($ua, 'Chrome') ? 'Chrome' : (str_contains($ua, 'Firefox') ? 'Firefox' : (str_contains($ua, 'Safari') ? 'Safari' : 'Browser'));
    // return "$browser on $os";

    $ua = $ua ?? '';

    $os = 'Other';
    if (str_contains($ua, 'Windows')) {
      $os = 'Windows';
    } elseif (str_contains($ua, 'Mac OS') || str_contains($ua, 'Macintosh')) {
      $os = 'macOS';
    } elseif (str_contains($ua, 'Android')) {
      $os = 'Android';
    } elseif (str_contains($ua, 'iPhone') || str_contains($ua, 'iPad')) {
      $os = 'iOS';
    }

    $browser = 'Browser';
    if (str_contains($ua, 'Edg')) {
      $browser = 'Edge';
    } elseif (str_contains($ua, 'Firefox')) {
      $browser = 'Firefox';
    } elseif (str_contains($ua, 'Chrome')) {
      $browser = 'Chrome';
    } elseif (str_contains($ua, 'Safari')) {
      $browser = 'Safari';
    }

    if (str_contains($ua, 'Brave')) {
      $browser = 'Brave';
    }

    return "$browser on $os";
  }
}

if (!function_exists('isOdd')) {
  function isOdd(int $number): bool
  {
    return ($number % 2) !== 0;
  }
}
