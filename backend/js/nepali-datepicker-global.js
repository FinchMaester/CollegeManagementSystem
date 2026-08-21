/*!
 * Global Nepali DatePicker Initializer (SAFE)
 * Works throughout the entire system
 */
(function ($) {
  'use strict';

  console.log('Global Nepali DatePicker Initializer loaded');

  // ---- helpers -------------------------------------------------------------
  function isPluginAvailable() {
    return (typeof $.fn.nepaliDatePicker !== 'undefined');
  }

  function initSet($scope) {
    // Select only real input elements we expect the plugin to handle.
    $scope.find(
      '.nepali-datepicker, .date-bs,' +
      ' input[name="dob"], #dob, #dob_bs,' +
      ' input[name*="admission_date"],' +
      ' input[name*="date"],' +
      ' input[name*="issue_date"], input[name*="return_date"], input[name*="due_date"],' +
      ' input[name*="postdate"], input[name*="applieddate"],' +
      ' input[name*="leave_from_date"], input[name*="leave_to_date"],' +
      ' input[name*="from_date"], input[name*="to_date"],' +
      ' input[name*="apply_date"], input[name*="publish_date"], input[name*="dateto"]'
    )
    // Ensure we’re only touching real elements (not strings/arrays/etc.)
    .filter(function () { return this && this.nodeType === 1; })
    .each(function () {
      var $field = $(this);
      var fieldId = $field.attr('id') || 'unknown';
      var fieldName = ($field.attr('name') || '').toLowerCase();
      var idLower = fieldId.toLowerCase();

      // Opt-out: fields that hold an English/A.D. date should not get a Nepali calendar.
      if ($field.hasClass('no-nepali-datepicker') || $field.data('no-nepali')) {
        return;
      }

      // Skip if already initialized
      if ($field.data('ndp-initialized') || $field.data('nepaliDatePicker')) {
        // console.log('Field already initialized:', fieldId);
        return;
      }

      // Year range tuning:
      // - DOB fields need a large range (birth years).
      // - Most transactional dates (issue/return/admission/etc.) are near-current.
      var isDobField = (fieldName.indexOf('dob') !== -1) || (idLower.indexOf('dob') !== -1);
      var yearCount = isDobField ? 90 : 15;

      try {
        // Initialize the Nepali datepicker
        $field.nepaliDatePicker({
          ndpTriggerButton: false,
          dateFormat: 'YYYY-MM-DD',
          ndpYear: true,
          ndpMonth: true,
          ndpYearCount: yearCount,
          readOnlyInput: false,
          ndpTrigger: 'click'
        });
        $field.data('ndp-initialized', true);
        console.log('Successfully initialized:', fieldId);
      } catch (error) {
        console.error('Error initializing field', fieldId, ':', error);
      }
    });
  }

  function safeInit($scope) {
    console.log('Checking for Nepali datepicker availability...');
    console.log('$.fn.nepaliDatePicker available:', typeof $.fn.nepaliDatePicker);

    if (!isPluginAvailable()) {
      console.log('Nepali DatePicker not available, retrying in 1000ms...');
      setTimeout(function () { safeInit($scope); }, 1000);
      return;
    }
    console.log('Nepali DatePicker is available, initializing...');
    initSet($scope);
  }

  // ---- boot ---------------------------------------------------------------
  $(document).ready(function () {
    console.log('DOM ready, initializing global Nepali datepickers...');
    safeInit($(document));

    // SAFER dynamic content handling: MutationObserver on a real Node
    var root = (document.body || document.documentElement);
    if (root && 'MutationObserver' in window) {
      try {
        var mo = new MutationObserver(function (mutations) {
          // Batch re-init after DOM changes (modals, ajax inserts, etc.)
          // Keep it cheap: just re-run within document scope.
          if (isPluginAvailable()) {
            initSet($(document));
          }
        });
        mo.observe(root, { childList: true, subtree: true });
      } catch (e) {
        console.warn('[NDP] MutationObserver setup skipped:', e && e.message);
      }
    }

    // Ensure input field clicks open the picker (no global preventDefault)
    $(document).on('click',
      '.date-bs, .nepali-datepicker, input[name="dob"], #dob, #dob_bs,' +
      ' input[name*="date"], input[name*="issue_date"], input[name*="return_date"], input[name*="due_date"],' +
      ' input[name*="postdate"], input[name*="applieddate"],' +
      ' input[name*="leave_from_date"], input[name*="leave_to_date"],' +
      ' input[name*="from_date"], input[name*="to_date"],' +
      ' input[name*="apply_date"], input[name*="publish_date"], input[name*="dateto"]',
      function () {
        var $field = $(this);
        if ($field.hasClass('no-nepali-datepicker') || $field.data('no-nepali')) {
          return;
        }
        if (isPluginAvailable() && ($field.data('ndp-initialized') || $field.data('nepaliDatePicker'))) {
          try { $field.nepaliDatePicker('show'); } catch (e) { /* ignore */ }
        }
      }
    );

    // IMPORTANT: Removed this block because it can swallow clicks inside modals:
    // $(document).on('click', '.nepali-datepicker button, .nepali-datepicker-container button', function(e) {
    //   e.preventDefault();
    //   e.stopPropagation();
    //   return false;
    // });
  });

})(jQuery);
