<ul class="sessionul fixedmenu">
    <?php
// Ensure menu helper is loaded first (critical for quick links functionality)
$this->load->helper('menu');

// Debug: Check if required objects exist
$debug_info = array();
try {
    $debug_info['rbac_exists'] = isset($this->rbac);
    $debug_info['lang_exists'] = isset($this->lang);
    $debug_info['customlib_exists'] = isset($this->customlib);
    $debug_info['setting_model_exists'] = isset($this->setting_model);
    
    if (isset($this->customlib) && method_exists($this->customlib, 'convertSessionToBS')) {
        $debug_info['convertSessionToBS_exists'] = true;
    } else {
        $debug_info['convertSessionToBS_exists'] = false;
    }
    
    if (isset($this->setting_model) && method_exists($this->setting_model, 'getCurrentSessionName')) {
        $debug_info['getCurrentSessionName_exists'] = true;
    } else {
        $debug_info['getCurrentSessionName_exists'] = false;
    }
} catch (Exception $e) {
    $debug_info['error'] = $e->getMessage();
}
?>
<?php
if ($this->rbac->hasPrivilege('quick_session_change', 'can_view')) {
    ?>
        <li class="removehover">
            <!-- Works with this JS-driven opener -->
            <a id="openSessionModal" href="javascript:void(0)">
                <span><?php 
                try {
                    $session_name = $this->setting_model->getCurrentSessionName();
                    // Display session name as-is without any conversion
                    echo $this->lang->line('current_session') . ": " . $session_name;
                } catch (Exception $e) {
                    // Fallback: Just show session name without conversion
                    try {
                        $session_name = $this->setting_model->getCurrentSessionName();
                        echo $this->lang->line('current_session') . ": " . $session_name;
                    } catch (Exception $e2) {
                        echo $this->lang->line('current_session');
                    }
                    error_log('Top sidemenu error: ' . $e->getMessage());
                }
                ?></span>
                <i class="fa fa-pencil pull-right"></i>
            </a>
            <!-- If you prefer the original data-attrs opener, JS below supports it too:
            <a data-toggle="modal" data-target="#sessionModal">
                <span>...</span><i class="fa fa-pencil pull-right"></i>
            </a>
            -->
        </li>
    <?php }?>

     <li class="dropdown mega-dropdown" style="position: relative;">
        <a class="dropdown-toggle drop5 quick-links-toggle" href="javascript:void(0);" aria-expanded="false">
            <span><?php echo $this->lang->line('quick_links'); ?></span> <i class="fa fa-th pull-right"></i>
        </a>
        <div class="dropdown-menu verticalmenu side-navbar-vertical" style="display: none; position: absolute; z-index: 999999;">
            <div class="side-navbar-width" style="overflow-y: auto !important; max-height: 85vh !important;">
                <div class="card-columns-sidebar">
       <?php
// Ensure menu helper is loaded
$this->load->helper('menu');

// Safely get side menu list
$side_list = array();
if (function_exists('side_menu_list')) {
    try {
        $side_list = side_menu_list('-1');
        if (!is_array($side_list)) {
            $side_list = array();
        }
    } catch (Exception $e) {
        $side_list = array();
    } catch (Throwable $e) {
        $side_list = array();
    }
}

