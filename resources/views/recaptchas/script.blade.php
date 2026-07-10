
@if (app()->environment('production'))
  <script src="{{ config('recaptchav3.origin') }}/api.js?render={{ config('recaptchav3.sitekey') }}"></script>
@endif
<script>
  function submitFormRecaptcha() {
    $("#loader").attr("hidden", false);
    @if (app()->environment('production'))
      grecaptcha.ready(function() {
        grecaptcha.execute("{{ config('recaptchav3.sitekey') }}", { action: '{{ $form_action }}' }).then(function(token) {
          $('#{{ $form_name }}').prepend('<input type="hidden" name="g-recaptcha-response" value="' + token + '">');
          $('#{{ $form_name }}').unbind('submit').submit();
        });
      });
    @else
      $('#{{ $form_name }}').unbind('submit').submit();
    @endif
  }
</script>
