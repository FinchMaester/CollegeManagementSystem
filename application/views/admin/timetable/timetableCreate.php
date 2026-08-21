<script>
  // Always available to this page’s scripts
  var base_url = '<?php echo base_url(); ?>';
</script>

<style type="text/css">
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 22px !important; border-radius: 0 !important; padding-left: 0 !important;}
    .input-group-addon .glyphicon{font-size: 12px;}

    .show{
        display : block;
        z-index: 100;
        background-image : url('../../backend/images/timeloader.gif');
        opacity : 0.6;
        background-repeat : no-repeat;
        background-position : center;
    }
    /* .tab-pane{min-height: 200px;}*/
    .commentForm .input-group {position: relative;display: block;border-collapse: separate;}
    .commentForm .input-group-addon{
        position: absolute;
        right: 26px;
        top: 0px;
        z-index: 3;
    }
    .relative{position: relative;}
    .commentForm .input-group-addon i,
    .commentForm .input-group-addon span{padding-left: 13px;}
    .commentForm .relative label.text-danger{position: absolute; bottom: 5px;}
    .addbtnright{ position: absolute;right: 0;top: -46px;}

    @media(max-width:767px){
        .timeresponsive{overflow-x: auto;     overflow-y: hidden;}
        .timeresponsive .dropdown-menu{z-index: 1060;    bottom: 0 !important; height: 250px; padding: 20px;}
        .tablewidthRS{width: 690px;}
    }
</style>
<script src="<?php echo base_url(); ?>backend/custom/jquery.validate.min.js"></script>

