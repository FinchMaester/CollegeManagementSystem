<div class="wrapper">
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1><i class="fa fa-calendar"></i> <?php echo $this->lang->line('calendar'); ?></h1>
        </section>
        <!-- Main content -->
        <section class="content">
            <div class="row">
                <!-- /.col -->
                <div class="col-md-9 col-sm-9">
                    <div class="box box-primary">
                        <div class="box-body">
                            <!-- THE CALENDAR -->
                            <div id="calendar"></div>
                        </div>
                        <!-- /.box-body -->
                    </div>
                    <!-- /. box -->
                </div> 
                <div class="col-md-3 col-sm-3">
                    <div class="box box-primary">
                        <div class="box-header ptbnull">
                            <h3 class="box-title"><?php echo $this->lang->line('to_do_list'); ?></h3>
                            <div class="box-tools pull-right">
                                <?php
                                if ($this->rbac->hasPrivilege('calendar_to_do_list', 'can_add')) {
                                    ?>

                                    <button class="btn btn-primary btn-sm pull-right" onclick="add_task()"><i class="fa fa-plus"></i></button>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="">
                            <?php foreach ($tasklist as $taskkey => $taskvalue) {
                                ?>

                                <div class="media mt5" style="padding:0 10px;">
                                    <div class="media-left">
                                        <input type="checkbox" <?php
                                        if ($taskvalue["is_active"] == 'yes') {
                                            echo "checked";
                                        }
                                        ?> id="check<?php echo $taskvalue["id"] ?>" onclick="markcomplete('<?php echo $taskvalue["id"] ?>')" name="eventcheck"  value="<?php echo $taskvalue["id"]; ?>">
                                    </div>
                                    <div class="media-body">
                                        <p class="tododesc" <?php if ($taskvalue["is_active"] == 'yes') {
                                            ?> style="text-decoration: line-through;color: #4f881d;" <?php } ?> ><?php echo $taskvalue["event_title"]; ?></p>

                                        <small class="tododate"><?php echo $this->customlib->dateformat($taskvalue["start_date"]); ?> 
                                            <?php
                                            if ($this->rbac->hasPrivilege('calendar_to_do_list', 'can_delete')) {
                                                ?><a href="#" onclick="deleteevent('<?php echo $taskvalue["id"]; ?>', ''); return false;" title="<?php echo $this->lang->line('delete'); ?>" class="pull-right text-muted"><i class="fa fa-remove"></i></a>
                                                <?php
                                            }
                                            if ($this->rbac->hasPrivilege('calendar_to_do_list', 'can_edit')) {
                                                ?>
                                                <a href="#" onclick="console.log('Edit icon clicked, eventid:', '<?php echo $taskvalue["id"]; ?>'); edit_todo_task('<?php echo $taskvalue["id"]; ?>'); return false;" class="pull-right text-muted mright5" style="margin-right: 5px" title="<?php echo $this->lang->line('edit'); ?>"><i class="fa fa-pencil"></i></a>
                                            <?php } ?>
                                        </small>
                                    </div>
                                </div> 
                                <div class="todo_divider"></div>   
                            <?php } ?>
                            <div class="todopagination"><?php echo $this->pagination->create_links(); ?></div>
                        </div>
                    </div>
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->
</div>

<div id="newTask" class="modal fade " role="dialog">
    <div class="modal-dialog modal-dialog2 modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="modal-title"></h4>
            </div>
            <div class="modal-body pb0">

                <div class="row">
                    <form role="form"  id="addtodo_form" method="post" enctype="multipart/form-data" action="">
                        <div class="form-group col-md-12">
                            <label for="exampleInputEmail1"><?php echo $this->lang->line('task_title'); ?><small class="req"> *</small></label>
                            <input class="form-control" name="task_title"  id="task-title"> 
                            <span class="text-danger"><?php echo form_error('title'); ?></span>
                        </div>
                        <div class="form-group col-md-12" style="position: relative;">
                            <label for="exampleInputEmail1"><?php echo $this->lang->line('date'); ?><small class="req"> *</small></label>
                            <input class="form-control nepali-datepicker" type="text" autocomplete="off"  name="task_date" placeholder="<?php echo $this->lang->line('date'); ?>" id="task-date">
                            <input class="form-control" type="hidden" name="eventid" id="taskid">
                        </div>
                        <div class="row">
                            <div class="box-footer clearboth" id="permission">
                                <?php if ($this->rbac->hasPrivilege('calendar_to_do_list', 'can_add')) { ?>

                                    <input type="submit" class="btn btn-primary submit_addtask pull-right" value="<?php echo $this->lang->line('save'); ?>">
                                <?php } ?>
                            </div>
                        </div>

                    </form>
                </div>

            </div>
        </div>
    </div>
</div>  

<div id="newEventModal" class="modal fade " role="dialog">
    <div class="modal-dialog modal-dialog2 modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?php echo $this->lang->line('add_new_event'); ?></h4>
            </div>
            <div class="modal-body pb0"> 

                <div class="row"> 
                    <form role="form" id="addevent_form" method="post" enctype="multipart/form-data" action="">
                        <div class="form-group col-md-12">
                            <label for="exampleInputEmail1"><?php echo $this->lang->line('event_title'); ?><small class="req"> *</small></label>
                            <input class="form-control" name="title" id="input-field"> 
                            <span class="text-danger"><?php echo form_error('title'); ?></span>

                        </div>

                        <div class="form-group col-md-12">
                            <label for="exampleInputEmail1"><?php echo $this->lang->line('event_description'); ?></label>
                            <textarea name="description" class="form-control" id="desc-field"></textarea></div>
                            <div class="row">
                                
                        <div class="form-group col-md-6">
                            <label for="exampleInputEmail1"><?php echo $this->lang->line('event_from'); ?><small class="req"> *</small></label>
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <input type="text" autocomplete="off" name="event_from" class="form-control pull-right event_from">
                            </div>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="exampleInputEmail1"><?php echo $this->lang->line('event_to'); ?><small class="req"> *</small></label>
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <input type="text" autocomplete="off" name="event_to" class="form-control pull-right event_to">
                            </div>
                        </div>
                            </div>

                        <div class="form-group col-md-12">
                            <label for="exampleInputEmail1"><?php echo $this->lang->line('event_color'); ?></label>
                            <input type="hidden" name="eventcolor" autocomplete="off" id="eventcolor" class="form-control">
                        </div>
                        <div class="form-group col-md-12">                          

                            <?php
                            $i = 0;
                            $colors = '';
                            foreach ($event_colors as $color) {
                                $color_selected_class = 'cpicker-small';
                                if ($i == 0) {
                                    $color_selected_class = 'cpicker-big';
                                }
                                $colors .= "<div class='calendar-cpicker cpicker " . $color_selected_class . "' data-color='" . $color . "' style='background:" . $color . ";border:1px solid " . $color . "; border-radius:100px'></div>";                              
                                $i++;
                            }
                            echo '<div class="cpicker-wrapper">';
                            echo $colors;
                            echo '</div>';
                            ?>
                        </div>

                        <div class="form-group col-md-12">
                            <label for="exampleInputEmail1"><?php echo $this->lang->line('event_type'); ?></label>
                            <br/>
                            <label class="radio-inline">

                                <input type="radio" name="event_type" value="public" id="public"><?php echo $this->lang->line('public'); ?>
                            </label>
                            <label class="radio-inline">

                                <input type="radio" name="event_type" value="private" checked id="private"><?php echo $this->lang->line('private'); ?>
                            </label>
                            <label class="radio-inline">

                                <input type="radio" name="event_type" value="sameforall" id="public"><?php echo $this->lang->line('all'); ?> <?php echo $role; ?>
                            </label>
                            <label class="radio-inline">

                                <input type="radio" name="event_type" value="protected" id="public"><?php echo $this->lang->line('protected'); ?>
                            </label> </div>
                        <?php if ($this->rbac->hasPrivilege('calendar_to_do_list', 'can_add')) { ?>
                            <div class="row">
                                <div class="box-footer clearboth"> 
                                    <input type="submit" class="btn btn-primary submit_addevent pull-right" value="<?php echo $this->lang->line('save'); ?>"></div>
                            </div>  
                        <?php } ?>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div> 
  
<div id="viewEventModal" class="modal fade " role="dialog">
    <div class="modal-dialog modal-dialog2 modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?php echo $this->lang->line('view_event'); ?></h4>
            </div>
            <div class="modal-body">

                <div class="row">
                    <form role="form"   method="post" id="updateevent_form"  enctype="multipart/form-data" action="" >
                        <div class="form-group col-md-12">
                            <label for="exampleInputEmail1"><?php echo $this->lang->line('event_title'); ?><small class="req"> *</small></label>
                            <input class="form-control" name="title" placeholder="<?php echo $this->lang->line('event_title'); ?>" id="event_title"> 
                        </div>
                        <div class="form-group col-md-12">
                            <label for="exampleInputEmail1"><?php echo $this->lang->line('event_description'); ?></label>
                            <textarea name="description" class="form-control" placeholder="<?php echo $this->lang->line('event_description'); ?>" id="event_desc"></textarea>
                        </div>
                           <div class="row">
                                
                        <div class="form-group col-md-6">
                            <label for="exampleInputEmail1"><?php  echo $this->lang->line('event_from'); ?></label>
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <input type="text" autocomplete="off" name="event_from" class="form-control pull-right event_from">
                            </div>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="exampleInputEmail1"><?php  echo $this->lang->line('event_to'); ?></label>
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <input type="text" autocomplete="off" name="event_to" class="form-control pull-right event_to">
                            </div>
                        </div>
                            </div>
                        <input type="hidden" name="eventid" id="eventid">
                        <div class="form-group col-md-12">
                            <label for="exampleInputEmail1"><?php echo $this->lang->line('event_color'); ?></label>
                            <input type="hidden" name="eventcolor" autocomplete="off" placeholder="<?php echo $this->lang->line('event_color'); ?>" id="event_color" class="form-control">
                        </div>
                        <div class="form-group col-md-12">

                            <?php
                            $i = 0;
                            $colors = '';
                            foreach ($event_colors as $color) {
                                $colorid = trim($color, "#");                              
                                $color_selected_class = 'cpicker-small';
                                if ($i == 0) {
                                    $color_selected_class = 'cpicker-big';
                                }
                                $colors .= "<div id=" . $colorid . " class='calendar-cpicker cpicker " . $color_selected_class . "' data-color='" . $color . "' style='background:" . $color . ";border:1px solid " . $color . "; border-radius:100px'></div>";                               
                                $i++;
                            }
                            echo '<div class="cpicker-wrapper selectevent">';
                            echo $colors;
                            echo '</div>';
                            ?>
                        </div>
                        <div class="form-group col-md-12">
                            <label for="exampleInputEmail1"><?php echo $this->lang->line('event'); ?> <?php echo $this->lang->line('type'); ?></label><br/>
                            <label class="radio-inline">

                                <input type="radio" name="eventtype" value="public" id="public"><?php echo $this->lang->line('public'); ?>
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="eventtype" value="private" id="private"><?php echo $this->lang->line('private'); ?>
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="eventtype" value="sameforall" id="public"><?php echo $this->lang->line('all'); ?> <?php echo $role; ?>
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="eventtype" value="protected" id="public"><?php echo $this->lang->line('protected'); ?> 
                            </label>
                        </div>
                        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-11">
                            <?php
                            if ($this->rbac->hasPrivilege('calendar_to_do_list', 'can_edit')) {
                                ?>
                                <input type="submit" class="btn btn-primary submit_update pull-right" value="<?php echo $this->lang->line('save'); ?>">
                            <?php } ?>
                        </div>
                        <div class="col-xs-1 col-sm-1 col-md-1 col-lg-1">
                            <?php
                            if ($this->rbac->hasPrivilege('calendar_to_do_list', 'can_delete')) {
                                ?>
                                <input type="button" id="delete_event" class="btn btn-primary submit_delete pull-right" value="<?php echo $this->lang->line('delete'); ?>">

                            <?php } ?>
                        </div>        
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>  
<!-- Page specific script -->
<style>
/* Fix Nepali DatePicker positioning inside modal */
#newTask .modal-dialog {
    z-index: 1050;
    position: relative;
}
#newTask .modal-content {
    z-index: 1051;
    position: relative;
}
#newTask .modal-body {
    position: relative;
    overflow: visible;
}

