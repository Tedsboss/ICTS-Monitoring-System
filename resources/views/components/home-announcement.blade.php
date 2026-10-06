@php
    $announcementDark = session('user_settings.class_theme', '') === 'dark';
    $announcementSurface = $announcementDark ? '!bg-[#1a293a] !border-[#34465a]' : '!bg-white !border-slate-200';
    $announcementHeading = $announcementDark ? '!text-slate-100' : '!text-[#203b55]';
    $announcementCopy = $announcementDark ? '!text-slate-300' : '!text-slate-600';
    $announcementTitle = $homeannouncement->title ?: 'DIREK announcement';
    $announcementPayload = [
        'type' => (string) $homeannouncement->type,
        'value' => (string) $homeannouncement->value,
    ];
@endphp

{{-- Keep the announcement accessible after the automatic modal is dismissed. --}}
<section class="!flex !flex-wrap !items-center !justify-between !gap-4 !rounded-xl !border !border-solid !p-5 {{ $announcementSurface }}" aria-labelledby="home-announcement-banner-title">
    <div class="!flex !min-w-0 !flex-1 !items-start !gap-4">
        <span class="!grid !h-11 !w-11 !shrink-0 !place-items-center !rounded-lg !bg-[#142d45] !text-[#e4cf9d]" aria-hidden="true"><i class="fa fa-bullhorn"></i></span>
        <div class="!min-w-0">
            <p class="!m-0 !text-xs !font-semibold !uppercase !tracking-[0.12em] {{ $announcementCopy }}">Latest announcement</p>
            <h2 id="home-announcement-banner-title" class="!m-0 !mt-1 !break-words !text-base !font-semibold !leading-6 {{ $announcementHeading }}">{{ $announcementTitle }}</h2>
        </div>
    </div>
    <button type="button" data-bs-toggle="modal" data-bs-target="#homeAnnouncementModal" class="!inline-flex !min-h-[44px] !items-center !justify-center !gap-2 !rounded-lg !border !border-solid !border-[#315e87] !bg-[#203b55] !px-4 !py-2.5 !font-sans !text-xs !font-semibold !text-white !transition hover:!bg-[#315e87] focus-visible:!outline-none focus-visible:!ring-2 focus-visible:!ring-[#b2863e] focus-visible:!ring-offset-2 motion-reduce:!transition-none">
        View announcement <i class="fa fa-arrow-right" aria-hidden="true"></i>
    </button>
</section>

<div class="modal fade" id="homeAnnouncementModal" tabindex="-1" role="dialog" aria-labelledby="homeAnnouncementTitle" hidden>
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable !mx-4 !my-6 !max-w-3xl sm:!mx-auto">
        <div class="modal-content !overflow-hidden !rounded-2xl !border !border-solid !shadow-xl {{ $announcementSurface }}">
            <div class="modal-header !items-start !gap-4 !border-0 !bg-[#142d45] !px-6 !py-6 !text-white">
                <div class="!flex !min-w-0 !items-start !gap-3">
                    <span class="!mt-1 !text-lg !text-[#e4cf9d]" aria-hidden="true"><i class="fa fa-bullhorn"></i></span>
                    <div class="!min-w-0">
                        <p class="!m-0 !text-xs !font-medium !uppercase !tracking-[0.12em] !text-[#e4cf9d]">DIREK announcement</p>
                        <h2 class="modal-title !m-0 !mt-2 !break-words !text-xl !font-semibold !leading-7 !text-white" id="homeAnnouncementTitle">{{ $announcementTitle }}</h2>
                    </div>
                </div>
                <button type="button" data-bs-dismiss="modal" aria-label="Close announcement" class="!grid !h-11 !w-11 !shrink-0 !place-items-center !rounded-lg !border-0 !bg-transparent !p-0 !text-white/80 hover:!bg-white/10 hover:!text-white focus-visible:!outline-none focus-visible:!ring-2 focus-visible:!ring-[#e4cf9d]"><i class="fa fa-times" aria-hidden="true"></i></button>
            </div>

            <div class="modal-body !p-6 !text-sm !leading-7 md:!p-8 {{ $announcementCopy }}">
                <div id="divText" hidden>
                    <p id="string_home_announcement" class="!m-0 !whitespace-pre-wrap !break-words !text-sm !leading-7 {{ $announcementCopy }}"></p>
                </div>
                {{-- These overrides apply only to this read-only announcement viewer. --}}
                <div id="divHtml" class="[&_.ql-toolbar]:!hidden [&_.ql-container]:!border-0 [&_.ql-editor]:!min-h-0 [&_.ql-editor]:!p-0 [&_.ql-editor]:!text-sm [&_.ql-editor]:!leading-7 [&_.ql-editor]:!text-inherit" hidden>
                    <div id="quill_home_announcement" class="!max-h-[55vh] !overflow-y-auto !border-0 !font-sans !text-inherit"></div>
                </div>
            </div>

            <div class="modal-footer !justify-between !gap-3 !border-0 !px-6 !pb-6 !pt-0 md:!px-8">
                <span class="!m-0 !text-xs {{ $announcementCopy }}">Department of Economy, Planning, and Development</span>
                <button type="button" data-bs-dismiss="modal" class="!m-0 !min-h-[44px] !rounded-lg !border-0 !bg-[#203b55] !px-5 !py-2.5 !font-sans !text-sm !font-semibold !text-white !transition hover:!bg-[#315e87] focus-visible:!outline-none focus-visible:!ring-2 focus-visible:!ring-[#b2863e] focus-visible:!ring-offset-2 motion-reduce:!transition-none">Got it</button>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
$(function () {
    'use strict';
    const announcement = {{ \Illuminate\Support\Js::from($announcementPayload) }};
    const modal = document.getElementById('homeAnnouncementModal');
    const textPanel = document.getElementById('divText');
    const htmlPanel = document.getElementById('divHtml');
    const text = document.getElementById('string_home_announcement');

    if (announcement.type === 'html') {
        if (typeof initQuillJs === 'function' && typeof quills !== 'undefined') {
            htmlPanel.hidden = false;
            initQuillJs('quill_home_announcement', true);
            const editor = quills['quill_home_announcement'];
            // Import stored editor content through Quill instead of editing its DOM.
            // Rich HTML must already be validated/sanitized when the announcement is saved.
            editor.clipboard.dangerouslyPasteHTML(announcement.value, 'silent');
            editor.enable(false);
        } else {
            // Retain readable content if the editor library is unavailable.
            const parsed = new DOMParser().parseFromString(announcement.value, 'text/html');
            text.textContent = parsed.body.textContent || '';
            textPanel.hidden = false;
        }
    } else {
        text.textContent = announcement.value;
        textPanel.hidden = false;
    }

    modal.hidden = false;
    // Retain the application's existing Bootstrap/jQuery modal integration.
    $(modal).modal('show');
});
</script>
@endpush
