<style>
  body .sidenav,
  body #sidenav-main,
  body .navbar-vertical,
  body aside.sidenav {
    display: none !important;
  }

  body.g-sidenav-show .main-content,
  body .main-content {
    margin-left: 0 !important;
    width: 100% !important;
    max-width: 100% !important;
  }

  body.g-sidenav-show {
    overflow-x: hidden;
  }

  :root {
    --preview-blue: #08428f;
    --preview-blue-dark: #06336f;
    --preview-blue-soft: #eef6ff;
    --preview-bg: #f5f8fc;
    --preview-border: #dbe5f1;
    --preview-border-soft: #edf2f7;
    --preview-text: #344767;
    --preview-muted: #64748b;
    --preview-muted-light: #94a3b8;
  }

  .preview-page {
    min-height: 100vh;
    background:
      radial-gradient(circle at top left, rgba(8, 66, 143, .055), transparent 26rem),
      linear-gradient(180deg, #f8fbff 0%, var(--preview-bg) 100%);
  }

  .preview-shell {
    max-width: 1280px;
    margin: 0 auto;
  }

  .preview-card {
    border: 1px solid var(--preview-border);
    border-radius: 10px;
    background: #fff;
    box-shadow: 0 4px 14px rgba(15, 23, 42, .055);
    overflow: hidden;
  }

  .preview-header {
    padding: 10px 12px;
    border-bottom: 1px solid var(--preview-border-soft);
    background: linear-gradient(180deg, #fff 0%, #f9fbff 100%);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
  }

  .preview-title-wrap {
    min-width: 0;
  }

  .preview-title-line {
    display: flex;
    align-items: center;
    gap: 7px;
    flex-wrap: wrap;
    margin-bottom: 2px;
  }

  .preview-title {
    color: var(--preview-text);
    font-size: .92rem;
    font-weight: 850;
    margin: 0;
    line-height: 1.25;
  }

  .preview-agency {
    color: var(--preview-muted);
    font-size: .68rem;
    margin: 0;
    line-height: 1.25;
  }

  .preview-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    min-height: 20px;
    padding: 2px 7px;
    border-radius: 999px;
    background: var(--preview-blue-soft);
    border: 1px solid #d6e8fb;
    color: var(--preview-blue);
    font-size: .62rem;
    font-weight: 850;
    white-space: nowrap;
  }

  .preview-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    min-height: 28px;
    padding: 4px 9px;
    border-radius: 7px;
    border: 1px solid #d7e3f0;
    background: #fff;
    color: var(--preview-blue);
    font-size: .7rem;
    font-weight: 850;
    text-decoration: none;
    transition: .15s ease;
    white-space: nowrap;
  }

  .preview-action:hover {
    color: #fff;
    background: var(--preview-blue);
    border-color: var(--preview-blue);
  }

  .preview-body {
    padding: 10px;
  }

  .preview-section {
    border: 1px solid var(--preview-border);
    border-radius: 9px;
    background: #fff;
    overflow: hidden;
    margin-bottom: 9px;
  }

  .preview-section:last-child {
    margin-bottom: 0;
  }

  .preview-section-head {
    padding: 7px 9px;
    background: #f8fbff;
    border-bottom: 1px solid var(--preview-border-soft);
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .preview-section-icon {
    width: 24px;
    height: 24px;
    min-width: 24px;
    border-radius: 7px;
    background: var(--preview-blue-soft);
    color: var(--preview-blue);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: .68rem;
  }

  .preview-section-title-wrap {
    min-width: 0;
  }

  .preview-section-title {
    color: var(--preview-text);
    font-size: .78rem;
    font-weight: 850;
    margin: 0;
    line-height: 1.25;
  }

  .preview-section-subtitle {
    color: var(--preview-muted);
    font-size: .65rem;
    margin: 1px 0 0;
    line-height: 1.25;
  }

  .preview-table {
    width: 100%;
  }

  .preview-row {
    display: grid;
    grid-template-columns: minmax(160px, 24%) minmax(0, 1fr);
    border-bottom: 1px solid var(--preview-border-soft);
    min-height: 46px;
  }

  .preview-row:last-child {
    border-bottom: 0;
  }

  .preview-info {
    padding: 8px 10px;
    background: #fbfdff;
    border-right: 1px solid var(--preview-border-soft);
  }

  .preview-input-area {
    padding: 8px 10px;
    background: #fff;
  }

  .preview-label {
    color: var(--preview-text);
    font-size: .71rem;
    font-weight: 850;
    line-height: 1.25;
    margin: 0;
  }

  .preview-help {
    color: var(--preview-muted);
    font-size: .63rem;
    line-height: 1.28;
    margin: 2px 0 0;
  }

  .preview-type {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: var(--preview-muted-light);
    font-size: .58rem;
    font-weight: 800;
    margin-top: 4px;
    text-transform: uppercase;
    letter-spacing: .02em;
  }

  .preview-control {
    min-height: 30px;
    border-radius: 7px !important;
    border: 1px solid #d8e4f1 !important;
    background-color: #f9fbfe !important;
    color: #94a3b8 !important;
    font-size: .7rem !important;
    padding: 4px 8px !important;
    box-shadow: none !important;
  }

  textarea.preview-control {
    min-height: 46px;
    resize: none;
  }

  .preview-remarks {
    margin-top: 6px;
  }

  .preview-remarks-label {
    display: block;
    color: var(--preview-muted);
    font-size: .6rem;
    font-weight: 850;
    margin-bottom: 3px;
    text-transform: uppercase;
    letter-spacing: .02em;
  }

  .preview-empty-section {
    padding: 10px;
    color: var(--preview-muted);
    font-size: .7rem;
    text-align: center;
    background: #fbfdff;
  }

  .preview-empty {
    padding: 34px 16px;
    text-align: center;
    color: var(--preview-muted);
  }

  .preview-empty-icon {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    background: var(--preview-blue-soft);
    color: var(--preview-blue);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 8px;
    font-size: 1rem;
  }

  .preview-empty h6 {
    color: var(--preview-text);
    font-size: .85rem;
    font-weight: 850;
    margin-bottom: 2px;
  }

  .preview-empty p {
    font-size: .72rem;
    margin: 0;
  }

  @media (max-width: 767.98px) {
    .preview-header {
      align-items: stretch;
      flex-direction: column;
    }

    .preview-action {
      width: 100%;
    }

    .preview-row {
      grid-template-columns: 1fr;
    }

    .preview-info {
      border-right: 0;
      border-bottom: 1px solid var(--preview-border-soft);
    }
  }
</style>