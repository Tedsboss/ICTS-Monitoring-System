<script src="{{ asset('assets/js/plugins/flatpickr.min.js') }}"></script>

<script>
function openDatePicker(inputId) {
  var input = $('#' + inputId)[0];

  if (!input || input.disabled || input.readOnly) return;

  input.focus();

  if (input._flatpickr) {
    input._flatpickr.open();
    return;
  }

  if (typeof input.showPicker === 'function') {
    try {
      input.showPicker();
    } catch (e) {}
  }
}

function updateDateRangeDays(input) {
  var $input = $(input);
  var $pair = $('#' + $input.data('pair-id'));
  var $target = $('#' + $input.data('days-target'));

  if (!$pair.length || !$target.length || !$input.val() || !$pair.val()) {
    $target.text('-');
    return;
  }

  var startDate = new Date(input.id.indexOf('date_start_') === 0 ? $input.val() : $pair.val());
  var endDate = new Date(input.id.indexOf('date_end_') === 0 ? $input.val() : $pair.val());
  var diff = Math.floor((endDate - startDate) / 86400000) + 1;

  $target.text(diff > 0 ? diff : '-');
}

function syncRepeatingGroup($group) {
  var rows = [];

  $group.find('.repeating-group-row').each(function () {
    var row = {};
    var hasValue = false;

    $(this).find('.repeating-group-column-input').each(function () {
      var columnId = $(this).data('column-id');
      var value = $.trim($(this).val());

      if (!columnId) {
        return;
      }

      row[columnId] = value;

      if (value !== '') {
        hasValue = true;
      }
    });

    if (hasValue) {
      rows.push(row);
    }
  });

  $group.find('.repeating-group-value').val(rows.length ? JSON.stringify(rows) : '');
}

$(document).on('change', '.date-range-input', function () {
  updateDateRangeDays(this);
});

$(function () {
  $('[data-date-picker-target]').on('click', function () {
    openDatePicker($(this).data('date-picker-target'));
  });

  if (typeof flatpickr === 'function') {
    $('.uplift-flatpickr').each(function () {
      if (this._flatpickr) return;

      flatpickr(this, {
        allowInput: true,

        // Displayed format: April 26, 2026
        altInput: true,
        altFormat: 'F j, Y',

        // Submitted/stored format: 2026-04-26
        dateFormat: 'Y-m-d',

        disableMobile: true,
        monthSelectorType: 'dropdown',
        nextArrow: '<i class="fa fa-chevron-right"></i>',
        prevArrow: '<i class="fa fa-chevron-left"></i>',

        onReady: function (_, __, instance) {
          $(instance.calendarContainer).addClass('uplift-calendar');

          $(instance.altInput)
            .addClass($(instance.input).attr('class'))
            .attr('placeholder', $(instance.input).attr('placeholder'));
        },

        onChange: function (_, __, instance) {
          $(instance.input).trigger('change');
        }
      });
    });
  }

  $('.date-range-input').each(function () {
    updateDateRangeDays(this);
  });

  $('.submission-repeating-group').each(function () {
    syncRepeatingGroup($(this));
  });

  $('.submission-remarks-editor').each(function () {
    var editorId = this.id;
    var $textarea = $('#' + $(this).data('html-input'));

    initQuillJs(editorId, $(this).data('readonly') == '1');

    if (quills[editorId]) {
      quills[editorId].root.innerHTML = $textarea.length ? $textarea.val() : '';
    }
  });

  $('form').on('submit', function () {
    $('.submission-repeating-group').each(function () {
      syncRepeatingGroup($(this));
    });

    $('.submission-remarks-editor').each(function () {
      var editorId = this.id;
      var $textarea = $('#' + $(this).data('html-input'));

      if ($textarea.length && quills[editorId]) {
        $textarea.val(quills[editorId].root.innerHTML);
      }
    });
  });
});

$(document).on('input', '.repeating-group-input', function () {
  syncRepeatingGroup($(this).closest('.submission-repeating-group'));
});

$(document).on('click', '.repeating-group-add', function () {
  var $group = $(this).closest('.submission-repeating-group');
  var $rows = $group.find('.repeating-group-rows');
  var template = $group.find('.repeating-group-row-template').html();
  var $row = $(template);

  $rows.append($row);
  $row.find('.repeating-group-column-input').first().focus();
  syncRepeatingGroup($group);
});

$(document).on('click', '.repeating-group-remove', function () {
  var $group = $(this).closest('.submission-repeating-group');
  var $rows = $group.find('.repeating-group-row');

  if ($rows.length <= 1) {
    $rows.find('.repeating-group-column-input').val('').first().focus();
  } else {
    $(this).closest('.repeating-group-row').remove();
  }

  syncRepeatingGroup($group);
});
</script>
