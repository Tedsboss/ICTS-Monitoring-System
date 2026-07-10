<?php

namespace App\Helpers;

class ComplexSQLHelper
{
  public static function getCustomSubmissionStatus()
  {
    return "CASE 
              WHEN submissions.is_rejected = 'Y' THEN
                '" . config('items.icc_submission_custom_statuses')[1] . "'
              WHEN submissions.status = 1 THEN
                '" . config('items.icc_submission_custom_statuses')[2] . "'
              WHEN submissions.status = 2 THEN
                CASE 
                    WHEN submissions.for_action = 'IA' THEN
                      '" . config('items.icc_submission_custom_statuses')[3] . "'
                    ELSE
                      '" . config('items.icc_submission_custom_statuses')[4] . "'
                END
              WHEN submissions.status = 3 THEN
                CASE 
                    WHEN submissions.for_action = 'IA' THEN
                      '" . config('items.icc_submission_custom_statuses')[5] . "'
                    ELSE
                      '" . config('items.icc_submission_custom_statuses')[6] . "'
                END
              WHEN submissions.status = 4 THEN
                '" . config('items.icc_submission_custom_statuses')[7] . "'
              ELSE
                ''
            END";
  }

  public static function getSubmissionsPerUserType($query, $permission_ids)
  {
    return $query->when(auth()->user()->isSuperAdmin(), function ($query) {
      return $query;
    }, function ($query) use ($permission_ids) {
      // View-all-icc completeness/compliance submissions 
      $query->when(in_array(25, $permission_ids), function ($query) {
        return $query;
      }, function ($query) use ($permission_ids) {
        $query->when(in_array(41, $permission_ids) || in_array(42, $permission_ids), function ($query) use ($permission_ids) {
          $my_status = [];
          if (in_array(41, $permission_ids)) {
            $my_status[] = 2;
          }
          if (in_array(42, $permission_ids)) {
            $my_status[] = 3;
          }
          return $query->whereIn('status', $my_status);
        }, function ($query) use ($permission_ids) {
          // Completeness check
          $query->when(in_array(29, $permission_ids), function ($query) {
            $funding_source_ids = (array) optional(auth()->user()->role->funding_sources)->pluck('id')->toArray();
            $funding_ids = (array) optional(auth()->user()->role->fundings)->pluck('id')->toArray();
            return $query->where('status', '>=', 2)
              ->whereHas('funding_source', function ($subQuery) use ($funding_source_ids) {
                $subQuery->whereIn('id', $funding_source_ids);
              })
              ->where(function ($query) use ($funding_ids) {
                $query->whereNotIn('pipol_funding_source', [2, 3])
                  ->orWhere(function ($query) use ($funding_ids) {
                    $query->whereIn('pipol_funding_source', [2, 3])
                      ->whereHas('fundings', function ($subQuery) use ($funding_ids) {
                        $subQuery->whereIn('id', $funding_ids);
                      });
                  });
              });
          }, function ($query) use ($permission_ids) {
            // Compliance check
            $query->when(in_array(30, $permission_ids), function ($query) {
              return $query->where('status', '>=', 3)
                ->whereHas('assignments', function ($subQuery) {
                  $subQuery->whereIn('division_id', [auth()->user()->division_id]);
                });
            }, function ($query) {
              // Users
              return $query->where('agency_id', auth()->user()->agency->id);
            });
          });
        });
      });
    });
  }

  public static function getForActionSubmissionsPerUserType($query, $permission_ids)
  {
    return $query->when(auth()->user()->isSuperAdmin(), function ($query) {
      return $query->whereIn('status', [2, 3]);
    }, function ($query) use ($permission_ids) {
      $query->when(in_array(25, $permission_ids), function ($query) {
        return $query->whereIn('status', [2, 3]);
      }, function ($query) use ($permission_ids) {
        // View-all-icc completeness/compliance submissions 
        $query->when(in_array(41, $permission_ids) || in_array(42, $permission_ids), function ($query) use ($permission_ids) {
          $my_status = [];
          if (in_array(41, $permission_ids)) {
            $my_status[] = 2;
          }
          if (in_array(42, $permission_ids)) {
            $my_status[] = 3;
          }
          return $query->whereIn('status', $my_status)->where('for_action', 'NEDA');
        }, function ($query) use ($permission_ids) {
          // Completeness check
          $query->when(in_array(29, $permission_ids), function ($query) {
            $funding_source_ids = (array) optional(auth()->user()->role->funding_sources)->pluck('id')->toArray();
            $funding_ids = (array) optional(auth()->user()->role->fundings)->pluck('id')->toArray();
            return $query->where('status', 2)->where('for_action', 'NEDA')
              ->whereHas('funding_source', function ($subQuery) use ($funding_source_ids) {
                $subQuery->whereIn('id', $funding_source_ids);
              })
              ->where(function ($query) use ($funding_ids) {
                $query->whereNotIn('pipol_funding_source', [2, 3])
                  ->orWhere(function ($query) use ($funding_ids) {
                    $query->whereIn('pipol_funding_source', [2, 3])
                      ->whereHas('fundings', function ($subQuery) use ($funding_ids) {
                        $subQuery->whereIn('id', $funding_ids);
                      });
                  });
              });
          }, function ($query) use ($permission_ids) {
            // Compliance check
            $query->when(in_array(30, $permission_ids), function ($query) {
              return $query->where('status', 3)->where('for_action', 'NEDA')
                ->whereHas('assignments', function ($subQuery) {
                  $subQuery->whereIn('division_id', [auth()->user()->division_id]);
                });
            }, function ($query) {
              // Users
              return $query->whereIn('status', [2, 3])->where('for_action', 'IA')
                ->where('agency_id', auth()->user()->agency->id);
            });
          });
        });
      });
    });
  }

  public static function getListOfEmails()
  {
    return "GROUP_CONCAT(DISTINCT new_users.email SEPARATOR ', ') ";
  }
}
