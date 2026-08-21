<?php $currency_symbol = $this->customlib->getSchoolCurrencyFormat(); ?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">




    <section class="content-header">
        <h1>
            <i class="fa fa-mortar-board"></i> <?php echo $this->lang->line('academics'); ?>     </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <?php
            if ($this->rbac->hasPrivilege('class', 'can_add')) {
                ?>  
                <div class="col-md-4">
                    <!-- Horizontal Form -->
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?php echo $this->lang->line('add_class'); ?></h3>
                        </div><!-- /.box-header -->
                        <form id="form1" action="<?php echo site_url('classes'); ?>" method="post" accept-charset="utf-8">
                            <div class="box-body">
                                <?php
                                    if ($this->session->flashdata('msg')) {
                                        echo $this->session->flashdata('msg');
                                        $this->session->unset_userdata('msg');
                                    }
                                ?>
                                <?php
                                if (isset($error_message)) {
                                    echo "<div class='alert alert-danger'>" . $error_message . "</div>";
                                }
                                ?>
                                <?php echo $this->customlib->getCSRF(); ?>




                                <div class="form-group">
                                    <label for="degree"><?php echo $this->lang->line('degree_type'); ?></label><small class="req"> *</small>
                                    <select name="degree" id="degree" class="form-control">
                                        <option value=""><?php echo $this->lang->line('select_degree_type'); ?></option>
                                        <option value="Bachelors" <?php echo set_select('degree', 'Bachelors'); ?>>Bachelors</option>
                                        <option value="Masters" <?php echo set_select('degree', 'Masters'); ?>>Masters</option>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('degree'); ?></span>
                                </div>
                               
                                <div class="form-group">
                                    <label for="class"><?php echo $this->lang->line('class'); ?></label><small class="req"> *</small>
                                    <input type="text" name="class" id="class" class="form-control" value="<?php echo set_value('class'); ?>">
                                    <span class="text-danger"><?php echo form_error('class'); ?></span>
                                </div>




                                <div class="form-group">
                                    <label for="sections"><?php echo $this->lang->line('sections'); ?></label><small class="req"> *</small>




                                    <?php
                                    foreach ($vehiclelist as $vehicle) {
                                        ?>
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="sections[]" value="<?php echo $vehicle['id'] ?>"
                                                       class="section-checkbox" data-section-id="<?php echo $vehicle['id'] ?>"
                                                       <?php echo set_checkbox('sections[]', $vehicle['id']); ?> >
                                                <?php echo $vehicle['section'] ?>
                                            </label>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                    <span class="text-danger"><?php echo form_error('sections[]'); ?></span>
                                </div>
                                <!-- Programs section - dynamically populated -->
                                <div class="form-group" id="programs-container" style="display: none;">
                                    <label for="section_programs"><?php echo $this->lang->line('program'); ?></label><small class="req"> *</small>
                                    <div id="programs-list">
                                        <!-- Programs will be loaded here via AJAX -->
                                    </div>
                                    <input type="hidden" name="section_programs" id="section_programs" value="">
                                    <span class="text-danger" id="programs-error"><?php echo form_error('section_programs'); ?></span>
                                </div>




                            </div><!-- /.box-body -->




                            <div class="box-footer">
                                <button type="submit" class="btn btn-info pull-right"><?php echo $this->lang->line('save'); ?></button>
                            </div>
                        </form>
                    </div>




                </div><!--/.col (right) -->
                <!-- left column -->
            <?php } ?>
            <div class="col-md-<?php
            if ($this->rbac->hasPrivilege('class', 'can_add')) {
                echo "8";
            } else {
                echo "12";
            }
            ?>">
                <!-- general form elements -->
                <div class="box box-primary">
                    <div class="box-header ptbnull">
                        <h3 class="box-title titlefix"><?php echo $this->lang->line('class_list'); ?></h3>
                        <div class="box-tools pull-right">
                        </div><!-- /.box-tools -->
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <div class="table-responsive mailbox-messages overflow-visible">
                            <div class="download_label"><?php echo $this->lang->line('class_list'); ?></div>
                            <table class="table table-striped table-bordered table-hover example">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('class'); ?></th>
                                        <th><?php echo $this->lang->line('degree_type'); ?></th>
                                        <th><?php echo $this->lang->line('sections'); ?></th>
                                        <th><?php echo $this->lang->line('program'); ?></th>
                                        <th class="text-right noExport"><?php echo $this->lang->line('action'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($vehroutelist as $vehroute) {
                                        ?>
                                        <tr>
                                            <td class="mailbox-name">
                                                <?php echo $vehroute->class; ?>
                                            </td>
                                            <td class="mailbox-name">
                                                <?php echo $vehroute->degree; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $vehicles = $vehroute->vehicles;
                                                if (!empty($vehicles)) {
                                                    foreach ($vehicles as $key => $value) {
                                                        echo "<div>" . $value->section . "</div>";
                                                    }
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                $vehicles = $vehroute->vehicles;
                                                if (!empty($vehicles)) {
                                                    foreach ($vehicles as $key => $value) {
                                                        if (!empty($value->program_name)) {
                                                            echo "<div>" . $value->program_name . "</div>";
                                                        } else {
                                                            echo "<div>-</div>";
                                                        }
                                                    }
                                                }
                                                ?>
                                            </td>
                                            <td class="mailbox-date pull-right">
                                                <?php
                                                if ($this->rbac->hasPrivilege('class', 'can_edit')) {
                                                    ?>  
                                                    <a href="<?php echo base_url(); ?>classes/edit/<?php echo $vehroute->id; ?>" class="btn btn-default btn-xs"  data-toggle="tooltip" title="<?php echo $this->lang->line('edit'); ?>">
                                                        <i class="fa fa-pencil"></i>
                                                    </a>
                                                    <?php
                                                }
                                                if ($this->rbac->hasPrivilege('class', 'can_delete')) {
                                                    ?>  
                                                    <a href="<?php echo base_url(); ?>classes/delete/<?php echo $vehroute->id; ?>"class="btn btn-default btn-xs"  data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>" onclick="return confirm('<?php echo $this->lang->line('deleting_class'); ?>');">
                                                        <i class="fa fa-remove"></i>
                                                    </a>
                                                <?php } ?>
                                            </td>
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
$(document).ready(function() {
    // Handle section checkbox changes
    $('.section-checkbox').change(function() {
        var selectedSections = [];
       
        // Get all checked sections
        $('.section-checkbox:checked').each(function() {
            selectedSections.push($(this).val());
        });
       
        console.log('Selected sections:', selectedSections);
       
        // Show/hide programs container based on selection
        if (selectedSections.length > 0) {
            $('#programs-container').show();
            loadPrograms(selectedSections);
        } else {
            $('#programs-container').hide();
            $('#programs-list').empty();
            $('#section_programs').val('');
        }
    });
   
    // Function to load programs via AJAX
    function loadPrograms(sectionIds) {
        console.log('Loading programs for sections:', sectionIds);
        
        $.ajax({
            url: '<?php echo base_url("classes/get_programs_by_sections"); ?>',
            type: 'POST',
            data: {
                section_ids: sectionIds,
                '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'
            },
            dataType: 'json',
            beforeSend: function() {
                $('#programs-list').html('<p class="text-info"><i class="fa fa-spinner fa-spin"></i> Loading programs...</p>');
            },
            success: function(response) {
                console.log('AJAX Response:', response);
                
                var programsHtml = '';
                
                if (response.success && response.data && response.data.length > 0) {
                    // Group programs by section for better display
                    var programsBySection = {};
                   
                    $.each(response.data, function(index, program) {
                        var sectionId = program.section_id;
                        var sectionName = program.section_name || 'Section ' + sectionId;
                       
                        if (!programsBySection[sectionId]) {
                            programsBySection[sectionId] = {
                                name: sectionName,
                                programs: []
                            };
                        }
                        programsBySection[sectionId].programs.push(program);
                    });
                   
                    console.log('Programs grouped by section:', programsBySection);
                   
                    // Generate HTML grouped by sections
                    $.each(programsBySection, function(sectionId, sectionData) {
                        programsHtml += '<div class="section-programs-group" style="margin-bottom: 15px;">';
                        programsHtml += '<h5 style="color: #337ab7; margin-bottom: 8px;">' + sectionData.name + ':</h5>';
                       
                        $.each(sectionData.programs, function(index, program) {
                            programsHtml += '<div class="checkbox" style="margin-left: 20px;">';
                            programsHtml += '<label>';
                            programsHtml += '<input type="checkbox" name="program_ids[]" value="' + program.id + '" class="program-checkbox" data-section-id="' + sectionId + '">';
                            programsHtml += program.title;
                            if (program.code) {
                                programsHtml += ' (' + program.code + ')';
                            }
                            programsHtml += '</label>';
                            programsHtml += '</div>';
                        });
                       
                        programsHtml += '</div>';
                    });
                } else if (response.success) {
                    // No programs found but request was successful
                    programsHtml = '<div class="alert alert-info">No programs available for selected sections. Please contact administrator to add programs for these sections.</div>';
                } else {
                    // Error in response
                    programsHtml = '<div class="alert alert-danger">' + (response.error || 'Error loading programs. Please try again.') + '</div>';
                }
               
                $('#programs-list').html(programsHtml);
               
                // Add change handler for program checkboxes
                $('.program-checkbox').change(function() {
                    updateSectionPrograms();
                });
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText);
                var errorMsg = 'Error loading programs. Please try again.';
                
                if (xhr.responseText) {
                    try {
                        var errorResponse = JSON.parse(xhr.responseText);
                        if (errorResponse.error) {
                            errorMsg = errorResponse.error;
                        }
                    } catch (e) {
                        console.error('Could not parse error response');
                    }
                }
                
                $('#programs-list').html('<div class="alert alert-danger">' + errorMsg + '</div>');
            }
        });
    }
   
    // Function to update the hidden field with section:programs data
    function updateSectionPrograms() {
        var sectionPrograms = {};
       
        // Group programs by section
        $('.program-checkbox:checked').each(function() {
            var sectionId = $(this).data('section-id');
            var programId = $(this).val();
           
            // Validate section_id
            if (!sectionId || sectionId === 'undefined') {
                console.warn('Invalid section ID for program:', programId);
                return; // Skip this program
            }
           
            if (!sectionPrograms[sectionId]) {
                sectionPrograms[sectionId] = [];
            }
           
            sectionPrograms[sectionId].push(programId);
        });
       
        console.log('Section programs:', sectionPrograms);
       
        // Convert to format: section_id:program_id1,program_id2|section_id2:program_id3
        var sectionProgramString = '';
        $.each(sectionPrograms, function(sectionId, programs) {
            if (sectionProgramString !== '') {
                sectionProgramString += '|';
            }
            sectionProgramString += sectionId + ':' + programs.join(',');
        });
       
        $('#section_programs').val(sectionProgramString);
        console.log('Section programs string:', sectionProgramString);
    }
   
    // Form validation before submit
    $('#form1').submit(function(e) {
        var selectedSections = $('.section-checkbox:checked').length;
        var selectedPrograms = $('.program-checkbox:checked').length;
       
        console.log('Form submit - Sections:', selectedSections, 'Programs:', selectedPrograms);
       
        if (selectedSections > 0 && selectedPrograms === 0) {
            e.preventDefault();
            $('#programs-error').text('Please select at least one program.');
            alert('Please select at least one program for the selected sections.');
            return false;
        }
       
        $('#programs-error').text('');
       
        // Ensure section_programs is updated before submission
        updateSectionPrograms();
       
        // Get final values for validation
        var finalSectionPrograms = $('#section_programs').val();
        console.log('Final section programs:', finalSectionPrograms);
       
        // Validate the final string format
        if (selectedSections > 0 && (!finalSectionPrograms || finalSectionPrograms.indexOf('undefined') !== -1)) {
            e.preventDefault();
            alert('Error: Invalid section-program data. Please reload the page and try again.');
            return false;
        }
       
        return true;
    });
});
</script>
