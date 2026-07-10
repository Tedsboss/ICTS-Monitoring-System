<div class="modal fade" id="termsAndConditionsModal" style="display: none" tabindex="-1" role="dialog" hidden>
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content {{ isset($class_theme) && $class_theme == 'dark' ? 'bg-default' : '' }}">
      <div class="modal-header">
        <h2 class="h2 modal-title">{{ $termsandcondition->title }}</h2>&nbsp;&nbsp;
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="pt-2 modal-body">
        <div class="multisteps-form__content">
          <div class="row">
            <div class="col-md-12">
              <div id="divText" hidden>
                <p id="string_terms_and_conditions"></p>
              </div>
              <div id="divHtml" hidden>
                <div id="quill_terms_and_conditions" class="overflow-auto"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@push('css')
  <style>
    .ql-container.ql-snow {
      border: none !important;
    }
  </style>
@endpush

@push('js')
  <script>
    var termsandcondition = @php echo json_encode($termsandcondition) @endphp;
    $(document).ready(function() {
      if(termsandcondition.type == 'string') {
        $("#divText").attr("hidden", false);
        $("#string_terms_and_conditions").text(termsandcondition.value);
      } else if(termsandcondition.type == 'html') {
        $("#divHtml").attr("hidden", false);
        initQuillJs('quill_terms_and_conditions', true);
        quills['quill_terms_and_conditions'].root.innerHTML = '{!! $termsandcondition->value !!}';
      }
    });

    function showTermsAndConditions() {
      $("#termsAndConditionsModal").attr("hidden", false);
      $('#termsAndConditionsModal').modal("show");
    }
  </script>
@endpush