if (!empty($side_list)) {
    foreach ($side_list as $side_list_key => $side_list_value) {

        // Get module permissions
        $module_permission = array();
        if (function_exists('access_permission_sidebar_remove_pipe') && isset($side_list_value->access_permissions)) {
            try {
                $module_permission = access_permission_sidebar_remove_pipe($side_list_value->access_permissions);
                if (!is_array($module_permission)) {
                    $module_permission = array();
                }
            } catch (Exception $e) {
                $module_permission = array();
            }
        }

        $module_access = false;
        if (!empty($module_permission)) {
            foreach ($module_permission as $m_permission_key => $m_permission_value) {
                // Get category permissions
                $cat_permission = array();
                if (function_exists('access_permission_remove_comma') && !empty($m_permission_value)) {
                    try {
                        $cat_permission = access_permission_remove_comma($m_permission_value);
                        if (!is_array($cat_permission)) {
                            $cat_permission = array();
                        }
                    } catch (Exception $e) {
                        $cat_permission = array();
                    }
                }

                if (!empty($cat_permission) && count($cat_permission) >= 2 && isset($cat_permission[0]) && isset($cat_permission[1]) && $this->rbac->hasPrivilege($cat_permission[0], $cat_permission[1])) {

                    $module_access = true;
                    break;
                }
            }
        }

        if ($module_access) {
            if ($this->module_lib->hasModule($side_list_value->short_code) && $this->module_lib->hasActive($side_list_value->short_code)) {
                ?>
                    <div class="card-sidebar">
                        <h4><i class="<?php echo $side_list_value->icon; ?>"></i> <?php echo $this->lang->line($side_list_value->lang_key); ?></h4>
                        <?php
                if (!empty($side_list_value->submenus)) {
                    ?>
                        <ul>
                            <?php
foreach ($side_list_value->submenus as $submenu_key => $submenu_value) {

                        // Get sidebar permissions
                        $sidebar_permission = array();
                        if (function_exists('access_permission_sidebar_remove_pipe') && isset($submenu_value->access_permissions)) {
                            try {
                                $sidebar_permission = access_permission_sidebar_remove_pipe($submenu_value->access_permissions);
                                if (!is_array($sidebar_permission)) {
                                    $sidebar_permission = array();
                                }
                            } catch (Exception $e) {
                                $sidebar_permission = array();
                            }
                        }
                        $sidebar_access     = false;

                        if (!empty($sidebar_permission)) {
                            foreach ($sidebar_permission as $sidebar_permission_key => $sidebar_permission_value) {
                                // Get sidebar category permissions
                                $sidebar_cat_permission = array();
                                if (function_exists('access_permission_remove_comma') && !empty($sidebar_permission_value)) {
                                    try {
                                        $sidebar_cat_permission = access_permission_remove_comma($sidebar_permission_value);
                                        if (!is_array($sidebar_cat_permission)) {
                                            $sidebar_cat_permission = array();
                                        }
                                    } catch (Exception $e) {
                                        $sidebar_cat_permission = array();
                                    }
                                }

                                if ($submenu_value->addon_permission != "") {
                                    if (!empty($sidebar_cat_permission) && count($sidebar_cat_permission) >= 2 
                                        && !empty($sidebar_permission) && count($sidebar_permission) >= 2
                                        && isset($sidebar_cat_permission[0]) && isset($sidebar_permission[1])
                                        && $this->rbac->hasPrivilege($sidebar_cat_permission[0], $sidebar_permission[1])
                                        && $this->auth->addonchk($submenu_value->addon_permission, false)) {
                                        $sidebar_access = true;
                                        break;
                                    }
                                } else {
                                    if (!empty($sidebar_cat_permission) && count($sidebar_cat_permission) >= 2 
                                        && isset($sidebar_cat_permission[0]) && isset($sidebar_cat_permission[1])
                                        && $this->rbac->hasPrivilege($sidebar_cat_permission[0], $sidebar_cat_permission[1])) {
                                        $sidebar_access = true;
                                        break;
                                    }
                                }
                            }
                        }

                        if ($sidebar_access) {
                            if (!empty($submenu_value->permission_group_id)) {
                                if (!$this->module_lib->hasActive($submenu_value->short_code)) {
                                    continue;
                                }
                            }
                            ?>
                        <li>
                          <a href="<?php echo site_url($submenu_value->url); ?>"><i class="fa fa-angle-double-right"></i><?php echo $this->lang->line($submenu_value->lang_key); ?></a>
                        </li>
                          <?php
}
                    }
                    ?>
                        </ul>
                        <?php
                }
                ?>
                    </div>
                    <?php
}
        }
        ?>
                 <?php
}
}
?>
                </div>
            </div>
        </div>
    </li>
</ul>

<style>
/* Ensure Quick Links dropdown is visible and properly styled */
.mega-dropdown {
  position: relative !important;
  overflow: visible !important;
}

.mega-dropdown .dropdown-menu.verticalmenu {
  position: fixed !important;
  z-index: 999999 !important;
  display: none !important;
  visibility: hidden !important;
  opacity: 0 !important;
  background: #fff !important;
  border: none !important;
  border-radius: 0 !important;
  box-shadow: none !important;
  overflow: hidden !important;
  overflow-x: hidden !important;
  overflow-y: hidden !important;
  padding: 0 !important;
  margin: 0 !important;
  top: 50px !important;
  left: 200px !important;
  right: 0 !important;
  bottom: 0 !important;
  width: auto !important;
  height: auto !important;
  /* Ensure dropdown is above all sidebar elements including scrollbars */
  pointer-events: auto !important;
}

/* Handle sidebar collapse state */
body.sidebar-collapse .mega-dropdown .dropdown-menu.verticalmenu,
.sidebar-collapse .mega-dropdown .dropdown-menu.verticalmenu {
  left: 0 !important;
}

/* Match reference project media queries */
@media (min-width: 1024px) {
  .mega-dropdown .card-sidebar {
    display: inline-block !important;
    width: 200px !important;
  }
  .mega-dropdown .card-columns-sidebar {
    -webkit-column-count: 5 !important;
    -moz-column-count: 5 !important;
    column-count: 5 !important;
    column-gap: 3rem !important;
  }
}

@media (max-width: 1023px) {
  .mega-dropdown .card-sidebar {
    display: inline-block !important;
    width: 200px !important;
  }
  .mega-dropdown .card-columns-sidebar {
    -webkit-column-count: 3 !important;
    -moz-column-count: 3 !important;
    column-count: 3 !important;
    column-gap: 3rem !important;
  }
}

