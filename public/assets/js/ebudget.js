function isNumeric2(x) {
  x = x + " ";
  x = x.trim().replaceAll(',', '');
  if (!isNaN(x)) {
    if (x.substr(-1) == '.') {
      return false
    } else if (x == '') {
      return false
    } else {
      return true;
    }
  } else {
    return false;
  }
}

function isNumeric(x) {
  x = x + " ";
  x = x.trim();
  if (!isNaN(x)) {
    if (x.substr(-1) == '.') {
      return false
    } else if (x == '') {
      return false
    } else {
      return true;
    }
  } else {
    return false;
  }
}

function pureNumber(x) {
  x = x + " ";
  let pn = x.replaceAll(' ', '');
  return pn.replaceAll(',', '');
}

function pureNumber3(x) {
  x = x + " ";
  let pn = x.replaceAll(' ', '');
  let fn = pn.replaceAll(',', '');
  if (fn == '' || fn == null) {
    return 0;
  } else {
    return fn;
  }
}

function pureNumber2(x) {
  x = x + " ";
  let pn = x.replaceAll(' ', '');
  return parseFloat(pn.replaceAll(',', '')).toFixed(2);
}

function numberWithCommas(y) {
  if (parseFloat(y) % 1 != 0) {
    var tmp = y.toString().split(".");
    if (tmp.length > 1) {
      if (tmp[1].length > 2) {
        y = parseFloat(y).toFixed(2);
      }
    }
  } else {
    var tmp = y.toString().split(".");
    if (Array.isArray(tmp) == true && tmp.length > 1) {
      if (tmp[1].length > 2) {
        y = parseFloat(y).toFixed(2);
      }
    }
  }
  // return y.toLocaleString('en-US');
  var parts = y.toString().split(".");
  parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
  return parts.join(".");
}

