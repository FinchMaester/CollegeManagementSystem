<footer class="main-footer">
    &copy;  <?php echo date('Y'); ?>
    <?php echo $this->customlib->getAppName(); ?>
</footer>
<div class="control-sidebar-bg"></div>
</div>
<!-- jQuery: MUST be loaded before any plugin -->
<script>
    $.widget.bridge('uibutton', $.ui.button);
</script>
<?php
$language      = $this->customlib->getLanguage();
$language_name = $language["short_code"];


$feesinbackdate = $this->customlib->getfeesinbackdate();


?>


<link href="<?php echo base_url(); ?>backend/toast-alert/toastr.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>backend/toast-alert/toastr.js"></script>
<?php /* Bootstrap JS is already loaded once in layout/header.php. Loading it again here
         double-binds the modal/tooltip/dropdown data-api, which made data-toggle="modal"
         buttons open then instantly close. Do not re-include it. */ ?>
<link rel="stylesheet" href="<?php echo base_url(); ?>backend/plugins/select2/select2.min.css">
<script src="<?php echo base_url(); ?>backend/plugins/select2/select2.full.min.js"></script>
<script src="<?php echo base_url(); ?>backend/plugins/input-mask/jquery.inputmask.js"></script>
<script src="<?php echo base_url(); ?>backend/plugins/input-mask/jquery.inputmask.date.extensions.js"></script>
<script src="<?php echo base_url(); ?>backend/plugins/input-mask/jquery.inputmask.extensions.js"></script>
<script src="<?php echo base_url(); ?>backend/dist/js/moment.min.js"></script>
<!-- Add Bootstrap Datetimepicker CSS and JS -->
<link rel="stylesheet" href="<?php echo base_url(); ?>backend/datepicker/css/bootstrap-datetimepicker.css">
<script src="<?php echo base_url(); ?>backend/datepicker/js/bootstrap-datetimepicker.js"></script>
<script src="<?php echo base_url(); ?>backend/plugins/daterangepicker/daterangepicker.js"></script>
<script src="<?php echo base_url(); ?>backend/plugins/colorpicker/bootstrap-colorpicker.min.js"></script>
<script src="<?php echo base_url(); ?>backend/plugins/timepicker/bootstrap-timepicker.min.js"></script>
<script src="<?php echo base_url(); ?>backend/plugins/slimScroll/jquery.slimscroll.min.js"></script>
<script src="<?php echo base_url(); ?>backend/dist/js/jquery.mCustomScrollbar.concat.min.js"></script>
<script type="text/javascript">
    $('body').tooltip({
        selector: '[data-toggle]',
        trigger: 'click hover',
        placement: 'top',
        delay: {
            show: 50,
            hide: 400
        }
    })
</script>
<!--language js-->
<script type="text/javascript" src="<?php echo base_url(); ?>backend/dist/js/bootstrap-select.min.js"></script>


<script type="text/javascript">
    $(function () {
        $('.languageselectpicker').selectpicker();
    });
</script>
<script type="text/javascript">
    $(document).ready(function () {
        $(".studentsidebar").mCustomScrollbar({
            theme: "minimal"
        });


        $('.studentsideclose, .overlay').on('click', function () {
            $('.studentsidebar').removeClass('active');
            $('.overlay').fadeOut();
        });


        $('#sidebarCollapse').on('click', function () {
            $('.studentsidebar').addClass('active');
            $('.overlay').fadeIn();
            $('.collapse.in').toggleClass('in');
            $('a[aria-expanded=true]').attr('aria-expanded', 'false');
        });
    });
</script>


<script src="<?php echo base_url(); ?>backend/plugins/iCheck/icheck.min.js"></script>
<script src="<?php echo base_url(); ?>backend/plugins/datepicker/bootstrap-datepicker.js"></script>
<?php
if ($language_name != 'en') {
    ?>
    <script src="<?php echo base_url(); ?>backend/plugins/datepicker/locales/bootstrap-datepicker.<?php echo $language_name ?>.js"></script>


<?php }?>


