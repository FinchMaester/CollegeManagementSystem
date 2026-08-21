<!DOCTYPE html>
<html <?php echo $this->customlib->getRTL(); ?>>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title><?php echo $this->customlib->getAppName(); ?></title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <meta http-equiv="Cache-control" content="no-cache">
  <meta name="theme-color" content="#424242" />

  <!-- Favicon -->
  <link href="<?php echo base_url('uploads/school_content/admin_small_logo/'.$this->setting_model->getAdminsmalllogo()); ?>" rel="shortcut icon" type="image/x-icon">

  <!-- Core CSS -->
  <link rel="stylesheet" href="<?php echo base_url(); ?>backend/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?php echo base_url(); ?>backend/dist/css/jquery.mCustomScrollbar.min.css">
  <?php $this->load->view('layout/theme'); ?>
  <link rel="stylesheet" href="<?php echo base_url(); ?>backend/dist/css/ss-print.css">
  <link rel="stylesheet" href="<?php echo base_url(); ?>backend/dist/css/font-awesome.min.css">
  <link rel="stylesheet" href="<?php echo base_url(); ?>backend/dist/css/ionicons.min.css">
  <link rel="stylesheet" href="<?php echo base_url(); ?>backend/plugins/iCheck/flat/blue.css">
  <link rel="stylesheet" href="<?php echo base_url(); ?>backend/plugins/morris/morris.css">
  <link rel="stylesheet" href="<?php echo base_url(); ?>backend/plugins/jvectormap/jquery-jvectormap-1.2.2.css">
  <link rel="stylesheet" href="<?php echo base_url(); ?>backend/plugins/datepicker/datepicker3.css">
  <link rel="stylesheet" href="<?php echo base_url(); ?>backend/plugins/colorpicker/bootstrap-colorpicker.css">
  <link rel="stylesheet" href="<?php echo base_url(); ?>backend/plugins/daterangepicker/daterangepicker-bs3.css">
  <link rel="stylesheet" href="<?php echo base_url(); ?>backend/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css">
  <link rel="stylesheet" href="<?php echo base_url(); ?>backend/dist/css/custom_style.css">
  <link rel="stylesheet" href="<?php echo base_url(); ?>backend/dist/css/dropify.min.css">
  <link href="<?php echo base_url(); ?>backend/dist/css/nprogress.css" rel="stylesheet">
  <link href="<?php echo base_url(); ?>backend/dist/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
  <link href="<?php echo base_url(); ?>backend/dist/datatables/css/buttons.dataTables.min.css" rel="stylesheet">
  <link href="<?php echo base_url(); ?>backend/dist/datatables/css/dataTables.bootstrap.min.css" rel="stylesheet">
  <link href="<?php echo base_url(); ?>backend/dist/datatables/css/responsive.dataTables.min.css" rel="stylesheet">
  <link href="<?php echo base_url(); ?>backend/dist/datatables/css/rowReorder.dataTables.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/0.8.2/css/flag-icon.min.css">
  <link rel="stylesheet" href="<?php echo base_url(); ?>backend/dist/css/bootstrap-select.min.css">

  <!-- English DatePicker (Bootstrap Datepicker) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">

  <!-- Nepali DatePicker (SIMPLE) -->
  <!-- <link rel="stylesheet" href="<?php echo base_url(); ?>backend/plugins/nepali-datepicker/css/nepali.datepicker.simple.css"> -->

  <!-- Optional DateTime picker CSS -->
  <link rel="stylesheet" href="<?php echo base_url(); ?>backend/datepicker/css/bootstrap-datetimepicker.css">

  <!-- ===== JS ORDER MATTERS ===== -->

  <!-- 1) jQuery (one copy only) -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

  <!-- 1.1) GLOBAL SAFETY SHIM for MutationObserver -->
