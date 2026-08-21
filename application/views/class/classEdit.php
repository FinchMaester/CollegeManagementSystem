<?php $currency_symbol = $this->customlib->getSchoolCurrencyFormat(); ?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

    <section class="content-header">
        <h1>
            <i class="fa fa-mortar-board"></i> <?php echo $this->lang->line('academics'); ?>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-4">
                <!-- Horizontal Form -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $this->lang->line('edit_class'); ?></h3>
                    </div><!-- /.box-header -->
                    <form id="form1" action="<?php echo site_url('classes/edit/' . $id) ?>" method="post" accept-charset="utf-8">
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
                            <input type="hidden" name="pre_class_id" value="<?php echo $vehroute[0]->id; ?>">

                            <div class="form-group">
                                <label for="degree"><?php echo $this->lang->line('degree_type'); ?></label><small class="req"> *</small>
                                <select name="degree" id="degree" class="form-control">
                                    <option value=""><?php echo $this->lang->line('select_degree_type'); ?></option>
                                    <option value="Bachelors" <?php if ($vehroute[0]->degree == 'Bachelors') echo "selected"; ?>>Bachelors</option>
                                    <option value="Masters" <?php if ($vehroute[0]->degree == 'Masters') echo "selected"; ?>>Masters</option>
                                </select>
                                <span class="text-danger"><?php echo form_error('degree'); ?></span>
                            </div>

                            <div class="form-group">
                                <label for="class"><?php echo $this->lang->line('class'); ?></label><small class="req"> *</small>
                                <input type="text" name="class" id="class" class="form-control" value="<?php echo $vehroute[0]->class; ?>">
                                <span class="text-danger"><?php echo form_error('class'); ?></span>
                            </div>

                            <div class="form-group">
                                <label for="sections"><?php echo $this->lang->line('sections'); ?></label><small class="req"> *</small>
                                <?php
                                $selectedSections = array();
                                // Get selected sections from class_sections table
                                if (!empty($vehroute[0]->vehicles)) {
                                    foreach ($vehroute[0]->vehicles as $vehicle) {
                                        $selectedSections[] = $vehicle->section_id;
                                    }
                                }
                                foreach ($vehiclelist as $vehicle) {
                                ?>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="sections[]" value="<?php echo $vehicle['id'] ?>"
                                                class="section-checkbox" data-section-id="<?php echo $vehicle['id'] ?>" <?php if (in_array($vehicle['id'], $selectedSections)) echo "checked"; ?>>
                                            <?php echo $vehicle['section'] ?>
                                        </label>
                                    </div>
                                <?php } ?>
                                <span class="text-danger"><?php echo form_error('sections[]'); ?></span>
                            </div>

                            <div class="form-group" id="programs-container" style="<?php echo (!empty($selectedSections)) ? 'display: block;' : 'display: none;'; ?>">
                                <label for="section_programs"><?php echo $this->lang->line('program'); ?></label><small class="req"> *</small>
                                <div id="programs-list">
                                    <!-- Programs will be dynamically populated -->
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
        </div>
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

