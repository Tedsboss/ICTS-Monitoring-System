<style>
    #direk-toast-container {
        position:fixed!important;
        top:16px!important;
        right:16px!important;
        z-index:1090!important;
        width:min(380px,calc(100vw - 32px));
        max-height:calc(100vh - 32px);
        max-height:calc(100dvh - 32px);
        overflow-y:auto;
        pointer-events:none;
    }
    #direk-toast-container .toast {
        width:100%;
        max-width:100%;
        pointer-events:auto;
    }
    #direk-toast-container .toast-body {
        overflow-wrap:anywhere;
    }
    #direk-toast-container .direk-toast-close {
        display:inline-flex;
        align-items:center;
        justify-content:center;
        flex-shrink:0;
        width:30px;
        height:30px;
        margin-left:12px;
        padding:0;
        border:0;
        border-radius:6px;
        background:transparent;
        color:#fff;
        font-size:24px;
        line-height:1;
        cursor:pointer;
    }
    #direk-toast-container .direk-toast-close:focus-visible {
        outline:2px solid #fff;
        outline-offset:2px;
    }
</style>
<div id="direk-toast-container" class="toast-container position-fixed top-2 end-2">
    <div class="toast fade hide p-2 mt-2 bg-gradient-info" role="alert" aria-live="assertive" id="infoToast" aria-atomic="true" data-bs-delay="10000">
        <div class="toast-header bg-transparent border-0">
            <i class="fa fa-info-circle fa-2x text-white me-3" aria-hidden="true"></i>
            <span class="me-auto text-white font-weight-bold" id="infoToastTitle" style="font-size:1.2rem;">For Information</span>
            <button type="button" class="direk-toast-close" data-bs-dismiss="toast" aria-label="Close notification"><span aria-hidden="true">&times;</span></button>
        </div>
        <hr class="horizontal light m-0">
        <div class="toast-body text-white"><span id="infoToastMessage"></span></div>
    </div>
    <div class="toast fade hide p-2 mt-2 bg-gradient-success" role="alert" aria-live="assertive" id="successToast" aria-atomic="true" data-bs-delay="10000">
        <div class="toast-header bg-transparent border-0">
            <i class="fa fa-check fa-2x text-white me-3" aria-hidden="true"></i>
            <span class="me-auto text-white font-weight-bold" id="successToastTitle" style="font-size:1.2rem;">Success</span>
            <button type="button" class="direk-toast-close" data-bs-dismiss="toast" aria-label="Close notification"><span aria-hidden="true">&times;</span></button>
        </div>
        <hr class="horizontal light m-0">
        <div class="toast-body text-white"><span id="successToastMessage"></span></div>
    </div>
    <div class="toast fade hide p-2 mt-2 bg-gradient-warning" role="alert" aria-live="assertive" id="warningToast" aria-atomic="true" data-bs-delay="10000">
        <div class="toast-header bg-transparent border-0">
            <i class="fa fa-exclamation-triangle fa-2x text-white me-3" aria-hidden="true"></i>
            <span class="me-auto text-white font-weight-bold" id="warningToastTitle" style="font-size:1.2rem;">Warning</span>
            <button type="button" class="direk-toast-close" data-bs-dismiss="toast" aria-label="Close notification"><span aria-hidden="true">&times;</span></button>
        </div>
        <hr class="horizontal light m-0">
        <div class="toast-body text-white"><span id="warningToastMessage"></span></div>
    </div>
    <div class="toast fade hide p-2 mt-2 bg-gradient-danger" role="alert" aria-live="assertive" id="dangerToast" aria-atomic="true" data-bs-delay="10000">
        <div class="toast-header bg-transparent border-0">
            <i class="fa fa-times-circle-o fa-2x text-white me-3" aria-hidden="true"></i>
            <span class="me-auto text-white font-weight-bold" id="dangerToastTitle" style="font-size:1.2rem;">Error</span>
            <button type="button" class="direk-toast-close" data-bs-dismiss="toast" aria-label="Close notification"><span aria-hidden="true">&times;</span></button>
        </div>
        <hr class="horizontal light m-0">
        <div class="toast-body text-white"><span id="dangerToastMessage"></span></div>
    </div>
</div>