@media (max-width: 768px) {
  .mega-dropdown .dropdown-menu.verticalmenu {
    left: 0 !important;
    top: 50px !important;
  }
  .mega-dropdown .card-sidebar {
    display: block !important;
    width: 100% !important;
  }
  .mega-dropdown .card-columns-sidebar {
    -webkit-column-count: 1 !important;
    -moz-column-count: 1 !important;
    column-count: 1 !important;
    column-gap: 3rem !important;
  }
}

.mega-dropdown.open .dropdown-menu.verticalmenu {
  display: block !important;
  visibility: visible !important;
  opacity: 1 !important;
}

/* Scroll area styling - MATCH REFERENCE */
.mega-dropdown .side-navbar-width {
  height: 100% !important;
  overflow-y: auto !important;
  overflow-x: hidden !important;
  padding-top: 1.5rem !important;
  padding-bottom: 2rem !important;
  background: #fff !important;
  position: relative !important;
}

/* Hide mCustomScrollbar if it gets applied */
.mega-dropdown .side-navbar-width.mCustomScrollbar {
  overflow-y: auto !important;
}

.mega-dropdown .side-navbar-width .mCSB_container {
  margin-right: 0 !important;
  padding-right: 0 !important;
}

/* Card columns layout - EXACT MATCH FROM REFERENCE PROJECT */
.mega-dropdown .dropdown-menu.verticalmenu .side-navbar-width .card-columns-sidebar,
.mega-dropdown .side-navbar-width .card-columns-sidebar,
.mega-dropdown .card-columns-sidebar {
  -webkit-column-count: 5 !important;
  -moz-column-count: 5 !important;
  column-count: 5 !important;
  column-gap: 3rem !important;
  margin: 0 !important;
  padding: 0 !important;
  width: 100% !important;
}

.mega-dropdown .dropdown-menu.verticalmenu .side-navbar-width .card-columns-sidebar .card-sidebar,
.mega-dropdown .side-navbar-width .card-columns-sidebar .card-sidebar,
.mega-dropdown .card-columns-sidebar .card-sidebar,
.mega-dropdown .card-sidebar {
  display: inline-block !important;
  width: 200px !important;
  padding: 0 !important;
  background: transparent !important;
  border: none !important;
  margin: 0 !important;
  margin-top: 0 !important;
  margin-bottom: 0 !important;
  vertical-align: top !important;
}

.mega-dropdown .card-columns-sidebar > .card-sidebar:first-child {
  margin-top: 0 !important;
  padding-top: 0 !important;
}

.mega-dropdown .card-sidebar h4 {
  color: #000 !important;
  padding-bottom: 0 !important;
  margin-top: 0 !important;
  margin-bottom: 0.2rem !important;
  font-size: 14px !important;
  font-family: 'Roboto-Medium' !important;
  font-weight: normal !important;
  text-transform: none !important;
  letter-spacing: normal !important;
  line-height: normal !important;
  border-bottom: none !important;
}

.mega-dropdown .card-sidebar h4 i {
  width: 18px !important;
  margin-right: 5px !important;
}

.mega-dropdown .card-sidebar ul {
  list-style: none !important;
  padding: 0 0 1rem 0 !important;
  margin: 0 0 0.5rem 0.5rem !important;
  border-bottom: 1px solid #f4f4f4 !important;
}

.mega-dropdown .card-sidebar ul li {
  display: block !important;
  margin: 0 !important;
  padding: 0 !important;
}

.mega-dropdown .card-sidebar ul li a {
  color: #282828 !important;
  padding: 1px 3px !important;
  font-size: 1.2rem !important;
  line-height: normal !important;
  text-decoration: none !important;
}

.mega-dropdown .card-sidebar ul li a:hover {
  color: #faa21c !important;
}

.mega-dropdown .card-sidebar ul li a .fa {
  width: 14px !important;
}

/* Ensure sidebar allows overflow for dropdown positioning */
.sessionul.fixedmenu {
  overflow: visible !important;
}

.sessionul.fixedmenu li {
  overflow: visible !important;
}

/* Only allow overflow visible on main-sidebar for dropdown positioning */
aside.main-sidebar {
  overflow: visible !important;
}

/* Keep sidebar scrollable - don't override overflow here */
/* section.sidebar and #sibe-box should maintain their natural overflow settings */