<script type="text/javascript">
$(document).ready(function() {
    // Store existing section-program relationships for pre-checking
    var existingSectionPrograms = {};
    
    // Get existing section-program relationships from PHP
    <?php if (!empty($existing_section_programs)): ?>
        <?php foreach ($existing_section_programs as $sp): ?>
            if (!existingSectionPrograms[<?php echo $sp->section_id; ?>]) {
                existingSectionPrograms[<?php echo $sp->section_id; ?>] = [];
            }
            existingSectionPrograms[<?php echo $sp->section_id; ?>].push('<?php echo $sp->program_id; ?>');
        <?php endforeach; ?>
    <?php endif; ?>
    
    // Debug: Log existing section programs
    console.log('Existing section programs:', existingSectionPrograms);

    $('.section-checkbox').change(function() {
        var selectedSections = [];
        $('.section-checkbox:checked').each(function() {
            selectedSections.push($(this).val());
        });

        if (selectedSections.length > 0) {
            $('#programs-container').show();
            loadPrograms(selectedSections);
        } else {
            $('#programs-container').hide();
            $('#programs-list').empty();
            $('#section_programs').val('');
        }
    });

    function loadPrograms(sectionIds) {
        $.ajax({
            url: '<?php echo base_url("classes/get_programs_by_sections"); ?>',
            type: 'POST',
            data: {
                section_ids: sectionIds,
                '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'
            },
            dataType: 'json',
            beforeSend: function() {
                $('#programs-list').html('<p class="text-info">Loading programs...</p>');
            },
            success: function(response) {
                console.log('AJAX Response:', response);
                
                var programsHtml = '';
                var programsBySection = {};

                // Handle response - check if it's an array or object with data property
                var programs = [];
                if (response.success && Array.isArray(response.data)) {
                    programs = response.data;
                } else if (Array.isArray(response)) {
                    programs = response;
                } else if (response.error) {
                    $('#programs-list').html('<p class="text-danger">' + response.error + '</p>');
                    return;
                }

                // If no programs found, show message
                if (programs.length === 0) {
                    $('#programs-list').html('<p class="text-warning">No programs found for selected sections.</p>');
                    return;
                }

                // Group programs by section
                $.each(programs, function(index, program) {
                    var sectionId = program.section_id;
                    if (!programsBySection[sectionId]) {
                        programsBySection[sectionId] = [];
                    }
                    programsBySection[sectionId].push(program);
                });

                // Generate HTML for each section's programs
                $.each(programsBySection, function(sectionId, sectionPrograms) {
                    // Get section name from the first program or use default
                    var sectionName = sectionPrograms[0].section_name || 'Section ' + sectionId;
                    
                    programsHtml += '<div class="section-programs-group" style="margin-bottom: 15px;">';
                    programsHtml += '<h5 style="color: #337ab7; margin-bottom: 8px;">' + sectionName + ':</h5>';

                    $.each(sectionPrograms, function(index, program) {
                        var programValue = sectionId + '-' + program.id;
                        var isChecked = '';

                        // Check if this program was previously selected for this section
                        if (existingSectionPrograms[sectionId] && 
                            existingSectionPrograms[sectionId].indexOf(program.id.toString()) !== -1) {
                            isChecked = 'checked';
                            console.log('Checking program:', program.id, 'for section:', sectionId);
                        }

                        programsHtml += '<div class="checkbox" style="margin-left: 20px;">';
                        programsHtml += '<label>';
                        programsHtml += '<input type="checkbox" name="program_ids[]" value="' + programValue + '" class="program-checkbox" data-section-id="' + sectionId + '" data-program-id="' + program.id + '" ' + isChecked + '>';
                        programsHtml += program.title + (program.code ? ' (' + program.code + ')' : '');
                        programsHtml += '</label>';
                        programsHtml += '</div>';
                    });

                    programsHtml += '</div>';
                });

                $('#programs-list').html(programsHtml);
                
                // Attach change event to program checkboxes
                $('.program-checkbox').change(function() {
                    updateSectionPrograms();
                });
                
                // Initial update of section programs
                updateSectionPrograms();
            },
            error: function(xhr, status, error) {
                $('#programs-list').html('<p class="text-danger">Error loading programs. Please try again.</p>');
                console.error('AJAX Error:', error);
                console.error('Response:', xhr.responseText);
            }
        });
    }

    function updateSectionPrograms() {
        var sectionPrograms = {};
        $('.program-checkbox:checked').each(function() {
            var sectionId = $(this).data('section-id');
            var programId = $(this).data('program-id');
            if (!sectionPrograms[sectionId]) {
                sectionPrograms[sectionId] = [];
            }
            sectionPrograms[sectionId].push(programId);
        });

        var sectionProgramString = '';
        $.each(sectionPrograms, function(sectionId, programs) {
            if (sectionProgramString !== '') {
                sectionProgramString += '|';
            }
            sectionProgramString += sectionId + ':' + programs.join(',');
        });

        $('#section_programs').val(sectionProgramString);
        console.log('Updated section_programs value:', sectionProgramString);
    }

    // Form submission validation
    $('#form1').submit(function(e) {
        var selectedSections = $('.section-checkbox:checked').length;
        var selectedPrograms = $('.program-checkbox:checked').length;

        if (selectedSections > 0 && selectedPrograms === 0) {
            e.preventDefault();
            $('#programs-error').text('Please select at least one program.');
            alert('Please select at least one program for the selected sections.');
            return false;
        }

        $('#programs-error').text('');
        updateSectionPrograms();

        var finalSectionPrograms = $('#section_programs').val();
        if (selectedSections > 0 && (!finalSectionPrograms || finalSectionPrograms.indexOf('undefined') !== -1)) {
            e.preventDefault();
            alert('Error: Invalid section-program data. Please reload the page and try again.');
            return false;
        }

        return true;
    });

    // Load programs on page load if sections are already selected
    if ($('.section-checkbox:checked').length > 0) {
        var selectedSections = [];
        $('.section-checkbox:checked').each(function() {
            selectedSections.push($(this).val());
        });
        console.log('Loading programs for pre-selected sections:', selectedSections);
        loadPrograms(selectedSections);
    }
});
</script>