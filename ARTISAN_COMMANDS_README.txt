UPLIFT Portal Artisan Command Cheat Sheet
========================================

Use these commands from the project root:

  cd C:\xampp\htdocs\uplift


1. System Smoke Check
---------------------
Purpose:
  Runs a non-destructive health check for routes, auth guard login/logout,
  submission counts, approval routing, and API protection.

Commands:
  php artisan system:smoke-check
  php artisan system:smoke-check --skip-http
  php artisan system:smoke-check --email=user@example.com
  php artisan system:smoke-check --user=1
  php artisan system:smoke-check --json


2. Refresh Submission Data
--------------------------
Purpose:
  Deletes submission rows only. Keeps Form Management and UPLIFT Builder
  definitions intact.

Commands:
  php artisan submission:refresh --force
  php artisan submission:refresh --indicator --force
  php artisan submission:refresh --uplift --force

Deletes:
  Indicator/Form submission data:
    submission_notifications
    form_submission_values
    form_submissions
    related approval history rows

  UPLIFT submission data:
    uplift_submission_indicator_values
    uplift_submission_field_values
    uplift_submissions
    related approval history rows


3. Refresh Builder Data
-----------------------
Purpose:
  Deletes builder definitions and dependent submission data. Keeps users,
  agencies, roles, permissions, staffs, divisions, and system settings.

Commands:
  php artisan builder:refresh --force
  php artisan builder:refresh --forms --force
  php artisan builder:refresh --uplift --force

Deletes:
  Form Management:
    submission_notifications
    form_submission_values
    form_submissions
    form_fields
    forms
    related approval history rows

  UPLIFT Builder:
    submission_notifications
    uplift_submission_indicator_values
    uplift_submission_field_values
    uplift_submissions
    uplift_indicators
    uplift_pillar_fields
    uplift_measure_supporting_agencies
    uplift_measures
    uplift_pillars
    related approval history rows


4. Backfill Approval History
----------------------------
Purpose:
  Creates approval history rows from existing audit columns on submissions.
  Useful after adding the approval history feature to old data.

Commands:
  php artisan submission:backfill-approval-history
  php artisan submission:backfill-approval-history --force


5. Common Cache Commands
------------------------
Purpose:
  Clear Laravel cached config/routes/views after changing .env, routes, or views.

Commands:
  php artisan config:clear
  php artisan route:clear
  php artisan view:clear
  php artisan view:cache


6. API Test Reminder
--------------------
Approved headline indicator API:

  GET /api/v1/approved/indicator-submissions

Headers:
  Authorization: Bearer YOUR_TOKEN

or:
  X-API-Token: YOUR_TOKEN

Required .env values:
  APPROVED_SUBMISSIONS_API_TOKEN=your-token
  APPROVED_SUBMISSIONS_API_ALLOWED_IPS=127.0.0.1,::1

After editing .env:
  php artisan config:clear