/* Hide main sidebar scrollbar when quick links dropdown is open - AGGRESSIVE */
body.quick-links-open aside.main-sidebar::-webkit-scrollbar,
body.quick-links-open aside.main-sidebar *::-webkit-scrollbar,
body.quick-links-open section.sidebar::-webkit-scrollbar,
body.quick-links-open section.sidebar *::-webkit-scrollbar,
body.quick-links-open #sibe-box::-webkit-scrollbar,
body.quick-links-open #sibe-box *::-webkit-scrollbar,
body.quick-links-open .main-sidebar::-webkit-scrollbar,
body.quick-links-open .main-sidebar *::-webkit-scrollbar,
body.quick-links-open .sidebar::-webkit-scrollbar,
body.quick-links-open .sidebar *::-webkit-scrollbar,
.mega-dropdown.open ~ * aside.main-sidebar::-webkit-scrollbar,
.mega-dropdown.open ~ * section.sidebar::-webkit-scrollbar,
.mega-dropdown.open ~ * #sibe-box::-webkit-scrollbar {
  display: none !important;
  width: 0px !important;
  background: transparent !important;
  visibility: hidden !important;
  opacity: 0 !important;
}

/* Hide scrollbar for Firefox when quick links is open */
body.quick-links-open aside.main-sidebar,
body.quick-links-open aside.main-sidebar *,
body.quick-links-open section.sidebar,
body.quick-links-open section.sidebar *,
body.quick-links-open #sibe-box,
body.quick-links-open #sibe-box *,
body.quick-links-open .main-sidebar,
body.quick-links-open .main-sidebar *,
body.quick-links-open .sidebar,
body.quick-links-open .sidebar *,
.mega-dropdown.open ~ * aside.main-sidebar,
.mega-dropdown.open ~ * section.sidebar,
.mega-dropdown.open ~ * #sibe-box {
  scrollbar-width: none !important;
  -ms-overflow-style: none !important;
}

/* Prevent main sidebar from scrolling when quick links is open */
/* Only target the scrollable container, not the outer wrapper */
body.quick-links-open section.sidebar,
body.quick-links-open #sibe-box {
  overflow: hidden !important;
  overflow-y: hidden !important;
}

/* Keep main-sidebar overflow visible for dropdown positioning */
body.quick-links-open aside.main-sidebar {
  overflow: visible !important;
}

/* Custom scrollbar styling for dropdown - FORCE SCROLLBAR TO BE VISIBLE ALWAYS */
.mega-dropdown.open .side-navbar-width,
.mega-dropdown .dropdown-menu.verticalmenu .side-navbar-width {
  overflow-y: scroll !important;
  overflow-x: hidden !important;
  scrollbar-width: thin !important;
  scrollbar-color: #c1c1c1 #f5f5f5 !important;
  -ms-overflow-style: scrollbar !important;
}

.mega-dropdown .side-navbar-width::-webkit-scrollbar {
  width: 8px !important;
  display: block !important;
  visibility: visible !important;
  opacity: 1 !important;
  -webkit-appearance: none !important;
}

.mega-dropdown .side-navbar-width::-webkit-scrollbar-track {
  background: #f5f5f5 !important;
  border-radius: 4px !important;
  margin: 5px 0 !important;
  display: block !important;
  visibility: visible !important;
  -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.1) !important;
}

.mega-dropdown .side-navbar-width::-webkit-scrollbar-thumb {
  background: #c1c1c1 !important;
  border-radius: 4px !important;
  transition: background 0.2s ease !important;
  display: block !important;
  visibility: visible !important;
  -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3) !important;
}

.mega-dropdown .side-navbar-width::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8 !important;
}

/* Firefox scrollbar - MUST BE VISIBLE */
.mega-dropdown .side-navbar-width {
  scrollbar-width: thin !important;
  scrollbar-color: #c1c1c1 #f5f5f5 !important;
  -ms-overflow-style: scrollbar !important;
}

/* ULTRA AGGRESSIVE OVERRIDE - Match reference project exactly */
.mega-dropdown.open .card-columns-sidebar,
.mega-dropdown.open .side-navbar-width .card-columns-sidebar,
.mega-dropdown.open .dropdown-menu .card-columns-sidebar,
.mega-dropdown.open .dropdown-menu.verticalmenu .side-navbar-width .card-columns-sidebar {
  -webkit-column-count: 5 !important;
  -moz-column-count: 5 !important;
  column-count: 5 !important;
  column-gap: 3rem !important;
}

.mega-dropdown.open .card-sidebar,
.mega-dropdown.open .card-columns-sidebar .card-sidebar {
  display: inline-block !important;
  width: 200px !important;
}
</style>

<script>
console.log('[TOP_SIDEMENU DEBUG] Script block starting...');
console.log('[TOP_SIDEMENU DEBUG] jQuery available:', typeof jQuery !== 'undefined');
console.log('[TOP_SIDEMENU DEBUG] window.jQuery available:', typeof window.jQuery !== 'undefined');
console.log('[TOP_SIDEMENU DEBUG] window.$ available:', typeof window.$ !== 'undefined');
console.log('[TOP_SIDEMENU DEBUG] Document ready state:', document.readyState);

