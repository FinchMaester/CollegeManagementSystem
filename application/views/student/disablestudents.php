<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-user-plus"></i> <?php echo $this->lang->line('student_information'); ?> <small><?php echo $this->lang->line('student1'); ?></small></h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                    </div>
                    <div class="box-body">
                        <?php if ($this->session->flashdata('msg')) {
    ?> <div class="alert alert-success">  <?php echo $this->session->flashdata('msg');
    $this->session->unset_userdata('msg'); ?> </div> <?php }?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="row">
                                    <form id="disablestudents-filter-form" role="form" action="<?php echo site_url('student/disablestudents') ?>" method="post" class="">
                                        <?php echo $this->customlib->getCSRF(); ?>
                                        <input type="hidden" name="search" value="search_filter">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label><?php echo $this->lang->line('section'); ?></label><small class="req"> *</small>
                                                <select id="section_id" name="section_id" class="form-control">
                                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                </select>
                                                <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label for="exampleInputEmail1"><?php echo $this->lang->line('program'); ?></label><small class="req"> *</small>
                                                <select id="program_id" name="program_id" class="form-control">
                                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                </select>
                                                <span class="text-danger"><?php echo form_error('program_id'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
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
                                                <button type="submit" name="search" value="search_filter" class="btn btn-primary btn-sm pull-right checkbox-toggle"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                                            </div>
                                        </div>
                                    </form>
                                </div><!--./row-->
                            </div><!--./col-md-6-->
                            <div class="col-md-6">
                                <div class="row">
                                    <form id="disablestudents-keyword-form" role="form" action="<?php echo site_url('student/disablestudents') ?>" method="post" class="">
                                        <?php echo $this->customlib->getCSRF(); ?>
                                        <input type="hidden" name="search" value="search_full">
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <label><?php echo $this->lang->line('search_by_keyword'); ?></label>
                                                <input type="text" name="search_text" class="form-control" placeholder="<?php echo $this->lang->line('search_by_student_name'); ?>">
                                            </div>
                                        </div>
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <button type="submit" name="search" value="search_full" class="btn btn-primary pull-right btn-sm checkbox-toggle"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="box-header ptbnull"></div>
                    <div id="disablestudents-results">
                        <?php if (isset($resultlist) && (is_array($resultlist) || $resultlist instanceof Countable)) { ?>
                            <?php $this->load->view('student/disablestudents_results', array('resultlist' => $resultlist, 'disable_reason' => isset($disable_reason) ? $disable_reason : array(), 'sch_setting' => $sch_setting)); ?>
                        <?php } else { ?>
                            <p class="text-muted pad10">Use the search above to load the disabled students list.</p>
                        <?php } ?>
                    </div>
                </div><!--./box box-primary-->
            </div>
        </div>
    </section>
</div>
<script type="text/javascript">
$(document).ready(function () {
    // Store the selected values from PHP
    var selected_section_id = '<?php echo set_value("section_id") ?>';
    var selected_program_id = '<?php echo set_value("program_id") ?>';
    var selected_class_id = '<?php echo set_value("class_id") ?>';
    
    console.log("Selected values:", {
        section: selected_section_id,
        program: selected_program_id,
        class: selected_class_id
    });
    
    // Fetch all sections
    $.ajax({
        url: '<?php echo base_url(); ?>sections/getAll',
        method: 'GET',
        dataType: 'json',
        success: function (response) {
            var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
            
            $.each(response, function (index, section) {
                var selected = (selected_section_id == section.id) ? 'selected' : '';
                html += '<option value="' + section.id + '" ' + selected + '>' + section.section + '</option>';
            });
            
            $('#section_id').html(html);
            
            // If there's a pre-selected section, load programs
            if(selected_section_id) {
                loadPrograms(selected_section_id, selected_program_id, selected_class_id);
            }
        }
    });
});

// Function to load programs
function loadPrograms(section_id, preselected_program_id, preselected_class_id) {
    if (section_id != '') {
        $('#program_id').html('<option value="">Loading...</option>');
        
        $.ajax({
            url: '<?php echo base_url(); ?>programs/getBySection', 
            method: 'POST',
            data: {section_id: section_id},
            dataType: 'json',
            success: function (response) {
                var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                
                if(response.length > 0) {
                    $.each(response, function (index, program) {
                        var selected = (preselected_program_id == program.id) ? 'selected' : '';
                        html += '<option value="' + program.id + '" ' + selected + '>' + program.title + '</option>';
                    });
                } else {
                    html += '<option value="" disabled>No programs found</option>';
                }
                
                $('#program_id').html(html);
                
                // If there's a pre-selected program, load classes
                if(preselected_program_id) {
                    loadClasses(section_id, preselected_program_id, preselected_class_id);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error fetching programs:", error);
                $('#program_id').html('<option value="">Error loading programs</option>');
            }
        });
    }
}

// Function to load classes
function loadClasses(section_id, program_id, preselected_class_id) {
    if (section_id && program_id) {
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
                var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                
                if (response && response.length > 0) {
                    $.each(response, function(index, classItem) {
                        var selected = (preselected_class_id == classItem.id) ? 'selected' : '';
                        html += '<option value="' + classItem.id + '" ' + selected + '>' + 
                               (classItem.degree ? classItem.degree + ' - ' : '') + 
                               classItem.class + '</option>';
                    });
                } else {
                    html += '<option value="" disabled>No classes found for this combination</option>';
                }
                
                $('#class_id').html(html);
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", status, error);
                $('#class_id').html('<option value="">Error loading classes</option>');
            }
        });
    }
}

