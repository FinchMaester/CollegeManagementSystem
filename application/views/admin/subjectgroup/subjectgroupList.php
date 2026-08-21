<?php $currency_symbol = $this->customlib->getSchoolCurrencyFormat();?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <?php
if ($this->rbac->hasPrivilege('subject_group', 'can_add')) {
    ?>
                <div class="col-md-4">
                    <!-- Horizontal Form -->
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?php echo $this->lang->line('add_subject_group'); ?></h3>
                        </div><!-- /.box-header -->
                        <form id="form1" action="<?php echo site_url('admin/subjectgroup') ?>"  id="employeeform" name="employeeform" method="post" accept-charset="utf-8">
                            <div class="box-body">
                                <?php if ($this->session->flashdata('msg')) {
        ?>
                                    <?php echo $this->session->flashdata('msg');
        $this->session->unset_userdata('msg'); ?>
                                <?php }?>
                                <?php
if (isset($error_message)) {
        echo "<div class='alert alert-danger'>" . $error_message . "</div>";
    }
    ?>
                                <?php echo $this->customlib->getCSRF(); ?>
 
                                <div class="form-group">
                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('name'); ?></label> <small class="req">*</small>
                                    <input autofocus="" id="name" name="name" placeholder="" type="text" class="form-control"  value="<?php echo set_value('name'); ?>" />
                                    <span class="text-danger"><?php echo form_error('name'); ?></span>
                                </div>
                               
                                <!-- Multi-select Faculty, Program, Academic Level Section -->
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('faculty'); ?> <small class="req"> *</small></label>
                                    <div id="faculty_checkboxes_container">
                                        <!-- Faculty checkboxes will be loaded here via AJAX -->
                                        <span class="text-info"><?php echo $this->lang->line('loading_faculties'); ?>...</span>
                                    </div>
                                    <span class="text-danger" id="faculty_checkboxes_error"></span>
                                </div>


                                <div id="dynamic_program_class_container">
                                    <!-- Program and Academic Level dropdowns will be dynamically added here based on selected faculties -->
                                </div>
                                <!-- End Multi-select Section -->


                                <div class="form-group">
                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('subject') ?></label><small class="req"> *</small>

                                    <h4 style="font-weight:bold; margin-top:10px;">Academic Subjects</h4>
                                    <?php
foreach ($subjectlist as $subject) {
    if (isset($subject['is_academic']) && $subject['is_academic'] == '1') { // Academic
        ?>
        <div class="checkbox">
            <label>
                <input type="checkbox" name="subject[]" value="<?php echo $subject['id'] ?>" <?php echo set_checkbox('subject[]', $subject['id']); ?> ><?php echo $subject['name'] ?>
            </label>
        </div>
        <?php
    }
}
?>

                                    <h4 style="font-weight:bold; margin-top:20px;">Non-Academic Subjects</h4>
                                    <?php
foreach ($subjectlist as $subject) {
    if (isset($subject['is_academic']) && $subject['is_academic'] == '0') { // Non-Academic
        ?>
        <div class="checkbox">
            <label>
                <input type="checkbox" name="subject[]" value="<?php echo $subject['id'] ?>" <?php echo set_checkbox('subject[]', $subject['id']); ?> ><?php echo $subject['name'] ?>
            </label>
        </div>
        <?php
    }
}
?>
                                    <span class="text-danger"><?php echo form_error('subject[]'); ?></span>
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('description'); ?></label>
                                    <textarea class="form-control" id="description" name="description" placeholder="" rows="3" placeholder="Enter ..."><?php echo set_value('description'); ?></textarea>
                                    <span class="text-danger"></span>
                                </div>
                            </div><!-- /.box-body -->
                            <div class="box-footer">
                                <button type="submit" class="btn btn-info pull-right"><?php echo $this->lang->line('save'); ?></button>
                            </div>
                        </form>
                    </div>
                </div><!--/.col (right) -->
                <!-- left column -->
            <?php }?>
            <div class="col-md-<?php