// 1) Keep your existing vertical menu guard - wait for jQuery
// Ensure jQuery is loaded before executing
try {
  if (typeof jQuery !== 'undefined') {
    console.log('[TOP_SIDEMENU DEBUG] jQuery found, initializing vertical menu guard...');
    (function($) {
      $(document).ready(function() {
        console.log('[TOP_SIDEMENU DEBUG] Document ready, setting up vertical menu guard');
        $('.verticalmenu').on('click', function(event){
          event.stopPropagation();
        });
        console.log('[TOP_SIDEMENU DEBUG] Vertical menu guard attached');
      });
    })(jQuery);
  } else {
    console.warn('[TOP_SIDEMENU DEBUG] jQuery not found, waiting for load event...');
    // Fallback: wait for jQuery to load
    window.addEventListener('load', function() {
      console.log('[TOP_SIDEMENU DEBUG] Window load event fired');
      if (typeof jQuery !== 'undefined') {
        console.log('[TOP_SIDEMENU DEBUG] jQuery now available, setting up vertical menu guard');
        jQuery('.verticalmenu').on('click', function(event){
          event.stopPropagation();
        });
      } else {
        console.error('[TOP_SIDEMENU DEBUG] jQuery still not available after load event');
      }
    });
  }
} catch (e) {
  console.error('[TOP_SIDEMENU DEBUG] Error in vertical menu guard:', e);
}