<script src="<?php echo base_url(); ?>backend/plugins/chartjs/Chart.min.js"></script>
<script src="<?php echo base_url(); ?>backend/plugins/fastclick/fastclick.min.js"></script>
<script src="<?php echo base_url(); ?>backend/dist/js/app.min.js"></script>
<!--nprogress-->
<script src="<?php echo base_url(); ?>backend/dist/js/nprogress.js"></script>
<!--file dropify-->
<script src="<?php echo base_url(); ?>backend/dist/js/dropify.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>backend/dist/datatables/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>backend/dist/datatables/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>backend/dist/datatables/js/jszip.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>backend/dist/datatables/js/pdfmake.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>backend/dist/datatables/js/vfs_fonts.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>backend/dist/datatables/js/buttons.html5.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>backend/dist/datatables/js/buttons.print.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>backend/dist/datatables/js/buttons.colVis.min.js" ></script>
<script type="text/javascript" src="<?php echo base_url(); ?>backend/dist/datatables/js/dataTables.responsive.min.js" ></script>
<script type="text/javascript" src="<?php echo base_url(); ?>backend/dist/datatables/js/ss.custom.js" ></script>


</body>
</html>
<!-- jQuery 3 -->
<script src="<?php echo base_url() ?>backend/fullcalendar/dist/fullcalendar.min.js"></script>
<script src="<?php echo base_url() ?>backend/fullcalendar/dist/locale-all.js"></script>
<?php if ($language_name != 'en') {?>
    <script src="<?php echo base_url() ?>backend/fullcalendar/dist/locale/<?php echo $language_name ?>.js"></script>
<?php }?>
<script type="text/javascript">


    $(document).ready(function () {


<?php
if ($this->session->flashdata('success_msg')) {
    ?>
            successMsg("<?php echo $this->session->flashdata('success_msg'); ?>");
            $this->session->unset_userdata('success_msg');
    <?php
} else if ($this->session->flashdata('error_msg')) {
    ?>
            errorMsg("<?php echo $this->session->flashdata('error_msg'); ?>");
            $this->session->unset_userdata('error_msg');
    <?php
} else if ($this->session->flashdata('warning_msg')) {
    ?>
            infoMsg("<?php echo $this->session->flashdata('warning_msg'); ?>");
            $this->session->unset_userdata('warning_msg');
    <?php
} else if ($this->session->flashdata('info_msg')) {
    ?>
            warningMsg("<?php echo $this->session->flashdata('info_msg'); ?>");
            $this->session->unset_userdata('info_msg');
    <?php
}
?>
    });


    function complete_event(id, status) {
        $.ajax({
            url: "<?php echo site_url("admin/calendar/markcomplete/") ?>" + id,
            type: "POST",
            data: {id: id, active: status},
            dataType: 'json',
            success: function (res)
            {
                if (res.status == "fail") {
                    var message = "";
                    $.each(res.error, function (index, value) {
                        message += value;
                    });
                    errorMsg(message);
                } else {
                    successMsg(res.message);
                    window.location.reload(true);
                }
            }
        });
    }


    function markc(id) {
        $('#newcheck' + id).change(function () {
            if (this.checked) {
                complete_event(id, 'yes');
            } else {
                complete_event(id, 'no');
            }
        });
    }


</script>