function toCurrency(myString, myCurrency = null) {
  if (isNumeric2(myString)) {
    myString = pureNumber(myString);
    if (myCurrency == null || myCurrency == '') {
      if (myString < 0) {
        // return parseFloat(myString).toLocaleString(undefined, { style: 'decimal', minimumFractionDigits: 2, maximumFractionDigits: 2 })
        return `(${Math.abs(myString).toLocaleString('en-US', {
          minimumFractionDigits: 2, maximumFractionDigits: 2,
        })})`;
      }
      return parseFloat(myString).toLocaleString(undefined, { style: 'decimal', minimumFractionDigits: 2, maximumFractionDigits: 2 })
    } else {
      return parseFloat(myString).toLocaleString(undefined, { style: 'currency', currency: myCurrency, minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
  } else {
    return '';
  }
}

function convertToDecimal(myFloat, myDigit = 2) {
  let tmpFloat = myFloat + '';
  if (myFloat == null || tmpFloat.trim() == '') {
    return 0;
  }
  return Number(parseFloat(pureNumber3(myFloat)).toFixed(myDigit));

  // return Number(Math.round(parseFloat(pureNumber3(myFloat) + 'e' + myDigit)) + 'e-' + myDigit);
  // return Math.round(parseFloat(pureNumber3(myFloat)).toFixed(myDigit) * 100) * 0.01;
}



function dynamicSortMultiple() {
  /*
   * save the arguments object as it will be overwritten
   * note that arguments object is an array-like object
   * consisting of the names of the properties to sort by
   */
  var props = arguments;
  return function (obj1, obj2) {
    var i = 0, result = 0, numberOfProperties = props.length;
    /* try getting a different result from 0 (equal)
     * as long as we have extra properties to compare
     */
    while (result === 0 && i < numberOfProperties) {
      result = dynamicSort(props[i])(obj1, obj2);
      i++;
    }
    return result;
  }
}

function dynamicSort(property) {
  var sortOrder = 1;
  if (property[0] === "-") {
    sortOrder = -1;
    property = property.substr(1);
  }
  return function (a, b) {
    /* next line works with strings and numbers, 
     * and you may want to customize it to your needs
     */
    var result = (a[property] < b[property]) ? -1 : (a[property] > b[property]) ? 1 : 0;
    return result * sortOrder;
  }
}

function mydebug(myVar = '') {
  console.log(myVar);
  debugger;
}

function dd(myVar = '') {
  console.log(myVar);
  debugger;
}

function commaSeparated(myInteger) {
  var val = myInteger;
  val = val.replace(/[^0-9\.]/g, '');
  if (val != "") {
    valArr = val.split('.');
    valArr[0] = (parseInt(valArr[0], 10)).toLocaleString();
    val = valArr.join('.');
  }
  return val;
}

function generateDateTime() {
  let currentDate = new Date();

  let year = currentDate.getFullYear();
  let month = padZero(currentDate.getMonth() + 1); // Month is zero-based
  let day = padZero(currentDate.getDate());
  let hours = padZero(currentDate.getHours());
  let minutes = padZero(currentDate.getMinutes());
  let seconds = padZero(currentDate.getSeconds());

  return year + month + day + hours + minutes + seconds;
}

function padZero(value) {
  return value < 10 ? '0' + value : value;
}

function removeAllElementError() {
  $('p[class^="text-danger text-xs"]').each(function () {
    $(this).remove();
  });
}

function generateCurrentDateTime(myDate) {
  let now = null;
  if (myDate == null || myDate == '') {
    now = new Date();
  } else {
    let dateParts = myDate.split(/[- :]/); // Split the string into components
    now = new Date(
      dateParts[0],          // Year
      dateParts[1] - 1,      // Month (0-based in JS)
      dateParts[2],          // Day
      dateParts[3],          // Hour
      dateParts[4],          // Minute
      dateParts[5]           // Second
    );
  }

  let monthNames = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
  ];

  let month = monthNames[now.getMonth()];
  let day = now.getDate();
  let year = now.getFullYear();
  let hours = now.getHours();
  let minutes = now.getMinutes().toString().padStart(2, '0');
  let ampm = hours >= 12 ? 'PM' : 'AM';
  hours = hours % 12 || 12; // Convert to 12-hour format

  let formattedDateTime = `${month} ${day}, ${year} ${hours}:${minutes}${ampm}`;

  return formattedDateTime;
}

function debounce(func, delay) {
  let timeout;
  return function (...args) {
    clearTimeout(timeout);
    timeout = setTimeout(() => func.apply(this, args), delay);
  };
}

function setupStateLoadParams(myTableId, data) {
  for (i = 0; i < data.columns["length"]; i++) {
    let col_search_val = data.columns[i].search.search;
    if (col_search_val !== "") {
      $("input", $("#" + myTableId + " thead th")[i]).val(col_search_val);
    }
  }
}

function setupInitComplete(myTable, myTableId, mySortCol = 0, myOrder = 'asc', myLen = 10) {
  $('[data-toggle="tooltip"]').tooltip();
  const clearButton = $('<button>')
    .attr('type', 'button')
    .attr('title', 'Clear Filters and Reset Order')
    .html('<img src="/assets/img/icons/clear-filter.png" alt="Clear Filter" width="16" height="16">')
    .addClass('btn btn-link p-2 m-0')
    .css({ marginLeft: '10px' })
    .click(function () {
      myTable.search('');
      myTable.columns().every(function () {
        this.search('');
        $(this.header()).find('input').val('');
      });
      myTable.order([[mySortCol, myOrder]]);
      myTable.page.len(myLen).draw();
    });

  $('#' + myTableId + '_wrapper .dt-search').first().append(clearButton);
}

function createColumnSearch(myTableId, myDisabledCol, myCenterCols) {
  $('#' + myTableId + ' thead th').each(function (i) {
    const title = $('#' + myTableId + ' thead th').eq($(this).index()).text();
    if (myDisabledCol.includes(i)) {
      $(this).html('<input class="form-control form-control-sm text-center" type="text" placeholder="' + title.trim() + '" disabled data-index="' + i + '" />');
    } else if (myCenterCols.includes(i)) {
      $(this).html('<input class="form-control form-control-sm text-center" type="search" placeholder="' + title.trim() + '" data-index="' + i + '" />');
    } else {
      $(this).html('<input class="form-control form-control-sm" type="search" placeholder="' + title.trim() + '" data-index="' + i + '" />');
    }
  });
}

function setupKeyUpColumnSearch(myTable, myTableId) {
  $('#' + myTableId + ' thead').on('input', 'input', debounce(function () {
    var index = $(this).data('index'); // Get the column index
    var value = $(this).val(); // Get the input value
    var previousValue = $(this).data('previous') || '';

    if (value !== previousValue) {
      $(this).data('previous', value); // Update the stored value
      myTable.column(index).search(value).draw();
    }
  }, 500));
}

function getLanguageConfig(myDisplay = null) {
  return {
    search: "_INPUT_",
    searchPlaceholder: "Search " + myDisplay,
    lengthMenu: "Show _MENU_ entries",
    paginate: {
      previous: "Prev",
      next: "Next",
      first: "First",
      last: "Last",
    }
  };
}

function getAjaxConfig(myUrl, myToken) {
  return {
    url: myUrl,
    type: 'POST',
    dataType: "json",
    data: function (d) {
      d._token = myToken;
    },
  };
}