// When section changes manually, fetch programs
$(document).on('change', '#section_id', function (e) {
    var section_id = $(this).val();
    
    // Clear program and class dropdowns
    $('#program_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
    $('#class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
    
    if (section_id != '') {
        loadPrograms(section_id, '', '');
    }
});

// When program changes manually, fetch classes
$(document).on('change', '#program_id', function (e) {
    var section_id = $('#section_id').val();
    var program_id = $(this).val();
    
    $('#class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
    
    if (section_id && program_id) {
        loadClasses(section_id, program_id, '');
    }
});

// Popover functionality (also run after AJAX load)
function initDisablestudentsPopovers() {
    $('#disablestudents-results .detail_popover').popover('destroy');
    $('#disablestudents-results .detail_popover').popover({
        placement: 'right',
        title: '',
        trigger: 'hover',
        container: 'body',
        html: true,
        content: function () {
            return $(this).closest('td').find('.fee_detail_popover').html();
        }
    });
}
$(document).ready(function () {
    initDisablestudentsPopovers();
});

// Search forms: submit via AJAX and show results in the same page table (no navigation)
var disablestudentsAjaxUrl = '<?php echo site_url("student/disablestudents_results_ajax"); ?>';
var csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>';
var csrfHash = '<?php echo $this->security->get_csrf_hash(); ?>';

function doDisablestudentsSearch($form) {
    var postData = $form.serialize();
    if (postData.indexOf(csrfName) === -1) {
        postData += '&' + csrfName + '=' + encodeURIComponent(csrfHash);
    }
    $('#disablestudents-results').html('<div class="pad10 text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</div>');
    $.ajax({
        url: disablestudentsAjaxUrl,
        type: 'POST',
        data: postData,
        dataType: 'html',
        success: function (html) {
            if (typeof html !== 'string' || html.trim() === '') {
                $('#disablestudents-results').html('<div class="alert alert-warning pad10">No content returned. Please try again.</div>');
                return;
            }
            $('#disablestudents-results').html(html);
            if ($.fn.DataTable && $('.disablestudents-table').length && !$.fn.DataTable.isDataTable('.disablestudents-table')) {
                $('.disablestudents-table').DataTable({
                    searching: true,
                    ordering: true,
                    paging: true,
                    retrieve: true,
                    destroy: true,
                    info: true
                });
            }
            initDisablestudentsPopovers();
            $('body').tooltip({ selector: '[data-toggle="tooltip"]' });
        },
        error: function (xhr) {
            var msg = 'Failed to load results. Please try again.';
            if (xhr.status === 403) msg = 'Access denied.';
            else if (xhr.status === 404) msg = 'Request URL not found.';
            $('#disablestudents-results').html('<div class="alert alert-danger pad10">' + msg + '</div>');
        }
    });
}

$(document).ready(function () {
$('#disablestudents-filter-form').on('submit', function (e) {
    e.preventDefault();
    var $form = $(this);
    var section_id = $form.find('[name="section_id"]').val();
    var class_id = $form.find('[name="class_id"]').val();
    if (!section_id || !class_id) {
        alert('Please select Section and Class.');
        return false;
    }
    doDisablestudentsSearch($form);
    return false;
});

$('#disablestudents-keyword-form').on('submit', function (e) {
    e.preventDefault();
    var $form = $(this);
    var search_text = ($form.find('[name="search_text"]').val() || '').trim();
    if (!search_text) {
        alert('Please enter a search keyword.');
        return false;
    }
    doDisablestudentsSearch($form);
    return false;
});
}); // end document.ready for form handlers
</script>