// 1.5) Fix Quick Links dropdown toggle
console.log('[TOP_SIDEMENU DEBUG] Starting Quick Links initialization...');
try {
  if (typeof jQuery === 'undefined') {
    console.error('[TOP_SIDEMENU DEBUG] jQuery is undefined, cannot initialize Quick Links');
    console.error('[TOP_SIDEMENU DEBUG] Available globals:', {
      jQuery: typeof jQuery,
      window_jQuery: typeof window.jQuery,
      window_$: typeof window.$,
      $: typeof $
    });
  } else {
    console.log('[TOP_SIDEMENU DEBUG] jQuery found, proceeding with Quick Links init');
  }
  
  (function($) {
    if (typeof $ === 'undefined') {
      console.error('[QUICK LINKS DEBUG] $ parameter is undefined in IIFE');
      return;
    }
    
    console.log('[QUICK LINKS DEBUG] Script loading...');
    console.log('[QUICK LINKS DEBUG] jQuery version:', $.fn.jquery || 'unknown');
    
    try {
      $(document).ready(function() {
        console.log('[QUICK LINKS DEBUG] Document ready, initializing...');
        
        // Check if element exists
        var $toggleBtn = $('.quick-links-toggle');
        var $dropdown = $('.mega-dropdown');
        var $menu = $('.mega-dropdown .dropdown-menu');
        
        console.log('[QUICK LINKS DEBUG] Element check:', {
          toggleExists: $toggleBtn.length > 0,
          dropdownExists: $dropdown.length > 0,
          menuExists: $menu.length > 0,
          toggleElement: $toggleBtn[0],
          dropdownElement: $dropdown[0],
          menuElement: $menu[0]
        });
        
        // Helper function to disable sidebar scrolling
        function disableSidebarScrolling() {
          $('body').addClass('quick-links-open');
          
          $('section.sidebar, #sibe-box').each(function() {
            var $el = $(this);
            
            // Store original values only if not already stored
            if (!$el.data('original-overflow-stored')) {
              var originalOverflow = $el.css('overflow');
              var originalOverflowY = $el.css('overflow-y');
              
              // If computed style is 'visible', use 'auto' as fallback for scrollable content
              if (originalOverflow === 'visible' || originalOverflow === '') {
                originalOverflow = 'auto';
              }
              if (originalOverflowY === 'visible' || originalOverflowY === '') {
                originalOverflowY = 'auto';
              }
              
              $el.data('original-overflow', originalOverflow);
              $el.data('original-overflow-y', originalOverflowY);
              $el.data('original-overflow-stored', true);
            }
            
            // Prevent scrolling
            $el.css({
              'overflow': 'hidden',
              'overflow-y': 'hidden'
            });
            
            // Prevent scroll events
            $el.on('scroll.quicklinks-disable wheel.quicklinks-disable touchmove.quicklinks-disable', function(e) {
              var $target = $(e.target);
              var isQuickLinks = $target.closest('.mega-dropdown, .side-navbar-width, .dropdown-menu.verticalmenu').length > 0;
              if (!isQuickLinks) {
                e.preventDefault();
                e.stopPropagation();
                return false;
              }
            });
          });
        }
        
        // Helper function to restore sidebar scrolling
        function restoreSidebarScrolling() {
          $('body').removeClass('quick-links-open');
          
          $('section.sidebar, #sibe-box').each(function() {
            var $el = $(this);
            
            // Restore original overflow settings
            var originalOverflow = $el.data('original-overflow');
            var originalOverflowY = $el.data('original-overflow-y');
            
            // Remove scroll prevention handlers
            $el.off('scroll.quicklinks-disable wheel.quicklinks-disable touchmove.quicklinks-disable');
            
            // Restore styles - use 'auto' if original was empty/visible
            if (originalOverflow && originalOverflow !== 'visible') {
              $el.css('overflow', originalOverflow);
            } else {
              $el.css('overflow', 'auto');
            }
            
            if (originalOverflowY && originalOverflowY !== 'visible') {
              $el.css('overflow-y', originalOverflowY);
            } else {
              $el.css('overflow-y', 'auto');
            }
            
            // Remove overflow-x restriction
            $el.css('overflow-x', '');
          });
        }
        
        // Flag to prevent document click from closing immediately after toggle
        var quickLinksToggling = false;
        
        // Handle Quick Links dropdown click - simplified logic
        $('.quick-links-toggle').off('click').on('click', function(e) {
          e.preventDefault();
          e.stopPropagation();
          e.stopImmediatePropagation();
          
          // Set flag to prevent document handler from closing
          quickLinksToggling = true;
          
          var $toggle = $(this);
          var $dropdown = $toggle.closest('.mega-dropdown');
          var $menu = $dropdown.find('.dropdown-menu.verticalmenu');
          
          // Toggle the dropdown
          var isOpen = $dropdown.hasClass('open');
          
          if (isOpen) {
            // Close dropdown
            $dropdown.removeClass('open');
            $menu.css({
              'display': 'none',
              'visibility': 'hidden',
              'opacity': '0'
            });
            $toggle.attr('aria-expanded', 'false');
            
            // Remove resize handler
            $(window).off('resize.quicklinks');
            
            // Restore sidebar scrolling
            restoreSidebarScrolling();
            
            // Clear flag after a short delay
            setTimeout(function() {
              quickLinksToggling = false;
            }, 200);
          } else {
            // Close any other open dropdowns first
            $('.mega-dropdown').not($dropdown).removeClass('open').find('.dropdown-menu').css({
              'display': 'none',
              'visibility': 'hidden',
              'opacity': '0'
            });
            $('.mega-dropdown').not($dropdown).find('.quick-links-toggle').attr('aria-expanded', 'false');
            
            // Open this dropdown
            $dropdown.addClass('open');
            
            // Disable sidebar scrolling while quick links is open
            disableSidebarScrolling();
            
            // Function to calculate and apply position
            function positionQuickLinksMenu() {
              // Get sidebar width (check if collapsed)
              var sidebarWidth = 200;
              var $sidebar = $('.main-sidebar, aside.main-sidebar');
              if ($sidebar.length) {
                // Check if sidebar is collapsed
                if ($('body').hasClass('sidebar-collapse') || $sidebar.hasClass('sidebar-collapse')) {
                  sidebarWidth = 0;
                } else {
                  sidebarWidth = $sidebar.outerWidth() || 200;
                }
              }
              
              // Get header height (default 50px)
              var headerHeight = 50;
              var $header = $('.main-header, header.main-header');
              if ($header.length) {
                headerHeight = $header.outerHeight() || 50;
              }
              
              // Position to cover entire content area (full page overlay)
              $menu.css({
                'display': 'block',
                'visibility': 'visible',
                'opacity': '1',
                'position': 'fixed',
                'top': headerHeight + 'px',
                'left': sidebarWidth + 'px',
                'right': '0',
                'bottom': '0',
                'width': 'auto',
                'height': 'auto',
                'z-index': '999999',
                'background': '#fff',
                'border': 'none',
                'border-radius': '0',
                'box-shadow': 'none',
                'padding': '0',
                'margin': '0',
                'overflow': 'hidden',
                'overflow-x': 'hidden',
                'overflow-y': 'hidden',
                'pointer-events': 'auto'
              });
            }
            
            // Position the menu
            positionQuickLinksMenu();
            
            // Update position on window resize
            $(window).on('resize.quicklinks', function() {
              if ($dropdown.hasClass('open')) {
                positionQuickLinksMenu();
              }
            });
            
            // Ensure scroll area has proper styling
            var $scrollArea = $menu.find('.side-navbar-width');
            if ($scrollArea.length) {
              $scrollArea.removeClass('mCustomScrollbar scroll-area');
              $scrollArea.css({
                'height': '100%',
                'overflow-y': 'auto',
                'overflow-x': 'hidden',
                'padding-top': '1.5rem',
                'padding-bottom': '2rem'
              });
            }
            
            // FORCE GRID LAYOUT - Apply directly via JavaScript
            var $cardColumns = $menu.find('.card-columns-sidebar');
            if ($cardColumns.length) {
              // Match reference project exactly - use CSS columns
              $cardColumns.css({
                '-webkit-column-count': '5',
                '-moz-column-count': '5',
                'column-count': '5',
                'column-gap': '3rem',
                'margin': '0',
                'padding': '0',
                'width': '100%'
              });
              
              // Match reference project - inline-block with 200px width
              $cardColumns.find('.card-sidebar').css({
                'display': 'inline-block',
                'width': '200px',
                'padding': '0',
                'background': 'transparent',
                'border': 'none',
                'margin': '0',
                'vertical-align': 'top'
              });
              
              // Match reference project h4 styling
              $cardColumns.find('.card-sidebar h4').css({
                'color': '#000',
                'padding-bottom': '0',
                'margin-top': '0',
                'margin-bottom': '0.2rem',
                'font-size': '14px',
                'font-family': 'Roboto-Medium',
                'font-weight': 'normal',
                'text-transform': 'none',
                'border-bottom': 'none'
              });
              
              // Match reference project ul styling
              $cardColumns.find('.card-sidebar ul').css({
                'padding': '0 0 1rem 0',
                'margin': '0 0 0.5rem 0.5rem',
                'border-bottom': '1px solid #f4f4f4'
              });
              
              // Match reference project link styling
              $cardColumns.find('.card-sidebar ul li a').css({
                'color': '#282828',
                'padding': '1px 3px',
                'font-size': '1.2rem',
                'line-height': 'normal'
              });
            }
            
            $toggle.attr('aria-expanded', 'true');
            
            // Clear flag after menu is shown
            setTimeout(function() {
              quickLinksToggling = false;
            }, 200);
          }
          
          return false;
        });
        
        // Close dropdown when clicking outside
        $(document).off('click.quicklinks').on('click.quicklinks', function(e) {
          // Don't close if we're currently toggling
          if (quickLinksToggling) {
            return;
          }
          
          var $target = $(e.target);
          var clickedOnToggle = $target.closest('.quick-links-toggle').length > 0;
          var clickedInside = $target.closest('.mega-dropdown').length > 0;
          var $dropdown = $('.mega-dropdown');
          var isCurrentlyOpen = $dropdown.hasClass('open');
          
          // Ignore clicks on toggle or inside dropdown
          if (clickedOnToggle || clickedInside) {
            return;
          }
          
          // Close if dropdown is open
          if (isCurrentlyOpen) {
            $('.mega-dropdown').removeClass('open').find('.dropdown-menu.verticalmenu').css({
              'display': 'none',
              'visibility': 'hidden',
              'opacity': '0'
            });
            $('.quick-links-toggle').attr('aria-expanded', 'false');
            restoreSidebarScrolling();
          }
        });
        
        // Prevent dropdown from closing when clicking inside the menu
        $(document).on('click', '.mega-dropdown .dropdown-menu.verticalmenu', function(e) {
          e.stopPropagation();
          e.stopImmediatePropagation();
        });
        
        // Also prevent clicks inside the scroll area from closing
        $(document).on('click', '.mega-dropdown .side-navbar-width', function(e) {
          e.stopPropagation();
          e.stopImmediatePropagation();
        });
        
        // Test click handler
        setTimeout(function() {
          console.log('[QUICK LINKS DEBUG] Testing element availability after 1 second...');
          var $testToggle = $('.quick-links-toggle');
          var $testDropdown = $('.mega-dropdown');
          var $testMenu = $('.mega-dropdown .dropdown-menu');
          
          console.log('[QUICK LINKS DEBUG] Delayed check:', {
            toggleExists: $testToggle.length > 0,
            dropdownExists: $testDropdown.length > 0,
            menuExists: $testMenu.length > 0,
            toggleClasses: $testToggle.attr('class'),
            dropdownClasses: $testDropdown.attr('class'),
            menuClasses: $testMenu.attr('class')
          });
        }, 1000);
      });
    } catch (innerError) {
      console.error('[QUICK LINKS DEBUG] Error inside document.ready:', innerError);
      console.error('[QUICK LINKS DEBUG] Error stack:', innerError.stack);
    }
  })(jQuery);
} catch (outerError) {
  console.error('[TOP_SIDEMENU DEBUG] Error initializing Quick Links:', outerError);
  console.error('[TOP_SIDEMENU DEBUG] Error stack:', outerError.stack);
  console.error('[TOP_SIDEMENU DEBUG] Error message:', outerError.message);
}

