<style>
.submission-shell {
  --uplift-blue: #08428f;
  --uplift-blue-2: #145fbd;
  --uplift-blue-3: #2d7bd9;
  --uplift-navy: #05306f;
  --uplift-surface: #f4f9ff;
  --uplift-line: #c9dcf2;
  --uplift-gold: #f8b817;
  color: #16345c;
}

.submission-summary {
  border: 1px solid var(--uplift-line);
  border-radius: 8px;
  padding: 18px;
  background: linear-gradient(135deg, #f7fbff 0%, #e6f3ff 100%);
  box-shadow: 0 8px 22px rgba(8, 66, 143, .08);
}

.submission-summary-icon,
.submission-field-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 42px;
  height: 42px;
  border-radius: 8px;
  background: linear-gradient(135deg, var(--uplift-blue) 0%, var(--uplift-blue-3) 100%);
  color: #fff;
  box-shadow: inset 0 -3px 0 rgba(0, 0, 0, .08);
  flex: 0 0 auto;
}

.submission-eyebrow {
  color: #3b78aa;
  font-size: .68rem;
  font-weight: 800;
  letter-spacing: .04em;
  text-transform: uppercase;
}

.submission-label {
  color: var(--uplift-navy);
  font-size: .82rem;
  font-weight: 800;
}

.submission-status-panel {
  text-align: left;
}

@media (min-width: 992px) {
  .submission-status-panel {
    text-align: right;
  }
}

.submission-status-badge {
  display: inline-flex;
  align-items: center;
  min-height: 30px;
  padding: 7px 14px;
  border-radius: 999px;
  font-size: .74rem;
  font-weight: 800;
  text-transform: uppercase;
}

.submission-status-badge.is-draft {
  background: #fff7df;
  color: #8a6100;
  border: 1px solid #f1cb64;
}

.submission-status-badge.is-submitted {
  background: #e8f7ef;
  color: #12643a;
  border: 1px solid #9bd7b4;
}

.date-picker-field .form-control {
  min-height: 43px;
}

.date-picker-field.cursor-pointer,
.date-picker-field.cursor-pointer .form-control,
.date-picker-field.cursor-pointer .input-group-text {
  cursor: pointer;
}

.date-picker-field .input-group-text {
  border-right: 0;
  background: #fff;
  color: var(--uplift-blue-2);
}

.date-picker-field .form-control {
  border-left: 0;
  background: #fff;
}

.date-picker-field:focus-within .input-group-text,
.date-picker-field:focus-within .form-control {
  border-color: var(--uplift-blue-3);
  box-shadow: none;
}

.submission-date-picker {
  border-radius: 8px;
  box-shadow: 0 6px 14px rgba(8, 66, 143, .06);
}

