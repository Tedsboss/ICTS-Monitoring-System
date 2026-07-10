<?php

namespace App\Rules;

use App\Models\MaliciousDomain;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Mews\Purifier\Facades\Purifier;

class PurifyHtml implements ValidationRule
{
  /**
   * Indicates whether the rule should be implicit.
   *
   * @var bool
   */
  public $implicit = true;
  private $allow_empty = false;

  public function __construct($allow_empty = false)
  {
    $this->allow_empty = $allow_empty;
  }

  /**
   * Run the validation rule.
   *
   * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
   */
  public function validate(string $attribute, mixed $value, Closure $fail): void
  {
    if ($this->allow_empty == true && ($value == null || $value == '' || $value == '<p><br></p>')) {
    } else {
      $cleanValue = Purifier::clean($value, 'allow_quilljs_element');
      // dd($cleanValue, $value);
      if (strip_tags($cleanValue) == '') {
        $fail('The :attribute cannot be empty.');
      }

      //File(Image) Validation
      preg_match_all('/<img[^>]+src=["\']data:([^;]+);base64,([^"\']+)["\'][^>]*>/', $cleanValue, $matches);
      foreach ($matches[2] as $index => $base64Data) {
        $mediaType = strtolower($matches[1][$index]);
        if (strpos($mediaType, 'image/') === false) {
          $fail("The :attribute contains unsupported file. Only images are allowed");
        } else {
          $imageType = explode('/', $mediaType)[1];
          $allowedTypes = ['png', 'jpeg', 'jpg', 'gif'];
          if (!in_array($imageType, $allowedTypes)) {
            $fail("The :attribute contains an invalid image type. Only PNG, JPG, and GIF images are allowed.");
          } else {
            $imageData = base64_decode($base64Data, true);
            if ($imageData === false) {
              $fail("The :attribute contains an invalid base64-encoded image.");
            } else {
              if (strlen($imageData) > 524288) { // 500KB limit
                $fail("The :attribute contains an image larger than 500KB.");
              } else {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_buffer($finfo, $imageData);
                finfo_close($finfo);
                if (!in_array($mimeType, ['image/png', 'image/jpeg', 'image/gif'])) {
                  $fail("The :attribute contains an invalid image type. Only PNG, JPG, and GIF images are allowed.");
                }
              }
            }
          }
        }
      }

      //Video Url Validation
      preg_match_all('/<iframe[^>]+src="([^"]+)"[^>]*><\/iframe>/', $cleanValue, $matches);
      foreach ($matches[1] as $index => $srcUrl) {
        if (!preg_match('/^https:\/\/(www\.)?youtube\.com\/embed\/([A-Za-z0-9_-]+)$/', $iframeSrc)) {
          $fail("The :attribute contains invalid youtube URL");
        }
      }

      //Link Validation
      preg_match_all('/<a\s+[^>]*href=["\'](.*?)["\'][^>]*>/i', $cleanValue, $matches);
      foreach ($matches[1] as $href) {
        if ($this->isMaliciousUrl($href, $errorType)) {
          $fail("The :attribute contains a $errorType URL");
        }
      }
    }
  }

  protected function isMaliciousUrl($url, &$errorType)
  {
    if (preg_match('/^(javascript:|data:)/i', $url)) {
      $errorType = "potentially malicious (javascript or data scheme)";
      return true;
    }
    $host = parse_url($url, PHP_URL_HOST);
    if (!$host) {
      $errorType = "broken or improperly formatted(must start with http:// or https://)";
      return true;
    }

    if (MaliciousDomain::where('domain', $host)->exists()) {
      $errorType = "blacklisted or malicious";
      return true;
    }
    return false;
  }
}