<!-- Button trigger modal -->
<!-- Modal -->
<div class="row">
    <div class="modal fade" id="sessionModal" tabindex="-1" role="dialog" aria-labelledby="sessionModalLabel">
        <form action="<?php echo site_url('admin/admin/activeSession') ?>" id="form_modal_session" class="">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="sessionModalLabel"><?php echo $this->lang->line('current_session'); ?></h4>
                    </div>
                    <div class="modal-body sessionmodal_body">
                        <!-- Sessions will be loaded here via AJAX -->
                        <div class="form-group">
                            <label><?php echo $this->lang->line('session'); ?></label>
                            <select name="session_id" id="modal_session_id" class="form-control">
                                <option value=""><?php echo $this->lang->line('loading'); ?>...</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="col-md-12">
                            <button type="button" class="btn btn-primary submit_session" data-loading-text="<i class='fa fa-spinner fa-spin '></i> <?php echo $this->lang->line('please_wait'); ?>"><?php echo $this->lang->line('save'); ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    
    <script type="text/javascript">
    // Load sessions in BS format when modal is shown
    $(document).ready(function() {
        $('#sessionModal').on('shown.bs.modal', function() {
            var $modalBody = $('.sessionmodal_body');
            var $select = $('#modal_session_id');
            
            // Show loading
            $select.html('<option value=""><?php echo $this->lang->line("loading"); ?>...</option>');
            
            // Get current session ID
            var currentSessionId = '<?php echo $this->setting_model->getCurrentSession(); ?>';
            
            // Load sessions via AJAX - WITH CACHE BUSTING
            var timestamp = new Date().getTime();
            var ajaxUrl = '<?php echo base_url(); ?>sessions/getCurrentAndFutureSessions?_=' + timestamp;
            console.log('Loading sessions from:', ajaxUrl);
            
            $.ajax({
                url: ajaxUrl,
                method: 'GET',
                dataType: 'json',
                cache: false,
                timeout: 10000,
                headers: {
                    'Cache-Control': 'no-cache, no-store, must-revalidate',
                    'Pragma': 'no-cache',
                    'Expires': '0'
                },
                beforeSend: function() {
                    console.log('AJAX request starting...');
                },
                success: function(sessions) {
                    console.log('=== RAW SESSIONS FROM SERVER ===');
                    console.log('Total sessions received:', sessions ? sessions.length : 0);
                    console.log('Sessions data:', JSON.stringify(sessions, null, 2));
                    
                    var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                    
                    if (sessions && sessions.length > 0) {
                        var validCount = 0;
                        var rejectedCount = 0;
                        
                        $.each(sessions, function(index, session) {
                            var selected = (currentSessionId == session.id) ? 'selected' : '';
                            var sessionText = (session.session || '').trim();
                            
                            console.log('Processing session:', session.id, sessionText);
                            
                            // ULTRA STRICT CLIENT-SIDE VALIDATION
                            var parts = sessionText.split('-');
                            var isValid = false;
                            
                            if (parts.length == 2) {
                                var year1 = parseInt(parts[0], 10);
                                var year2 = parseInt(parts[1], 10);
                                
                                console.log('  Year1:', year1, 'Year2:', year2);
                                
                                // ONLY accept sessions in valid BS range (2070-2090)
                                if (year1 >= 2070 && year1 <= 2090 && !isNaN(year1)) {
                                    isValid = true;
                                    validCount++;
                                    console.log('  ✓ VALID - Adding to dropdown');
                                } else {
                                    rejectedCount++;
                                    console.warn('  ✗ REJECTED - Year', year1, 'is outside valid range (2070-2090)');
                                }
                            } else {
                                rejectedCount++;
                                console.warn('  ✗ REJECTED - Invalid format (not YYYY-YY)');
                            }
                            
                            if (isValid) {
                                html += '<option value="' + session.id + '" ' + selected + '>' + sessionText + '</option>';
                            }
                        });
                        
                        console.log('=== FILTERING SUMMARY ===');
                        console.log('Valid sessions:', validCount);
                        console.log('Rejected sessions:', rejectedCount);
                        
                        if (validCount === 0) {
                            html += '<option value="" disabled>No valid sessions found. Check console for details.</option>';
                            console.error('❌ NO VALID SESSIONS FOUND! All', rejectedCount, 'sessions were rejected.');
                        }
                    } else {
                        html += '<option value="" disabled><?php echo $this->lang->line("no_sessions_found"); ?></option>';
                        console.warn('⚠ No sessions returned from server');
                    }
                    
                    $select.html(html);
                    console.log('✅ Dropdown updated with', validCount, 'valid sessions');
                },
                error: function(xhr, status, error) {
                    console.error('❌ AJAX ERROR loading sessions');
                    console.error('Status:', status);
                    console.error('Error:', error);
                    console.error('Response:', xhr.responseText);
                    console.error('Status Code:', xhr.status);
                    $select.html('<option value="">Error: ' + error + '. Check console (F12) for details.</option>');
                }
            });
        });
    });
    </script>
</div>


<?php $this->load->view('layout/routine_update');?>
<?php $this->load->view('layout/addon_update');?>
<?php  if (($this->module_lib->hasModule('multi_branch') && $this->module_lib->hasActive('multi_branch')) || $this->db->multi_branch)  { ?>
<?php $this->load->view('layout/multi_branch');?>
<?php } ?>


