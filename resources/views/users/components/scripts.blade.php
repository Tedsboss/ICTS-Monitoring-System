<script>
  function ocTwoFactor() {
    if ($("#twofactor" ).is(":checked")) {
      $("#divTwoFactorType").attr("hidden", false);
      $("#divTrustedDevices").attr("hidden", false);
    } else {
      $("#divTwoFactorType").attr("hidden", true);
      $("#divTrustedDevices").attr("hidden", true);
    }
  }
</script>