<script>
(function () {
  if (!('MutationObserver' in window)) return;
  var _observe = window.MutationObserver.prototype.observe;

  function toNode(target) {
    // jQuery object → first DOM element
    if (target && target.jquery) return target[0] || null;
    // selector string → resolve against document
    if (typeof target === 'string') return document.querySelector(target);
    return target;
  }

  window.MutationObserver.prototype.observe = function (target, options) {
    try {
      target = toNode(target);
      if (!(target && (target.nodeType === 1 || target.nodeType === 9 || target.nodeType === 11))) {
        console.warn('[MO guard] skipped observe on non-Node:', target);
        return; // safely ignore
      }
      return _observe.call(this, target, options);
    } catch (e) {
      console.warn('[MO guard] observe failed but swallowed:', e && e.message);
    }
  };
})();
</script>


  <!-- 2) jQuery UI MUST come BEFORE Bootstrap & theme bridge() calls -->
  <script src="<?php echo base_url(); ?>backend/dist/js/jquery-ui.min.js"></script>

  <!-- 2.1) Resolve jQuery UI vs Bootstrap plugin name conflicts (AdminLTE expects this) -->
  <script>
    if (window.jQuery && window.jQuery.ui && window.jQuery.ui.button && window.jQuery.widget) {
      jQuery.widget.bridge('uibutton', jQuery.ui.button);
    }
  </script>

  <!-- 3) Bootstrap JS -->
  <script src="<?php echo base_url(); ?>backend/bootstrap/js/bootstrap.min.js"></script>

  <!-- 4) English DatePicker -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

  <!-- 5) Nepali DatePicker (Sajjan Maharjan v4.0.4 - Working CDN) -->
  <link href="https://unpkg.com/@sajanm/nepali-date-picker@4.0.8/dist/nepali.datepicker.v4.0.8.min.css" rel="stylesheet" type="text/css"/>
  <link href="<?php echo base_url(); ?>backend/css/nepali-datepicker-fix.css" rel="stylesheet" type="text/css"/>
  <script src="https://unpkg.com/@sajanm/nepali-date-picker@4.0.8/dist/nepali.datepicker.v4.0.8.min.js"></script>
  <script>
    console.log('Sajjan Maharjan Nepali DatePicker v4.0.4 loaded');
    console.log('$.fn.nepaliDatePicker available:', typeof $.fn.nepaliDatePicker);
  </script>
  
  <!-- 6) Global Nepali DatePicker Initializer -->
  <script src="<?php echo base_url(); ?>backend/js/nepali-datepicker-global.js"></script>

  <!-- 6) Other scripts -->
  <script src="<?php echo base_url(); ?>backend/dist/js/moment.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
  <script src="<?php echo base_url(); ?>backend/datepicker/js/bootstrap-datetimepicker.js"></script>
  <script src="<?php echo base_url(); ?>backend/plugins/colorpicker/bootstrap-colorpicker.js"></script>

  <!-- File handling -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

  <!-- Custom scripts -->
  <script src="<?php echo base_url(); ?>backend/js/school-custom.js"></script>
  <script src="<?php echo base_url(); ?>backend/js/school-admin-custom.js"></script>
  <script src="<?php echo base_url(); ?>backend/js/sstoast.js"></script>

  <!-- fullCalendar -->
  <link rel="stylesheet" href="<?php echo base_url(); ?>backend/fullcalendar/dist/fullcalendar.min.css">
  <link rel="stylesheet" href="<?php echo base_url(); ?>backend/fullcalendar/dist/fullcalendar.print.min.css" media="print">

  <script type="text/javascript">
    var baseurl = "<?php echo base_url(); ?>";
    var start_week = <?php echo $this->customlib->getStartWeek(); ?>;
    var chk_validate = "<?php echo $this->config->item('SSLK') ?>";
    
    // Set calendar mode for Nepali datepicker - Force BS mode for Nepali datepickers
    window.APP_CALENDAR_MODE = 'BS';
    
    // Auto-Session Detection Debug Info
    <?php 
    $debug_info = $this->session->userdata('auto_session_debug');
    if ($debug_info && is_array($debug_info)) {
        $this->session->unset_userdata('auto_session_debug'); // Clear after use
        // Escape all values to prevent JavaScript errors
        $ad_date = isset($debug_info['ad_date']) ? addslashes($debug_info['ad_date']) : 'N/A';
        $ad_year = isset($debug_info['ad_year']) ? (int)$debug_info['ad_year'] : 'null';
        $ad_month = isset($debug_info['ad_month']) ? (int)$debug_info['ad_month'] : 'null';
        $bs_year = isset($debug_info['bs_year']) && $debug_info['bs_year'] !== null ? (int)$debug_info['bs_year'] : 'null';
        $bs_month = isset($debug_info['bs_month']) && $debug_info['bs_month'] !== null ? (int)$debug_info['bs_month'] : 'null';
        $converted_bs = isset($debug_info['converted_bs_date']) ? addslashes($debug_info['converted_bs_date']) : 'N/A';
        $target_session = isset($debug_info['target_session']) ? addslashes($debug_info['target_session']) : 'N/A';
        $prev_session_id = isset($debug_info['previous_session_id']) && $debug_info['previous_session_id'] !== null ? (int)$debug_info['previous_session_id'] : 'null';
        $curr_session_id = isset($debug_info['session_id']) && $debug_info['session_id'] !== null ? (int)$debug_info['session_id'] : 'null';
        $was_updated = isset($debug_info['was_updated']) && $debug_info['was_updated'] ? 'true' : 'false';
        $status = isset($debug_info['status']) ? addslashes($debug_info['status']) : 'unknown';
    ?>
    console.log('%c=== AUTO-SESSION DETECTION DEBUG ===', 'background: #2196F3; color: white; padding: 5px; font-weight: bold;');
    console.log('AD Date:', '<?php echo $ad_date; ?>');
    console.log('AD Year:', <?php echo $ad_year; ?>, '| AD Month:', <?php echo $ad_month; ?>);
    console.log('BS Year:', <?php echo $bs_year; ?>, '| BS Month:', <?php echo $bs_month; ?>);
    console.log('Converted BS Date:', '<?php echo $converted_bs; ?>');
    console.log('Target Session:', '<?php echo $target_session; ?>');
    console.log('Previous Session ID:', <?php echo $prev_session_id; ?>);
    console.log('Current Session ID:', <?php echo $curr_session_id; ?>);
    console.log('Found Session Name:', '<?php echo isset($debug_info['found_session_name']) ? addslashes($debug_info['found_session_name']) : 'N/A'; ?>');
    console.log('Current Session Name:', '<?php echo isset($debug_info['current_session_name']) ? addslashes($debug_info['current_session_name']) : 'N/A'; ?>');
    console.log('Session Updated:', <?php echo $was_updated; ?>);
    console.log('Status:', '<?php echo $status; ?>');
    <?php if (isset($debug_info['error'])) { ?>
    console.error('Error:', '<?php echo addslashes($debug_info['error']); ?>');
    <?php } ?>
    <?php if (isset($debug_info['fallback_reason'])) { ?>
    console.warn('Fallback Reason:', '<?php echo addslashes($debug_info['fallback_reason']); ?>');
    <?php } ?>
    console.log('%c=====================================', 'background: #2196F3; color: white; padding: 5px; font-weight: bold;');
    <?php } ?>

    // Safe, idempotent initializer for AD (English) date pickers
    function initializeDatePickers(context) {
      var $scope = context ? $(context) : $(document);

      // OLD Nepali BS fields — DISABLED (replaced by new system)
      // This section is intentionally disabled to prevent conflicts with the new datepicker system
      /*
      if ($.fn.nepaliDatePicker) {
        $scope.find('.nepali-date, .nepali-datepicker, input[name*="dob"]:not([name*="dob_ad"])')
          .each(function(){
            var $el = $(this);
            if (!$el.data('ndp-initialized')) {
              $el.nepaliDatePicker({
                dateFormat: '%Y-%m-%d',
                ndpMonth: true,
                ndpYear: true,
                readOnlyInput: true
              });
              $el.data('ndp-initialized', true);
            }
          });
      }
      */

      // English AD fields
      if ($.fn.datepicker) {
        $scope.find('.english-date, input[name*="dob_ad"]')
          .each(function(){
            var $el = $(this);
            if (!$el.data('adp-initialized')) {
              $el.datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                todayHighlight: true
              });
              $el.data('adp-initialized', true);
            }
          });
      }
    }

    // Initial run
    $(function(){ initializeDatePickers(); });

    // Re-init for dynamically added content (modals/AJAX)
    var observer = new MutationObserver(function(mutations){
      for (var i=0;i<mutations.length;i++){
        if (mutations[i].addedNodes && mutations[i].addedNodes.length){
          initializeDatePickers(mutations[i].target);
        }
      }
    });
    observer.observe(document.body, {childList: true, subtree: true});
  </script>

  <!-- Global Nepali DatePicker Initializer - Using Sajjan Maharjan's datepicker instead -->
  <!-- <script src="<?php echo base_url(); ?>backend/js/date-bs-init.js"></script> -->

  <style>
    span.flag-icon.flag-icon-us { text-orientation: mixed; }
    
    /* Header Search Box Styling */
    /* Header Search Box - White Rounded Design */
    .main-header .header-search-box .input-group {
      position: relative;
    }
    
    .main-header #navbar-search-input.form-control {
      background: #fff;
      border: 1px solid #ddd;
      border-radius: 20px;
      color: #333;
      height: 36px;
      padding: 6px 40px 6px 15px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .main-header #navbar-search-input.form-control::placeholder {
      color: #999;
      opacity: 1;
    }
    
    .main-header #navbar-search-input.form-control:focus {
      background: #fff;
      border-color: #3c8dbc;
      color: #333;
      box-shadow: 0 2px 5px rgba(60,141,188,0.3);
      outline: none;
    }
    
    .main-header .header-search-box .input-group-btn {
      position: absolute;
      right: 5px;
      top: 50%;
      transform: translateY(-50%);
      z-index: 10;
    }
    
    .main-header #navbar-search-submit,
    .main-header #navbar-search-clear {
      background: transparent;
      border: none;
      color: #666;
      padding: 0;
      height: auto;
    }
    
    .main-header #navbar-search-submit:hover,
    .main-header #navbar-search-clear:hover {
      color: #3c8dbc;
    }
    
    .main-header .header-search-box {
      margin-top: 7px;
    }
    
    .main-header .header-search-box form {
      margin: 0;
    }
    
    /* Dropdown Styling - Fix overflow and positioning issues */
    .main-header {
      overflow: visible !important;
    }
    
    .main-header .navbar-nav {
      overflow: visible !important;
    }
    
    .main-header .navbar-nav > li {
      position: relative;
      overflow: visible !important;
    }
    
    .main-header .dropdown {
      position: relative !important;
    }
    
    .main-header .dropdown-menu {
      border-radius: 4px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.2);
      position: absolute !important;
      top: 100% !important;
      left: auto !important;
      right: 0 !important;
      margin-top: 5px !important;
      z-index: 1050 !important;
      display: none;
      overflow: visible !important;
      min-width: 160px;
    }
    
    .main-header .dropdown-menu-right {
      right: 0 !important;
      left: auto !important;
    }
    
    .main-header .dropdown.open .dropdown-menu {
      display: block !important;
    }
    
    .main-header .navbar {
      overflow: visible !important;
    }
    
    .main-header .navbar-custom-menu {
      overflow: visible !important;
    }
    
    .main-header .navbar-static-top {
      overflow: visible !important;
    }
    
    .main-header .dropdown-menu li a {
      padding: 10px 15px;
      display: block;
      color: #333;
      text-decoration: none;
      transition: background-color 0.2s;
    }
    
    .main-header .dropdown-menu li a:hover {
      background: #f5f5f5;
      color: #333;
    }
    
    .main-header .dropdown-menu li.divider {
      height: 1px;
      margin: 5px 0;
      overflow: hidden;
      background-color: #e5e5e5;
    }
    
    /* Profile dropdown specific styling */
    .main-header .user-menu .dropdown-menu,
    .main-header .profile-dropdown {
      padding: 0;
      border: 1px solid #ddd;
      box-shadow: 0 2px 10px rgba(0,0,0,0.15);
    }
    
    .main-header .user-menu .dropdown-menu > li > a {
      padding: 10px 15px;
      font-size: 14px;
    }
    
    .main-header .user-menu .dropdown-menu > li > a i {
      width: 16px;
      text-align: center;
    }
    
    /* Tasks dropdown specific styling */
    .main-header .tasks-dropdown {
      border: 1px solid #ddd;
      box-shadow: 0 2px 10px rgba(0,0,0,0.15);
    }
    
    .main-header .dropdown-menu li.header {
      padding: 10px 15px;
      background: #f4f4f4;
      border-bottom: 1px solid #ddd;
      margin: 0;
      font-size: 14px;
    }
    
    .main-header .dropdown-menu li.footer {
      padding: 10px 15px;
      background: #f4f4f4;
      border-top: 1px solid #ddd;
      text-align: center;
      margin: 0;
    }
    
    .main-header .dropdown-menu li.footer a {
      color: #3c8dbc;
      font-weight: 500;
      font-size: 14px;
    }
    
    .main-header .dropdown-menu li.footer a:hover {
      color: #2e6da4;
      background: transparent;
      text-decoration: underline;
    }
    
    /* Clean dropdown menu styling */
    .main-header .dropdown-menu {
      border: 1px solid #ddd;
      box-shadow: 0 2px 10px rgba(0,0,0,0.15);
      background: #fff;
    }
    
    .main-header .dropdown-menu > li > a {
      font-size: 14px;
    }
    
    .main-header .dropdown-menu > li > a:hover {
      background-color: #f5f5f5;
    }
    
    /* Ensure dropdown arrow/caret points down (not up) */
    .main-header .dropdown-toggle .caret {
      border-top: 4px dashed;
      border-right: 4px solid transparent;
      border-left: 4px solid transparent;
      border-bottom: 0;
      margin-left: 2px;
      vertical-align: middle;
    }
    
    /* Hide any upward pointing arrows/carets */
    .main-header .dropdown-toggle .caret-up,
    .main-header .dropdown-toggle .fa-caret-up {
      display: none !important;
    }
    
    /* Prevent Bootstrap from adding dropup class */
    .main-header .dropdown.dropup {
      position: relative !important;
    }
    
    .main-header .dropdown.dropup .dropdown-menu {
      top: 100% !important;
      bottom: auto !important;
      margin-top: 5px !important;
      margin-bottom: 0 !important;
    }
    
    /* Ensure dropdown menus are not clipped by parent containers */
    body {
      overflow-x: hidden;
    }
    
    .wrapper {
      overflow: visible !important;
    }
    
    /* Force all header dropdowns to open downward */
    .main-header .navbar-nav > .dropdown > .dropdown-menu {
      top: 100% !important;
      bottom: auto !important;
      margin-top: 5px !important;
    }
    
    @media (max-width: 768px) {
      .main-header .header-search-box {
        display: none;
      }
    }
  </style>
  