<script type="text/javascript">
    var calendar_date_time_format = '<?php echo $result = strtr($this->customlib->getSchoolDateFormat(), ['d' => 'DD', 'm' => 'MM', 'M' => 'MMM', 'Y' => 'YYYY']) ?>';


    var datetime_format = '<?php echo $result = strtr($this->customlib->getSchoolDateFormat(true, true), ['d' => 'DD', 'm' => 'MM', 'Y' => 'YYYY', 'H' => 'hh', 'i' => 'mm']) ?>';


    var date_format = '<?php echo $result = strtr($this->customlib->getSchoolDateFormat(), ['d' => 'dd', 'm' => 'mm', 'Y' => 'yyyy', 'M' => 'M']) ?>';


    function savedata(eventData) {
        var base_url = '<?php echo base_url() ?>';
        $.ajax({
            url: base_url + 'admin/calendar/saveevent',
            type: 'POST',
            data: eventData,
            dataType: "json",
            success: function (msg) {
                alert(msg);


            }
        });
    }


    $calendar = $('#calendar');
    var base_url = '<?php echo base_url() ?>';
    today = new Date();
    y = today.getFullYear();
    m = today.getMonth();
    d = today.getDate();
    var viewtitle = 'month';
    var pagetitle = "<?php
if (isset($title)) {
    echo $title;
}
?>";


    if (pagetitle == "Dashboard") {
        viewtitle = 'agendaWeek';
    }


    // Custom title formatter to convert AD dates to BS dates
    function formatBSTitle(date, view) {
        if (!window.NepaliFunctions || typeof window.NepaliFunctions.AD2BS !== 'function') {
            // Fallback to default format if NepaliFunctions not available
            if (view.type === 'month') {
                return date.format('MMMM YYYY');
            } else if (view.type === 'agendaWeek') {
                var start = view.start;
                var end = view.end;
                return start.format('MMMM D') + ' – ' + end.subtract(1, 'day').format('D YYYY');
            } else if (view.type === 'agendaDay') {
                return date.format('D MMM, YYYY');
            }
            return date.format('MMMM YYYY');
        }
        
        // Nepali month names
        var nepaliMonths = {
            1: 'बैशाख', 2: 'जेठ', 3: 'असार', 4: 'साउन', 5: 'भदौ', 6: 'असोज',
            7: 'कार्तिक', 8: 'मंसिर', 9: 'पुस', 10: 'माघ', 11: 'फागुन', 12: 'चैत'
        };
        
        // Get current session for reference (not used for date conversion, but available if needed)
        var currentSession = '<?php echo $this->setting_model->getCurrentSessionName(); ?>';
        
        if (view.type === 'month') {
            // Month view: Convert the month and year
            var adDate = {
                year: date.year(),
                month: date.month() + 1,
                day: 15 // Use middle of month for conversion
            };
            var bs = window.NepaliFunctions.AD2BS(adDate);
            if (bs && bs.month && bs.year) {
                return nepaliMonths[bs.month] + ' ' + bs.year;
            }
            return date.format('MMMM YYYY');
        } else if (view.type === 'agendaWeek') {
            // Week view: Convert start and end dates
            var start = view.start;
            var end = view.end.clone().subtract(1, 'day');
            
            var startAD = {
                year: start.year(),
                month: start.month() + 1,
                day: start.date()
            };
            var endAD = {
                year: end.year(),
                month: end.month() + 1,
                day: end.date()
            };
            
            var startBS = window.NepaliFunctions.AD2BS(startAD);
            var endBS = window.NepaliFunctions.AD2BS(endAD);
            
            if (startBS && endBS && startBS.month && endBS.month) {
                var startMonth = nepaliMonths[startBS.month] || '';
                var endMonth = nepaliMonths[endBS.month] || '';
                var startDay = startBS.day || '';
                var endDay = endBS.day || '';
                var year = startBS.year || endBS.year || (new Date().getFullYear() + 57);
                
                if (startBS.month === endBS.month) {
                    // Same month: "मंसिर 3 – 9 2082"
                    return startMonth + ' ' + startDay + ' – ' + endDay + ' ' + year;
                } else {
                    // Different months: "मंसिर 3 – कार्तिक 5 2082"
                    return startMonth + ' ' + startDay + ' – ' + endMonth + ' ' + endDay + ' ' + year;
                }
            }
            return start.format('MMMM D') + ' – ' + end.format('D YYYY');
        } else if (view.type === 'agendaDay') {
            // Day view: Convert single date
            var adDate = {
                year: date.year(),
                month: date.month() + 1,
                day: date.date()
            };
            var bs = window.NepaliFunctions.AD2BS(adDate);
            if (bs && bs.month && bs.day && bs.year) {
                var monthName = nepaliMonths[bs.month] || '';
                return bs.day + ' ' + monthName + ', ' + bs.year;
            }
            return date.format('D MMM, YYYY');
        }
        
        return date.format('MMMM YYYY');
    }

    // Function to format day column headers to BS dates
    function formatBSColumnHeader(date) {
        if (!window.NepaliFunctions || typeof window.NepaliFunctions.AD2BS !== 'function') {
            return date.format('ddd D/M');
        }
        
        // Nepali month names
        var nepaliMonths = {
            1: 'बैशाख', 2: 'जेठ', 3: 'असार', 4: 'साउन', 5: 'भदौ', 6: 'असोज',
            7: 'कार्तिक', 8: 'मंसिर', 9: 'पुस', 10: 'माघ', 11: 'फागुन', 12: 'चैत'
        };
        
        // Day names in Nepali (short)
        var nepaliDays = {
            0: 'आइत', 1: 'सोम', 2: 'मंगल', 3: 'बुध', 4: 'बिहि', 5: 'शुक्र', 6: 'शनि'
        };
        
        var adDate = {
            year: date.year(),
            month: date.month() + 1,
            day: date.date()
        };
        
        var bs = window.NepaliFunctions.AD2BS(adDate);
        if (bs && bs.month && bs.day) {
            var dayName = nepaliDays[date.day()] || '';
            var monthName = nepaliMonths[bs.month] || '';
            // Format: "आइत 3/8" (Day BS_Day/BS_Month)
            return dayName + ' ' + bs.day + '/' + bs.month;
        }
        
        return date.format('ddd D/M');
    }

    $calendar.fullCalendar({
        viewRender: function (view, element) {
            // Update title after view renders with BS dates
            if (window.NepaliFunctions && typeof window.NepaliFunctions.AD2BS === 'function') {
                setTimeout(function() {
                    var $title = $('.fc-center h2');
                    if ($title.length && view) {
                        var formattedTitle = formatBSTitle(view.start, view);
                        $title.text(formattedTitle);
                    }
                    
                    // Update day column headers to BS format
                    $('.fc-day-header').each(function() {
                        var $header = $(this);
                        // Try multiple ways to get the date
                        var dateStr = $header.attr('data-date') || $header.data('date');
                        
                        // If no data-date, try to extract from text or find the date from the view
                        if (!dateStr && view && view.start) {
                            // Calculate date based on column index
                            var colIndex = $header.index();
                            var headerDate = view.start.clone().add(colIndex, 'days');
                            dateStr = headerDate.format('YYYY-MM-DD');
                        }
                        
                        if (dateStr) {
                            var momentDate = moment(dateStr);
                            if (momentDate.isValid()) {
                                var bsHeader = formatBSColumnHeader(momentDate);
                                // Update the header text
                                $header.contents().filter(function() {
                                    return this.nodeType === 3; // Text node
                                }).remove();
                                $header.prepend('<span>' + bsHeader + '</span>');
                            }
                        } else {
                            // Fallback: try to parse existing text
                            var existingText = $header.text().trim();
                            if (existingText && existingText.match(/\d+\/\d+/)) {
                                // Has date format, try to convert
                                var parts = existingText.split(' ');
                                if (parts.length >= 2) {
                                    // Try to find date in view
                                    var colIndex = $header.index();
                                    if (view && view.start) {
                                        var headerDate = view.start.clone().add(colIndex, 'days');
                                        var bsHeader = formatBSColumnHeader(headerDate);
                                        $header.contents().filter(function() {
                                            return this.nodeType === 3;
                                        }).remove();
                                        $header.prepend('<span>' + bsHeader + '</span>');
                                    }
                                }
                            }
                        }
                    });
                }, 150);
            }
        },


        header: {
            center: 'title',
            right: 'month,agendaWeek,agendaDay',
            left: 'prev,next,today'
        },
        firstDay: start_week,
        defaultDate: today,
        defaultView: viewtitle,
        selectable: true,
        selectHelper: true,
        views: {
            month: {// name of view
                titleFormat: 'MMMM YYYY', // Will be overridden by viewRender
                columnFormat: 'ddd D/M' // Will be overridden by viewRender
                        // other view-specific options here
            },
            week: {
                titleFormat: " MMMM D YYYY", // Will be overridden by viewRender
                columnFormat: 'ddd D/M' // Will be overridden by viewRender
            },
            day: {
                titleFormat: 'D MMM, YYYY', // Will be overridden by viewRender
                columnFormat: 'ddd D/M' // Will be overridden by viewRender
            }
        },
        timezone: 'UTC',
        draggable: false,
        lang: '<?php echo $language_name ?>',
        editable: false,
        eventLimit: false, // allow "more" link when too many events


        // color classes: [ event-blue | event-azure | event-green | event-orange | event-red ]
        events: {
            url: base_url + 'admin/calendar/getevents'


        },


        eventRender: function (event, element) {


            element.attr('title', event.title);
            element.attr('onclick', event.onclick);
            element.attr('data-toggle', 'tooltip');
            if ((!event.url) && (event.event_type != 'task')) {
                element.attr('title', event.title + '-' + event.description);
                element.click(function () {
                    view_event(event.id);
                });
            }


        },
        dayClick: function (date, jsEvent, view) {
           console.log('Clicked on the entire day: ' + date.format());




<?php if ($this->rbac->hasPrivilege('calendar_to_do_list', 'can_add')) {?>
                var newEventModal= $('#newEventModal');
                $("#input-field").val('');
                $("#desc-field").text('');
                var event_start_from = new Date(date);
                console.log(event_start_from.toISOString());
                $('.event_from',newEventModal).data("DateTimePicker").date(event_start_from);
                $('.event_to',newEventModal).data("DateTimePicker").date(event_start_from);
                $('#newEventModal').modal('show');


<?php
}?>
            return false;
        }


    });


    function view_event(id) {


        $('.selectevent').find('.cpicker-big').removeClass('cpicker-big').addClass('cpicker-small');
        var base_url = '<?php echo base_url() ?>';
        if (typeof (id) == 'undefined') {
            return;
        }
        $.ajax({
            url: base_url + 'admin/calendar/view_event/' + id,
            type: 'POST',
            //data: '',
            dataType: "json",
            success: function (msg) {


                $("#event_title").val(msg.event_title);
                $("#event_desc").text(msg.event_description);


                $('#eventid').val(id);
                if (msg.event_type == 'public') {
                    $('input:radio[name=eventtype]')[0].checked = true;


                } else if (msg.event_type == 'private') {
                    $('input:radio[name=eventtype]')[1].checked = true;


                } else if (msg.event_type == 'sameforall') {
                    $('input:radio[name=eventtype]')[2].checked = true;


                } else if (msg.event_type == 'protected') {
                    $('input:radio[name=eventtype]')[3].checked = true;


                }
                //===========


                var __viewModal=$('#viewEventModal');
 var event_start_from = new Date(msg.start_date);
 $('.event_from',__viewModal).data("DateTimePicker").date(event_start_from);


  var event_end_to = new Date(msg.end_date);
 $('.event_to',__viewModal).data("DateTimePicker").date(event_end_to);
                //============


                $("#event_color").val(msg.event_color);
                $("#delete_event").attr("onclick", "deleteevent(" + id + ",'Event')");
                $("#" + msg.colorid).removeClass('cpicker-small').addClass('cpicker-big');
                $('#viewEventModal').modal('show');
            }
        });
    }


    $(document).ready(function (e) {
        $("#addevent_form").on('submit', (function (e) {


            e.preventDefault();


            $(".submit_addevent").prop("disabled", true);


            $.ajax({
                url: "<?php echo site_url("admin/calendar/saveevent") ?>",
                type: "POST",
                data: new FormData(this),
                dataType: 'json',
                contentType: false,
                cache: false,
                processData: false,
                success: function (res)
                {


                    if (res.status == "fail") {


                        var message = "";
                        $.each(res.error, function (index, value) {


                            message += value;
                        });
                        errorMsg(message);


                    } else {


                        successMsg(res.message);


                        window.location.reload(true);
                    }
                    $(".submit_addevent").prop("disabled", false);
                }
            });
        }));
    });


    $(document).ready(function (e) {
        $("#updateevent_form").on('submit', (function (e) {


            e.preventDefault();
            $(".submit_update").prop("disabled", true);
            $.ajax({
                url: "<?php echo site_url("admin/calendar/updateevent") ?>",
                type: "POST",
                data: new FormData(this),
                dataType: 'json',
                contentType: false,
                cache: false,
                processData: false,
                success: function (res)
                {
                    if (res.status == "fail") {
                        var message = "";
                        $.each(res.error, function (index, value) {
                            message += value;
                        });
                        errorMsg(message);


                    } else {


                        successMsg(res.message);
                        window.location.reload(true);
                    } $(".submit_update").prop("disabled", false);
                }
            });
        }));
    });


    function deleteevent(id, msg) {
        if (typeof (id) == 'undefined') {
            return;
        }
        if (confirm("<?php echo $this->lang->line('are_you_sure_want_to_delete'); ?> ")) {
            $.ajax({
                url: base_url + 'admin/calendar/delete_event/' + id,
                type: 'POST',
                dataType: "json",
                success: function (res) {
                    if (res.status == "fail") {
                        errorMsg(res.message);
                    } else {
                        successMsg(msg + " <?php echo $this->lang->line('delete_message'); ?>");
                        window.location.reload(true);
                    }
                }
            })
        }
    }


    $("body").on('click', '.cpicker', function () {
        var color = $(this).data('color');
        // Clicked on the same selected color
        if ($(this).hasClass('cpicker-big')) {
            return false;
        }


        $(this).parents('.cpicker-wrapper').find('.cpicker-big').removeClass('cpicker-big').addClass('cpicker-small');
        $(this).removeClass('cpicker-small', 'fast').addClass('cpicker-big', 'fast');
        if ($(this).hasClass('kanban-cpicker')) {
            $(this).parents('.panel-heading-bg').css('background', color);
            $(this).parents('.panel-heading-bg').css('border', '1px solid ' + color);
        } else if ($(this).hasClass('calendar-cpicker')) {
            $("body").find('input[name="eventcolor"]').val(color);
        }
    });


    $(document).ready(function () {
        moment.lang('en', {
          week: { dow: start_week }
        });


        $("body").delegate(".date", "focusin", function () {
            // CRITICAL: Skip income search date fields by ID
            if ($(this).attr('id') === 'date_from' || $(this).attr('id') === 'date_to') {
                return false;
            }
            // Skip if this is a Nepali date field
            if ($(this).attr('data-nepali-date') === 'true' || 
                $(this).hasClass('nepali-datepicker') || 
                $(this).attr('data-prevent-english-calendar') === 'true' ||
                $(this).hasClass('income-date-bs') ||
                $(this).hasClass('date-bs')) {
                return false;
            }
            $(this).datepicker({
                todayHighlight: false,
                format: date_format,
                autoclose: true,
                weekStart : start_week,
                language: '<?php echo $language_name ?>'
            });
        });


        $("body").delegate(".datetime", "focusin", function () {
            // CRITICAL: Skip income search date fields by ID
            if ($(this).attr('id') === 'date_from' || $(this).attr('id') === 'date_to') {
                return false;
            }
            // Skip if this is a Nepali date field
            if ($(this).attr('data-nepali-date') === 'true' || 
                $(this).hasClass('nepali-datepicker') || 
                $(this).attr('data-prevent-english-calendar') === 'true' ||
                $(this).hasClass('income-date-bs') ||
                $(this).hasClass('date-bs')) {
                return false;
            }
            $(this).datetimepicker({
                format: calendar_date_time_format + ' hh:mm a'


            });
        });


        $('body').on('focus',".date_fee", function(){
            // CRITICAL: Skip income search date fields by ID
            if ($(this).attr('id') === 'date_from' || $(this).attr('id') === 'date_to') {
                return false;
            }
            // Skip if this is a Nepali date field
            if ($(this).attr('data-nepali-date') === 'true' || 
                $(this).hasClass('nepali-datepicker') || 
                $(this).attr('data-prevent-english-calendar') === 'true' ||
                $(this).hasClass('income-date-bs') ||
                $(this).hasClass('date-bs')) {
                return false;
            }
            $(this).datepicker({
                format: date_format,
                autoclose: true,
                language: '<?php echo $language_name; ?>',
                endDate: '+0d',
                startDate: '<?php if ($feesinbackdate == 0) {echo "-0m";}
;?>',
                weekStart : start_week,
                todayHighlight: true
            });
      });


        $('.datetime_twelve_hour').datetimepicker({
               format:  calendar_date_time_format + ' hh:mm a'
        });




            $("#event_date").daterangepicker({
            timePickerIncrement: 5,
            locale: {
            format: calendar_date_time_format
            }
           });


///================


        $('.event_from').datetimepicker({
               format:  calendar_date_time_format + ' hh:mm A',
        });


        $('.event_to').datetimepicker({
               format:  calendar_date_time_format + ' hh:mm A',
        });


//==============


    });


    function loadDate() {


        var date_format = '<?php echo $result = strtr($this->customlib->getSchoolDateFormat(), ['d' => 'dd', 'm' => 'mm', 'Y' => 'yyyy']) ?>';


        $('.date').datetimepicker({
            format: datetime_format,
            locale:
                    '<?php echo $language_name ?>',


        });
    }


    // showdate('this_year');


    function showdate(type) {


<?php
if (isset($_POST['date_from']) && $_POST['date_from'] != '' && isset($_POST['date_to']) && $_POST['date_to'] != '') {
    ?>
            var date_from = '<?php echo date($this->customlib->getSchoolDateFormat(), $this->customlib->datetostrtotime($_POST['date_from'])); ?>';
            var date_to = '<?php echo date($this->customlib->getSchoolDateFormat(), $this->customlib->datetostrtotime($_POST['date_to'])); ?>';




    <?php
} else {
    ?>
            var date_from = '<?php echo date($this->customlib->getSchoolDateFormat()); ?>';
            var date_to = '<?php echo date($this->customlib->getSchoolDateFormat()); ?>';
    <?php
}
?>


        if (type == 'period') {


            $.ajax({
                url: base_url + 'Report/get_betweendate/' + type,
                type: 'POST',
                data: {date_from: date_from, date_to: date_to},
                success: function (res) {


                    $('#date_result').html(res);


                    loadDate();
                }


            });


        } else {
            $('#date_result').html('');
        }


    }