// 2) Guard mCustomScrollbar to avoid breaking all JS if plugin is missing
console.log('[TOP_SIDEMENU DEBUG] Initializing mCustomScrollbar...');
(function(){
  try {
    console.log('[TOP_SIDEMENU DEBUG] Checking for jQuery and mCustomScrollbar...');
    console.log('[TOP_SIDEMENU DEBUG] typeof $:', typeof $);
    console.log('[TOP_SIDEMENU DEBUG] typeof jQuery:', typeof jQuery);
    
    if (typeof jQuery !== 'undefined') {
      var $ = jQuery;
      if ($ && $.fn && $.fn.mCustomScrollbar) {
        console.log('[TOP_SIDEMENU DEBUG] mCustomScrollbar found, initializing...');
        // Exclude quick links dropdown from mCustomScrollbar to prevent double scrollbar
        $(".mCustomScrollbar").not('.mega-dropdown .side-navbar-width').mCustomScrollbar({
          scrollInertia: 1000,
          mouseWheelPixels: 170,
          autoDraggerLength:false
        });
        console.log('[TOP_SIDEMENU DEBUG] mCustomScrollbar initialized successfully (excluding quick links)');
      } else {
        console.warn('[TOP_SIDEMENU DEBUG] mCustomScrollbar plugin not found; skipping init');
        console.warn('[TOP_SIDEMENU DEBUG] $.fn:', $.fn ? 'exists' : 'undefined');
        console.warn('[TOP_SIDEMENU DEBUG] $.fn.mCustomScrollbar:', $.fn && $.fn.mCustomScrollbar ? 'exists' : 'undefined');
      }
    } else {
      console.warn('[TOP_SIDEMENU DEBUG] jQuery not available for mCustomScrollbar init');
    }
  } catch (e) {
    console.error('[TOP_SIDEMENU DEBUG] Error initializing mCustomScrollbar:', e);
    console.error('[TOP_SIDEMENU DEBUG] Error details:', {
      message: e.message,
      stack: e.stack,
      name: e.name
    });
  }
})();

