<div class="modal fade" id="homeAnnouncementModal" style="display: none" tabindex="-1" role="dialog" hidden>
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content {{ isset($class_theme) && $class_theme == 'dark' ? 'bg-default' : '' }}">
      <div class="modal-header">
        <h2 class="h4 modal-title">{{ $homeannouncement->title }}</h4>&nbsp;&nbsp;
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="pt-2 modal-body">
        <div class="multisteps-form__content">
          <div class="row">
            <div class="col-md-12">
              <div id="divText" hidden>
                <p id="string_home_announcement"></p>
              </div>
              <div id="divHtml" hidden>
                <div id="quill_home_announcement" class="overflow-auto"></div>
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
    var homeannouncement = @php echo json_encode($homeannouncement) @endphp;
    $(document).ready(function() {
      if(homeannouncement.type == 'string') {
        $("#divText").attr("hidden", false);
        $("#string_home_announcement").text(homeannouncement.value);
      } else if(homeannouncement.type == 'html') {
        $("#divHtml").attr("hidden", false);
        initQuillJs('quill_home_announcement', true);
        quills['quill_home_announcement'].root.innerHTML = '{!! $homeannouncement->value !!}';
      }
      
      $("#homeAnnouncementModal").attr("hidden", false);
      $('#homeAnnouncementModal').modal("show");
    });
  </script>
@endpush