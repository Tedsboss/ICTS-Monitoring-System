<div class="modal fade modal-transparent" id="happyModal" style="display: none" tabindex="-1" role="dialog" hidden>
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content {{ isset($class_theme) && $class_theme == 'dark' ? 'bg-default' : '' }}">
      {{-- <div class="modal-header">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div> --}}
      <div class="pt-2 modal-body">
        <div class="multisteps-form__content">
          <div class="col-lg-12 mx-auto" style="height: 800px; width: 800px; background-image: url('{{ asset('assets/img/hbd.png') }}'); background-size: contain; background-position: center; background-repeat: no-repeat;">
        </div>
      </div>
    </div>
  </div>
</div>

@push('js')
  <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>
  <script>
    let confettiInterval = null;

    $(document).ready(function() {
      const key = `birthday_shown_${new Date().getFullYear()}`;
      if (localStorage.getItem(key) == 1) {
        return;
      } else {
        $("#happyModal").attr("hidden", false);
        $("#happyModal").modal("show");
        localStorage.setItem(key, 1);
      }
    });

    $('#happyModal').on('shown.bs.modal', function () {
      startConfetti();
      const $m = $(this);
      $m.data('autoCloseTimer', setTimeout(function () {
        $m.modal('hide');
      }, 15 * 1000));
    });

    $('#happyModal').on('hidden.bs.modal', function () {
      stopConfetti(true);
    });

    function startConfetti() {
      if (confettiInterval) return;
      const defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 999999 };
      confettiInterval = setInterval(function () {
        const particleCount = 50;
        confetti({
          ...defaults,
          particleCount,
          origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 }
        });
        confetti({
          ...defaults,
          particleCount,
          origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 }
        });
      }, 250);
    }

    function stopConfetti(clearParticles = false) {
      if (confettiInterval) {
        clearInterval(confettiInterval);
        confettiInterval = null;
      }
      if (clearParticles) {
        confetti.reset();
      }
    }

    function randomInRange(min, max) {
      return Math.random() * (max - min) + min;
    }
  </script>
@endpush