</script>
<script type="text/javascript">
    $(document).on('change','#currencySwitcher',function(e){
            $.ajax({
                type: 'POST',
                url: baseurl+'admin/currency/change_currency',
                data: {currency_id: $(this).val()},
                dataType: 'JSON',
                beforeSend: function() {


                },
                success: function(data) {


                    successMsg(data.message);
                    if(data.status){


                  window.location.reload('true');
                    }
                },
                error: function(xhr) { // if error occured
                    alert("Error occured.please try again");


                },
                complete: function() {


                }
            });
    });


    $calendar_event = $('#calendar_event');


    $calendar_event.fullCalendar({
        viewRender: function (view, element) {


        },


        header: {
            center: 'title',
            right: '',
            left: 'prev,next'
        },
        firstDay: start_week,
        displayEventTime : false,
        defaultDate: today,
        defaultView: viewtitle,
        selectable: true,
        selectHelper: true,
        views: {
            month: {// name of view
                titleFormat: 'MMMM YYYY'
                        // other view-specific options here
            }
        },
        timezone: 'UTC',
        draggable: false,
        lang: '<?php echo $language_name ?>',
        editable: false,
        eventLimit: false, // allow "more" link when too many events


        // color classes: [ event-blue | event-azure | event-green | event-orange | event-red ]
        events: {
            url: base_url + 'admin/alumni/getevent'


        },


        eventRender: function (event, element) {


            element.attr('title', event.title);
            element.attr('data-toggle', 'tooltip');
            element.click(function () {


                view_event_data(event.id);
            });
        }


    });
</script>

