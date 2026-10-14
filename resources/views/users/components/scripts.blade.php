<script>
    function ocTwoFactor() {
        const checkbox = document.getElementById('twofactor');
        if (!checkbox) return;

        const enabled = checkbox.checked;

        ['divTwoFactorType', 'divTrustedDevices'].forEach(function (id) {
            const section = document.getElementById(id);
            if (section) section.hidden = !enabled;
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', ocTwoFactor, {
            once: true
        });
    } else {
        ocTwoFactor();
    }

    window.addEventListener('pageshow', ocTwoFactor);
</script>