// 3) Robust modal open logic (supports Bootstrap 3/4/5)
console.log('[TOP_SIDEMENU DEBUG] Initializing session modal handler...');
try {
  if (typeof jQuery !== 'undefined') {
    console.log('[TOP_SIDEMENU DEBUG] jQuery available for session modal');
    (function($){
      console.log('[TOP_SIDEMENU DEBUG] Inside session modal IIFE');
      $(function(){
        console.log('[TOP_SIDEMENU DEBUG] Document ready in session modal handler');
        var $modal = $('#sessionModal');
        console.log('[TOP_SIDEMENU DEBUG] Session modal element found:', $modal.length > 0);

        if (!$modal.length) {
          console.error('[TOP_SIDEMENU DEBUG] Session modal (#sessionModal) not found in DOM.');
          console.error('[TOP_SIDEMENU DEBUG] Searching for modal in body...');
          var bodyModal = document.getElementById('sessionModal');
          console.error('[TOP_SIDEMENU DEBUG] Modal in document:', bodyModal ? 'found' : 'not found');
          return; // nothing to open
        }

        // Make sure modal is a direct child of <body> to avoid dropdown interference
        try { 
          $modal.appendTo('body'); 
          console.log('[TOP_SIDEMENU DEBUG] Modal moved to body');
        } catch(e) {
          console.error('[TOP_SIDEMENU DEBUG] Error moving modal to body:', e);
        }

        // Click handler supports BOTH: new #openSessionModal and legacy [data-target="#sessionModal"]
        $(document).on('click', '#openSessionModal, a[data-target="#sessionModal"]', function(e){
          console.log('[TOP_SIDEMENU DEBUG] Session modal trigger clicked');
          e.preventDefault();
          e.stopPropagation();

          if (!$.fn || !$.fn.modal) {
            console.error('[TOP_SIDEMENU DEBUG] Bootstrap modal plugin ($.fn.modal) not found. Include bootstrap.js before this script.');
            console.error('[TOP_SIDEMENU DEBUG] $.fn:', $.fn ? 'exists' : 'undefined');
            console.error('[TOP_SIDEMENU DEBUG] $.fn.modal:', $.fn && $.fn.modal ? 'exists' : 'undefined');
            return;
          }

          console.log('[TOP_SIDEMENU DEBUG] Opening session modal...');
          // Open the modal
          $modal.modal({ backdrop: 'static', keyboard: true });
          console.log('[TOP_SIDEMENU DEBUG] Session modal opened');

          // Keep dropdown from closing and re-opening flicker
          $('.mega-dropdown').one('hide.bs.dropdown', function(ev){
            if ($modal.hasClass('in') || $modal.hasClass('show')) {
              ev.preventDefault();
              ev.stopImmediatePropagation();
              return false;
            }
          });
        });
        console.log('[TOP_SIDEMENU DEBUG] Session modal handler attached');

      });
    })(jQuery);
  } else {
    console.error('[TOP_SIDEMENU DEBUG] jQuery is not loaded for session modal');
  }
} catch (modalError) {
  console.error('[TOP_SIDEMENU DEBUG] Error in session modal handler:', modalError);
  console.error('[TOP_SIDEMENU DEBUG] Modal error stack:', modalError.stack);
}

console.log('[TOP_SIDEMENU DEBUG] Script block completed');
</script>
