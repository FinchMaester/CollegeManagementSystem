<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-book"></i> <?php //echo $this->lang->line('library'); ?></h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                    </div>
                    <div class="box-body">
                        <?php if ($this->session->flashdata('msg')) { ?>
                            <div class="alert alert-success"><?php echo $this->session->flashdata('msg');
                            $this->session->unset_userdata('msg'); ?></div> 
                        <?php } ?>
                        <div class="row">
                            <form role="form" action="<?php echo site_url('admin/member/student') ?>" method="post" class="">
                                <?php echo $this->customlib->getCSRF(); ?>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('section'); ?></label><small class="req"> *</small>
                                        <select id="section_id" name="section_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('program'); ?></label><small class="req"> *</small>
                                        <select id="program_id" name="program_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('program_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('class'); ?></label> <small class="req"> *</small>
                                        <select id="class_id" name="class_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger" id="error_class_id"></span>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <button type="submit" name="search" value="search_filter" class="btn btn-primary btn-sm pull-right checkbox-toggle">
                                            <i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <?php
if (isset($resultlist)) {
    ?>
                        <div class="" id="tachelist">
                            <div class="box-header ptbnull"></div>
                            <div class="box-header ptbnull">
                                <h3 class="box-title titlefix"><?php echo $this->lang->line('student_members_list'); ?></h3>
                                <div class="box-tools pull-right">

                                </div>
                            </div>
                            <div class="box-body">
                                <div class="mailbox-controls">
                                </div>
                                <div class="table-responsive mailbox-messages overflow-visible">
                                    <div class="download_label"><?php echo $this->lang->line('student_members_list'); ?></div>
                                    <table class="table table-striped table-bordered table-hover example">
                                        <thead>
                                            <tr>
                                                <th><?php echo $this->lang->line('member_id'); ?></th>
                                                <th><?php echo $this->lang->line('library_card_no'); ?></th>
                                                <th><?php echo $this->lang->line('admission_no'); ?></th>
                                                <th><?php echo $this->lang->line('student_name'); ?></th>
                                                <th><?php echo $this->lang->line('class'); ?></th>
                                                <th><?php echo $this->lang->line('father_name'); ?></th>
                                                <th><?php echo $this->lang->line('date_of_birth'); ?></th>
                                                <th><?php echo $this->lang->line('gender'); ?></th>
                                                <th><?php echo $this->lang->line('mobile_number'); ?></th>
                                                <th class="text text-right noExport"><?php echo $this->lang->line('action'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                        if (empty($resultlist)) {
                                                ?>

                                                                                        <?php
                                        } else {
                                                $count = 1;

                                                foreach ($resultlist as $student) {
                                                    $clsactive       = "a";
                                                    $member_id       = "";
                                                    $library_card_no = "";
                                                    if ($student['libarary_member_id'] != 0) {
                                                        $clsactive       = "success";
                                                        $member_id       = $student['libarary_member_id'];
                                                        $library_card_no = $student['library_card_no'];
                                                    }
                                                    ?>
                                                    <tr class="<?php echo $clsactive; ?>">
                                                        <td><?php echo $member_id; ?></td>
                                                        <td><?php echo $library_card_no; ?></td>
                                                        <td><?php echo $student['admission_no']; ?></td>
                                                        <td>
                                                            <?php echo $this->customlib->getFullName($student['firstname'], $student['middlename'], $student['lastname'], $sch_setting->middlename, $sch_setting->lastname); ?>

                                                        </td>
                                                        <td><?php echo $student['class'] . "(" . $student['section'] . ")" ?></td>
                                                        <td><?php echo $student['father_name']; ?></td>
                                                        <td><?php if ($student['dob'] != '') {echo date($student['dob']);}?></td>
                                                        <td><?php echo $this->lang->line(strtolower($student['gender'])); ?></td>
                                                        <td><?php echo $student['mobileno']; ?></td>
                                                        <td class="text text-right">
                                                            <?php
                                                            if ($student['libarary_member_id'] == 0) {
                                                                            ?>
                                                                <button  data-stdid="<?php echo $student['id'] ?>" data-admission-no="<?php echo $student['admission_no']; ?>" class="btn btn-default btn-xs add-student"  data-toggle="tooltip" title="<?php echo $this->lang->line('add'); ?>" >
                                                                    <i class="fa fa-plus"></i>
                                                                </button>

                                                                <?php
                                                                    } else {
                                                                                    ?>
                                                                <button type="button" class="btn btn-default btn-xs surrender-student" data-loading-text="<i class='fa fa-spinner fa-spin '></i>" data-toggle="tooltip" data-memberid="<?php echo $member_id; ?>" title="<?php echo $this->lang->line('surrender_membership'); ?>"><i class="fa fa-mail-reply"></i></button>

                                                                <?php
                                                                    }
                                                                                ?>
                                                        </td>
                                                    </tr>
                                                    <?php
$count++;
        }
    }
    ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <?php
}
?>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="squarespaceModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" onclick="window.location.reload
                                (true);" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="lineModalLabel"><?php echo $this->lang->line('add_member'); ?></h4>
            </div>
            <div class="modal-body">
                <input type="hidden" name="click_member_id" value="0" id="click_member_id">
                <!-- content goes here -->
                <form action="<?php echo site_url('admin/member/add') ?>" id="add_member" method="post">
                    <input type="hidden" name="member_id" value="0" id="member_id">
                    <div class="form-group">
                        <label for="exampleInputEmail1"><?php echo $this->lang->line('library_card_no'); ?></label>
                        <input type="name" class="form-control" name="library_card_no" id="library_card_no" >
                        <span class="text-danger" id="library_card_no_error"></span>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm add-member" data-loading-text="<i class='fa fa-spinner fa-spin '></i>"><?php echo $this->lang->line('add'); ?></button>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("#squarespaceModal").modal({
            show: false,
            backdrop: 'static'
        });
    });

    var base_url = '<?php echo base_url() ?>';
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

    $(".surrender-student").click(function () {
        if (confirm('<?php echo $this->lang->line("are_you_sure_you_want_to_surrender_membership"); ?>')) {
            var memberid = $(this).data('memberid');
            var $this = $(this);
            $this.button('loading');
            $.ajax({
                type: "POST",
                url: '<?php echo site_url('admin/member/surrender') ?>',
                data: {'member_id': memberid}, // serializes the form's elements.
                dataType: 'JSON',
                success: function (response)
                {
                    if (response.status == "success") {
                        successMsg(response.message);
                        $this.button('reset');
                        if(response.row_affected){
                        window.setTimeout('location.reload()', 3000);

                        }else{
                            
                        }
                    }
                }
            });
        }
    });

    $(".add-student").click(function () {
    var student = $(this).data('stdid');
    var admissionNo = $(this).data('admission-no');
    $('#click_member_id').val(student);
    $('#member_id').val(student);
    $('#library_card_no').val(admissionNo);
    $('#squarespaceModal').modal('show');
});

    $("#add_member").submit(function (e) {
        var student = $('#click_member_id').val();
        var $this = $('.add-member');
        $this.button('loading');
        $.ajax({
            type: "POST",
            url: $(this).attr('action'),
            data: $("#add_member").serialize(), // serializes the form's elements.
            dataType: 'JSON',
            success: function (response)
            {
                if (response.status == "success") {
                    $('#squarespaceModal').modal('hide');
                    $('#add_member')[0].reset();
                    successMsg(response.message);
                    $this.button('reset');
                    $('*[data-stdid="' + student + '"]').closest('tr').find('td:first').text(response.inserted_id);
                    $('*[data-stdid="' + student + '"]').closest('tr').find('td:nth-child(2)').text(response.library_card_no);
                    $('*[data-stdid="' + student + '"]').closest("tr").addClass("success");
                    $('*[data-stdid="' + student + '"]').closest("td").empty();
                         window.setTimeout('location.reload()', 3000);
                } else if (response.status == "fail") {
                    $.each(response.error, function (index, value) {
                        var errorDiv = '#' + index + '_error';
                        $(errorDiv).empty().append(value);
                    });
                    $this.button('reset');
                }

            }
        });

        e.preventDefault(); // avoid to execute the actual submit of the form.
    });
</script>