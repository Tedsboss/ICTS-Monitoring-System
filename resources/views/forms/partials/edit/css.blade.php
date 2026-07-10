<style>
  :root {
    --builder-blue: #08428f;
    --builder-blue-dark: #05306f;
    --builder-bg: #f6f8fb;
    --builder-border: #dbe7f3;
    --builder-soft: #eef7ff;
    --builder-text: #344767;
    --builder-muted: #6c7f96;
  }

  .builder-page {
    min-height: calc(100vh - 80px);
  }

  body.form-builder-sidenav-page.g-sidenav-hidden #sidenav-main {
    max-width: 17.225rem !important;
    transform: translateX(-18.725rem) !important;
  }

  body.form-builder-sidenav-page.g-sidenav-hidden .navbar-vertical.fixed-start + .main-content {
    margin-left: 0 !important;
  }

  body.form-builder-sidenav-page.g-sidenav-pinned #sidenav-main {
    max-width: 17.225rem !important;
    transform: translateX(0) !important;
    z-index: 1050;
  }

  .builder-details-card {
    position: relative;
    z-index: 20;
    border: 1px solid var(--builder-border);
    border-radius: 14px;
    background: #fff;
    box-shadow: 0 8px 20px rgba(8, 66, 143, .05);
    overflow: visible;
  }

  .builder-details-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    padding: 10px 12px;
    background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    border-bottom: 1px solid #edf2f7;
  }

  .builder-details-heading {
    display: flex;
    align-items: flex-start;
    gap: 9px;
    min-width: 0;
  }

  .builder-details-icon {
    width: 32px;
    height: 32px;
    min-width: 32px;
    border-radius: 10px;
    background: var(--builder-soft);
    color: var(--builder-blue);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: .82rem;
  }

  .builder-details-title {
    margin: 0;
    color: var(--builder-text);
    font-size: .86rem;
    font-weight: 900;
    line-height: 1.25;
  }

  .builder-details-subtitle {
    margin: 2px 0 0;
    color: var(--builder-muted);
    font-size: .68rem;
    line-height: 1.35;
  }

  .builder-details-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    min-height: 24px;
    padding: 3px 9px;
    border-radius: 999px;
    font-size: .66rem;
    font-weight: 850;
    white-space: nowrap;
    border: 1px solid transparent;
  }

  .builder-details-status span {
    width: 7px;
    height: 7px;
    border-radius: 999px;
    display: inline-block;
  }

  .builder-details-status.is-active {
    color: #15803d;
    background: #f0fdf4;
    border-color: #dcfce7;
  }

  .builder-details-status.is-active span {
    background: #22c55e;
  }

  .builder-details-status.is-inactive {
    color: #64748b;
    background: #f8fafc;
    border-color: #e2e8f0;
  }

  .builder-details-status.is-inactive span {
    background: #94a3b8;
  }

  .builder-details-body {
    overflow: visible;
    padding: 10px 12px;
  }

  .builder-details-grid {
    display: grid;
    grid-template-columns: minmax(210px, .95fr) minmax(260px, 1.35fr) minmax(180px, .7fr);
    gap: 10px;
    align-items: start;
  }

  .builder-field-group {
    position: relative;
    min-width: 0;
  }

  .builder-field-label {
    display: block;
    margin-bottom: 4px;
    color: var(--builder-text);
    font-size: .68rem;
    font-weight: 850;
    line-height: 1.2;
  }

  .builder-compact-input {
    width: 100%;
    min-height: 34px !important;
    height: 34px !important;
    border-radius: 9px !important;
    border: 1px solid #d8e4f1 !important;
    background-color: #fbfdff !important;
    color: var(--builder-text) !important;
    font-size: .74rem !important;
    padding: 6px 9px !important;
    box-shadow: none !important;
  }

  select.builder-compact-input,
  select.form-select.builder-compact-input {
    appearance: auto !important;
    -webkit-appearance: menulist !important;
    padding-right: 32px !important;
    background-color: #fbfdff !important;
    cursor: pointer;
  }

  .builder-compact-input:focus {
    border-color: var(--builder-blue) !important;
    background-color: #fff !important;
    box-shadow: 0 0 0 3px rgba(8, 66, 143, .08) !important;
  }

  .builder-compact-input:disabled {
    background-color: #f3f7fb !important;
    color: #8a98aa !important;
  }

  .agency-tomselect.ts-wrapper {
    width: 100%;
    min-height: 34px;
    padding: 0 !important;
    border: 0 !important;
    background: transparent !important;
    box-shadow: none !important;
  }

  .agency-tomselect .ts-control {
    min-height: 34px !important;
    height: 34px !important;
    display: flex !important;
    align-items: center !important;
    padding: 0 34px 0 9px !important;
    border-radius: 9px !important;
    border: 1px solid #d8e4f1 !important;
    background: #fbfdff !important;
    color: var(--builder-text) !important;
    font-size: .74rem !important;
    font-weight: 700;
    line-height: 32px !important;
    box-shadow: none !important;
    flex-wrap: nowrap;
  }

  .agency-tomselect.focus .ts-control,
  .agency-tomselect.dropdown-active .ts-control {
    border-color: var(--builder-blue) !important;
    background: #fff !important;
    box-shadow: 0 0 0 3px rgba(8, 66, 143, .08) !important;
  }

  .agency-tomselect.is-invalid .ts-control {
    border-color: #dc3545 !important;
  }

  .agency-tomselect .ts-control .item {
    display: block;
    max-width: 100%;
    line-height: 32px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .agency-tomselect .ts-control > input {
    height: 32px !important;
    font-size: .74rem !important;
    line-height: 32px !important;
  }

  .agency-tomselect-dropdown {
    z-index: 9999 !important;
    width: min(640px, calc(100vw - 32px)) !important;
    max-height: 320px;
    border: 1px solid #d8e4f1 !important;
    border-radius: 10px !important;
    box-shadow: 0 14px 30px rgba(15, 23, 42, .12) !important;
    padding: 8px !important;
  }

  .agency-tomselect-dropdown .dropdown-input-wrap {
    padding: 0 0 8px !important;
    margin: 0 0 6px !important;
    border-bottom: 1px solid #edf2f7;
  }

  .agency-tomselect-dropdown .dropdown-input {
    min-height: 32px !important;
    border-radius: 8px !important;
    border: 1px solid #d8e4f1 !important;
    font-size: .76rem !important;
    padding: 5px 8px !important;
    outline: none !important;
  }

  .agency-tomselect-dropdown .ts-dropdown-content {
    max-height: 260px !important;
  }

  .agency-tomselect-dropdown .option {
    padding: 8px 10px !important;
    border-radius: 7px !important;
    color: #344767 !important;
    font-size: .76rem !important;
    font-weight: 700;
  }

  .agency-tomselect-dropdown .option.active,
  .agency-tomselect-dropdown .option.selected {
    background: var(--builder-soft) !important;
    color: var(--builder-blue) !important;
  }

  .sector-tomselect.ts-wrapper {
    width: 100%;
    min-height: 34px;
    padding: 0 !important;
    border: 0 !important;
    background: transparent !important;
    box-shadow: none !important;
  }

  .sector-tomselect .ts-control {
    min-height: 34px !important;
    height: 34px !important;
    display: flex !important;
    align-items: center !important;
    padding: 0 34px 0 9px !important;
    border-radius: 9px !important;
    border: 1px solid #d8e4f1 !important;
    background: #fbfdff !important;
    color: var(--builder-text) !important;
    font-size: .74rem !important;
    font-weight: 700;
    line-height: 32px !important;
    box-shadow: none !important;
    flex-wrap: nowrap;
  }

  .sector-tomselect.focus .ts-control,
  .sector-tomselect.dropdown-active .ts-control {
    border-color: var(--builder-blue) !important;
    background: #fff !important;
    box-shadow: 0 0 0 3px rgba(8, 66, 143, .08) !important;
  }

  .sector-tomselect.is-invalid .ts-control {
    border-color: #dc3545 !important;
  }

  .sector-tomselect .ts-control .item {
    display: block;
    max-width: 100%;
    line-height: 32px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .sector-tomselect .ts-control > input {
    height: 32px !important;
    font-size: .74rem !important;
    line-height: 32px !important;
  }

  .sector-tomselect-dropdown {
    left: 0 !important;
    right: auto !important;
    z-index: 9999 !important;
    box-sizing: border-box !important;
    max-height: 320px;
    overflow: hidden;
    border: 1px solid #d8e4f1 !important;
    border-radius: 10px !important;
    box-shadow: 0 14px 30px rgba(15, 23, 42, .12) !important;
    padding: 8px !important;
  }

  .sector-tomselect-dropdown .dropdown-input-wrap {
    padding: 0 0 8px !important;
    margin: 0 0 6px !important;
    border-bottom: 1px solid #edf2f7;
  }

  .sector-tomselect-dropdown .dropdown-input {
    width: 100% !important;
    min-height: 32px !important;
    border-radius: 8px !important;
    border: 1px solid #d8e4f1 !important;
    font-size: .76rem !important;
    padding: 5px 8px !important;
    outline: none !important;
  }

  .sector-tomselect-dropdown .ts-dropdown-content {
    max-height: 260px !important;
    overflow-x: hidden !important;
  }

  .sector-tomselect-dropdown .option {
    padding: 8px 10px !important;
    border-radius: 7px !important;
    color: #344767 !important;
    font-size: .76rem !important;
    font-weight: 700;
    white-space: normal !important;
    overflow-wrap: anywhere;
  }

  .sector-tomselect-dropdown .option.active,
  .sector-tomselect-dropdown .option.selected {
    background: var(--builder-soft) !important;
    color: var(--builder-blue) !important;
  }

  @media (max-width: 767.98px) {
    .agency-tomselect-dropdown {
      width: 100% !important;
    }
  }

  .builder-compact-input.is-invalid,
  .was-validated .builder-compact-input:invalid {
    border-color: #dc3545 !important;
    box-shadow: none !important;
  }

  .builder-error-text {
    margin: 4px 0 0;
    color: #dc2626;
    font-size: .66rem;
    line-height: 1.25;
  }

  .builder-status-toggle {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 6px;
  }

  .builder-status-option {
    position: relative;
    margin: 0;
    cursor: pointer;
  }

  .builder-status-option input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
  }

  .builder-status-option span {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 34px;
    padding: 6px 9px;
    border-radius: 9px;
    border: 1px solid #d8e4f1;
    background: #fbfdff;
    color: #64748b;
    font-size: .72rem;
    font-weight: 850;
    transition: .15s ease;
  }

  .builder-status-option input:checked + span {
    color: var(--builder-blue);
    background: var(--builder-soft);
    border-color: #bfd2e8;
  }

  .builder-status-option input:disabled + span {
    opacity: .6;
    cursor: not-allowed;
  }

  .builder-details-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 9px 12px;
    background: #fbfdff;
    border-top: 1px solid #edf2f7;
  }

  .builder-details-meta {
    display: flex;
    align-items: center;
    gap: 7px;
    flex-wrap: wrap;
  }

  .builder-details-meta span {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    min-height: 24px;
    padding: 3px 8px;
    border-radius: 999px;
    background: #fff;
    border: 1px solid #e2e8f0;
    color: #64748b;
    font-size: .67rem;
    font-weight: 800;
  }

  .builder-details-meta i {
    color: var(--builder-blue);
  }

  .builder-details-actions {
    display: flex;
    align-items: center;
    gap: 7px;
    flex-wrap: wrap;
    justify-content: flex-end;
  }

  .builder-primary-btn,
  .builder-secondary-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    min-height: 30px;
    padding: 5px 10px;
    border-radius: 9px;
    font-size: .72rem;
    font-weight: 850;
    text-decoration: none;
    border: 1px solid transparent;
    transition: .15s ease;
    white-space: nowrap;
  }

  .builder-primary-btn {
    color: #fff;
    background: var(--builder-blue);
    border-color: var(--builder-blue);
  }

  .builder-primary-btn:hover {
    color: #fff;
    background: var(--builder-blue-dark);
    border-color: var(--builder-blue-dark);
  }

  .builder-secondary-btn {
    color: var(--builder-blue);
    background: #fff;
    border-color: #d8e4f1;
  }

  .builder-secondary-btn:hover {
    color: #fff;
    background: var(--builder-blue);
    border-color: var(--builder-blue);
  }

  .builder-shell {
    display: grid;
    grid-template-columns: 280px minmax(0, 1fr) 330px;
    gap: 16px;
    align-items: start;
  }

  .builder-card {
    border: 1px solid var(--builder-border);
    border-radius: 14px;
    background: #fff;
    box-shadow: 0 10px 24px rgba(8, 66, 143, .06);
  }

  .builder-card-header {
    padding: 14px;
    border-bottom: 1px solid #edf2f7;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
  }

  .builder-card-title {
    margin: 0;
    color: var(--builder-text);
    font-weight: 800;
    font-size: .92rem;
  }

  .builder-card-subtitle {
    margin: 3px 0 0;
    color: var(--builder-muted);
    font-size: .74rem;
    line-height: 1.35;
  }

  .builder-panel {
    position: sticky;
    top: 92px;
    max-height: calc(100vh - 120px);
    overflow: hidden;
    display: flex;
    flex-direction: column;
  }

  .builder-panel-body {
    padding: 12px;
    overflow-y: auto;
  }

  .builder-panel-body::-webkit-scrollbar,
  .builder-canvas-body::-webkit-scrollbar {
    width: 8px;
  }

  .builder-panel-body::-webkit-scrollbar-thumb,
  .builder-canvas-body::-webkit-scrollbar-thumb {
    background: #c8d4e0;
    border-radius: 999px;
  }

  .field-type-item {
    display: flex;
    gap: 10px;
    align-items: flex-start;
    padding: 10px;
    border: 1px solid #e5edf6;
    border-radius: 11px;
    background: #fff;
    cursor: grab;
    transition: .15s ease;
    margin-bottom: 9px;
    user-select: none;
  }

  .field-type-item:hover {
    border-color: #b9cce2;
    background: #f8fbff;
    transform: translateY(-1px);
    box-shadow: 0 8px 18px rgba(8, 66, 143, .08);
  }

  .field-type-section {
    border-color: #bfd2e8;
    background: #f8fbff;
  }

  .field-type-icon {
    width: 34px;
    height: 34px;
    min-width: 34px;
    border-radius: 10px;
    background: var(--builder-soft);
    color: var(--builder-blue);
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }

  .field-type-name {
    color: var(--builder-text);
    font-size: .8rem;
    font-weight: 850;
    line-height: 1.2;
  }

  .field-type-desc {
    color: var(--builder-muted);
    font-size: .68rem;
    line-height: 1.35;
    margin-top: 2px;
  }

  .repeating-columns-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 8px;
  }

  .repeating-columns-list {
    display: grid;
    gap: 10px;
  }

  .repeating-column-row {
    display: grid;
    gap: 8px;
    min-width: 0;
    padding: 8px;
    border: 1px solid #dbe9f7;
    border-radius: 8px;
    background: #fbfdff;
  }

  .repeating-column-main {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 86px 34px;
    gap: 7px;
    align-items: end;
    min-width: 0;
  }

  .repeating-column-field {
    display: grid;
    gap: 4px;
    min-width: 0;
    margin: 0;
  }

  .repeating-column-field span {
    color: #356b9c;
    font-size: .62rem;
    font-weight: 850;
    line-height: 1.2;
    text-transform: uppercase;
  }

  .repeating-column-source-field.is-hidden {
    display: none;
  }

  @media (max-width: 767.98px) {
    .repeating-column-main {
      grid-template-columns: 1fr;
    }

    .repeating-column-remove {
      justify-self: end;
    }
  }

  .repeating-column-add,
  .repeating-column-remove {
    width: 30px;
    height: 30px;
    border: 1px solid #dbe7f3;
    border-radius: 8px;
    background: #fff;
    color: var(--builder-blue);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: .72rem;
  }

  .repeating-column-add:hover,
  .repeating-column-remove:hover {
    border-color: var(--builder-blue);
    background: var(--builder-soft);
  }

  .repeating-column-add:disabled,
  .repeating-column-remove:disabled {
    cursor: not-allowed;
    opacity: .5;
  }

  .builder-canvas {
    min-height: calc(100vh - 140px);
    overflow: hidden;
    display: flex;
    flex-direction: column;
  }

  .builder-canvas-body {
    padding: 14px;
    overflow-y: auto;
    min-height: 600px;
    background:
      radial-gradient(circle at top left, rgba(8, 66, 143, .07), transparent 26rem),
      var(--builder-bg);
  }

  .canvas-toolbar {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
  }

  .field-count-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    min-height: 26px;
    padding: 4px 9px;
    border-radius: 999px;
    border: 1px solid #dbe7f3;
    background: #f8fbff;
    color: var(--builder-blue);
    font-size: .7rem;
    font-weight: 800;
  }

  .canvas-drop-zone {
    min-height: 520px;
    border: 2px dashed #c9dcf2;
    border-radius: 16px;
    padding: 14px;
    transition: .15s ease;
  }

  .canvas-drop-zone.is-dragover {
    border-color: var(--builder-blue);
    background: rgba(8, 66, 143, .04);
  }

  .canvas-empty {
    min-height: 460px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    color: var(--builder-muted);
    flex-direction: column;
  }

  .canvas-empty-icon {
    width: 58px;
    height: 58px;
    border-radius: 18px;
    background: #fff;
    border: 1px solid #dbe7f3;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: var(--builder-blue);
    font-size: 1.3rem;
    margin-bottom: 14px;
    box-shadow: 0 10px 22px rgba(8, 66, 143, .08);
  }

  .canvas-empty h6 {
    color: var(--builder-text);
    font-weight: 800;
    margin-bottom: 4px;
  }

  .canvas-empty p {
    max-width: 420px;
    font-size: .84rem;
    line-height: 1.45;
    margin-bottom: 0;
  }

  .builder-section {
    margin-bottom: 14px;
    border: 1px solid #dbe7f3;
    border-radius: 15px;
    background: #ffffff;
    box-shadow: 0 8px 20px rgba(15, 23, 42, .04);
    overflow: hidden;
    transition: .15s ease;
  }

  .builder-section:hover {
    border-color: #bfd2e8;
  }

  .builder-section.is-selected {
    border-color: var(--builder-blue);
    box-shadow: 0 0 0 3px rgba(8, 66, 143, .10);
  }

  .builder-section.is-inactive {
    opacity: .6;
  }

  .builder-section-header {
    display: grid;
    grid-template-columns: 40px minmax(0, 1fr) auto;
    gap: 12px;
    align-items: flex-start;
    padding: 13px;
    border-bottom: 1px solid #edf2f7;
    cursor: pointer;
  }

  .builder-section-icon {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: #f1f7ff;
    color: var(--builder-blue);
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }

  .builder-section-title {
    color: var(--builder-text);
    font-size: .94rem;
    font-weight: 850;
    line-height: 1.35;
    margin-bottom: 2px;
  }

  .builder-section-help {
    color: var(--builder-muted);
    font-size: .72rem;
    line-height: 1.4;
    margin-top: 2px;
  }

  .builder-section-meta,
  .canvas-field-meta {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
    margin-top: 7px;
  }

  .section-field-list {
    display: grid;
    grid-template-columns: repeat(12, minmax(0, 1fr));
    grid-auto-rows: auto;
    gap: 10px;
    padding: 11px;
    min-height: 82px;
    background: #f8fbff;
    transition: .15s ease;
  }

  .section-field-list.is-dragover {
    background: #eef7ff;
    box-shadow: inset 0 0 0 2px rgba(8, 66, 143, .12);
  }

  .section-empty {
    grid-column: 1 / -1;
    padding: 16px;
    border: 1px dashed #c9dcf2;
    border-radius: 12px;
    background: #fff;
    color: var(--builder-muted);
    font-size: .78rem;
    text-align: center;
  }

  .section-slot-placeholder {
    min-height: 96px;
    border: 1px dashed #c9dcf2;
    border-radius: 13px;
    background: rgba(255, 255, 255, .6);
    color: #7b8fa8;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 3px;
    font-size: .66rem;
    font-weight: 800;
    text-align: center;
    transition: .15s ease;
  }

  .section-slot-placeholder strong {
    color: var(--builder-blue);
    font-size: .7rem;
  }

  .section-slot-placeholder.is-dragover {
    border-color: var(--builder-blue);
    background: #eef7ff;
    box-shadow: inset 0 0 0 2px rgba(8, 66, 143, .12);
  }

  .section-row-drop-target {
    justify-self: stretch;
    min-height: 34px;
    margin: 0;
    border: 1px dashed #c9dcf2;
    border-radius: 10px;
    background: #fff;
    color: #6c7f96;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .62rem;
    font-weight: 850;
    opacity: .72;
    transition: .15s ease;
  }

  .section-row-drop-target:hover,
  .section-row-drop-target.is-dragover {
    opacity: 1;
    border-color: var(--builder-blue);
    background: #eef7ff;
    color: var(--builder-blue);
    box-shadow: 0 8px 18px rgba(8, 66, 143, .08);
  }

  .canvas-field {
    display: grid;
    grid-template-columns: 32px minmax(0, 1fr);
    gap: 9px;
    padding: 12px;
    min-width: 0;
    min-height: 150px;
    height: 100%;
    border: 1px solid #e2e8f0;
    border-radius: 13px;
    background: #ffffff;
    cursor: pointer;
    transition: .15s ease;
  }

  .canvas-field:hover {
    border-color: #cbd5e1;
    box-shadow: 0 8px 20px rgba(15, 23, 42, .06);
  }

  .canvas-field.is-selected {
    border-color: var(--builder-blue);
    box-shadow: 0 0 0 3px rgba(8, 66, 143, .10);
  }

  .canvas-field.is-inactive {
    opacity: .55;
    background: #f8fafc;
  }

  .canvas-field-icon {
    width: 32px;
    height: 32px;
    border-radius: 11px;
    background: #f1f7ff;
    color: var(--builder-blue);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: .9rem;
    cursor: grab;
  }

  .canvas-field-main {
    min-width: 0;
  }

  .canvas-field-header {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
    margin-bottom: 5px;
  }

  .canvas-field-label {
    color: var(--builder-text);
    font-size: .86rem;
    font-weight: 800;
    line-height: 1.35;
    margin-bottom: 2px;
    word-break: break-word;
  }

  .canvas-field-help {
    display: block;
    color: var(--builder-muted);
    font-size: .71rem;
    line-height: 1.4;
    margin-top: 2px;
  }

  .canvas-field-type {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #64748b;
    font-size: .68rem;
    font-weight: 700;
    min-width: 0;
  }

  .mini-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    min-height: 21px;
    padding: 3px 7px;
    border-radius: 999px;
    background: #f8fafc;
    color: #64748b;
    font-size: .64rem;
    font-weight: 750;
    line-height: 1;
    border: 1px solid #e2e8f0;
  }

  .mini-badge.is-blue {
    background: #f1f7ff;
    color: var(--builder-blue);
    border-color: #dbeafe;
  }

  .mini-badge.is-green {
    background: #f0fdf4;
    color: #15803d;
    border-color: #dcfce7;
  }

  .mini-badge.is-gray {
    background: #f8fafc;
    color: #64748b;
    border-color: #e2e8f0;
  }

  .mini-badge.is-red {
    background: #fff7f7;
    color: #b91c1c;
    border-color: #fee2e2;
  }

  .canvas-actions {
    display: flex;
    flex-direction: column;
    gap: 6px;
    align-items: flex-end;
  }

  .canvas-field .canvas-actions {
    grid-column: 1 / -1;
    flex-direction: row;
    align-items: center;
    justify-content: flex-end;
  }

  .canvas-field.is-full-row .move-left,
  .canvas-field.is-full-row .move-right {
    display: none;
  }

  .canvas-icon-btn {
    width: 28px;
    height: 28px;
    border: 1px solid #e2e8f0;
    border-radius: 9px;
    background: #fff;
    color: #64748b;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: .15s ease;
  }

  .canvas-icon-btn:hover {
    background: #f8fafc;
    color: var(--builder-blue);
    border-color: #cbd5e1;
  }

  .settings-empty {
    padding: 38px 16px;
    text-align: center;
    color: var(--builder-muted);
  }

  .settings-empty-icon {
    width: 54px;
    height: 54px;
    border-radius: 17px;
    background: #f8fbff;
    border: 1px solid #dbe7f3;
    color: var(--builder-blue);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    margin-bottom: 12px;
  }

  .settings-section-title {
    color: var(--builder-text);
    font-size: .72rem;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: .04em;
    margin-bottom: 10px;
  }

  .field-help-text {
    display: block;
    margin-top: 4px;
    color: #6c7f96;
    font-size: .7rem;
    line-height: 1.35;
  }

  .form-floating-actions {
    position: fixed;
    top: 50%;
    right: 22px;
    transform: translateY(-50%);
    z-index: 1040;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 12px;
    pointer-events: none;
  }

  .form-floating-action {
    display: inline-flex;
    align-items: center;
    justify-content: flex-start;
    gap: 10px;
    width: 52px;
    min-height: 52px;
    padding: 0 16px;
    overflow: hidden;
    border: 1px solid transparent;
    border-radius: 999px;
    background: var(--builder-blue);
    color: #fff;
    font-size: .78rem;
    font-weight: 800;
    line-height: 1;
    white-space: nowrap;
    box-shadow: 0 12px 28px rgba(8, 66, 143, .24);
    transition: width .18s ease, background .18s ease, border-color .18s ease, transform .18s ease;
    pointer-events: auto;
  }

  .form-floating-action i {
    width: 18px;
    min-width: 18px;
    font-size: 1rem;
    text-align: center;
  }

  .form-floating-action span {
    opacity: 0;
    transition: opacity .12s ease;
  }

  .form-floating-action:hover,
  .form-floating-action:focus {
    width: 154px;
    background: #05306f;
    color: #fff;
    transform: translateX(-3px);
  }

  .form-floating-action:hover span {
    opacity: 1;
  }

  .form-floating-action.is-secondary {
    background: #f8b817;
    border-color: #f8b817;
    color: #073671;
    box-shadow: 0 12px 28px rgba(248, 184, 23, .28);
  }

  .form-floating-action.is-secondary:hover,
  .form-floating-action.is-secondary:focus {
    background: #e0a200;
    border-color: #e0a200;
    color: #073671;
  }

  @media (max-width: 1439.98px) {
    .builder-shell {
      grid-template-columns: 240px minmax(0, 1fr) 300px;
      gap: 12px;
    }

    .section-field-list {
      gap: 8px;
      padding: 9px;
    }

    .canvas-field {
      grid-template-columns: minmax(0, 1fr);
      min-height: 124px;
      padding: 10px;
      gap: 7px;
    }

    .canvas-field-icon {
      display: none;
    }

    .canvas-field-header {
      gap: 6px;
      margin-bottom: 2px;
    }

    .canvas-field-label {
      font-size: .78rem;
      line-height: 1.28;
    }

    .canvas-field-help {
      font-size: .66rem;
      line-height: 1.3;
    }

    .canvas-field-type,
    .mini-badge {
      font-size: .6rem;
    }

    .canvas-field-meta {
      gap: 4px;
      margin-top: 5px;
    }

    .mini-badge {
      min-height: 18px;
      padding: 2px 5px;
    }

    .canvas-field .canvas-actions {
      gap: 4px;
    }

    .canvas-icon-btn {
      width: 24px;
      height: 24px;
      border-radius: 7px;
      font-size: .7rem;
    }

    .section-slot-placeholder {
      min-height: 76px;
      font-size: .6rem;
    }
  }

  @media (max-width: 1199.98px) {
    .builder-shell {
      grid-template-columns: 250px minmax(0, 1fr);
    }

    .settings-panel {
      grid-column: 1 / -1;
      position: static;
      max-height: none;
    }

    .builder-details-grid {
      grid-template-columns: 1fr 1fr;
    }

    .builder-field-title {
      grid-column: 1 / -1;
      order: -1;
    }

    .section-field-list {
      grid-template-columns: repeat(12, minmax(0, 1fr));
    }
  }

  @media (max-width: 767.98px) {
    .builder-shell {
      grid-template-columns: 1fr;
    }

    .builder-panel {
      position: static;
      max-height: none;
    }

    .builder-section-header,
    .canvas-field {
      grid-template-columns: 40px minmax(0, 1fr);
    }

    .section-field-list {
      grid-template-columns: 1fr;
    }

    .canvas-field,
    .section-slot-placeholder,
    .section-row-drop-target {
      grid-column: 1 !important;
      grid-row: auto !important;
    }

    .canvas-actions {
      grid-column: 1 / -1;
      flex-direction: row;
      justify-content: flex-end;
    }

    .builder-details-header,
    .builder-details-footer {
      flex-direction: column;
      align-items: stretch;
    }

    .builder-details-grid {
      grid-template-columns: 1fr;
    }

    .builder-details-actions {
      justify-content: stretch;
    }

    .builder-primary-btn,
    .builder-secondary-btn {
      flex: 1;
    }
  }

  /* Sticky field settings action bar */
  .settings-panel {
    padding: 0;
  }

  .settings-panel-header {
    flex: 0 0 auto;
  }

  .settings-panel-scroll {
    flex: 1 1 auto;
    min-height: 0;
    overflow-y: auto;
    padding: 12px;
  }

  .settings-selected-badge {
    border: 1px solid #cfe0f4;
    border-radius: 999px;
    background: #f4f9ff;
    color: var(--builder-blue);
    font-size: .64rem;
    font-weight: 900;
    line-height: 1;
    padding: 6px 8px;
    white-space: nowrap;
  }

  .settings-group-card {
    border: 1px solid #e2edf7;
    border-radius: 12px;
    background: #fbfdff;
    padding: 11px;
  }

  .builder-setting-input {
    min-height: 34px !important;
    border-radius: 9px !important;
    border-color: #d8e4f1 !important;
    background: #fff !important;
    font-size: .78rem !important;
  }

  textarea.builder-setting-input {
    min-height: 76px !important;
    height: auto !important;
    resize: vertical;
  }

  .settings-radio-row {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 8px;
  }

  .settings-radio-pill {
    margin: 0;
    min-height: 32px;
    display: flex;
    align-items: center;
    gap: 7px;
    border: 1px solid #d8e4f1;
    border-radius: 9px;
    background: #fff;
    padding: 6px 9px;
    color: var(--builder-text);
    font-size: .74rem;
    font-weight: 800;
    cursor: pointer;
  }

  .settings-radio-pill input {
    margin: 0;
  }

  .settings-sticky-actions {
    flex: 0 0 auto;
    border-top: 1px solid #e2edf7;
    background: rgba(255, 255, 255, .96);
    backdrop-filter: blur(10px);
    padding: 10px;
    box-shadow: 0 -8px 18px rgba(8, 66, 143, .05);
  }

  .settings-action-note {
    display: flex;
    align-items: center;
    gap: 6px;
    min-height: 22px;
    margin-bottom: 8px;
    color: var(--builder-muted);
    font-size: .68rem;
    font-weight: 750;
    line-height: 1.3;
  }

  .settings-action-note i {
    color: var(--builder-blue);
  }

  .settings-action-buttons {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
  }

  .settings-save-btn,
  .settings-danger-btn {
    min-height: 34px;
    border-radius: 9px;
    border: 1px solid transparent;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    font-size: .74rem;
    font-weight: 900;
    transition: .15s ease;
  }

  .settings-save-btn {
    background: var(--builder-blue);
    border-color: var(--builder-blue);
    color: #fff;
  }

  .settings-save-btn:hover:not(:disabled) {
    background: var(--builder-blue-dark);
    border-color: var(--builder-blue-dark);
    color: #fff;
  }

  .settings-danger-btn {
    background: #fff;
    border-color: #fecaca;
    color: #b91c1c;
  }

  .settings-danger-btn:hover:not(:disabled) {
    background: #fff1f2;
    border-color: #fca5a5;
    color: #991b1b;
  }

  .settings-save-btn:disabled,
  .settings-danger-btn:disabled {
    cursor: not-allowed;
    opacity: .45;
  }

  /*
  |--------------------------------------------------------------------------
  | DEPDev / UPLIFT theme refresh
  |--------------------------------------------------------------------------
  */

  .builder-page {
    --builder-blue: #08428f;
    --builder-blue-2: #145fbd;
    --builder-blue-3: #2d7bd9;
    --builder-blue-dark: #05306f;
    --builder-gold: #f8b817;
    --builder-bg: #f7fbff;
    --builder-border: #c9dcf2;
    --builder-soft: #e6f3ff;
    --builder-text: #16345c;
    --builder-muted: #55769a;
    color: var(--builder-text);
  }

  .builder-details-card,
  .builder-card {
    border: 1px solid var(--builder-border);
    border-radius: 8px;
    background: #fff;
    box-shadow: 0 10px 26px rgba(8, 66, 143, .08);
    overflow: hidden;
  }

  .builder-details-card {
    overflow: visible;
  }

  .builder-details-header,
  .builder-card-header {
    border-bottom: 1px solid #dbe9f7;
    background: linear-gradient(135deg, #ffffff 0%, #eef7ff 100%);
  }

  .builder-details-header {
    padding: 18px;
  }

  .builder-card-header {
    padding: 16px;
  }

  .builder-details-heading {
    gap: 12px;
  }

  .builder-details-icon,
  .field-type-icon,
  .builder-section-icon,
  .canvas-field-icon,
  .settings-empty-icon {
    border-radius: 8px;
    background: linear-gradient(135deg, var(--builder-blue) 0%, var(--builder-blue-3) 100%);
    color: #fff;
    box-shadow: inset 0 -3px 0 rgba(0, 0, 0, .08);
  }

  .builder-details-icon {
    width: 42px;
    height: 42px;
    min-width: 42px;
    font-size: 1rem;
  }

  .builder-details-title,
  .builder-card-title,
  .builder-section-title,
  .canvas-field-label,
  .settings-empty h6 {
    color: var(--builder-blue-dark);
  }

  .builder-details-title {
    font-size: 1rem;
    line-height: 1.3;
  }

  .builder-details-subtitle,
  .builder-card-subtitle,
  .field-type-desc,
  .builder-section-help,
  .canvas-field-help,
  .field-help-text,
  .settings-empty p,
  .settings-action-note {
    color: var(--builder-muted);
  }

  .builder-details-subtitle,
  .builder-card-subtitle {
    font-size: .78rem;
    line-height: 1.45;
  }

  .builder-details-status {
    min-height: 30px;
    padding: 7px 14px;
    font-size: .74rem;
    font-weight: 800;
    text-transform: uppercase;
  }

  .builder-details-status.is-active {
    color: #12643a;
    background: #e8f7ef;
    border-color: #9bd7b4;
  }

  .builder-details-status.is-inactive {
    color: #6c757d;
    background: #f1f3f5;
    border-color: #d9e0e7;
  }

  .builder-details-body {
    padding: 16px 18px;
  }

  .builder-details-grid {
    gap: 14px;
  }

  .builder-field-label,
  .settings-group-card label {
    color: var(--builder-blue-dark);
    font-size: .78rem;
    font-weight: 800;
  }

  .builder-compact-input,
  .builder-setting-input {
    min-height: 43px !important;
    height: 43px !important;
    border-radius: 8px !important;
    border: 1px solid #cfddec !important;
    background-color: #fff !important;
    color: var(--builder-text) !important;
    font-size: .82rem !important;
    font-weight: 700;
    padding: 9px 12px !important;
  }

  textarea.builder-setting-input {
    min-height: 112px !important;
    height: auto !important;
  }

  .builder-compact-input:focus,
  .builder-setting-input:focus {
    border-color: var(--builder-blue-3) !important;
    box-shadow: 0 0 0 3px rgba(45, 123, 217, .12) !important;
  }

  .agency-tomselect.ts-wrapper,
  .agency-tomselect .ts-control {
    min-height: 43px !important;
    height: 43px !important;
  }

  .agency-tomselect .ts-control {
    align-items: center !important;
    padding: 0 40px 0 12px !important;
    border-radius: 8px !important;
    border-color: #cfddec !important;
    background: #fff !important;
    color: var(--builder-text) !important;
    font-size: .82rem !important;
    line-height: 41px !important;
  }

  .agency-tomselect .ts-control .item,
  .agency-tomselect .ts-control > input {
    height: 41px !important;
    line-height: 41px !important;
    font-size: .82rem !important;
  }

  .agency-tomselect.focus .ts-control,
  .agency-tomselect.dropdown-active .ts-control {
    border-color: var(--builder-blue-3) !important;
    box-shadow: 0 0 0 3px rgba(45, 123, 217, .12) !important;
  }

  .builder-status-toggle {
    gap: 8px;
  }

  .builder-status-option span,
  .settings-radio-pill {
    min-height: 43px;
    border-radius: 8px;
    border-color: #cfddec;
    background: #fff;
    color: var(--builder-text);
    font-size: .8rem;
  }

  .builder-status-option input:checked + span {
    color: var(--builder-blue);
    background: #e6f3ff;
    border-color: #8dbdeb;
    box-shadow: inset 0 0 0 1px rgba(8, 66, 143, .08);
  }

  .builder-details-footer {
    padding: 14px 18px;
    background: #fbfdff;
    border-top: 1px solid #dbe9f7;
  }

  .builder-details-meta span,
  .field-count-pill,
  .mini-badge,
  .settings-selected-badge {
    border-color: #dbe9f7;
    background: #f4f9ff;
    color: var(--builder-blue);
    font-size: .72rem;
    font-weight: 800;
  }

  .builder-primary-btn,
  .builder-secondary-btn,
  .settings-save-btn,
  .settings-danger-btn {
    min-height: 38px;
    border-radius: 8px;
    padding: 8px 14px;
    font-size: .78rem;
    font-weight: 800;
  }

  .builder-primary-btn,
  .settings-save-btn {
    background: var(--builder-blue);
    border-color: var(--builder-blue);
    color: #fff;
    box-shadow: 0 8px 18px rgba(8, 66, 143, .18);
  }

  .builder-primary-btn:hover,
  .settings-save-btn:hover:not(:disabled) {
    background: var(--builder-blue-dark);
    border-color: var(--builder-blue-dark);
    color: #fff;
  }

  .builder-secondary-btn {
    color: var(--builder-blue);
    background: #fff;
    border-color: #cfddec;
  }

  .builder-secondary-btn:hover {
    color: #fff;
    background: var(--builder-blue);
    border-color: var(--builder-blue);
  }

  .builder-shell {
    gap: 18px;
  }

  .builder-panel {
    top: 100px;
    max-height: calc(100vh - 132px);
  }

  .builder-panel-body,
  .settings-panel-scroll {
    padding: 14px;
  }

  .field-type-item {
    align-items: center;
    gap: 12px;
    padding: 12px;
    border-color: #dbe9f7;
    border-radius: 8px;
    background: #fff;
    margin-bottom: 10px;
  }

  .field-type-item:hover {
    border-color: #8dbdeb;
    background: #f7fbff;
    box-shadow: 0 8px 18px rgba(8, 66, 143, .08);
  }

  .field-type-section {
    border-color: #8dbdeb;
    background: #eef7ff;
  }

  .field-type-icon {
    width: 38px;
    height: 38px;
    min-width: 38px;
  }

  .field-type-name {
    color: var(--builder-blue-dark);
    font-size: .86rem;
  }

  .field-type-desc {
    font-size: .72rem;
  }

  .builder-canvas {
    min-height: calc(100vh - 148px);
  }

  .builder-canvas-body {
    padding: 16px;
    background: #fbfdff;
  }

  .canvas-drop-zone {
    min-height: 560px;
    border: 1px dashed var(--builder-border);
    border-radius: 8px;
    background: #f7fbff;
    padding: 14px;
  }

  .canvas-drop-zone.is-dragover {
    border-color: var(--builder-blue-3);
    background: #eef7ff;
    box-shadow: inset 0 0 0 2px rgba(45, 123, 217, .12);
  }

  .canvas-empty-icon {
    width: 56px;
    height: 56px;
  }

  .builder-section {
    border: 1px solid var(--builder-border);
    border-radius: 8px;
    background: #f7fbff;
    box-shadow: 0 8px 18px rgba(8, 66, 143, .06);
    margin-bottom: 16px;
  }

  .builder-section:hover {
    border-color: #8dbdeb;
  }

  .builder-section.is-selected {
    border-color: var(--builder-blue-3);
    box-shadow: 0 0 0 3px rgba(45, 123, 217, .12);
  }

  .builder-section-header {
    grid-template-columns: 42px minmax(0, 1fr) auto;
    padding: 14px;
    border-bottom: 1px solid #dbe9f7;
    background: linear-gradient(135deg, #ffffff 0%, #eef7ff 100%);
  }

  .builder-section-icon {
    width: 38px;
    height: 38px;
  }

  .builder-section-title {
    font-size: .94rem;
  }

  .section-field-list {
    gap: 14px;
    padding: 14px;
    background: #f7fbff;
  }

  .section-empty,
  .section-slot-placeholder,
  .section-row-drop-target {
    border-color: var(--builder-border);
    border-radius: 8px;
    background: #fff;
  }

  .section-slot-placeholder {
    min-height: 104px;
  }

  .canvas-field {
    grid-template-columns: 38px minmax(0, 1fr);
    gap: 12px;
    min-height: 154px;
    padding: 14px;
    border: 1px solid var(--builder-border);
    border-radius: 8px;
    background: #fff;
    box-shadow: 0 8px 18px rgba(8, 66, 143, .06);
  }

  .canvas-field:hover {
    border-color: #8dbdeb;
    box-shadow: 0 10px 22px rgba(8, 66, 143, .10);
  }

  .canvas-field.is-selected {
    border-color: var(--builder-blue-3);
    box-shadow: 0 0 0 3px rgba(45, 123, 217, .12);
  }

  .canvas-field-icon {
    width: 36px;
    height: 36px;
  }

  .canvas-field-label {
    font-size: .86rem;
  }

  .canvas-field-help {
    font-size: .74rem;
  }

  .canvas-field-type {
    color: #356b9c;
    font-size: .72rem;
    font-weight: 800;
    text-transform: uppercase;
  }

  .mini-badge.is-blue {
    background: #e6f3ff;
    color: var(--builder-blue);
    border-color: #c9dcf2;
  }

  .mini-badge.is-green {
    background: #e8f7ef;
    color: #12643a;
    border-color: #9bd7b4;
  }

  .mini-badge.is-red {
    background: #fff1f2;
    color: #b91c1c;
    border-color: #fecaca;
  }

  .canvas-icon-btn,
  .repeating-column-add,
  .repeating-column-remove {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border-color: #cfddec;
    color: var(--builder-blue);
  }

  .canvas-icon-btn:hover,
  .repeating-column-add:hover,
  .repeating-column-remove:hover {
    border-color: var(--builder-blue-3);
    background: #eef7ff;
    color: var(--builder-blue);
  }

  .settings-group-card {
    border-color: #dbe9f7;
    border-radius: 8px;
    background: #fbfdff;
    padding: 14px;
  }

  .settings-section-title {
    color: #3b78aa;
    font-size: .72rem;
    font-weight: 800;
  }

  .settings-sticky-actions {
    border-top-color: #dbe9f7;
    padding: 14px;
    box-shadow: 0 -8px 18px rgba(8, 66, 143, .06);
  }

  .settings-action-buttons {
    gap: 10px;
  }

  .settings-danger-btn {
    background: #fff;
    border-color: #fecaca;
    color: #b91c1c;
  }

  .settings-danger-btn:hover:not(:disabled) {
    background: #fff1f2;
    border-color: #fca5a5;
    color: #991b1b;
  }

  @media (max-width: 1439.98px) {
    .canvas-field {
      min-height: 136px;
      padding: 12px;
    }
  }

  body.form-builder-sidenav-page.g-sidenav-pinned .builder-shell {
    grid-template-columns: 280px minmax(0, 1fr);
    grid-template-areas:
      "field-types canvas"
      "settings canvas";
    align-items: start;
  }

  body.form-builder-sidenav-page.g-sidenav-pinned .builder-panel:not(.settings-panel) {
    grid-area: field-types;
    position: static;
    max-height: none;
    overflow: visible;
  }

  body.form-builder-sidenav-page.g-sidenav-pinned .builder-panel:not(.settings-panel) .builder-panel-body {
    overflow: visible;
  }

  body.form-builder-sidenav-page.g-sidenav-pinned .builder-canvas {
    grid-area: canvas;
  }

  body.form-builder-sidenav-page.g-sidenav-pinned .settings-panel {
    grid-area: settings;
    position: static;
    max-height: none;
  }

  body.form-builder-sidenav-page.g-sidenav-pinned .settings-panel-scroll {
    max-height: 460px;
  }

  body.form-builder-sidenav-page.g-sidenav-pinned .builder-section-header {
    grid-template-columns: 42px minmax(0, 1fr);
  }

  .builder-shell {
    grid-template-columns: minmax(280px, 340px) minmax(0, 1fr);
    grid-template-areas: "sidebar canvas";
  }

  .builder-sidebar {
    grid-area: sidebar;
    position: sticky;
    top: 100px;
    align-self: start;
    display: grid;
    gap: 14px;
    max-height: calc(100vh - 132px);
    overflow-y: auto;
    padding-right: 2px;
  }

  .builder-sidebar::-webkit-scrollbar {
    width: 8px;
  }

  .builder-sidebar::-webkit-scrollbar-thumb {
    background: #c8d4e0;
    border-radius: 999px;
  }

  .builder-sidebar .builder-panel {
    grid-area: auto;
    position: static;
    top: auto;
    max-height: none;
    overflow: hidden;
  }

  .builder-sidebar .builder-panel-body,
  .builder-sidebar .settings-panel-scroll {
    overflow: visible;
  }

  .builder-canvas {
    grid-area: canvas;
  }

  .settings-header-actions {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    flex: 0 0 auto;
  }

  .settings-collapse-toggle {
    width: 32px;
    height: 32px;
    border: 1px solid #dbe7f3;
    border-radius: 8px;
    background: #fff;
    color: var(--builder-blue);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: .15s ease;
  }

  .settings-collapse-toggle:hover,
  .settings-collapse-toggle:focus {
    border-color: var(--builder-blue);
    background: var(--builder-soft);
    outline: none;
  }

  .settings-panel.is-collapsed .settings-panel-header {
    border-bottom: 0;
  }

  .field-types-panel .field-type-item:last-child {
    margin-bottom: 0;
  }

  body.form-builder-sidenav-page.g-sidenav-pinned .builder-shell {
    grid-template-columns: minmax(280px, 340px) minmax(0, 1fr);
    grid-template-areas: "sidebar canvas";
  }

  body.form-builder-sidenav-page.g-sidenav-pinned .builder-sidebar {
    grid-area: sidebar;
    position: sticky;
    top: 100px;
    max-height: calc(100vh - 132px);
    overflow-y: auto;
  }

  body.form-builder-sidenav-page.g-sidenav-pinned .builder-canvas {
    grid-area: canvas;
  }

  body.form-builder-sidenav-page.g-sidenav-pinned .settings-panel,
  body.form-builder-sidenav-page.g-sidenav-pinned .builder-panel:not(.settings-panel) {
    grid-area: auto;
    position: static;
    max-height: none;
    overflow: hidden;
  }

  body.form-builder-sidenav-page.g-sidenav-pinned .settings-panel-scroll {
    max-height: none;
    overflow: visible;
  }

  @media (max-width: 1199.98px) {
    .builder-shell,
    body.form-builder-sidenav-page.g-sidenav-pinned .builder-shell {
      grid-template-columns: minmax(250px, 300px) minmax(0, 1fr);
      grid-template-areas: "sidebar canvas";
    }

    .builder-sidebar,
    body.form-builder-sidenav-page.g-sidenav-pinned .builder-sidebar {
      position: sticky;
      top: 88px;
      max-height: calc(100vh - 112px);
    }

    .settings-panel {
      grid-column: auto;
    }
  }

  @media (max-width: 767.98px) {
    .builder-shell,
    body.form-builder-sidenav-page.g-sidenav-pinned .builder-shell {
      grid-template-columns: 1fr;
      grid-template-areas:
        "sidebar"
        "canvas";
    }

    .builder-sidebar,
    body.form-builder-sidenav-page.g-sidenav-pinned .builder-sidebar {
      position: static;
      max-height: none;
      overflow: visible;
      padding-right: 0;
    }
  }

</style>