<div class="content-wrapper">
    <!-- Content Header (Page header) -->

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                        <div class="box-tools pull-right">
                        </div>
                    </div>
                    <form class="create_time_table" action="<?php echo site_url('admin/timetable/create') ?>" method="post" accept-charset="utf-8">
                        <div class="box-body">

                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('faculty'); ?><small class="req"> *</small></label>
                                        <select autofocus="" id="section_id" name="section_id" class="form-control" >
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php
foreach ($sectionlist as $section) {
    ?>
                                                <option value="<?php echo $section['id'] ?>" <?php
if (set_value('section_id') == $section['id']) {
        echo "selected=selected";
    }
    ?>><?php echo $section['section'] ?></option>
                                                        <?php
}
?>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="program_id"><?php echo $this->lang->line('program'); ?></label><small class="req"> *</small>
                                        <select id="program_id" name="program_id" class="form-control" >
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('program_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('academic_level'); ?><small class="req"> *</small></label>
                                        <select  id="class_id" name="class_id" class="form-control" >
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('class_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('subject_group'); ?><small class="req"> *</small></label>
                                        <select  id="subject_group_id" name="subject_group_id" class="form-control" >
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('subject_group_id'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" id="insertbtn" class="btn btn-primary pull-right btn-sm"><?php echo $this->lang->line('search'); ?></button>
                        </div>
                    </form>

                    <?php
if (isset($getDaysnameList)) {
    ?>
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_parameter_to_generate_time_table_quickly'); ?></h3>
                       
                </div>
                <div class="box-header ptbnull">                      
                <form id="universal_from" class="timeTableForm">          
         
        <div class="row">
            <div class="col-sm-2">
                <div class="form-group">
                    <label for="form_name"><?php echo $this->lang->line('period_start_time'); ?><small class="req"> *</small></label>
                   
                    <div class="input-group">
                                        <input type="text" name="start_time" class="form-control time" id="start_time" value="">
                                        <div class="input-group-addon">
                                            <span class="fa fa-clock-o"></span>
                                        </div>
                                    </div>

                    <div class="text text-danger"></div>
                </div>
            </div>
            <div class="col-sm-2">
                <div class="form-group">
                    <label for="form_email"><?php echo $this->lang->line('duration_minute'); ?><small class="req"> *</small></label>                    
                    <div class="input-group">
                                        <input type="number" name="duration" class="form-control" id="duration" value="">
                                        <div class="input-group-addon">
                                            <span class="fa fa-hourglass-start"></span>
                                        </div>
                                    </div>
                    <div class="text text-danger"></div>
                </div>
            </div>
            <div class="col-sm-2">
                <div class="form-group">
                    <label for="form_phone"><?php echo $this->lang->line('interval_minute'); ?><small class="req"> *</small></label>
                   <div class="input-group">
                                        <input type="number" name="interval" class="form-control" id="interval" value="0">
                                        <div class="input-group-addon">
                                            <span class="fa fa-hourglass-start"></span>
                                        </div>
                                    </div>
                    <div class="text text-danger"></div>
                </div>
            </div>
                 <div class="col-sm-2">
                <div class="form-group">
                    <label for="form_phone"><?php echo $this->lang->line('room_no'); ?></label>
                    <input type="text" name="room_no" class="form-control" id="froom_no">
                    <div class="help-block with-errors"></div>
                </div>
            </div>
              <div class="col-sm-2">
                <div class="form-group">
                    <label for="form_phone" class="displayblock opacity">&nbsp;</label>
                    <button type="button" class="btn btn-primary btn-sm" onclick="generateTimeTable()"><?php echo $this->lang->line('apply'); ?></button>
                    <div class="help-block with-errors"></div>
                </div>
            </div>
        </div>
   </form>

                        </div>
                        <div class="nav-tabs-custom">
                            <ul class="nav nav-tabs" id="myTabs">
                                <?php
$count = 1;

    foreach ($getDaysnameList as $days_key => $days_value) {
        $cls = "";
        if ($count == 1) {
        }
        ?>
                                    <li <?php echo $cls; ?>><a href="#tab_<?php echo $count; ?>" data-c="<?php echo set_value('class_id'); ?>" data-days="<?php echo $days_value; ?>" data-s="<?php echo set_value('section_id'); ?>" data-group="<?php echo set_value('subject_group_id'); ?>" data-day="<?php echo $days_key; ?>" data-toggle="tab" aria-expanded="true"><?php echo $days_value; ?></a></li>

                                    <?php
$count++;
    }
    ?>
                            </ul>
                            <div class="tab-content">
                                <?php
$count = 1;
    foreach ($getDaysnameList as $days_key => $days_value) {
        $cls = "class='tab-pane'";
        if ($count == 1) {
        }
        ?>
                                    <div <?php echo $cls; ?> id="tab_<?php echo $count; ?>">
                                    </div>

                                    <?php
$count++;
    }
    ?>
                            </div>
                        </div>
                    </div>
                    <?php
}
?>
                </section>
            </div>

<script type="text/javascript">
    $(document).on('submit','.create_time_table',function(e){
        document.getElementById("insertbtn").disabled = true;
    });  
     
    $(document).on('focus', '.time', function () {
        var $this = $(this);
        $this.datetimepicker({
            format: 'LT'
        });
    });
    var tot_count = 0;
    var section_id_initial = '<?php echo set_value('section_id') ?>';
    var program_id_initial = '<?php echo set_value('program_id') ?>';
    var class_id_initial = '<?php echo set_value('class_id') ?>';
    var subject_group_id_initial = '<?php echo set_value('subject_group_id') ?>';
               
    $(document).ready(function () {
        $('#myTabs a:first').tab('show') // Select first tab
                   
        // Fetch all sections
        $.ajax({
            url: '<?php echo base_url(); ?>sections/getAll',
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                var html = '<option value=""><?php echo $this->lang->line('select'); ?></option>';

                $.each(response, function (index, section) {
                    var selected = (section_id_initial == section.id) ? 'selected' : '';
                    html += '<option value="' + section.id + '" ' + selected + '>' + section.section + '</option>';
                });
                           
                $('#section_id').html(html);
                           
                // If there's a pre-selected section, trigger program load
                if(section_id_initial) {
                    getProgramsBySection(section_id_initial, program_id_initial);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error fetching sections:", error);
                $('#section_id').html('<option value="">Error loading sections</option>');
            }
        });

        // Initial load of subject group if all criteria are present
        if (class_id_initial && section_id_initial && subject_group_id_initial) {
            getGroupByClassandSection(class_id_initial, section_id_initial, subject_group_id_initial);
        }

        $(document).on('change', '#section_id', function (e) {
            $('#program_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
            $('#class_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
            $('#subject_group_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
                       
            var section_id = $(this).val();
            if (section_id) {
                getProgramsBySection(section_id);
            }
        });

        $(document).on('change', '#program_id', function (e) {
            $('#class_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
            $('#subject_group_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
                       
            var program_id = $(this).val();
            if (program_id) {
                getClassesByProgram(program_id);
            }
        });

        $(document).on('change', '#class_id', function (e) {
            $('#subject_group_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
            var class_id = $(this).val();
            var section_id = $('#section_id').val();
            var program_id = $('#program_id').val(); // Get program_id
            if (class_id && section_id && program_id) { // Include program_id in condition
                getGroupByClassandSection(class_id, section_id, subject_group_id_initial); // Keep initial subject_group_id for pre-selection
            }
        });
    });

    function getProgramsBySection(section_id, selected_program_id = null) {
        if (section_id != "") {
            var base_url = '<?php echo base_url() ?>';
            var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';

            $.ajax({
                type: "POST",
                url: base_url + "admin/timetable/getProgramsBySection",
                data: {'section_id': section_id},
                dataType: "json",
                success: function (data) {
                    $.each(data, function (i, obj) {
                        var sel = "";
                        if (selected_program_id == obj.id) {
                            sel = "selected";
                        }
                        div_data += "<option value=" + obj.id + " " + sel + ">" + obj.title + (obj.code && obj.code !== obj.title ? " (" + obj.code + ")" : "") + "</option>";
                    });
                    $('#program_id').html(div_data);
                               
                    // If we have a selected program, trigger class loading
                    if (selected_program_id) {
                        getClassesByProgram(selected_program_id, class_id_initial);
                    }
                },
                error: function() {
                    $('#program_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
                }
            });
        }
    }

    function getClassesByProgram(program_id, selected_class_id = null) {
        if (program_id != "") {
            var base_url = '<?php echo base_url() ?>';
            var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';

            $.ajax({
                type: "POST",
                url: base_url + "admin/timetable/getClassesByProgram",
                data: {'program_id': program_id},
                dataType: "json",
                success: function (data) {
                    $.each(data, function (i, obj) {
                        var sel = "";
                        if (selected_class_id == obj.id) {
                            sel = "selected";
                        }
                        div_data += "<option value=" + obj.id + " " + sel + ">" + obj.class + " - " + obj.degree + "</option>";
                    });
                    $('#class_id').html(div_data);
                               
                    // If we have a selected class, trigger subject group loading
                    if (selected_class_id) {
                        var section_id = $('#section_id').val();
                        if (section_id) {
                            getGroupByClassandSection(selected_class_id, section_id, subject_group_id_initial);
                        }
                    }
                },
                error: function() {
                    $('#class_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
                }
            });
        }
    }

    function getGroupByClassandSection(class_id, section_id, subject_group_id) {
        if (class_id != "" && section_id != "") { // Removed subject_group_id from this condition
            $('#subject_group_id').html("");

            var base_url = '<?php echo base_url() ?>';
            var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
            $.ajax({
                type: "POST",
                url: base_url + "admin/subjectgroup/getGroupByClassandSection",
                data: {'class_id': class_id, 'section_id': section_id},
                dataType: "json",
                success: function (data) {
                    console.log(subject_group_id);
                    $.each(data, function (i, obj)
                    {
                        var sel = "";
                        if (subject_group_id == obj.subject_group_id) {
                            sel = "selected";
                        }
                        div_data += "<option value=" + obj.subject_group_id + " " + sel + ">" + obj.name + "</option>";
                    });

                    $('#subject_group_id').append(div_data);
                }
            });
        }
    }

    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        var target = $(e.target).attr("href"); // activated tab
        var target_id = $(e.target).attr("id"); // activated tab
        var ajax_data = $(e.target).data(); // activated tab
        $(target).html("");
        getGroupdata(target, target_id, ajax_data);
    })
</script>

<script type="text/javascript">
function getGroupdata(target, target_id, ajax_data) {
  var $target = (typeof target === 'string') ? $(target) : target;
  if (!$target || $target.length === 0) {
    console.error('[TT] getGroupdata: invalid target', target);
    return;
  }
  $.ajax({
    type: 'POST',
    url: base_url + "admin/timetable/getBydategroupclasssection",
    data: {
      day: ajax_data.day,
      class_id: ajax_data.c,
      section_id: ajax_data.s,
      subject_group_id: ajax_data.group
    },
    dataType: 'json',
    beforeSend: function () {
      console.debug('[TT] load day tab', ajax_data);
      $target.addClass('show');
    },
    success: function (data) {
      console.debug('[TT] day tab loaded', {count: data.total_count});
      $target.html(data.html);

      $('.staff', $target).select2({
        dropdownAutoWidth: true,
        width: '100%'
      });
      $('.subject', $target).select2({
        dropdownAutoWidth: true,
        width: '100%'
      });
      tot_count = data.total_count + 1;
    },
    error: function (xhr) {
      console.error('[TT] getGroupdata error', xhr);
      $target.html('<div class="alert alert-danger">Failed to load day tab.</div>');
    },
    complete: function () {
      $target.removeClass('show');
    }
  });
}
</script>

<script type="text/javascript">
$(document).ready(function () {
    var counter = 0;
    $(document).on("click", ".addrow", function () {                      
        var newRow = $("<tr>");
        var cols = "";
        cols += '<td class="relative"><input type="hidden" name="total_row[]" value="' + tot_count + '"><input type="hidden" name="prev_id_' + tot_count + '" value="0"><select class="form-control subject" id="subject_id_' + tot_count + '" name="subject_' + tot_count + '">' + $("#subject_dropdown").text() + '</select></td>';
        cols += '<td class="relative"><select class="form-control staff" id="staff_id_' + tot_count + '" name="staff_' + tot_count + '">' + $("#staff_dropdown").text() + '</select></td>';

        cols += '<td><div class="input-group"><input type="text" name="time_from_' + tot_count + '" class="form-control time_from time" id="time_from_' + tot_count + '"  aria-invalid="false"><div class="input-group-addon"><i class="fa fa-clock-o"></i></div></div></td>';

        cols += '<td><div class="input-group"><input type="text" name="time_to_' + tot_count + '" class="form-control time_to time" id="time_to_' + tot_count + '"  aria-invalid="false"><div class="input-group-addon"><i class="fa fa-clock-o"></i></div></div></td>';

        cols += '<td><input type="text" class="form-control room_no" name="room_no_' + tot_count + '" id="room_no_' + tot_count + '"/> </td>';
        cols += '<td class="text-right"><button type="button" class="ibtnDel btn btn-danger btn-sm"><i class="fa fa-trash"></i></button></td>';
        newRow.append(cols);

        $("table.order-list").append(newRow);

        $('.staff', newRow).select2({
            dropdownAutoWidth: true,
            width: '100%'
        });

        $('.subject', newRow).select2({
            dropdownAutoWidth: true,
            width: '100%'
        });
        tot_count++;
    });

    $(document).on("click", ".ibtnDel", function (event) {
        // read the hidden prev_id_* from this row (not the previous sibling)
        if($(this).closest('tr').find('input[name^="prev_id_"]').val()){
            if (confirm('<?php echo $this->lang->line("are_you_sure_you_want_to_delete"); ?>')) {
                $(this).closest("tr").remove();
                counter -= 1
            }
            return false;
        }else{
            $(this).closest("tr").remove();
            counter -= 1
        }
    });
});
</script>
           
<script type="text/template" id="staff_dropdown">
    <option value=""><?php echo $this->lang->line('select') ?></option>
    <?php
foreach ($staff as $staff_key => $staff_value) {
    ?>
        <option value="<?php echo $staff_value['id']; ?>"><?php echo $staff_value['name'] . " " . $staff_value['surname'] . " (" . $staff_value['employee_id'] . ")"; ?></option>
        <?php
}
?>
</script>

<script type="text/template" id="subject_dropdown">
    <option value=""><?php echo $this->lang->line('select') ?></option>
    <?php
foreach ($subject as $subject_key => $subject_value) {
    if ($subject_value->code !== '') {
        $sub_name = $subject_value->name . " (" . $subject_value->code . ")";
    } else {
        $sub_name = $subject_value->name;
    }
    ?>
        <option value="<?php echo $subject_value->id; ?>" ><?php echo $sub_name; ?></option>
        <?php
}
?>
</script>

<script type="text/javascript">
$(document).ready(function() {
  $("#universal_from").validate({    
    rules: {
      start_time: { required: true },
      duration:   { required: true },
      interval:   { required: true }
    },
    messages: {
      start_time: "<?php echo $this->lang->line('required');?>",
      duration:   "<?php echo $this->lang->line('required');?>",
      interval:   "<?php echo $this->lang->line('required');?>",
    },
    errorClass: 'text-danger',
    validClass: 'valid',
    errorPlacement: function(error, element) {
      $("#errorText").empty();
      if(error[0].htmlFor == 'start_time'){ error.appendTo($(element).parents('div.form-group')); }
      if(error[0].htmlFor == 'duration'){ error.appendTo($(element).parents('div.form-group')); }
      if(error[0].htmlFor == 'interval'){ error.appendTo($(element).parents('div.form-group')); }
    },
    submitHandler: function(form) {
      let start_time= $('#start_time',form).val();
      let duration  = $('#duration',form).val();
      let interval  = $('#interval',form).val();
      let froom_no  = $('#froom_no',form).val();
      var interest  = $('div.tab-pane.active').find('table#tab_logic');
      $('tbody  > tr',interest).each(function() {
        var new_time = moment(start_time, "hh:mm A")
                          .add(duration, 'minutes')
                          .format('hh:mm A');

        $(this).find(".time_from").val(start_time);    
        $(this).find(".time_to").val(new_time);    
        $(this).find(".room_no").val(froom_no);    

        start_time=moment(new_time, "hh:mm A")
                          .add(interval, 'minutes')
                          .format('hh:mm A');
      });
    }
  });
});
</script>

<script type="text/javascript">
function generateTimeTable() {
  // Reuse the validator + submitHandler you already wrote.
  var $form = $('#universal_from');
  if ($form.valid()) {
    // This will run your submitHandler (which fills time_from, time_to, room_no)
    $form.submit();
    // Give immediate feedback
    successMsg('<?php echo $this->lang->line("success_message"); ?>');
  } else {
    errorMsg('<?php echo $this->lang->line("please_fill_all_required_fields"); ?>');
  }
}


// Initialize time picker
$(document).ready(function() {
  $('.time').datetimepicker({ format: 'LT' });
});
</script>

<script type="text/javascript">
$(document).ready(function() {
    // Initialize form submission
    $('#save_timetable').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var activeTab = $('.tab-pane.active');
        var activeTabData = $('.nav-tabs .active a').data();
       
        // Collect all form data from the active tab
        var formData = new FormData(form[0]);
        formData.append('day', activeTabData.day);
       
        // Add all inputs from active tab
        activeTab.find(':input').each(function() {
            var input = $(this);
            if (input.attr('name')) {
                formData.append(input.attr('name'), input.val());
            }
        });

        $.ajax({
            type: 'POST',
            url: base_url + "admin/timetable/savegroup",
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            beforeSend: function() {
                $('#submit_timetable').button('loading');
            },
            success: function(response) {
                if (response.status == 1) {
                    successMsg(response.message);
                    // Refresh the timetable view
                    getGroupdata(activeTab, null, activeTabData);
                } else {
                    var message = "";
                    $.each(response.error, function(index, value) {
                        message += value;
                    });
                    errorMsg(message);
                }
            },
            error: function(xhr) {
                errorMsg('<?php echo $this->lang->line("something_went_wrong"); ?>');
            },
            complete: function() {
                $('#submit_timetable').button('reset');
            }
        });
    });
});
</script>