/* Position Nepali calendar container relative to input field inside modal */
#newTask .form-group {
    position: relative;
}

#newTask .nepali-datepicker-container,
#newTask #ndp-nepali-box {
    position: absolute !important;
    top: 100% !important;
    left: 0 !important;
    margin-top: 2px !important;
    z-index: 1055 !important;
    max-width: 100%;
}

/* Ensure calendar appears inside modal, not outside */
#newTask #ndp-nepali-box {
    position: absolute !important;
    z-index: 1055 !important;
    display: block !important;
}

/* Prevent calendar from appearing at top-left (outside modal) */
body > #ndp-nepali-box:not(#newTask #ndp-nepali-box) {
    display: none !important;
}

/* Style the calendar box */
#newTask #ndp-nepali-box {
    background: #fff !important;
    border: 1px solid #ccc !important;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2) !important;
    border-radius: 4px !important;
    padding: 10px !important;
}
</style>
<script>
    $(document).ready(function () {
    $('#viewEventModal,#newEventModal,#newTask').modal({
         backdrop: 'static',
         keyboard: false,
         show: false
    });
     });
    
    // Initialize Nepali DatePicker for task-date field
    function initTaskDatePicker() {
        var $taskDate = $('#task-date');
        var $modal = $('#newTask');
        
        // Remove any existing English datepicker
        if ($taskDate.data('datepicker')) {
            $taskDate.datepicker('destroy');
        }
        
        // Remove any existing Nepali datepicker
        if ($taskDate.data('nepaliDatePicker')) {
            try {
                $taskDate.nepaliDatePicker('destroy');
            } catch(e) {}
        }
        
        // Clear initialization flags
        $taskDate.removeData('ndp-initialized');
        $taskDate.removeData('nepaliDatePicker');
        
        // Remove any calendar boxes outside the modal
        $('body > #ndp-nepali-box').not($modal.find('#ndp-nepali-box')).remove();
        
        // Initialize Nepali DatePicker
        if (typeof $.fn.nepaliDatePicker !== 'undefined') {
            try {
                $taskDate.nepaliDatePicker({
                    ndpTriggerButton: false,
                    dateFormat: 'YYYY-MM-DD',
                    ndpYear: true,
                    ndpMonth: true,
                    ndpYearCount: 10,
                    readOnlyInput: false,
                    ndpTrigger: 'click',
                    closeOnDateSelect: true
                });
                $taskDate.data('ndp-initialized', true);
                
                // After initialization, ensure calendar is positioned correctly
                setTimeout(function() {
                    var $calendarBox = $('#ndp-nepali-box');
                    if ($calendarBox.length) {
                        // Move calendar box inside modal if it's outside
                        if (!$modal.find('#ndp-nepali-box').length && $calendarBox.parent().is('body')) {
                            $taskDate.closest('.form-group').append($calendarBox);
                        }
                        
                        // Position calendar below input field
                        var inputOffset = $taskDate.offset();
                        var modalOffset = $modal.find('.modal-body').offset();
                        if (inputOffset && modalOffset) {
                            $calendarBox.css({
                                'position': 'absolute',
                                'top': ($taskDate.outerHeight() + 2) + 'px',
                                'left': '0',
                                'z-index': '1055'
                            });
                        }
                    }
                }, 300);
            } catch(e) {
                console.error('Error initializing Nepali datepicker:', e);
            }
        }
    }
    
    // Initialize when modal is shown
    $('#newTask').on('shown.bs.modal', function() {
        setTimeout(function() {
            initTaskDatePicker();
        }, 200);
    });
    
    // Reposition calendar when input is clicked
    $(document).on('click', '#newTask #task-date', function() {
        setTimeout(function() {
            repositionCalendarInModal();
        }, 100);
    });
    
    // Function to reposition calendar inside modal
    function repositionCalendarInModal() {
        var $taskDate = $('#task-date');
        var $calendarBox = $('#ndp-nepali-box');
        var $modal = $('#newTask');
        var $formGroup = $taskDate.closest('.form-group');
        
        if ($calendarBox.length && $modal.length && $formGroup.length) {
            // Ensure calendar is inside modal form-group
            if (!$formGroup.find('#ndp-nepali-box').length) {
                if ($calendarBox.parent().is('body')) {
                    $formGroup.append($calendarBox);
                } else {
                    $calendarBox.detach().appendTo($formGroup);
                }
            }
            
            // Position calendar below input field
            var inputTop = $taskDate.position().top + $taskDate.outerHeight();
            $calendarBox.css({
                'position': 'absolute',
                'top': inputTop + 'px',
                'left': '0',
                'z-index': '1055',
                'margin-top': '2px'
            });
        }
    }
    
    // Watch for calendar appearance and reposition it
    var calendarObserver = new MutationObserver(function(mutations) {
        var $calendarBox = $('#ndp-nepali-box');
        if ($calendarBox.length && $('#newTask').is(':visible')) {
            setTimeout(function() {
                repositionCalendarInModal();
            }, 50);
        }
    });
    
    // Observe body for calendar box insertion
    if (document.body) {
        calendarObserver.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
    
    // Also reposition when modal is shown
    $('#newTask').on('shown.bs.modal', function() {
        setTimeout(function() {
            repositionCalendarInModal();
        }, 300);
    });

    function add_task() {
        // Reset form
        $("#addtodo_form")[0].reset();
        $("#modal-title").html("<?php echo $this->lang->line('add_task'); ?>");
        $("#task-title").val('');
        $("#taskid").val('');
        $('#task-date').val('');
        
        // Reset submit button for add mode
        $('#permission').html('<?php if ($this->rbac->hasPrivilege('calendar_to_do_list', 'can_add')) { ?><input type="submit" class="btn btn-primary submit_addtask pull-right" value="<?php echo $this->lang->line('save'); ?>"><?php } ?>');
        
        // Enable submit button
        $(".submit_addtask").prop("disabled", false);
        
        $('#newTask').modal('show');
        setTimeout(function() {
            initTaskDatePicker();
        }, 200);
    }
    
    // Reset form when modal is hidden
    $('#newTask').on('hidden.bs.modal', function() {
        $("#addtodo_form")[0].reset();
        $("#task-title").val('');
        $("#taskid").val('');
        $('#task-date').val('');
        $(".submit_addtask").prop("disabled", false);
    });

    // Make edit_todo_task globally accessible
    window.edit_todo_task = function(eventid) {
        console.log('=== EDIT TASK DEBUG START ===');
        console.log('1. Edit Task Function Called');
        console.log('1a. Event ID received:', eventid);
        console.log('1b. Event ID type:', typeof eventid);
        console.log('1c. Window object check:', typeof window.edit_todo_task);
        console.log('1d. jQuery available:', typeof $ !== 'undefined');
        console.log('1e. Modal element exists:', $('#newTask').length > 0);
        
        // Check if function is being called
        if (arguments.length === 0) {
            console.error('1f. No arguments passed to function');
        }
        
        if (!eventid || eventid === '' || eventid === 'undefined') {
            console.error('1g. Event ID is missing or invalid:', eventid);
            errorMsg('<?php echo $this->lang->line("something_went_wrong"); ?>');
            return false;
        }
        
        // Convert to string and trim
        eventid = String(eventid).trim();
        console.log('1h. Event ID after processing:', eventid);
        
        var ajaxUrl = "<?php echo site_url("admin/calendar/gettaskbyid/") ?>" + eventid;
        console.log('2. AJAX Request:', {
            url: ajaxUrl,
            method: 'POST',
            data: {eventid: eventid},
            dataType: 'json'
        });
        
        console.log('2a. About to make AJAX call');
        console.log('2b. AJAX URL:', ajaxUrl);
        console.log('2c. AJAX Data:', {eventid: eventid});
        
        $.ajax({
            url: ajaxUrl,
            type: "POST",
            data: {eventid: eventid},
            dataType: 'json',
            contentType: 'application/x-www-form-urlencoded',
            cache: false,
            processData: true,
            beforeSend: function(xhr, settings) {
                console.log('3. Before Send:', {
                    url: settings.url,
                    type: settings.type,
                    data: settings.data
                });
            },
            success: function (res)
            {
                console.log('4. AJAX Success - Response received');
                console.log('4a. Full Response:', res);
                console.log('4b. Response Type:', typeof res);
                console.log('4c. Response String:', JSON.stringify(res));
                console.log('4d. Event Title:', res ? res.event_title : 'undefined');
                console.log('4e. Start Date:', res ? res.start_date : 'undefined');
                console.log('4f. Response Keys:', res ? Object.keys(res) : 'undefined');
                
                if (!res) {
                    console.error('4g. Response is null or undefined');
                    errorMsg('<?php echo $this->lang->line("something_went_wrong"); ?>');
                    return;
                }
                
                if (!res.event_title) {
                    console.error('4h. Missing event_title in response:', res);
                    errorMsg('<?php echo $this->lang->line("something_went_wrong"); ?>');
                    return;
                }
                
                console.log('5. Setting form values:', {
                    event_title: res.event_title,
                    eventid: eventid,
                    start_date: res.start_date
                });
                
                $("#modal-title").html("<?php echo $this->lang->line('edit_task'); ?>");
                $("#task-title").val(res.event_title);
                $("#taskid").val(eventid);
                
                // Convert AD date to BS date for Nepali datepicker
                var adDate = new Date(res.start_date);
                console.log('6. Date Conversion:', {
                    originalStartDate: res.start_date,
                    parsedADDate: adDate,
                    adDateString: adDate.toString(),
                    adDateISO: adDate.toISOString()
                });
                
                if (typeof $.fn.nepaliDatePicker !== 'undefined') {
                    console.log('7. Nepali DatePicker available, converting AD to BS');
                    var $tempInput = $('<input type="text">');
                    $tempInput.nepaliDatePicker({
                        dateFormat: 'YYYY-MM-DD',
                        ndpYear: true,
                        ndpMonth: true
                    });
                    var adDateStr = adDate.getFullYear() + '-' + String(adDate.getMonth() + 1).padStart(2, '0') + '-' + String(adDate.getDate()).padStart(2, '0');
                    console.log('7a. AD Date String:', adDateStr);
                    $tempInput.val(adDateStr);
                    var nepaliDate = $tempInput.nepaliDatePicker('getNepaliDate');
                    console.log('7b. Nepali Date Object:', nepaliDate);
                    
                    if (nepaliDate && nepaliDate.year && nepaliDate.month && nepaliDate.day) {
                        var month = String(nepaliDate.month).padStart(2, '0');
                        var day = String(nepaliDate.day).padStart(2, '0');
                        var bsDateStr = nepaliDate.year + '-' + month + '-' + day;
                        console.log('7c. BS Date String:', bsDateStr);
                        $('#task-date').val(bsDateStr);
                    } else {
                        console.warn('7d. Failed to get Nepali date from datepicker');
                    }
                    $tempInput.remove();
                } else {
                    console.warn('7. Nepali DatePicker not available, using AD date format');
                    // Fallback to AD date format
                    var dateStr = adDate.getFullYear() + '-' + String(adDate.getMonth() + 1).padStart(2, '0') + '-' + String(adDate.getDate()).padStart(2, '0');
                    console.log('7a. AD Date String (fallback):', dateStr);
                    $('#task-date').val(dateStr);
                }
                
                // Update submit button for edit mode
                $('#permission').html('<?php if ($this->rbac->hasPrivilege('calendar_to_do_list', 'can_edit')) { ?><input type="submit" class="btn btn-primary submit_addtask pull-right" value="<?php echo $this->lang->line('save'); ?>"><?php } ?>');
                
                console.log('8. About to show modal');
                console.log('8a. Modal element:', $('#newTask'));
                console.log('8b. Modal element length:', $('#newTask').length);
                console.log('8c. Modal visible before show:', $('#newTask').is(':visible'));
                
                // Show modal
                try {
                    $('#newTask').modal('show');
                    console.log('8d. Modal show() called');
                    
                    // Check if modal is visible after show
                    setTimeout(function() {
                        console.log('8e. Modal visible after show:', $('#newTask').is(':visible'));
                        console.log('8f. Modal display style:', $('#newTask').css('display'));
                    }, 100);
                } catch(e) {
                    console.error('8g. Error showing modal:', e);
                }
                
                // Initialize datepicker after modal is shown
                setTimeout(function() {
                    console.log('9. Initializing datepicker');
                    try {
                        initTaskDatePicker();
                        console.log('9a. Datepicker initialized');
                    } catch(e) {
                        console.error('9b. Error initializing datepicker:', e);
                    }
                }, 200);
                
                console.log('=== EDIT TASK DEBUG END (SUCCESS) ===');
            },
            error: function(xhr, status, error) {
                console.error('10. AJAX Error - Request failed');
                console.error('10a. Error Status:', status);
                console.error('10b. Error Message:', error);
                console.error('10c. Status Code:', xhr.status);
                console.error('10d. Status Text:', xhr.statusText);
                console.error('10e. Response Text:', xhr.responseText);
                console.error('10f. Ready State:', xhr.readyState);
                console.error('10g. Response Headers:', xhr.getAllResponseHeaders());
                
                // Try to parse error response
                try {
                    if (xhr.responseText) {
                        console.log('10h. Attempting to parse error response');
                        var errorResponse = JSON.parse(xhr.responseText);
                        console.error('10i. Parsed Error Response:', errorResponse);
                    } else {
                        console.warn('10j. No response text available');
                    }
                } catch(e) {
                    console.error('10k. Failed to parse error response:', e);
                    console.error('10l. Response might be HTML or invalid JSON');
                }
                
                errorMsg('<?php echo $this->lang->line("something_went_wrong"); ?>');
                console.log('=== EDIT TASK DEBUG END (ERROR) ===');
            },
            complete: function(xhr, status) {
                console.log('11. AJAX Complete - Request finished');
                console.log('11a. Final Status:', status);
                console.log('11b. Final Ready State:', xhr.readyState);
                console.log('11c. Final Status Code:', xhr.status);
            }
        });
        
        console.log('2d. AJAX call initiated');
        return false;
    };
    
    // Also add click handler as backup
    $(document).ready(function() {
        console.log('EDIT DEBUG: Document ready, setting up click handlers');
        $(document).on('click', 'a[onclick*="edit_todo_task"]', function(e) {
            console.log('EDIT DEBUG: Click handler triggered');
            console.log('EDIT DEBUG: Element:', this);
            console.log('EDIT DEBUG: onclick attribute:', $(this).attr('onclick'));
        });
        
        // Check if function exists
        console.log('EDIT DEBUG: edit_todo_task function exists:', typeof window.edit_todo_task);
        console.log('EDIT DEBUG: edit_todo_task function:', window.edit_todo_task);
    });

                    $(document).ready(function (e) {
                        $("#addtodo_form").on('submit', (function (e) {
                            e.preventDefault();
                            
                            console.log('=== TASK FORM SUBMISSION DEBUG START ===');
                            
                            var $submitBtn = $(".submit_addtask");
                            var $form = $(this);
                            
                            // Validate form
                            var taskTitle = $("#task-title").val().trim();
                            var taskDate = $("#task-date").val().trim();
                            var taskId = $("#taskid").val();
                            
                            console.log('1. Form Values:', {
                                taskTitle: taskTitle,
                                taskDate: taskDate,
                                taskId: taskId,
                                formAction: $form.attr('action'),
                                formMethod: $form.attr('method')
                            });
                            
                            if (!taskTitle) {
                                console.error('Validation failed: Task title is empty');
                                errorMsg('<?php echo $this->lang->line("task_title"); ?> <?php echo $this->lang->line("is_required"); ?>');
                                return false;
                            }
                            
                            if (!taskDate) {
                                console.error('Validation failed: Task date is empty');
                                errorMsg('<?php echo $this->lang->line("date"); ?> <?php echo $this->lang->line("is_required"); ?>');
                                return false;
                            }
                            
                            // Convert BS date to AD date and format according to school date format
                            // Format: YYYY-MM-DD (e.g., 2082-08-19)
                            var convertedDate = taskDate;
                            var schoolDateFormat = '<?php echo $this->customlib->getSchoolDateFormat(); ?>';
                            
                            console.log('2. Date Conversion START:', {
                                originalDate: taskDate,
                                schoolDateFormat: schoolDateFormat,
                                schoolDateFormatType: typeof schoolDateFormat,
                                schoolDateFormatLength: schoolDateFormat ? schoolDateFormat.length : 0,
                                dateMatch: taskDate.match(/^\d{4}-\d{2}-\d{2}$/)
                            });
                            
                            if (taskDate.match(/^\d{4}-\d{2}-\d{2}$/)) {
                                var parts = taskDate.split('-');
                                var bsYear = parseInt(parts[0]);
                                var month = parts[1];
                                var day = parts[2];
                                
                                console.log('3. Date Parts:', {
                                    bsYear: bsYear,
                                    month: month,
                                    day: day,
                                    isBSDate: (bsYear > 2000 && bsYear < 2100)
                                });
                                
                                // If year is > 2000, it's likely BS date, convert to AD
                                if (bsYear > 2000 && bsYear < 2100) {
                                    var adYear = bsYear - 57;
                                    var adDateISO = adYear + '-' + month + '-' + day;
                                    
                                    // Convert to school date format
                                    if (schoolDateFormat == 'd-m-Y') {
                                        convertedDate = day + '-' + month + '-' + adYear;
                                    } else if (schoolDateFormat == 'd/m/Y') {
                                        convertedDate = day + '/' + month + '/' + adYear;
                                    } else if (schoolDateFormat == 'm-d-Y') {
                                        convertedDate = month + '-' + day + '-' + adYear;
                                    } else if (schoolDateFormat == 'm/d/Y') {
                                        convertedDate = month + '/' + day + '/' + adYear;
                                    } else if (schoolDateFormat == 'Y/m/d') {
                                        convertedDate = adYear + '/' + month + '/' + day;
                                    } else {
                                        // Default to d-m-Y format
                                        convertedDate = day + '-' + month + '-' + adYear;
                                    }
                                    
                                    console.log('4. Date Converted:', {
                                        from: taskDate,
                                        toISO: adDateISO,
                                        toSchoolFormat: convertedDate,
                                        conversion: 'BS (' + bsYear + ') -> AD (' + adYear + ')',
                                        schoolFormat: schoolDateFormat
                                    });
                                } else {
                                    // Already AD date, just convert format
                                    if (schoolDateFormat == 'd-m-Y') {
                                        convertedDate = day + '-' + month + '-' + bsYear;
                                    } else if (schoolDateFormat == 'd/m/Y') {
                                        convertedDate = day + '/' + month + '/' + bsYear;
                                    } else if (schoolDateFormat == 'm-d-Y') {
                                        convertedDate = month + '-' + day + '-' + bsYear;
                                    } else if (schoolDateFormat == 'm/d/Y') {
                                        convertedDate = month + '/' + day + '/' + bsYear;
                                    } else if (schoolDateFormat == 'Y/m/d') {
                                        convertedDate = bsYear + '/' + month + '/' + day;
                                    } else {
                                        // Default to d-m-Y format
                                        convertedDate = day + '-' + month + '-' + bsYear;
                                    }
                                    
                                    console.log('4. Date Format Converted (already AD):', {
                                        from: taskDate,
                                        to: convertedDate,
                                        schoolFormat: schoolDateFormat
                                    });
                                }
                            } else {
                                console.warn('4. Date format does not match YYYY-MM-DD:', taskDate);
                            }
                            
                            console.log('4a. Final Converted Date:', convertedDate);
                            
                            // Create FormData and update date - FORCE school format conversion
                            var formData = new FormData(this);
                            
                            // CRITICAL: Ensure date is in school format, not Y-m-d
                            var schoolDateFormat = '<?php echo $this->customlib->getSchoolDateFormat(); ?>';
                            var finalDate = convertedDate;
                            
                            // If still in Y-m-d format, convert it NOW
                            if (convertedDate.match(/^\d{4}-\d{2}-\d{2}$/)) {
                                var parts = convertedDate.split('-');
                                var year = parts[0];
                                var month = parts[1];
                                var day = parts[2];
                                
                                if (schoolDateFormat == 'd-m-Y') {
                                    finalDate = day + '-' + month + '-' + year;
                                } else if (schoolDateFormat == 'd/m/Y') {
                                    finalDate = day + '/' + month + '/' + year;
                                } else if (schoolDateFormat == 'm-d-Y') {
                                    finalDate = month + '-' + day + '-' + year;
                                } else if (schoolDateFormat == 'm/d/Y') {
                                    finalDate = month + '/' + day + '/' + year;
                                } else if (schoolDateFormat == 'Y/m/d') {
                                    finalDate = year + '/' + month + '/' + day;
                                } else {
                                    // Default to d-m-Y
                                    finalDate = day + '-' + month + '-' + year;
                                }
                                console.log('5. FORCED DATE FORMAT CONVERSION:', {
                                    from: convertedDate,
                                    to: finalDate,
                                    schoolFormat: schoolDateFormat
                                });
                            }
                            
                            // Remove old task_date and add converted one
                            formData.delete('task_date');
                            formData.append('task_date', finalDate);
                            
                            // Log all form data
                            console.log('5. FormData Contents:');
                            console.log('5a. Final Date being sent (school format):', finalDate);
                            for (var pair of formData.entries()) {
                                console.log('   -', pair[0] + ':', pair[1]);
                            }
                            
                            // Verify task_date is set correctly
                            var verifyDate = formData.get('task_date');
                            console.log('5b. Verified task_date in FormData:', verifyDate);
                            if (verifyDate !== finalDate) {
                                console.error('5c. WARNING: FormData date mismatch! Expected:', finalDate, 'Got:', verifyDate);
                            }
                            
                            var ajaxUrl = "<?php echo site_url("admin/calendar/addtodo") ?>";
                            console.log('6. AJAX Request:', {
                                url: ajaxUrl,
                                method: 'POST',
                                dataType: 'json',
                                contentType: false,
                                processData: false
                            });
                            
                            $submitBtn.prop("disabled", true);
                            
                            $.ajax({
                                url: ajaxUrl,
                                type: "POST",
                                data: formData,
                                dataType: 'json',
                                contentType: false,
                                cache: false,
                                processData: false,
                                beforeSend: function(xhr, settings) {
                                    console.log('7. Before Send:', {
                                        url: settings.url,
                                        type: settings.type
                                    });
                                },
                                success: function (res)
                                {
                                    console.log('8. AJAX Success Response:', {
                                        fullResponse: res,
                                        status: res ? res.status : 'undefined',
                                        message: res ? res.message : 'undefined',
                                        error: res ? res.error : 'undefined',
                                        responseType: typeof res,
                                        responseString: JSON.stringify(res)
                                    });
                                    
                                    if (!res) {
                                        console.error('8a. Response is null or undefined');
                                        errorMsg('<?php echo $this->lang->line("something_went_wrong"); ?>');
                                        $submitBtn.prop("disabled", false);
                                        return;
                                    }
                                    
                                    if (res.status == "fail") {
                                        console.error('8b. Status is "fail":', res);
                                        var message = "";
                                        if (res.error) {
                                            $.each(res.error, function (index, value) {
                                                message += value + " ";
                                            });
                                        } else {
                                            message = res.message || '<?php echo $this->lang->line("something_went_wrong"); ?>';
                                        }
                                        console.error('8b. Error Message:', message);
                                        errorMsg(message);
                                        $submitBtn.prop("disabled", false);
                                    } else if (res.status == "success") {
                                        console.log('8c. Status is "success" - Closing modal and reloading');
                                        // Close modal first
                                        $('#newTask').modal('hide');
                                        // Show success message
                                        var successMessage = res.message || '<?php echo $this->lang->line("success_message"); ?>';
                                        console.log('8c. Success Message:', successMessage);
                                        successMsg(successMessage);
                                        // Reload page after a short delay to show the message
                                        setTimeout(function() {
                                            console.log('8c. Reloading page...');
                                            window.location.reload(true);
                                        }, 500);
                                    } else {
                                        console.warn('8d. Unexpected status:', res.status, 'Full response:', res);
                                        // Handle unexpected response format
                                        errorMsg(res.message || '<?php echo $this->lang->line("something_went_wrong"); ?>');
                                        $submitBtn.prop("disabled", false);
                                    }
                                    
                                    console.log('=== TASK FORM SUBMISSION DEBUG END (SUCCESS) ===');
                                },
                                error: function(xhr, status, error) {
                                    // Extract JSON from response - look for {"status" at the end
                                    var responseStr = (xhr.responseText || '').toString();
                                    var jsonStart = responseStr.lastIndexOf('{"status"');
                                    if (jsonStart >= 0) {
                                        var jsonEnd = responseStr.lastIndexOf('}');
                                        if (jsonEnd > jsonStart) {
                                            try {
                                                var jsonStr = responseStr.substring(jsonStart, jsonEnd + 1);
                                                var jsonObj = JSON.parse(jsonStr);
                                                if (jsonObj.status === "success") {
                                                    $('#newTask').modal('hide');
                                                    if (typeof successMsg === 'function') {
                                                        successMsg(jsonObj.message || '<?php echo $this->lang->line("success_message"); ?>');
                                                    }
                                                    setTimeout(function() { window.location.reload(); }, 500);
                                                    return;
                                                }
                                            } catch(e) {}
                                        }
                                    }
                                    
                                    console.error('9. AJAX Error Details:', {
                                        status: status,
                                        error: error,
                                        statusCode: xhr.status,
                                        statusText: xhr.statusText,
                                        responseText: xhr.responseText,
                                        responseHeaders: xhr.getAllResponseHeaders(),
                                        readyState: xhr.readyState
                                    });
                                    
                                    // Try to extract JSON from response even if there are PHP errors
                                    var errorMessage = '<?php echo $this->lang->line("something_went_wrong"); ?>';
                                    var parsedError = null;
                                    
                                    try {
                                        if (xhr.responseText) {
                                            console.log('9a. Attempting to extract JSON from response');
                                            var responseText = xhr.responseText;
                                            console.log('9a. Response text length:', responseText.length);
                                            console.log('9a. Response text last 200 chars:', responseText.substring(responseText.length - 200));
                                            
                                            // Try multiple methods to find JSON
                                            var jsonMatch = null;
                                            
                                            // Method 1: Look for JSON after the last </div> (most reliable)
                                            var lastDivIndex = responseText.lastIndexOf('</div>');
                                            if (lastDivIndex !== -1) {
                                                var afterLastDiv = responseText.substring(lastDivIndex + 6).trim();
                                                console.log('9a. Text after last </div>:', afterLastDiv);
                                                if (afterLastDiv.startsWith('{')) {
                                                    // Find the complete JSON object
                                                    var jsonStart = afterLastDiv.indexOf('{');
                                                    var braceCount = 0;
                                                    var jsonEnd = -1;
                                                    for (var i = jsonStart; i < afterLastDiv.length; i++) {
                                                        if (afterLastDiv[i] === '{') braceCount++;
                                                        if (afterLastDiv[i] === '}') {
                                                            braceCount--;
                                                            if (braceCount === 0) {
                                                                jsonEnd = i + 1;
                                                                break;
                                                            }
                                                        }
                                                    }
                                                    if (jsonEnd > jsonStart) {
                                                        jsonMatch = afterLastDiv.substring(jsonStart, jsonEnd);
                                                        console.log('9a. Found JSON using method 1 (after last div):', jsonMatch);
                                                    }
                                                }
                                            }
                                            
                                            // Method 2: Look for JSON pattern with status field
                                            if (!jsonMatch) {
                                                var statusMatch = responseText.match(/\{"status"\s*:\s*"[^"]+"\s*,\s*"error"\s*:\s*"[^"]*"\s*,\s*"message"\s*:\s*"[^"]*"\}/);
                                                if (statusMatch) {
                                                    jsonMatch = statusMatch[0];
                                                    console.log('9a. Found JSON using method 2 (status pattern):', jsonMatch);
                                                }
                                            }
                                            
                                            // Method 3: Find last complete JSON object in response
                                            if (!jsonMatch) {
                                                var lastBrace = responseText.lastIndexOf('}');
                                                if (lastBrace !== -1) {
                                                    // Work backwards to find the opening brace
                                                    var braceCount = 1;
                                                    var jsonStart = lastBrace;
                                                    for (var i = lastBrace - 1; i >= 0; i--) {
                                                        if (responseText[i] === '}') braceCount++;
                                                        if (responseText[i] === '{') {
                                                            braceCount--;
                                                            if (braceCount === 0) {
                                                                jsonStart = i;
                                                                break;
                                                            }
                                                        }
                                                    }
                                                    if (jsonStart < lastBrace) {
                                                        jsonMatch = responseText.substring(jsonStart, lastBrace + 1);
                                                        // Verify it looks like JSON
                                                        if (jsonMatch.includes('"status"') && jsonMatch.includes('"message"')) {
                                                            console.log('9a. Found JSON using method 3 (last complete object):', jsonMatch);
                                                        } else {
                                                            jsonMatch = null;
                                                        }
                                                    }
                                                }
                                            }
                                            
                                            if (jsonMatch) {
                                                console.log('9a. Extracted JSON string:', jsonMatch);
                                                try {
                                                    jsonResponse = JSON.parse(jsonMatch);
                                                    console.log('9a. Successfully parsed JSON:', jsonResponse);
                                                    
                                                    // If status is success, treat it as success even though parser errored
                                                    if (jsonResponse.status == "success") {
                                                        console.log('9a. Status is success - treating as success');
                                                        // Close modal first
                                                        $('#newTask').modal('hide');
                                                        // Show success message
                                                        var successMessage = jsonResponse.message || '<?php echo $this->lang->line("success_message"); ?>';
                                                        console.log('9a. Success Message:', successMessage);
                                                        successMsg(successMessage);
                                                        // Reload page after a short delay
                                                        setTimeout(function() {
                                                            console.log('9a. Reloading page...');
                                                            window.location.reload(true);
                                                        }, 500);
                                                        console.log('=== TASK FORM SUBMISSION DEBUG END (SUCCESS - EXTRACTED FROM ERROR) ===');
                                                        return; // Exit early, don't show error
                                                    }
                                                    
                                                    if (jsonResponse.message) {
                                                        errorMessage = jsonResponse.message;
                                                    } else if (jsonResponse.error) {
                                                        var msg = "";
                                                        $.each(jsonResponse.error, function (index, value) {
                                                            msg += value + " ";
                                                        });
                                                        errorMessage = msg;
                                                    }
                                                } catch(parseError) {
                                                    console.error('9a. Failed to parse extracted JSON:', parseError);
                                                }
                                            } else {
                                                console.warn('9a. No JSON found in response');
                                            }
                                        }
                                    } catch(e) {
                                        console.error('9b. Exception in JSON extraction:', e);
                                    }
                                    
                                    // FINAL FALLBACK: If we have responseText, try to extract JSON manually
                                    if (!jsonResponse && xhr.responseText) {
                                        try {
                                            var responseStr = xhr.responseText.toString();
                                            // Find the JSON object at the end - it's always after the last </div>
                                            var jsonStart = responseStr.lastIndexOf('{"status"');
                                            if (jsonStart !== -1) {
                                                var jsonStr = responseStr.substring(jsonStart);
                                                // Find the closing brace
                                                var jsonEnd = jsonStr.indexOf('}') + 1;
                                                if (jsonEnd > 0) {
                                                    jsonStr = jsonStr.substring(0, jsonEnd);
                                                    console.log('9c. FINAL FALLBACK - Extracted JSON:', jsonStr);
                                                    jsonResponse = JSON.parse(jsonStr);
                                                    console.log('9c. FINAL FALLBACK - Parsed:', jsonResponse);
                                                    
                                                    if (jsonResponse.status == "success") {
                                                        console.log('9c. SUCCESS FOUND - Closing modal');
                                                        $('#newTask').modal('hide');
                                                        successMsg(jsonResponse.message || '<?php echo $this->lang->line("success_message"); ?>');
                                                        setTimeout(function() {
                                                            window.location.reload(true);
                                                        }, 500);
                                                        return;
                                                    }
                                                }
                                            }
                                        } catch(finalError) {
                                            console.error('9c. Final fallback failed:', finalError);
                                        }
                                    }
                                    
                                    console.error('9d. Final Error Message:', errorMessage);
                                    errorMsg(errorMessage);
                                    $submitBtn.prop("disabled", false);
                                    
                                    console.log('=== TASK FORM SUBMISSION DEBUG END (ERROR) ===');
                                },
                                complete: function(xhr, status) {
                                    console.log('10. AJAX Complete:', {
                                        status: status,
                                        readyState: xhr.readyState,
                                        statusCode: xhr.status
                                    });
                                }
                            });

                            return false;
                        }));
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

                    function markcomplete(id) {
                        $('#check' + id).change(function () {
                            if (this.checked) {
                                complete_event(id, 'yes');
                            } else {
                                complete_event(id, 'no');
                            }
                        });
                    }
</script>