.submission-date-picker .input-group-text {
  width: 44px;
  justify-content: center;
  border-color: #cfddec;
  border-radius: 8px 0 0 8px;
  background: linear-gradient(180deg, #ffffff 0%, #edf6ff 100%);
  color: var(--uplift-blue);
}

.submission-date-picker .form-control {
  border-color: #cfddec;
  border-radius: 0 8px 8px 0;
  color: var(--uplift-navy);
  font-weight: 700;
}

.submission-date-picker:hover .input-group-text,
.submission-date-picker:hover .form-control {
  border-color: #8dbdeb;
}

.submission-date-picker:focus-within {
  box-shadow: 0 0 0 3px rgba(45, 123, 217, .12);
}

.submission-grid {
  position: relative;
}

.submission-section {
  border: 1px solid var(--uplift-line);
  border-radius: 8px;
  background: #f7fbff;
  overflow: hidden;
}

.submission-section-header {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 14px;
  border-bottom: 1px solid #dbe9f7;
  background: linear-gradient(135deg, #ffffff 0%, #eef7ff 100%);
}

.submission-section-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  min-width: 36px;
  border-radius: 7px;
  background: #e6f3ff;
  color: var(--uplift-blue);
}

.submission-section-title {
  margin: 0;
  color: var(--uplift-navy);
  font-size: .92rem;
  font-weight: 850;
  line-height: 1.3;
}

.submission-section-subtitle {
  margin: 3px 0 0;
  color: #55769a;
  font-size: .74rem;
  line-height: 1.4;
}

.submission-section .submission-row {
  padding: 14px 14px 0;
}

.submission-section-grid {
  display: grid;
  grid-template-columns: repeat(12, minmax(0, 1fr));
  grid-auto-rows: minmax(0, auto);
  gap: 14px;
  padding: 14px;
}

.submission-field-cell {
  min-width: 0;
}

.submission-section-empty {
  padding: 16px;
  color: #55769a;
  font-size: .8rem;
  text-align: center;
}

.submission-row {
  margin-bottom: 4px;
}

.submission-field {
  height: 100%;
  border: 1px solid var(--uplift-line);
  border-radius: 8px;
  background: #fff;
  overflow: hidden;
  box-shadow: 0 8px 18px rgba(8, 66, 143, .06);
}

.submission-field-header {
  display: flex;
  gap: 12px;
  align-items: flex-start;
  padding: 14px 14px 10px;
  background: linear-gradient(180deg, #f5fbff 0%, #ffffff 100%);
  border-bottom: 1px solid #e5eef8;
}

.submission-field-icon {
  width: 36px;
  height: 36px;
  border-radius: 7px;
  font-size: .9rem;
}

.submission-field-title {
  margin: 0;
  color: var(--uplift-navy);
  font-size: .86rem;
  font-weight: 800;
  line-height: 1.35;
  overflow-wrap: anywhere;
  word-break: break-word;
}

.submission-required {
  display: inline-flex;
  align-items: center;
  margin-left: 4px;
  color: #d3452f;
  font-weight: 900;
}

.submission-field-subtitle {
  margin: 4px 0 0;
  color: #55769a;
  font-size: .74rem;
  line-height: 1.4;
}

.submission-field-body {
  padding: 14px;
}

.submission-field .form-control {
  border-color: #cfddec;
  color: #16345c;
}

.submission-field .form-control:focus {
  border-color: var(--uplift-blue-3);
  box-shadow: 0 0 0 3px rgba(45, 123, 217, .12);
}

.submission-field .form-control:disabled,
.submission-field textarea:disabled {
  background: #f7fbff;
  color: #435f7e;
}

.submission-repeating-group {
  display: grid;
  gap: 10px;
}

.repeating-group-rows {
  display: grid;
  gap: 8px;
}

.repeating-group-header {
  display: grid;
  grid-template-columns: repeat(var(--repeating-column-count, 1), minmax(120px, 1fr));
  gap: 8px;
  padding-right: 42px;
}

.submission-repeating-group[data-readonly="1"] .repeating-group-header {
  padding-right: 0;
}

.repeating-group-header span {
  min-width: 0;
  padding: 0 2px;
  color: #356b9c;
  font-size: .66rem;
  font-weight: 850;
  line-height: 1.25;
}

.repeating-group-row {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 34px;
  gap: 8px;
  align-items: end;
}

.submission-repeating-group[data-readonly="1"] .repeating-group-row {
  grid-template-columns: minmax(0, 1fr);
}

.repeating-group-fields {
  display: grid;
  grid-template-columns: repeat(var(--repeating-column-count, 1), minmax(120px, 1fr));
  gap: 8px;
}

.repeating-group-cell {
  display: grid;
  min-width: 0;
  margin: 0;
}

.repeating-group-remove,
.repeating-group-add {
  border: 1px solid var(--uplift-line);
  border-radius: 8px;
  background: #fff;
  color: var(--uplift-blue);
  font-size: .72rem;
  font-weight: 800;
}

.repeating-group-remove {
  width: 34px;
  height: 34px;
}

.repeating-group-add {
  justify-self: start;
  min-height: 34px;
  padding: 0 12px;
}

.repeating-group-add:hover,
.repeating-group-remove:hover {
  border-color: var(--uplift-blue-2);
  background: #eef7ff;
}

@media (max-width: 767.98px) {
  .repeating-group-row,
  .submission-repeating-group[data-readonly="1"] .repeating-group-row {
    grid-template-columns: 1fr;
  }

  .repeating-group-fields {
    grid-template-columns: 1fr;
  }

  .repeating-group-header {
    display: none;
  }

  .repeating-group-remove {
    justify-self: end;
  }
}

.date-range-total {
  min-height: 43px;
  border: 1px solid var(--uplift-line);
  border-radius: 8px;
  background: #f4f9ff;
  color: var(--uplift-navy);
}

.date-range-total .days-value {
  font-size: 1rem;
  font-weight: 700;
}

.submission-remarks-label {
  display: block;
  color: #356b9c;
  font-size: .74rem;
  font-weight: 800;
  text-transform: uppercase;
}

.submission-remarks-editor {
  min-height: 140px;
  border-color: #cfddec;
}

.submission-remarks-editor .ql-editor {
  min-height: 100px;
}

.submission-actions {
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

.submission-action-btn {
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
  color: #fff;
  font-size: .78rem;
  font-weight: 800;
  line-height: 1;
  white-space: nowrap;
  box-shadow: 0 12px 28px rgba(8, 66, 143, .24);
  transition: width .18s ease, background .18s ease, border-color .18s ease, transform .18s ease;
  pointer-events: auto;
}

.submission-action-btn i {
  width: 18px;
  min-width: 18px;
  font-size: 1rem;
  text-align: center;
}

.submission-action-btn span {
  opacity: 0;
  transition: opacity .12s ease;
}

.submission-action-btn:hover,
.submission-action-btn:focus {
  width: 142px;
  color: #fff;
  transform: translateX(-3px);
}

.submission-action-btn:hover span,
.submission-action-btn:focus span {
  opacity: 1;
}

.submission-action-save {
  background: var(--uplift-blue);
  border-color: var(--uplift-blue);
}

.submission-action-save:hover,
.submission-action-save:focus {
  background: var(--uplift-navy);
  border-color: var(--uplift-navy);
}

.submission-action-submit {
  background: #f8b817;
  border-color: #f8b817;
  color: #073671;
  box-shadow: 0 12px 28px rgba(248, 184, 23, .28);
}

.submission-action-submit:hover,
.submission-action-submit:focus {
  background: #e0a200;
  border-color: #e0a200;
  color: #073671;
}

@media (max-width: 767.98px) {
  .submission-actions {
    top: auto;
    right: 14px;
    bottom: 18px;
    transform: none;
  }

  .submission-section-grid {
    grid-template-columns: 1fr;
  }

  .submission-field-cell {
    grid-column: 1 !important;
    grid-row: auto !important;
  }

  .submission-action-btn,
  .submission-action-btn:hover,
  .submission-action-btn:focus {
    width: 142px;
    transform: none;
  }

  .submission-action-btn span {
    opacity: 1;
  }
}

.submission-page-card {
  border: 1px solid var(--uplift-line);
  border-radius: 8px;
  box-shadow: 0 10px 26px rgba(8, 66, 143, .08);
  overflow: hidden;
}

.submission-page-card > .card-header {
  border-bottom: 1px solid #dbe9f7;
  background: linear-gradient(135deg, #ffffff 0%, #eef7ff 100%);
}

.submission-page-title {
  color: var(--uplift-navy);
  font-weight: 800;
  overflow-wrap: anywhere;
  word-break: break-word;
}

.submission-page-subtitle {
  color: #49739e;
  overflow-wrap: anywhere;
  word-break: break-word;
}

.btn-uplift-submit {
  background: var(--uplift-blue);
  border-color: var(--uplift-blue);
  color: #fff;
  box-shadow: 0 8px 18px rgba(8, 66, 143, .18);
}

.btn-uplift-submit:hover {
  background: var(--uplift-navy);
  border-color: var(--uplift-navy);
  color: #fff;
}

.btn-submission-edit {
  background: var(--uplift-gold);
  border-color: var(--uplift-gold);
  color: #073671;
  box-shadow: 0 8px 18px rgba(248, 184, 23, .28);
}

.btn-submission-edit:hover,
.btn-submission-edit:focus {
  background: #e0a200;
  border-color: #e0a200;
  color: #073671;
}

.flatpickr-calendar {
  position: absolute;
  display: none;
  width: 310px;
  padding: 0;
  border: 1px solid #c9dcf2;
  border-radius: 8px;
  background: #fff;
  box-shadow: 0 18px 42px rgba(8, 66, 143, .18);
  color: #16345c;
  font-size: 14px;
  line-height: 24px;
  opacity: 0;
  visibility: hidden;
  direction: ltr;
  touch-action: manipulation;
  z-index: 99999;
}

.flatpickr-calendar.open {
  display: inline-block;
  opacity: 1;
  visibility: visible;
}

.flatpickr-calendar.arrowTop:before,
.flatpickr-calendar.arrowTop:after,
.flatpickr-calendar.arrowBottom:before,
.flatpickr-calendar.arrowBottom:after {
  display: none;
}

.uplift-calendar .flatpickr-months {
  display: flex;
  align-items: center;
  border-radius: 8px 8px 0 0;
  background: linear-gradient(135deg, #08428f 0%, #145fbd 100%);
}

.uplift-calendar .flatpickr-prev-month,
.uplift-calendar .flatpickr-next-month {
  position: absolute;
  top: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 42px;
  height: 44px;
  color: #fff;
  cursor: pointer;
  z-index: 3;
}

.uplift-calendar .flatpickr-prev-month {
  left: 0;
}

.uplift-calendar .flatpickr-next-month {
  right: 0;
}

.uplift-calendar .flatpickr-prev-month:hover,
.uplift-calendar .flatpickr-next-month:hover {
  color: #f8b817;
}

.uplift-calendar .flatpickr-month {
  position: relative;
  height: 44px;
  flex: 1;
  overflow: hidden;
  color: #fff;
  text-align: center;
}

.uplift-calendar .flatpickr-current-month {
  position: absolute;
  inset: 0 42px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  font-weight: 800;
}

.uplift-calendar .flatpickr-current-month select,
.uplift-calendar .flatpickr-current-month input {
  border: 0;
  background: transparent;
  color: #fff;
  font-weight: 800;
  text-align: center;
}

.uplift-calendar .flatpickr-current-month select option {
  color: #16345c;
}

.uplift-calendar .numInputWrapper {
  position: relative;
  width: 64px;
}

.uplift-calendar .numInputWrapper span {
  display: none;
}

.uplift-calendar .flatpickr-weekdays {
  display: flex;
  align-items: center;
  height: 34px;
  background: #f4f9ff;
  border-bottom: 1px solid #dbe9f7;
}

.uplift-calendar .flatpickr-weekdaycontainer,
.uplift-calendar .dayContainer {
  display: flex;
  flex-wrap: wrap;
  width: 310px;
  min-width: 310px;
  max-width: 310px;
}

.uplift-calendar .flatpickr-weekday {
  width: 14.2857143%;
  color: #08428f;
  font-size: .72rem;
  font-weight: 800;
  text-align: center;
  text-transform: uppercase;
}

.uplift-calendar .flatpickr-days {
  width: 310px;
  overflow: hidden;
}

.uplift-calendar .flatpickr-day {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 14.2857143%;
  max-width: 44px;
  height: 38px;
  margin: 0;
  border: 1px solid transparent;
  border-radius: 6px;
  color: #16345c;
  font-weight: 700;
  cursor: pointer;
}

.uplift-calendar .flatpickr-day.prevMonthDay,
.uplift-calendar .flatpickr-day.nextMonthDay {
  color: #8aa4c0;
}

.uplift-calendar .flatpickr-day.today:not(.selected):not(.startRange):not(.endRange) {
  background: transparent;
  border-color: transparent;
  color: #16345c;
}

.uplift-calendar .flatpickr-day.today.prevMonthDay:not(.selected):not(.startRange):not(.endRange),
.uplift-calendar .flatpickr-day.today.nextMonthDay:not(.selected):not(.startRange):not(.endRange) {
  color: #8aa4c0;
}

.uplift-calendar .flatpickr-day:hover,
.uplift-calendar .flatpickr-day:focus {
  background: #e6f3ff;
  border-color: #8dbdeb;
}

.uplift-calendar .flatpickr-day.selected,
.uplift-calendar .flatpickr-day.startRange,
.uplift-calendar .flatpickr-day.endRange {
  background: #08428f;
  border-color: #08428f;
  color: #fff;
}

.uplift-calendar .flatpickr-day.disabled,
.uplift-calendar .flatpickr-day.disabled:hover {
  background: transparent;
  border-color: transparent;
  color: #c6d3df;
  cursor: not-allowed;
}

</style>
