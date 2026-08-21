attendance_<style type="text/css">
    .radio {
        padding-left: 20px;}
    .radio label {
        display: inline-block;
        vertical-align: middle;
        position: relative;
        padding-left: 5px; }
    .radio label::before {
        content: "";
        display: inline-block;
        position: absolute;
        width: 17px;
        height: 17px;
        left: 0;
        margin-left: -20px;
        border: 1px solid #cccccc;
        border-radius: 50%;
        background-color: #fff;
        -webkit-transition: border 0.15s ease-in-out;
        -o-transition: border 0.15s ease-in-out;
        transition: border 0.15s ease-in-out; }
    .radio label::after {
        display: inline-block;
        position: absolute;
        content: " ";
        width: 11px;
        height: 11px;
        left: 3px;
        top: 3px;
        margin-left: -20px;
        border-radius: 50%;
        background-color: #555555;
        -webkit-transform: scale(0, 0);
        -ms-transform: scale(0, 0);
        -o-transform: scale(0, 0);
        transform: scale(0, 0);
        -webkit-transition: -webkit-transform 0.1s cubic-bezier(0.8, -0.33, 0.2, 1.33);
        -moz-transition: -moz-transform 0.1s cubic-bezier(0.8, -0.33, 0.2, 1.33);
        -o-transition: -o-transform 0.1s cubic-bezier(0.8, -0.33, 0.2, 1.33);
        transition: transform 0.1s cubic-bezier(0.8, -0.33, 0.2, 1.33); }
    .radio input[type="radio"] {
        opacity: 0;
        z-index: 1; }
    .radio input[type="radio"]:focus + label::before {
        outline: thin dotted;
        outline: 5px auto -webkit-focus-ring-color;
        outline-offset: -2px; }
    .radio input[type="radio"]:checked + label::after {
        -webkit-transform: scale(1, 1);
        -ms-transform: scale(1, 1);
        -o-transform: scale(1, 1);
        transform: scale(1, 1); }
    .radio input[type="radio"]:disabled + label {
        opacity: 0.65; }
    .radio input[type="radio"]:disabled + label::before {
        cursor: not-allowed; }
    .radio.radio-inline {
        margin-top: 0; }
    .radio-primary input[type="radio"] + label::after {
        background-color: #337ab7; }
    .radio-primary input[type="radio"]:checked + label::before {
        border-color: #337ab7; }
    .radio-primary input[type="radio"]:checked + label::after {
        background-color: #337ab7; }
    .radio-danger input[type="radio"] + label::after {
        background-color: #d9534f; }
    .radio-danger input[type="radio"]:checked + label::before {
        border-color: #d9534f; }
    .radio-danger input[type="radio"]:checked + label::after {
        background-color: #d9534f; }
    .radio-info input[type="radio"] + label::after {
        background-color: #5bc0de; }
    .radio-info input[type="radio"]:checked + label::before {
        border-color: #5bc0de; }
    .radio-info input[type="radio"]:checked + label::after {
        background-color: #5bc0de; }
    @media (max-width:767px){
        .radio.radio-inline {display: inherit;}
    }
</style>

<div class="content-wrapper" style="min-height: 946px;">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            <i class="fa fa-calendar-check-o"></i> <?php echo $this->lang->line('attendance'); ?> <small><?php echo $this->lang->line('by_date1'); ?></small></h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                    </div>
                    <form id='form1' action="<?php echo site_url('admin/stuattendence/index') ?>"  method="post" accept-charset="utf-8">
                        <div class="box-body">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="row">
                                <?php if ($this->session->flashdata('msg')) {
    ?>
                                        <?php echo $this->session->flashdata('msg');
    $this->session->unset_userdata('msg'); ?>
                                    <?php }?>
                                    <div class="col-md-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('section'); ?></label><small class="req"> *</small>
                                        <select id="section_id" name="section_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('program'); ?></label><small class="req"> *</small>
                                        <select id="program_id" name="program_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('program_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('class'); ?></label> <small class="req"> *</small>
                                        <select id="class_id" name="class_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger" id="error_class_id"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">
                                        <?php echo $this->lang->line('attendance_date'); ?>
                                    </label><small class="req"> *</small>
                                    <input id="date" name="date" placeholder="" type="text" class="form-control" value="<?php echo set_value('date', isset($date) ? $date : date('Y-m-d')); ?>">
                                    <span class="text-danger"><?php echo form_error('date'); ?></span>
                                </div>
                            </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <button type="submit" name="search" value="search" class="btn btn-primary btn-sm pull-right checkbox-toggle"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <?php
if (isset($resultlist)) {
    ?>
                        <div class="">
                            <div class="box-header ptbnull"></div>
                            <div class="box-header with-border">
                                <h3 class="box-title"><i class="fa fa-users"></i> <?php echo $this->lang->line('student_list'); ?></h3>
                                <div class="box-tools pull-right">
                                </div>
                            </div>
                            <div class="box-body">
                                <?php
if (!empty($resultlist)) {
        $can_edit = 1;
        $checked  = "";
        if (!($this->session->flashdata('msg'))) {
            if ($resultlist[0]['attendence_type_id'] != "") {
                if ($resultlist[0]['attendence_type_id'] != 5) {
                    if ($this->rbac->hasPrivilege('student_attendance', 'can_edit')) {
                        $can_edit = 1;
                    } else {
                        $can_edit = 0;
                    }
                    ?>
                                                <div class="alert alert-success"><?php echo $this->lang->line('attendance_already_submitted_you_can_edit_record'); ?></div>
                                                <?php
} else {
                    $checked = "checked='checked'";
                    ?>
                                                <div class="alert alert-warning"><?php echo $this->lang->line('attendance_already_submitted_as_holiday_you_can_edit_record'); ?></div>
                                                <?php
}
            }
        } else {
            ?>
                                        <div class="alert alert-success"><?php echo $this->lang->line('attendance_saved_successfully'); ?></div>
                                        <?php
}
        ?>
                                    <form action="<?php echo site_url('admin/stuattendence/index') ?>" method="post" class="form_attendence">
                                        <?php echo $this->customlib->getCSRF(); ?>
                                        <div class="mailbox-controls">
                                            <span class="button-checkbox">
                                                <?php if ($this->rbac->hasPrivilege('student_attendance', 'can_add')) {?>
                                                    <button type="button" class="btn btn-sm btn-primary" data-color="primary"><?php echo $this->lang->line('mark_as_holiday'); ?></button>
                                                    <input type="checkbox" id="checkbox1" class="hidden" name="holiday" value="checked" <?php echo $checked; ?>/>
                                                </span>
                                                <div class="pull-right">
                                                    <?php
}
        if ($can_edit == 1) {
            if ($this->rbac->hasPrivilege('student_attendance', 'can_add')) {
                ?>
                                                        <button type="submit" name="search" value="saveattendence" class="btn btn-primary btn-sm pull-right checkbox-toggle"><i class="fa fa-save"></i> <?php echo $this->lang->line('save_attendance'); ?> </button>
                                                    <?php }
        }
        ?>
                                            </div>
                                        </div>
                                        <input type="hidden" name="class_id" value="<?php echo $class_id; ?>">
                                        <input type="hidden" name="section_id" value="<?php echo $section_id; ?>">
                                        <input type="hidden" name="date" value="<?php echo $date; ?>">
                                        <div class="table-responsive ptt10">
                                            <table class="table table-hover table-striped example">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th><?php echo $this->lang->line('admission_no'); ?></th>
                                                        <?php
if ($sch_setting->biometric) {
            ?>
                                                            <th><?php echo $this->lang->line('date'); ?></th>
                                                            <?php
}
        ?>
                                                        <th><?php echo $this->lang->line('roll_number'); ?></th>
                                                        <th><?php echo $this->lang->line('name'); ?></th>
                                                        <th width="30%"><?php echo $this->lang->line('attendance'); ?></th>
                                                        <th class="noteinput"><?php echo $this->lang->line('note'); ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
$row_count = 1;
        foreach ($resultlist as $key => $value) {
            ?>
                                                        <tr>
                                                            <td>
                                                                <input type="hidden" name="student_session[]" value="<?php echo $value['student_session_id']; ?>">
                                                                <input  type="hidden" value="<?php echo $value['attendence_id']; ?>"  name="attendendence_id<?php echo $value['student_session_id']; ?>">
            <?php echo $row_count; ?>
                                                            </td>
                                                            <td>
                                                            <?php echo $value['admission_no']; ?>
                                                            </td>
                                                            <?php
if ($sch_setting->biometric) {
                ?>
                                                                <td>
                                                                    <?php
if ($value['biometric_attendence']) {

                    echo $value['attendence_dt'];
                }
                ?>
                                                                </td>
                                                                <?php
}
            ?>
                                                            <td>
            <?php echo $value['roll_no']; ?>
                                                            </td>
                                                            <td>
            <?php
echo $this->customlib->getFullName($value['firstname'], $value['middlename'], $value['lastname'], $sch_setting->middlename, $sch_setting->lastname); ?>
                                                            </td>
                                                            <td>
                                                                <?php
$c     = 1;
            $count = 0;
            foreach ($attendencetypeslist as $key => $type) {
                if ($type['key_value'] != "H") {
                    $att_type = str_replace(" ", "_", strtolower($type['type']));
                    if ($value['date'] != "xxx") {
                        ?>
                                                                            <div class="radio radio-info radio-inline">
                                                                                <input <?php if ($value['attendence_type_id'] == $type['id']) {
                            echo "checked";
                        }
                        ?> type="radio" id="attendencetype<?php echo $value['student_session_id'] . "-" . $count; ?>" value="<?php echo $type['id'] ?>" name="attendencetype<?php echo $value['student_session_id']; ?>" >

                                                                                <label for="attendencetype<?php echo $value['student_session_id'] . "-" . $count; ?>">
                        <?php echo $this->lang->line($att_type); ?>
                                                                                </label>

                                                                            </div>
                                                                            <?php
} else {
                        ?>
                                                                            <div class="radio radio-info radio-inline">
                                                                                <?php
if ($sch_setting->biometric) {
                            ?>
                                                                                    <input <?php if ($att_type == "absent") {
                                echo "checked";
                            }
                            ?> type="radio" id="attendencetype<?php echo $value['student_session_id'] . "-" . $count; ?>" value="<?php echo $type['id'] ?>" name="attendencetype<?php echo $value['student_session_id']; ?>" >
                                                                                    <?php
} else {
                            ?>
                                                                                    <input <?php if ($c == 1) {
                                echo "checked";
                            }
                            ?> type="radio" id="attendencetype<?php echo $value['student_session_id'] . "-" . $count; ?>" value="<?php echo $type['id'] ?>" name="attendencetype<?php echo $value['student_session_id']; ?>" >
                                                                                    <?php
}
                        ?>

                                                                                <label for="attendencetype<?php echo $value['student_session_id'] . "-" . $count; ?>">
                        <?php echo $this->lang->line($att_type); ?>
                                                                                </label>
                                                                            </div>
                                                                            <?php
}
                    $c++;
                    $count++;
                }
            }
            ?>
                                                            </td>
                                                            <?php if ($date == 'xxx') {?>
                                                                <td class="text-right"><input type="text" class="noteinput" name="remark<?php echo $value["student_session_id"] ?>" ></td>
            <?php } else {?>

                                                                <td class="text-right"><input type="text" class="noteinput" name="remark<?php echo $value["student_session_id"] ?>" value="<?php echo $value["remark"]; ?>" ></td>
                                                        <?php }?>
                                                        </tr>
                                                        <?php
$row_count++;
        }
        ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </form>
                                    <?php
} else {
        ?>
                                    <div class="alert alert-info"><?php echo $this->lang->line('admited_alert'); ?></div>
                                    <?php
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
                $(document).ready(function () {
    // Fetch all sections
    $.ajax({
        url: '<?php echo base_url(); ?>sections/getAll',
        method: 'GET',
        dataType: 'json',
        success: function (response) {
            var section_id = '<?php echo set_value("section_id") ?>';
            var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
            
            $.each(response, function (index, section) {
                var selected = (section_id == section.id) ? 'selected' : '';
                html += '<option value="' + section.id + '" ' + selected + '>' + section.section + '</option>';
            });
            
            $('#section_id').html(html);
            
            // If there's a pre-selected section, trigger program load
            if(section_id) {
                $('#section_id').trigger('change');
            }
        }
    });
});

// When section changes, fetch programs
$(document).on('change', '#section_id', function (e) {
    var section_id = $(this).val();
    
    // Clear program and class dropdowns
    $('#program_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
    $('#class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
    
    if (section_id != '') {
        // Show loading indicator
        $('#program_id').html('<option value="">Loading...</option>');
        
        // Fetch programs for this section
        $.ajax({
            url: '<?php echo base_url(); ?>programs/getBySection', 
            method: 'POST',
            data: {section_id: section_id},
            dataType: 'json',
            success: function (response) {
                var program_id = '<?php echo set_value("program_id") ?>';
                var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                
                if(response.length > 0) {
                    $.each(response, function (index, program) {
                        var selected = (program_id == program.id) ? 'selected' : '';
                        html += '<option value="' + program.id + '" ' + selected + '>' + program.title + '</option>';
                    });
                } else {
                    html += '<option value="" disabled>No programs found</option>';
                }
                
                $('#program_id').html(html);
                
                // If there's a pre-selected program, trigger class load
                if(program_id) {
                    $('#program_id').trigger('change');
                }
            },
            error: function(xhr, status, error) {
                console.error("Error fetching programs:", error);
                console.error("Response:", xhr.responseText);
                $('#program_id').html('<option value="">Error loading programs</option>');
            }
        });
    }
});

// When program changes, fetch classes
$(document).on('change', '#program_id', function (e) {
    var section_id = $('#section_id').val();
    var program_id = $(this).val();
    
    console.log("Fetching classes for Section:", section_id, "Program:", program_id);
    
    $('#class_id').html('<option value="">Loading...</option>');
    
    $.ajax({
        url: '<?php echo base_url(); ?>classes/getBySectionAndProgram',
        method: 'POST',
        data: {
            section_id: section_id,
            program_id: program_id,
            <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'
        },
        dataType: 'json',
        success: function(response) {
            console.log("Raw response:", response);
            
            var class_id = '<?php echo set_value("class_id") ?>'; // ADD THIS LINE
            var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
            
            if (response && response.length > 0) {
                $.each(response, function(index, classItem) {
                    // ADD SELECTED LOGIC HERE
                    var selected = (class_id == classItem.id) ? 'selected' : '';
                    html += '<option value="' + classItem.id + '" ' + selected + '>' + 
                           (classItem.degree ? classItem.degree + ' - ' : '') + 
                           classItem.class + '</option>';
                });
            } else {
                html += '<option value="" disabled>No classes found for this combination</option>';
                console.warn("No classes found for Section:", section_id, "Program:", program_id);
            }
            
            $('#class_id').html(html);
        },
        error: function(xhr, status, error) {
            console.error("AJAX Error:", status, error);
            console.error("Response:", xhr.responseText);
            $('#class_id').html('<option value="">Error loading classes</option>');
        }
    });
});

                $(document).ready(function () {
                    $.extend($.fn.dataTable.defaults, {
                        searching: false,
                        ordering: true,
                        paging: false,
                        retrieve: true,
                        destroy: true,
                        info: false
                    });
                    var table = $('.example').DataTable();
                    table.buttons('.export').remove();
                    var section_id_post = '<?php echo $section_id; ?>';
                    var class_id_post = '<?php echo $class_id; ?>';

                    $(document).on('change', '#class_id', function (e) {
                        var class_id = $(this).val();
                        var base_url = '<?php echo base_url() ?>';
                        var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
                        var url = "<?php
                        $userdata = $this->customlib->getUserData();
                        if (($userdata["role_id"] == 2)) {
                            echo "getClassTeacherSection";
                        } 
                        ?>";
                    });

                });
              
            </script>

            <script type="text/javascript">
                $(function () {
                    $('.button-checkbox').each(function () {
                        var $widget = $(this),
                                $button = $widget.find('button'),
                                $checkbox = $widget.find('input:checkbox'),
                                color = $button.data('color'),
                                settings = {
                                    on: {
                                        icon: 'glyphicon glyphicon-check'
                                    },
                                    off: {
                                        icon: 'glyphicon glyphicon-unchecked'
                                    }
                                };
                        $button.on('click', function () {

                            $checkbox.prop('checked', !$checkbox.is(':checked'));
                            $checkbox.triggerHandler('change');
                            updateDisplay();
                        });
                        $checkbox.on('change', function () {
                            updateDisplay();
                        });

                        function updateDisplay() {
                            var isChecked = $checkbox.is(':checked');
                            $button.data('state', (isChecked) ? "on" : "off");
                            $button.find('.state-icon')
                                    .removeClass()
                                    .addClass('state-icon ' + settings[$button.data('state')].icon);
                            if (isChecked) {
                                $button
                                        .removeClass('btn-success')
                                        .addClass('btn-' + color + ' active');
                            } else {
                                $button
                                        .removeClass('btn-' + color + ' active')
                                        .addClass('btn-primary');
                            }
                        }

                        function init() {
                            updateDisplay();
                            if ($button.find('.state-icon').length == 0) {
                                $button.prepend('<i class="state-icon ' + settings[$button.data('state')].icon + '"></i> ');
                            }
                        }
                        init();
                    });
                });

                $('#checkbox1').change(function () {
                    if (this.checked) {
                   
                        var returnVal = confirm("<?php echo $this->lang->line('are_you_sure'); ?>");
                        $(this).prop("checked", returnVal);
                        $("input[type=radio]").attr('disabled', returnVal);
                    } else {
                        $("input[type=radio]").attr('disabled', false);
                        $("input[type=radio][value='1']").attr("checked", "checked");
                    }
                });

                $('form.form_attendence').on('submit', function (e) {
                    $(this).submit(function () {
                        return false;
                    });
                    return true;
                });

                // Set today's Nepali date using global datepicker
                $(document).ready(function(){
                    var $date = $('#date');
                    if (!$date.val()) {
                        // Wait for global datepicker to be available
                        function setTodaysNepaliDate() {
                            if (typeof $.fn.nepaliDatePicker !== 'undefined') {
                                try {
                                    // Get today's Nepali date using the global datepicker
                                    var now = new Date();
                                    var today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                                    
                                    // Use the datepicker's conversion method
                                    var $temp = $('<input>');
                                    $temp.nepaliDatePicker({
                                        dateFormat: 'YYYY-MM-DD',
                                        ndpYear: true,
                                        ndpMonth: true,
                                        ndpYearCount: 10,
                                        readOnlyInput: false
                                    });
                                    
                                    // Set today's date
                                    $temp.val(today.getFullYear() + '-' + 
                                            String(today.getMonth() + 1).padStart(2, '0') + '-' + 
                                            String(today.getDate()).padStart(2, '0'));
                                    
                                    // Get the converted Nepali date
                                    var nepaliDate = $temp.nepaliDatePicker('getNepaliDate');
                                    if (nepaliDate && nepaliDate.year) {
                                        var yy = nepaliDate.year;
                                        var mm = String(nepaliDate.month).padStart(2, '0');
                                        var dd = String(nepaliDate.day).padStart(2, '0');
                                        $date.val(yy + '-' + mm + '-' + dd);
                                    }
                                    
                                    $temp.remove();
                                } catch (e) {
                                    console.log('Error getting Nepali date:', e);
                                }
                            } else {
                                // Retry in 500ms if datepicker not ready
                                setTimeout(setTodaysNepaliDate, 500);
                            }
                        }
                        
                        setTodaysNepaliDate();
                    }
                });
            </script>