</head>
<body class="hold-transition skin-blue fixed sidebar-mini">
<?php if ($this->config->item('SSLK') == "") { ?>
  <div class="topaleart">
    <div class="slidealert">
      <div class="alert alert-dismissible topaleart-inside">
        <p class="palert">
          <strong>Alert!</strong> You are using unregistered version of Smart School.
          Please <a href="#" class="purchasemodal">click here</a> to register your purchase code for Smart School.
        </p>
      </div>
    </div>
  </div>
<?php } ?>

<script>
  function collapseSidebar() {
    if (Boolean(sessionStorage.getItem('sidebar-toggle-collapsed'))) {
      sessionStorage.setItem('sidebar-toggle-collapsed', '');
    } else {
      sessionStorage.setItem('sidebar-toggle-collapsed', '1');
    }
  }
  function checksidebar() {
    if (Boolean(sessionStorage.getItem('sidebar-toggle-collapsed'))) {
      var body = document.getElementsByTagName('body')[0];
      body.className = body.className + ' sidebar-collapse';
    }
  }
  checksidebar();
</script>

<div class="wrapper">
  <header class="main-header" id="alert">
    <a href="<?php echo base_url(); ?>admin/admin/dashboard" class="logo">
      <?php 
      $admin_small_logo = $this->setting_model->getAdminsmalllogo();
      $admin_logo = $this->setting_model->getAdminlogo();
      ?>
      <?php if (!empty($admin_small_logo)): ?>
      <span class="logo-mini"><img src="<?php echo base_url('uploads/school_content/admin_small_logo/' . rawurlencode($admin_small_logo)); ?>" alt="<?php echo $this->customlib->getAppName() ?>" onerror="this.style.display='none';" /></span>
      <?php endif; ?>
      <?php if (!empty($admin_logo)): ?>
      <span class="logo-lg"><img src="<?php echo base_url('uploads/school_content/admin_logo/' . rawurlencode($admin_logo)); ?>" alt="<?php echo $this->customlib->getAppName() ?>" onerror="this.style.display='none';" /></span>
      <?php endif; ?>
    </a>
    <nav class="navbar navbar-static-top" role="navigation">
      <a onclick="collapseSidebar()" href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
        <span class="sr-only"><?php echo $this->lang->line('toggle_navigation'); ?></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </a>
      <div class="col-lg-5 col-md-3 col-sm-2 col-xs-4">
        <span href="#" class="sidebar-session">
          <?php echo $this->setting_model->getCurrentSchoolName(); ?>
        </span>
      </div>
      <div class="col-lg-7 col-md-9 col-sm-10 col-xs-8">
        <div class="pull-right">
          <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
              <?php 
              $userdata = $this->customlib->getUserData();
              $this->load->model("calendar_model");
              $pending_tasks = array();
              if (!empty($userdata)) {
                $pending_tasks = $this->calendar_model->getincompleteTask($userdata['id'], $userdata['role_id']);
              }
              $pending_count = count($pending_tasks);
              $header_currencies = function_exists('get_currency_list') ? get_currency_list() : array();
              $admin_session = $this->session->userdata('admin');
              $school_setting = $this->setting_model->getSetting();
              $header_current_currency_id = 0;
              if (!empty($admin_session['id'])) {
                  $staff_currency_row = $this->db->select('currency_id')->from('staff')->where('id', (int) $admin_session['id'])->limit(1)->get()->row();
                  if (!empty($staff_currency_row) && (int) $staff_currency_row->currency_id > 0) {
                      $header_current_currency_id = (int) $staff_currency_row->currency_id;
                  }
              }
              if ($header_current_currency_id < 1 && !empty($school_setting->currency_id)) {
                  $header_current_currency_id = (int) $school_setting->currency_id;
              } elseif ($header_current_currency_id < 1 && !empty($school_setting->currency)) {
                  $header_current_currency_id = (int) $school_setting->currency;
              } elseif ($header_current_currency_id < 1 && !empty($admin_session['currency'])) {
                  $header_current_currency_id = (int) $admin_session['currency'];
              }
              $header_currency_label = $this->customlib->getSchoolCurrencyFormat();
              $header_current_lang_id = 0;
              $header_current_lang_name = 'English';
              $header_current_lang_flag = 'us';
              if (!empty($admin_session['id'])) {
                  $defoultlang = $this->setting_model->get_stafflang($admin_session['id']);
                  if (!empty($defoultlang['lang_id'])) {
                      $header_current_lang_id = (int) $defoultlang['lang_id'];
                  }
              }
              if ($header_current_lang_id < 1 && !empty($school_setting->lang_id)) {
                  $header_current_lang_id = (int) $school_setting->lang_id;
              }
              if ($header_current_lang_id > 0) {
                  $lang_row = $this->db->select('language, country_code')->from('languages')->where('id', $header_current_lang_id)->get()->row_array();
                  if (!empty($lang_row)) {
                      $header_current_lang_name = $lang_row['language'];
                      if (!empty($lang_row['country_code'])) {
                          $header_current_lang_flag = strtolower($lang_row['country_code']);
                          if ($header_current_lang_flag === 'ne') {
                              $header_current_lang_flag = 'np';
                          }
                      }
                  }
              }
              ?>
              <!-- Student Search Box - White Rounded Design -->
              <li class="header-search-box" style="padding: 8px 10px;">
                <form id="header-student-search-form" action="<?php echo site_url('admin/admin/search'); ?>" method="POST" style="display: inline-block; margin: 0;">
                  <?php echo $this->customlib->getCSRF(); ?>
                  <div class="input-group" style="width: 300px;">
                    <input type="text" id="navbar-search-input" name="search_text1" class="form-control" placeholder="Search By Student Name" style="background: #fff; border: 1px solid #ddd; border-radius: 20px; color: #333; padding: 6px 15px;">
                    <span class="input-group-btn" style="position: absolute; right: 5px; top: 50%; transform: translateY(-50%); z-index: 10;">
                      <button type="submit" id="navbar-search-submit" class="btn btn-link" style="background: transparent; border: none; color: #666; padding: 0;">
                        <i class="fa fa-search"></i>
                      </button>
                      <button type="button" id="navbar-search-clear" class="btn btn-link" style="background: transparent; border: none; color: #666; padding: 0; display: none;">
                        <i class="fa fa-times"></i>
                      </button>
                    </span>
                  </div>
                </form>
              </li>
              <!-- Currency (from System Settings → Currencies) -->
              <?php if (!empty($header_currencies)) { ?>
              <li class="dropdown" style="padding: 8px 5px;">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" style="color: #fff; padding: 0 8px;" title="<?php echo $this->lang->line('currency'); ?>">
                  <?php
                  foreach ($header_currencies as $hc) {
                      if ((int) $hc->id === $header_current_currency_id) {
                          $header_currency_label = trim($hc->short_name . ' (' . $hc->symbol . ')');
                          break;
                      }
                  }
                  echo htmlspecialchars($header_currency_label);
                  ?>
                  <span class="caret"></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-right" style="min-width: 180px;">
                  <?php
                  $flag_map = array('NPR' => 'np', 'USD' => 'us', 'EUR' => 'eu', 'GBP' => 'gb', 'INR' => 'in');
                  foreach ($header_currencies as $hc) {
                      $sn = strtoupper(trim((string) $hc->short_name));
                      $flag = isset($flag_map[$sn]) ? $flag_map[$sn] : strtolower(substr($sn, 0, 2));
                      $active = ((int) $hc->id === $header_current_currency_id) ? ' <i class="fa fa-check text-success"></i>' : '';
                      ?>
                  <li>
                    <a href="#" onclick="setCurrency(<?php echo (int) $hc->id; ?>); return false;">
                      <i class="flag-icon flag-icon-<?php echo htmlspecialchars($flag); ?>"></i>
                      <?php echo htmlspecialchars($hc->name . ' — ' . $hc->symbol); ?><?php echo $active; ?>
                    </a>
                  </li>
                  <?php } ?>
                  <li class="divider"></li>
                  <li><a href="<?php echo site_url('admin/currency'); ?>"><i class="fa fa-cog"></i> <?php echo $this->lang->line('currencies'); ?></a></li>
                </ul>
              </li>
              <?php } ?>
              <!-- Calendar Link -->
              <li style="padding: 8px 5px;">
                <a href="<?php echo site_url('admin/calendar/events'); ?>" style="color: #fff; padding: 0 5px;" title="Calendar">
                  <i class="fa fa-calendar"></i>
                </a>
              </li>
              <!-- Tasks Dropdown -->
              <li class="dropdown" style="padding: 8px 5px;">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" style="color: #fff; padding: 0 5px; position: relative; cursor: pointer;" title="Tasks">
                  <i class="fa fa-check-square-o"></i>
                  <?php if ($pending_count > 0): ?>
                    <span class="badge" style="background: #f39c12; position: absolute; top: -5px; right: -5px; font-size: 10px; padding: 2px 5px;"><?php echo $pending_count; ?></span>
                  <?php endif; ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-right tasks-dropdown" style="min-width: 250px; padding: 10px 15px; margin-top: 5px;">
                  <li style="padding: 0; margin: 0; list-style: none;">
                    <span style="color: #333; font-size: 14px;">Today You Have <?php echo $pending_count; ?> Pending Task<?php echo $pending_count != 1 ? 's' : ''; ?>. </span>
                    <a href="<?php echo site_url('admin/calendar/events'); ?>" style="color: #3c8dbc; text-decoration: underline; font-size: 14px;">View All</a>
                  </li>
                </ul>
              </li>
              <!-- WhatsApp Link -->
              <li style="padding: 8px 5px;">
                <a href="<?php echo site_url('admin/chat'); ?>" style="color: #fff; padding: 0 5px;" title="WhatsApp">
                  <i class="fa fa-whatsapp"></i>
                </a>
              </li>
              <!-- User Profile Dropdown -->
              <?php 
              $logged_in_user_id = $this->customlib->getStaffID();
              $profile_url = site_url('admin/staff/profile/' . $logged_in_user_id);
              $username_display = isset($userdata['username']) ? ucfirst($userdata['username']) : 'User';
              $role_display = 'Admin';
              if (!empty($userdata['roles'])) {
                $roles = $userdata['roles'];
                $role_display = ucfirst(key($roles));
              }
              ?>
              <li class="dropdown user-menu" style="padding: 8px 5px;">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" style="color: #fff; padding: 0 5px;" title="Profile">
                  <i class="fa fa-user"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-right profile-dropdown" style="min-width: 220px; padding: 0; margin-top: 5px;">
                  <?php if (!empty($userdata)): ?>
                    <li style="padding: 15px; background: #f4f4f4; border-bottom: 1px solid #ddd; margin: 0;">
                      <div style="display: flex; align-items: center;">
                        <i class="fa fa-user-circle" style="font-size: 48px; color: #999; margin-right: 12px; flex-shrink: 0;"></i>
                        <div style="flex: 1;">
                          <div style="font-size: 14px; color: #333; font-weight: 500; margin-bottom: 2px;"><?php echo $username_display; ?></div>
                          <div style="font-size: 12px; color: #999; margin-top: 2px;"><?php echo $role_display; ?></div>
                        </div>
                      </div>
                    </li>
                  <?php endif; ?>
                  <li style="margin: 0; padding: 10px 15px; border-bottom: 1px solid #eee;">
                    <div style="display: flex; justify-content: space-around; align-items: center;">
                      <a href="<?php echo $profile_url; ?>" style="color: #333; text-decoration: none; display: flex; flex-direction: column; align-items: center; padding: 5px;">
                        <i class="fa fa-user" style="font-size: 18px; margin-bottom: 5px;"></i>
                        <span style="font-size: 12px;">Profile</span>
                      </a>
                      <a href="<?php echo site_url('admin/admin/changepass'); ?>" style="color: #333; text-decoration: none; display: flex; flex-direction: column; align-items: center; padding: 5px;">
                        <i class="fa fa-key" style="font-size: 18px; margin-bottom: 5px;"></i>
                        <span style="font-size: 12px;">Password</span>
                      </a>
                      <a href="<?php echo site_url('site/logout'); ?>" style="color: #333; text-decoration: none; display: flex; flex-direction: column; align-items: center; padding: 5px;">
                        <i class="fa fa-sign-out" style="font-size: 18px; margin-bottom: 5px;"></i>
                        <span style="font-size: 12px;">Logout</span>
                      </a>
                    </div>
                  </li>
                </ul>
              </li>
              <!-- Language -->
              <li class="dropdown" style="padding: 8px 10px;">
                <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" style="background: rgba(255, 255, 255, 0.2); border-color: transparent; color: #fff; padding: 5px 10px; border-radius: 3px;">
                  <i class="flag-icon flag-icon-<?php echo htmlspecialchars($header_current_lang_flag); ?>"></i>
                  <?php echo htmlspecialchars($header_current_lang_name); ?>
                  <span class="caret"></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-right">
                  <?php
                  if (!empty($admin_session) && function_exists('get_header_language_ids')) {
                      foreach (get_header_language_ids() as $value) {
                          $result = $this->db->select('languages.language, languages.country_code')->from('languages')->where('id', (int) $value)->get()->row_array();
                          if (!empty($result)) {
                              $flag_code = strtolower(trim((string) $result['country_code']));
                              if ($flag_code === 'ne') {
                                  $flag_code = 'np';
                              }
                              $sel = ((int) $value === $header_current_lang_id) ? ' <i class="fa fa-check text-success"></i>' : '';
                              ?>
                        <li><a href="#" onclick="set_languages(<?php echo (int) $value; ?>); return false;"><i class="flag-icon flag-icon-<?php echo htmlspecialchars($flag_code); ?>"></i> <?php echo htmlspecialchars($result['language']); ?><?php echo $sel; ?></a></li>
                              <?php
                          }
                      }
                  }
                  ?>
                  <li class="divider"></li>
                  <li><a href="<?php echo site_url('admin/language'); ?>"><i class="fa fa-cog"></i> <?php echo $this->lang->line('languages'); ?></a></li>
                </ul>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </nav>
  </header>

  <?php $this->load->view('layout/sidebar'); ?>

  <script>
    function set_languages(lang_id){
      $.ajax({
        type: "POST",
        url: baseurl + "admin/language/user_language/" + lang_id,
        data: {},
        success: function () {
          successMsg("<?php echo $this->lang->line('status_change_successfully'); ?>");
          window.location.reload('true');
        }
      });
    }

    // Global Header Student Search Functionality
    $(document).ready(function() {
      // Debug and fix dropdown menus in header
      console.log('[DROPDOWN DEBUG] Initializing dropdown fixes...');
      
      // Prevent Bootstrap from auto-detecting dropup by intercepting dropdown plugin
      if ($.fn.dropdown && $.fn.dropdown.Constructor) {
        var originalGetPlacement = $.fn.dropdown.Constructor.prototype.getPlacement;
        if (originalGetPlacement) {
          $.fn.dropdown.Constructor.prototype.getPlacement = function() {
            // Force 'bottom' placement for header dropdowns
            if ($(this.$element).closest('.main-header').length > 0) {
              console.log('[DROPDOWN DEBUG] Forcing bottom placement for header dropdown');
              return 'bottom';
            }
            return originalGetPlacement.call(this);
          };
          console.log('[DROPDOWN DEBUG] Dropdown placement override installed');
        } else {
          console.log('[DROPDOWN DEBUG] getPlacement method not found, using alternative method');
        }
      }
      
      // Monitor for dropup class being added via MutationObserver
      var observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
          if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
            var $target = $(mutation.target);
            if ($target.hasClass('dropdown') && $target.hasClass('dropup') && $target.closest('.main-header').length > 0) {
              console.log('[DROPDOWN DEBUG] Dropup class detected via MutationObserver, removing it');
              $target.removeClass('dropup');
              var $menu = $target.find('.dropdown-menu');
              $menu.css({
                'top': '100%',
                'bottom': 'auto',
                'margin-top': '5px'
              });
            }
          }
        });
      });
      
      // Observe all dropdowns in header
      $('.main-header .dropdown').each(function() {
        observer.observe(this, {
          attributes: true,
          attributeFilter: ['class']
        });
      });
      console.log('[DROPDOWN DEBUG] MutationObserver installed for', $('.main-header .dropdown').length, 'dropdowns');
      
      // Monitor all dropdown toggles in header
      $('.main-header .dropdown-toggle').on('click', function(e) {
        var $toggle = $(this);
        var $dropdown = $toggle.closest('.dropdown');
        var $menu = $dropdown.find('.dropdown-menu');
        
        console.log('[DROPDOWN DEBUG] Dropdown clicked:', {
          toggle: $toggle[0],
          dropdown: $dropdown[0],
          menu: $menu[0],
          hasDropup: $dropdown.hasClass('dropup'),
          menuDisplay: $menu.css('display'),
          menuPosition: $menu.css('position'),
          menuTop: $menu.css('top'),
          menuBottom: $menu.css('bottom'),
          menuZIndex: $menu.css('z-index')
        });
        
        // Immediately remove dropup if present
        if ($dropdown.hasClass('dropup')) {
          console.log('[DROPDOWN DEBUG] Removing dropup class on click');
          $dropdown.removeClass('dropup');
        }
      });
      
      // Fix dropdown positioning when opened
      $('.main-header .dropdown').on('show.bs.dropdown', function(e) {
        var $dropdown = $(this);
        var $menu = $dropdown.find('.dropdown-menu');
        
        console.log('[DROPDOWN DEBUG] Dropdown opening:', {
          dropdown: $dropdown[0],
          menu: $menu[0],
          hasDropup: $dropdown.hasClass('dropup')
        });
        
        // Remove dropup class if present
        if ($dropdown.hasClass('dropup')) {
          console.log('[DROPDOWN DEBUG] Removing dropup class');
          $dropdown.removeClass('dropup');
        }
        
        // Force menu to appear below
        setTimeout(function() {
          var menuTop = $menu.css('top');
          var menuBottom = $menu.css('bottom');
          var computedStyle = window.getComputedStyle($menu[0]);
          
          console.log('[DROPDOWN DEBUG] Menu computed styles:', {
            top: computedStyle.top,
            bottom: computedStyle.bottom,
            position: computedStyle.position,
            display: computedStyle.display,
            visibility: computedStyle.visibility,
            zIndex: computedStyle.zIndex,
            overflow: computedStyle.overflow,
            parentOverflow: window.getComputedStyle($menu[0].parentElement).overflow
          });
          
          // Force correct positioning
          $menu.css({
            'top': '100%',
            'bottom': 'auto',
            'margin-top': '5px',
            'position': 'absolute',
            'z-index': '1050',
            'display': 'block'
          });
          
          // Check if menu is visible
          var rect = $menu[0].getBoundingClientRect();
          console.log('[DROPDOWN DEBUG] Menu bounding rect:', {
            top: rect.top,
            bottom: rect.bottom,
            left: rect.left,
            right: rect.right,
            width: rect.width,
            height: rect.height,
            visible: rect.top >= 0 && rect.left >= 0 && rect.bottom <= window.innerHeight && rect.right <= window.innerWidth
          });
        }, 10);
      });
      
      // Additional fix on shown event
      $('.main-header .dropdown').on('shown.bs.dropdown', function(e) {
        var $dropdown = $(this);
        var $menu = $dropdown.find('.dropdown-menu');
        
        console.log('[DROPDOWN DEBUG] Dropdown shown, final check');
        
        // Ensure menu is positioned correctly
        $menu.css({
          'top': '100%',
          'bottom': 'auto',
          'position': 'absolute',
          'z-index': '1050'
        });
        
        // Remove any dropup class
        $dropdown.removeClass('dropup');
      });
      
      // Prevent dropup class from being added via jQuery addClass
      var originalAddClass = $.fn.addClass;
      $.fn.addClass = function() {
        var result = originalAddClass.apply(this, arguments);
        // Check if dropup was added to header dropdowns
        if (arguments[0] && (arguments[0].indexOf('dropup') !== -1 || arguments[0] === 'dropup')) {
          this.each(function() {
            var $el = $(this);
            if ($el.hasClass('dropdown') && $el.hasClass('dropup') && $el.closest('.main-header').length > 0) {
              console.log('[DROPDOWN DEBUG] Prevented dropup class via addClass override');
              $el.removeClass('dropup');
              var $menu = $el.find('.dropdown-menu');
              $menu.css({
                'top': '100%',
                'bottom': 'auto',
                'margin-top': '5px'
              });
            }
          });
        }
        return result;
      };
      
      // Continuous monitoring to catch any dropup additions
      setInterval(function() {
        $('.main-header .dropdown.dropup').each(function() {
          var $dropdown = $(this);
          console.log('[DROPDOWN DEBUG] Found dropup class in continuous check, removing');
          $dropdown.removeClass('dropup');
          var $menu = $dropdown.find('.dropdown-menu');
          $menu.css({
            'top': '100%',
            'bottom': 'auto',
            'margin-top': '5px'
          });
        });
      }, 100);
      
      console.log('[DROPDOWN DEBUG] Dropdown fixes initialized');
      // Show/hide clear button based on input
      $('#navbar-search-input').on('input', function() {
        if ($(this).val().length > 0) {
          $('#navbar-search-clear').show();
          $('#navbar-search-submit').hide();
        } else {
          $('#navbar-search-clear').hide();
          $('#navbar-search-submit').show();
        }
      });

      // Clear search
      $('#navbar-search-clear').on('click', function(e) {
        e.preventDefault();
        $('#navbar-search-input').val('');
        $(this).hide();
        $('#navbar-search-submit').show();
      });

      // Submit form on Enter key
      $('#navbar-search-input').on('keypress', function(e) {
        if (e.which === 13) {
          e.preventDefault();
          if ($(this).val().trim() !== '') {
            $('#header-student-search-form').submit();
          }
        }
      });

      // Form submission - ensure search_text1 is sent
      $('#header-student-search-form').on('submit', function(e) {
        var searchValue = $('#navbar-search-input').val().trim();
        if (searchValue === '') {
          e.preventDefault();
          return false;
        }
        // Ensure the value is properly set
        $(this).find('input[name="search_text1"]').val(searchValue);
      });
    });
    
    function setCurrency(currencyId) {
      if (!currencyId) {
        return;
      }
      $.ajax({
        type: 'POST',
        url: baseurl + 'admin/currency/change_currency',
        data: { currency_id: currencyId },
        dataType: 'json',
        success: function (res) {
          if (res.status == 1) {
            if (typeof successMsg === 'function') {
              successMsg(res.message);
            }
            window.location.reload();
          } else if (res.message) {
            alert(res.message);
          }
        },
        error: function () {
          alert('Could not change currency. Please try again.');
        }
      });
    }
  </script>