if ($this->rbac->hasPrivilege('subject_group', 'can_add')) {
    echo "8";
} else {
    echo "12";
}
?>">
                <!-- general form elements -->
                <div class="box box-primary" id="subject_list">
                    <div class="box-header ptbnull">
                        <h3 class="box-title titlefix"><?php echo $this->lang->line('subject_group_list'); ?></h3>
                        <div class="box-tools pull-right">
                        </div><!-- /.box-tools -->
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <div class="table-responsive mailbox-messages">
                            <div class="download_label"><?php echo $this->lang->line('subject_group_list'); ?></div>


                            <a class="btn btn-default btn-xs pull-right" title="<?php echo $this->lang->line('print'); ?>" id="print" onclick="printDiv()" ><i class="fa fa-print"></i></a>
                            <a class="btn btn-default btn-xs pull-right" title="<?php echo $this->lang->line('export'); ?>"  id="btnExport" onclick="fnExcelReport();"> <i class="fa fa-file-excel-o"></i> </a>
                           
                            <table class="table table-striped table-hover" id="headerTable">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('name'); ?></th>
                                        <th class="text-left"><?php echo $this->lang->line('class_section'); ?></th>
                                        <th><?php echo $this->lang->line('subject'); ?></th>
                                        <th class="text-right no_print"><?php echo $this->lang->line('action'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (!empty($subjectgroupList)) {
                                        foreach ($subjectgroupList as $subjectgroup) {
                                            ?>
                                            <tr>
                                                <td class="mailbox-name">
                                                    <a href="#" data-toggle="popover" class="detail_popover"><?php echo $subjectgroup->name; ?></a>
                                                    <div class="fee_detail_popover" style="display: none">
                                                        <?php
                                                        if (empty($subjectgroup->description)) {
                                                            ?>
                                                            <p class="text-danger"><?php echo $this->lang->line('no_description'); ?></p>
                                                            <?php
                                                        } else {
                                                            ?>
                                                            <p class="text-info"><?php echo $subjectgroup->description; ?></p>
                                                            <?php
                                                        }
                                                        ?>
                                                    </div>
                                                </td>
                                                <td class="text-left">
                                                    <?php if (!empty($subjectgroup->sections)) { ?>
                                                        <ul class="list-unstyled mb-0">
                                                            <?php
                                                            $grouped_assignments = array();
                                                            foreach ($subjectgroup->sections as $section) {
                                                                $key = $section->class . ' - ' . $section->program_name;
                                                                if (!isset($grouped_assignments[$key])) {
                                                                    $grouped_assignments[$key] = array();
                                                                }
                                                                $grouped_assignments[$key][] = $section->section;
                                                            }
                                                            foreach ($grouped_assignments as $key => $sections) {
                                                            ?>
                                                                <li>
                                                                    <strong><?php echo $key; ?></strong>
                                                                    <br>
                                                                    <small class="text-muted">
                                                                        <?php echo $this->lang->line('faculty'); ?>:
                                                                        <?php echo implode(', ', $sections); ?>
                                                                    </small>
                                                                </li>
                                                            <?php } ?>
                                                        </ul>
                                                    <?php } else { ?>
                                                        <span class="text-muted"><?php echo $this->lang->line('no_sections_assigned'); ?></span>
                                                    <?php } ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($subjectgroup->group_subject)) { ?>
                                                        <ul class="list-unstyled mb-0">
                                                            <?php foreach ($subjectgroup->group_subject as $subject) { ?>
                                                                <li><?php echo $subject->name; ?></li>
                                                            <?php } ?>
                                                        </ul>
                                                    <?php } else { ?>
                                                        <span class="text-muted"><?php echo $this->lang->line('no_subjects_assigned'); ?></span>
                                                    <?php } ?>
                                                </td>
                                                <td class="text-right no_print">
                                                    <?php if ($this->rbac->hasPrivilege('subject_group', 'can_edit')) { ?>
                                                        <a href="<?php echo base_url(); ?>admin/subjectgroup/edit/<?php echo $subjectgroup->id; ?>"
                                                           class="btn btn-default btn-xs" data-toggle="tooltip"
                                                           title="<?php echo $this->lang->line('edit'); ?>">
                                                            <i class="fa fa-pencil"></i>
                                                        </a>
                                                    <?php } ?>
                                                    <?php if ($this->rbac->hasPrivilege('subject_group', 'can_delete')) { ?>
                                                        <a href="<?php echo base_url(); ?>admin/subjectgroup/delete/<?php echo $subjectgroup->id; ?>"
                                                           class="btn btn-default btn-xs" data-toggle="tooltip"
                                                           title="<?php echo $this->lang->line('delete'); ?>"
                                                           onclick="return confirm('<?php echo $this->lang->line('delete_confirm'); ?>');">
                                                            <i class="fa fa-remove"></i>
                                                        </a>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                    } else {
                                        ?>
                                        <tr>
                                            <td colspan="4" class="text-center"><?php echo $this->lang->line('no_record_found'); ?></td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                </tbody>
                            </table><!-- /.table -->
                        </div><!-- /.mail-box-messages -->
                    </div><!-- /.box-body -->
                </div>
            </div><!--/.col (left) -->
            <!-- right column -->
        </div>
        <div class="row">
            <!-- left column -->
            <!-- right column -->
            <div class="col-md-12">
            </div><!--/.col (right) -->
        </div>   <!-- /.row -->
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->


<script type="text/javascript">
$(document).ready(function () {
    var post_assignments = <?php echo json_encode(isset($assigned_sections_data) ? $assigned_sections_data : []); ?>; // This will hold existing assignments for edit


    // Function to load all faculties and populate checkboxes
    function loadFaculties(selected_faculties = []) {
        $.ajax({
            url: base_url + "sections/getAll",
            type: "GET",
            dataType: "json",
            success: function(data) {
                var html = '';
                if (data && Array.isArray(data) && data.length > 0) {
                    data.forEach(function(faculty) {
                        var checked = (selected_faculties.includes(faculty.id)) ? 'checked' : '';
                        html += '<div class="checkbox">';
                        html += '<label>';
                        html += '<input type="checkbox" name="selected_faculties[]" value="' + faculty.id + '" ' + checked + ' class="faculty_checkbox">' + faculty.section;
                        html += '</label>';
                        html += '</div>';
                    });
                } else {
                    html = '<?php echo $this->lang->line("no_faculty_found"); ?>';
                }
                $('#faculty_checkboxes_container').html(html);
               
                // Trigger change for pre-selected faculties on load
                $('.faculty_checkbox:checked').each(function() {
                    handleFacultyCheckboxChange($(this).val(), true);
                });
            },
            error: function(xhr, status, error) {
                console.error('Error loading faculties:', error);
                $('#faculty_checkboxes_container').html('<span class="text-danger">Error loading faculties.</span>');
            }
        });
    }


    // Handle faculty checkbox change
    $(document).on('change', '.faculty_checkbox', function() {
        var facultyId = $(this).val();
        var isChecked = $(this).is(':checked');
        handleFacultyCheckboxChange(facultyId, isChecked);
    });


    function handleFacultyCheckboxChange(facultyId, isChecked) {
        var containerId = 'faculty_programs_' + facultyId;


        if (isChecked) {
            // Add container for programs and academic levels for this faculty
            var facultyName = $('input.faculty_checkbox[value="' + facultyId + '"]').parent().text().trim();
            var newHtml = `
                <div id="${containerId}" class="panel panel-default" style="margin-top: 15px;">
                    <div class="panel-heading">
                        <h3 class="panel-title">${facultyName} - <?php echo $this->lang->line('assignments'); ?></h3>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('program'); ?> <small class="req">*</small></label>
                                    <select class="form-control program_select" data-faculty-id="${facultyId}" name="program_ids[${facultyId}]" required>
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('academic_level'); ?> <small class="req">*</small></label>
                                    <select class="form-control class_select" data-faculty-id="${facultyId}" name="class_ids[${facultyId}]" required>
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $('#dynamic_program_class_container').append(newHtml);
           
            // Load programs for this faculty
            loadProgramsForFaculty(facultyId, containerId);


        } else {
            // Remove the container if unchecked
            $('#' + containerId).remove();
        }
    }


    // Function to load programs for a specific faculty
    function loadProgramsForFaculty(facultyId, containerId) {
        var programSelect = $('#' + containerId).find('.program_select');
        programSelect.html('<option value="">Loading programs...</option>');


        $.ajax({
            url: base_url + "programs/getBySection", // This endpoint returns programs by section (faculty)
            type: "POST",
            data: {
                section_id: facultyId,
                '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'
            },
            dataType: "json",
            success: function(data) {
                programSelect.empty();
                programSelect.append('<option value=""><?php echo $this->lang->line('select'); ?></option>');
                if (data && Array.isArray(data) && data.length > 0) {
                    data.forEach(function(program) {
                        programSelect.append(`<option value="${program.id}">${program.title}</option>`);
                    });
                }


                // Pre-select program if exists in post_assignments
                if (post_assignments[facultyId] && post_assignments[facultyId].program_id) {
                    programSelect.val(post_assignments[facultyId].program_id).trigger('change');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading programs for faculty ' + facultyId + ':', error);
                programSelect.html('<option value="">Error loading programs</option>');
            }
        });
    }


    // Handle program select change (to load academic levels)
    $(document).on('change', '.program_select', function() {
        var facultyId = $(this).data('faculty-id');
        var programId = $(this).val();
        var classSelect = $('#faculty_programs_' + facultyId).find('.class_select');
        classSelect.html('<option value="">Loading academic levels...</option>');


        if (programId) {
            $.ajax({
                url: base_url + "classes/getBySectionAndProgram",
                type: "POST",
                data: {
                    section_id: facultyId,
                    program_id: programId,
                    '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'
                },
                dataType: "json",
                success: function(data) {
                    classSelect.empty();
                    classSelect.append('<option value=""><?php echo $this->lang->line('select'); ?></option>');
                    if (data && Array.isArray(data) && data.length > 0) {
                        data.forEach(function(classItem) {
                            // Concatenate degree and class for display
                            var display_text = classItem.class;
                            if (classItem.degree) {
                                display_text = classItem.degree + ' - ' + classItem.class;
                            }
                            classSelect.append(`<option value="${classItem.id}">${display_text}</option>`);
                        });
                    }


                    // Pre-select class if exists in post_assignments
                    if (post_assignments[facultyId] && post_assignments[facultyId].class_id) {
                        classSelect.val(post_assignments[facultyId].class_id);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading academic levels for program ' + programId + ':', error);
                    classSelect.html('<option value="">Error loading academic levels</option>');
                }
            });
        } else {
            classSelect.html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
        }
    });


    // Initial load for edit mode
    var initialSelectedFaculties = Object.keys(post_assignments);
    loadFaculties(initialSelectedFaculties);


    // Popover initialization
    $('.detail_popover').popover({
        placement: 'right',
        trigger: 'hover',
        container: 'body',
        html: true,
        content: function () {
            return $(this).closest('td').find('.fee_detail_popover').html();
        }
    });
});


// Print and Excel functions (kept as is)
function printDiv() {
    $(".no_print").css("display", "none");
    document.getElementById("print").style.display = "none";
    document.getElementById("btnExport").style.display = "none";
    var divElements = document.getElementById('subject_list').innerHTML;
    var oldPage = document.body.innerHTML;
    document.body.innerHTML =
            "<html><head><title></title></head><body>" +
            divElements + "</body>";
    window.print();
    document.body.innerHTML = oldPage;
    location.reload(true);
}


function fnExcelReport() {
    var tab_text = "<table border='2px'><tr >";
    var textRange;
    var j = 0;
    tab = document.getElementById('headerTable');


    for (j = 0; j < tab.rows.length; j++) {
        tab_text = tab_text + tab.rows[j].innerHTML + "</tr>";
    }


    tab_text = tab_text + "</table>";
    tab_text = tab_text.replace(/<A[^>]*>|<\/A>/g, "");
    tab_text = tab_text.replace(/<img[^>]*>/gi, "");
    tab_text = tab_text.replace(/<input[^>]*>|<\/input>/gi, "");


    var ua = window.navigator.userAgent;
    var msie = ua.indexOf("MSIE ");


    if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) {
        txtArea1.document.open("txt/html", "replace");
        txtArea1.document.write(tab_text);
        txtArea1.document.close();
        txtArea1.focus();
        sa = txtArea1.document.execCommand("SaveAs", true, "Say Thanks to Sumit.xls");
    } else {
        sa = window.open('data:application/vnd.ms-excel,' + encodeURIComponent(tab_text));
    }


    return (sa);
}
</script>




