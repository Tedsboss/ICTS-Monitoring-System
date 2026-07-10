<script>
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
      // .html('<img src="/assets/img/icons/clear-filter.png" alt="Clear Filter" width="16" height="16">')
      .html('<svg id="clear-filter-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" width="16" height="16"><path d="M2 1H14L10 7V12L6 14V7L2 1Z" fill="none" stroke="currentColor" stroke-width="1.2"/><line x1="12.5" y1="10" x2="15.5" y2="13" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/><line x1="15.5" y1="10" x2="12.5" y2="13" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>')
      .addClass('btn btn-link p-2 m-0 custom-clear-filter')
      .css({ marginLeft: '10px' })
      .click(function () {
        // myTable.state.clear();
        myTable.search('');
        myTable.columns().every(function () {
          this.search('');
          $(this).data('previous', '');
          $(this.header()).find('input').val('').data('previous', '');
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

  function getAjaxConfig(myUrl, myToken, myError = 0) {
    return {
      url: myUrl,
      type: 'POST',
      dataType: "json",
      data: function (d) {
        d._token = myToken;
      },
      error: function(xhr, error, thrown) {
        myMsg = 'Unable to fetch data'
        if (myError == 0) {
        } else {
          myMsg = xhr.responseText;
        }
        showToast("warning", myMsg)
      },
    };
  }

  function showToast(myType, myMessage, myTitle = null, myDelay = null) {
    $('#' + myType + 'ToastMessage').html(myMessage);

    if (myTitle == null || myTitle == '') {
      if (myType == 'info') {
        myTitle = 'For Information';
      } else if (myType == 'success') {
        myTitle = 'Success';
      } else if (myType == 'warning') {
        myTitle = 'Warning';
      } else if (myType == 'danger') {
        myTitle = 'Error';
      }
    }
    $('#' + myType + 'ToastTitle').html(myTitle);

    if (myDelay == null || myDelay == '') {
      myDelay = '10000';
    }
    $('#' + myType + 'Toast').attr('data-bs-delay', myDelay);

    let toastElement = document.getElementById(myType + 'Toast');
    if (toastElement) {
      var toast = bootstrap.Toast.getInstance(toastElement);
      toast.show();
    }

  }

  function initializeChoiceJs(myElementId, shouldSort = true, removeItemButton = true, searchEnabled = true, itemSelectText = '', placeholder = true, placeholderValue = 'Select an option') {
    let $element = $('#' + myElementId);
    let fuseOptions = { includeScore: true, includeMatches: true, threshold: 0.4, location: 0, distance: 100, maxPatternLength: 32, minMatchCharLength: 1 };
    choiceSelects[myElementId] = new Choices($element[0], {
                                                              removeItemButton: removeItemButton,
                                                              searchEnabled: searchEnabled,
                                                              itemSelectText: itemSelectText, 
                                                              placeholder: placeholder, 
                                                              placeholderValue: placeholderValue, 
                                                              shouldSort: shouldSort,
                                                              // fuseOptions: fuseOptions
                                                            });

    // $element.prepend('<option value="" selected disabled hidden>' + placeholderValue + '</option>');

    // let searchInput = $element.next('.choices').find('.choices__input');
    // searchInput.attr('placeholder', 'Type keyword...');



  }

  function triggerClick(myElement) {
    $('#' + myElement).click();
  }

  function updateFileDisplay(myFileElement, myDispElement) {
    let fileName = $('#' + myFileElement).val().split("\\").pop();
    $('#' + myDispElement).val(fileName);
    $('#' + myDispElement).removeAttr('name');
    $('#' + myDispElement).next().find('.btn-with-attachment').remove();
  }

  function initTomSelect(myElementId, mySort = true, myMultiple = false, myBody = false, myOnChange = null, myOnDropDownOpen = null, myRender = null, myCreate = false) {
    sortField = [{field:'$order'},{field:'$score'}];
    if (mySort == true) {
      sortField = {
        field: "text",
        direction: "asc"
      };
    }

    plugins = ['dropdown_input'];
    if (myMultiple == true) {
      plugins.push('remove_button');
      plugins.push('clear_button');
    }

    dropdownParent = '';
    if (myBody == true) {
      dropdownParent = 'body'
    }

    tomSelects[myElementId] = new TomSelect('#' + myElementId, {
      onChange: myOnChange,
      onDropdownOpen: myOnDropDownOpen,
      dropdownParent: dropdownParent,
      plugins: plugins,
			persist: false,
      sortField: sortField,
      maxOptions : null,
      create: myCreate,
      render: myRender,
    });
    
    tomSelects[myElementId].on('clear', function() {
      this.setValue([]);
      this.refreshOptions(false);
    });
  }

  function initQuillJs(myElementId, myReadOnly = false) {
    toolbar = [
                // [{ 'lineHeight': ['1', '1.5', '2'] }],
                // ['bold', 'italic', 'underline', 'strike'],        // toggled buttons
                ['bold', 'italic', 'underline'],        // toggled buttons
                [{ 'align': [] }],
                // ['blockquote', 'code-block'],
                // ['code-block'],
                // ['link', 'image', 'video', 'formula'],
                ['link', 'image', 'video'],

                // [{ 'header': 1 }, { 'header': 2 }],               // custom button values
                // [{ 'list': 'ordered'}, { 'list': 'bullet' }, { 'list': 'check' }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                // [{ 'list': 'ordered' }],
                // [{ 'script': 'sub'}, { 'script': 'super' }],      // superscript/subscript
                // [{ 'indent': '-1'}, { 'indent': '+1' }],          // outdent/indent
                // [{ 'direction': 'rtl' }],                         // text direction
                [{ 'size': ['small', false, 'large', 'huge'] }],  // custom dropdown
                // [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                [{ 'color': [] }, { 'background': [] }],          // dropdown with defaults from theme
                // [{ 'font': [] }],
                // [{ 'align': [] }],
                ['clean']                                         // remove formatting button
              ];
    readOnly = false;
    placeholder = 'Details';
    
    if (myReadOnly == true) {
      toolbar = false;
      readOnly = true;
      placeholder = '';
    }

    quills[myElementId] = new Quill('#' + myElementId, {
      theme: 'snow', // Options: 'snow', 'bubble'
      placeholder: placeholder,
      modules: {
        toolbar: toolbar,
      },
      readOnly: readOnly
    });
  }

  function refreshToolTip() {
    $("[data-bs-toggle='tooltip']").tooltip();
    $('.tooltip').tooltip('hide');
  }

  function confirmSwal(myTitle, myText, myShowDeny = false, myConfirmText = 'Proceed', myDenyButtonText = 'Skip', myCancelText = 'Cancel') {
    return new Promise((resolve) => {
      const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
          confirmButton: 'btn bg-gradient-success',
          cancelButton: 'btn bg-gradient-danger mx-2',
          denyButton: 'btn bg-gradient-info mx-2'
        },
        buttonsStyling: false
      });
      swalWithBootstrapButtons.fire({
        title: myTitle,
        // text: myText,
        html: myText,
        icon: 'warning',
        showCancelButton: true,
        showDenyButton: myShowDeny,
        confirmButtonText: myConfirmText,
        cancelButtonText: myCancelText,
        denyButtonText: myDenyButtonText,
      }).then((result) => {
        // resolve(result.isConfirmed);
        if (result.isConfirmed) {
          resolve('confirmed');
        } else if (result.isDenied) {
          resolve('skipped');
        } else {
          resolve('cancelled');
        }
      });
    });
  }

  function safeString(myString) {
    if (myString == null) {
      return '';
    }
    return myString.replace(/"/g, '\\x22').replace(/'/g, '\\x27');
  }

  function formatDateNow(now) {
    const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    
    const month = months[now.getMonth()];
    const day = String(now.getDate()).padStart(2, '0');
    const year = now.getFullYear();
    
    let hours = now.getHours();
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const ampm = hours >= 12 ? 'PM' : 'AM';
    
    hours = hours % 12 || 12; // Convert 24h to 12h format

    return `${month} ${day}, ${year} ${hours}:${minutes} ${ampm}`;
  }

  function formatDateNow2(now) {
    const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    
    const month = months[now.getMonth()];
    const day = String(now.getDate()).padStart(2, '0');
    const year = now.getFullYear();
    
    let hours = now.getHours();
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    const ampm = hours >= 12 ? 'PM' : 'AM';
    
    hours = hours % 12 || 12; // Convert 24h to 12h format

    return `${month} ${day}, ${year} ${hours}:${minutes}:${seconds} ${ampm}`;
  }

  function downloadFileWithLoader(myURL) {
    $('#loader').fadeIn('slow');
    setTimeout(function() { 
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      $.ajax({
        // data: new FormData($('#frmReport')[0]),
        url: myURL,
        method: "GET",
        contentType: false,
        processData: false,
        xhrFields: {
          responseType: "blob",
        },
        success: function (response, status, xhr) {
          let filename = '';
          let contentDisposition = xhr.getResponseHeader('Content-Disposition');
          if (contentDisposition && contentDisposition.indexOf('attachment') !== -1) {
            let filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
            let matches = filenameRegex.exec(contentDisposition);
            if (matches != null && matches[1]) {
              filename = matches[1].replace(/['"]/g, '');
            }
          }
          let blob = new Blob([response], { type: "application/octet-stream" });
          let url = URL.createObjectURL(blob);
          let $link = $("<a></a>");
          $link.attr({
            href: url,
            download: filename || 'download.pdf'
          });
          $link.css('display', 'none');
          $("body").append($link);
          $link[0].click();
          $link.remove();
          $('#loader').fadeOut('slow');
        },
        error: function (data, textStatus, errorThrown) {
          $('#loader').fadeOut('slow');
        }
      });
    }, 100);
  }

  function convertErrorIdElement(myErrorId) {
    return myErrorId.replaceAll('.', '_') + '_error_id';
  }

  function hideAllErrors() {
    $('p[id$="_error_id"]').each(function() {
      $(this).attr('hidden', true);
    });
  }

  function refreshToolTip() {
    $('[data-bs-toggle="tooltip"]').tooltip();
    $('[data-bs-toggle="tooltip"]').tooltip('hide');
    $('[data-toggle="tooltip"]').tooltip();
    $('[data-toggle="tooltip"]').tooltip('hide');
    $('.tooltip').tooltip('hide');
  }

  function isOdd(number) {
    return Math.abs(number % 2) === 1; 
  }

  function formatFieldToCurrency(myId) {
    $('#' + myId).val(commaSeparated($('#' + myId).val()));
  }

  function showFormErrors(errors) {
    let err_ctr = 0;
    $.each(errors, function (key, value) {
      myElem = $("#" + convertErrorIdElement(key));
      if (myElem.length) {
        myElem.text(value[0]);
        myElem.attr('hidden', false);
      }
      if (err_ctr == 0) {
        showToast("warning", value[0]);
        if (myElem.length) {
          scrollToCenter(myElem);
        }
      }
      err_ctr++;
    });
  }

  function scrollToCenter($el, duration = 600) {
    if (!$el || !$el.length) return;
    const elTop = $el.offset().top;
    const elH   = $el.outerHeight(true);
    const winH  = $(window).height();
    const targetScrollTop = elTop - (winH / 2) + (elH / 2);
    $('html, body').stop(true).animate(
      { scrollTop: Math.max(0, targetScrollTop) },
      duration
    );
  }

  function generateRecaptchaToken(actionName) {
    var deferred = $.Deferred();
    @if (!app()->environment('production'))
    deferred.resolve('');
    return deferred.promise();
    @endif
    grecaptcha.ready(function() {
      grecaptcha.execute("{{ config('recaptchav3.sitekey') }}", { action: actionName })
        .then(function(token) {
          $("[name='g-recaptcha-response']").val(token);
          deferred.resolve(token);
        })
        .catch(function(error) {
          deferred.reject(error);
        });
    });
    return deferred.promise();
  }
</script>
