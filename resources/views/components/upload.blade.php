<form method="POST" action="{{ $url }}" enctype="multipart/form-data" id="frmUpload">
  @csrf
  <div class="modal fade" id="uploadModal" style="display: none" tabindex="-1" role="dialog" hidden>
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="h5 modal-title" id="uploadheader">Import {{ $title }}</h5>
          <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="pt-2 modal-body">
          <div class="multisteps-form__content">
            <div class="row mt-3">
              <div class="col-12">
                <div class="row">
                  <div class="col-10">
                    <label class="form-label">Upload File</label>
                  </div>
                  <div class="col-2 text-end">
                    <a href="{{ url('documents/templates/' . $filename) }}">
                      <i class="fa fa-cloud-download text-secondary text-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Download template"></i>
                    </a>
                  </div>
                </div>
                <div class="input-group">
                  <input type="file" name="excelfile" accept=".xlsx" id="excelfile" class="form-control" onclick="hideExcelFileError()">
                </div>
                <p class='text-danger text-xs' id="parExcelFile" hidden>Uploading failed. Please check error logs</p>
              </div>
            </div>
          </div>
          <div class="text-center pt-4 pb-2">
            <button class="m-1 btn btn-primary" type="button" id="btnUpload" onclick="submitFile()">
              Upload
            </button>
            <button class="m-1 btn btn-primary" type="button" id="btnUploadDisabled" disabled hidden>
              <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
              Uploading...
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>

<script>
  var upload_url = @php echo json_encode($url) @endphp;
  function hideExcelFileError() {
    $("#parExcelFile").attr("hidden", true);
  }

  function showImportModal() {
    hideExcelFileError();
    $("#uploadModal").attr("hidden", false);
    $("#uploadModal").modal("show");
  }

  function submitFile() {
    $("#btnUpload").attr("hidden", true);
    $("#btnUploadDisabled").attr("hidden", false);
    setTimeout(function() { 
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      $.ajax({
        data: new FormData($('#frmUpload')[0]),
        url: upload_url,
        method: "POST",
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function (data) {
          if (data.status == 'success') {
            alert(data.data);
            location.reload();
          } else {
            var fileContent = data.data;
            var blob = new Blob([fileContent], { type: "text/plain" });
            var $link = $("<a>");
            $link.attr({
              href: window.URL.createObjectURL(blob),
              download: "eBudget_Error_" + generateDateTime() + ".log"
            });
            $("body").append($link);
            $link[0].click();
            $link.remove();
            $("#parExcelFile").text("Uploading failed. Please check error logs");
            $("#parExcelFile").attr("hidden", false);
          }
          $("#btnUpload").attr("hidden", false);
          $("#btnUploadDisabled").attr("hidden", true);
        },
        error: function (data) {
          if (data && data.responseJSON && data.responseJSON.message !== undefined) {
            $("#parExcelFile").text(data.responseJSON.message);
            $("#parExcelFile").attr("hidden", false);
          } else {
            alert('(1)Unknown error: Contact ICTS');
          }

          $("#btnUpload").attr("hidden", false);
          $("#btnUploadDisabled").attr("hidden", true);
        }
      });
    }, 100);
  }
</script>