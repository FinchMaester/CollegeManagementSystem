<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<style type="text/css">
    /*REQUIRED*/
    .carousel-row {
        margin-bottom: 10px;
    }
    .slide-row {
        padding: 0;
        background-color: #ffffff;
        min-height: 150px;
        border: 1px solid #e7e7e7;
        overflow: hidden;
        height: auto;
        position: relative;
    }
    .slide-carousel {
        width: 20%;
        float: left;
        display: inline-block;
    }
    .slide-carousel .carousel-indicators {
        margin-bottom: 0;
        bottom: 0;
        background: rgba(0, 0, 0, .5);
    }
    .slide-carousel .carousel-indicators li {
        border-radius: 0;
        width: 20px;
        height: 6px;
    }
    .slide-carousel .carousel-indicators .active {
        margin: 1px;
    }
    .slide-content {
        position: absolute;
        top: 0;
        left: 20%;
        display: block;
        float: left;
        width: 80%;
        max-height: 76%;
        padding: 1.5% 2% 2% 2%;
        overflow-y: auto;
    }
    .slide-content h4 {
        margin-bottom: 3px;
        margin-top: 0;
    }
    .slide-footer {
        position: absolute;
        bottom: 0;
        left: 20%;
        width: 78%;
        height: 20%;
        margin: 1%;
    }
    /* Scrollbars */
    .slide-content::-webkit-scrollbar {
        width: 5px;
    }
    .slide-content::-webkit-scrollbar-thumb:vertical {
        margin: 5px;
        background-color: #999;
        -webkit-border-radius: 5px;
    }
    .slide-content::-webkit-scrollbar-button:start:decrement,
    .slide-content::-webkit-scrollbar-button:end:increment {
        height: 5px;
        display: block;
    }
</style>

<div class="content-wrapper" style="min-height: 946px;">
    <section class="content-header">
        <h1><i class="fa fa-building-o"></i> <?php //echo $this->lang->line('hostel'); ?></h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                    </div>
                    <form role="form" action="<?php echo site_url('admin/hostelroom/searchvalidation') ?>" method="post" class="" id="class_search_form" >
                        <div class="box-body row">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('section'); ?></label><small class="req"> *</small>
                                    <select id="section_id" name="section_id" class="form-control">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('program'); ?></label><small class="req"> *</small>
                                    <select id="program_id" name="program_id" class="form-control">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('program_id'); ?></span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('class'); ?></label> <small class="req"> *</small>
                                    <select id="class_id" name="class_id" class="form-control">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    </select>
                                    <span class="text-danger" id="error_class_id"></span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('hostel_name'); ?></label>

                                    <select class="form-control" name="hostel_name">
                                        <option value=""><?php echo $this->lang->line("select") ?></option>
                                        <?php foreach ($hostellist as $key => $value) {
    ?>
                                            <option value="<?php echo $value["hostel_name"]; ?>"><?php echo $value["hostel_name"]; ?></option>
                                        <?php }?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <button type="submit" name="search" value="search_filter" class="btn btn-primary btn-sm checkbox-toggle pull-right"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="">
                        <div class="box-header ptbnull"></div>
                        <div class="box-header ptbnull">
                            <h3 class="box-title titlefix"><i class="fa fa-users"></i> <?php echo form_error('student'); ?> <?php echo $this->lang->line('student_hostel_report'); ?></h3>
                        </div>
                        <div class="box-body table-responsive">
                            <div class="download_label"><?php echo $this->lang->line('student_hostel_report') . "\n";
$this->customlib->get_postmessage();
?></div>
                                    <table class="table table-striped table-bordered table-hover hostel-list" data-export-title="<?php echo $this->lang->line('student_hostel_report') . "\n";
$this->customlib->get_postmessage();
?>" data-target="parent-list">
                                    <thead>
                                        <tr>
                                            <th><?php echo $this->lang->line('class_section'); ?></th>
                                            <th><?php echo $this->lang->line('admission_no'); ?></th>
                                            <th><?php echo $this->lang->line('student_name'); ?></th>
                                            <th><?php echo $this->lang->line('mobile_number'); ?></th>
                                            <th><?php echo $this->lang->line('guardian_phone'); ?></th>
                                            <th><?php echo $this->lang->line('hostel_name'); ?></th>
                                            <th><?php echo $this->lang->line('room_number_name'); ?></th>
                                            <th><?php echo $this->lang->line('room_type'); ?></th>
                                            <th class="text-right"><?php echo $this->lang->line('cost_per_bed') . " (" . $currency_symbol . ")"; ?></th>
                                        </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div><!--./box box-primary-->
            </div><!--./col-md-12-->
        </div>
    </div>
</section>
</div>

<script type="text/javascript">
$(document).ready(function () {
    // Store selected values
    var selectedSection = '<?php echo set_value("section_id") ?>';
    var selectedProgram = '<?php echo set_value("program_id") ?>';
    var selectedClass = '<?php echo set_value("class_id") ?>';
    
    // Fetch all sections on page load
    loadSections();
    
    function loadSections() {
        $.ajax({
            url: '<?php echo base_url(); ?>sections/getAll',
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                
                $.each(response, function (index, section) {
                    var selected = (selectedSection == section.id) ? 'selected' : '';
                    html += '<option value="' + section.id + '" ' + selected + '>' + section.section + '</option>';
                });
                
                $('#section_id').html(html);
                
                // If there's a pre-selected section, load programs
                if(selectedSection) {
                    loadPrograms(selectedSection);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error loading sections:", error);
            }
        });
    }
    
    function loadPrograms(sectionId) {
        if (!sectionId) return;
        
        $('#program_id').html('<option value="">Loading...</option>');
        $('#class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
        
        $.ajax({
            url: '<?php echo base_url(); ?>programs/getBySection', 
            method: 'POST',
            data: {
                section_id: sectionId,
                <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'
            },
            dataType: 'json',
            success: function (response) {
                var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                
                if(response && response.length > 0) {
                    $.each(response, function (index, program) {
                        var selected = (selectedProgram == program.id) ? 'selected' : '';
                        html += '<option value="' + program.id + '" ' + selected + '>' + program.title + '</option>';
                    });
                } else {
                    html += '<option value="" disabled>No programs found</option>';
                }
                
                $('#program_id').html(html);
                
                // If there's a pre-selected program, load classes
                if(selectedProgram) {
                    loadClasses(sectionId, selectedProgram);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error fetching programs:", error);
                $('#program_id').html('<option value="">Error loading programs</option>');
            }
        });
    }
    
    function loadClasses(sectionId, programId) {
        if (!sectionId || !programId) return;
        
        $('#class_id').html('<option value="">Loading...</option>');
        
        $.ajax({
            url: '<?php echo base_url(); ?>classes/getBySectionAndProgram',
            method: 'POST',
            data: {
                section_id: sectionId,
                program_id: programId,
                <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'
            },
            dataType: 'json',
            success: function(response) {
                var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                
                if (response && response.length > 0) {
                    $.each(response, function(index, classItem) {
                        var selected = (selectedClass == classItem.id) ? 'selected' : '';
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
    
    // Event handlers
    $('#section_id').on('change', function() {
        var sectionId = $(this).val();
        selectedSection = sectionId;
        selectedProgram = '';
        selectedClass = '';
        
        if (sectionId) {
            loadPrograms(sectionId);
        } else {
            $('#program_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
            $('#class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
        }
    });
    
    $('#program_id').on('change', function() {
        var programId = $(this).val();
        var sectionId = $('#section_id').val();
        selectedProgram = programId;
        selectedClass = '';
        
        if (sectionId && programId) {
            loadClasses(sectionId, programId);
        } else {
            $('#class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
        }
    });
});
</script>

<script>
$(document).ready(function() {
     emptyDatatable('hostel-list','data');
});
</script>

<script type="text/javascript">
$(document).ready(function(){
$(document).on('submit','#class_search_form',function(e){
   e.preventDefault(); // avoid to execute the actual submit of the form.
    var $this = $(this).find("button[type=submit]:focus");

    var form = $(this);
    var url = form.attr('action');
    var form_data = form.serializeArray();
    $.ajax({
           url: url,
           type: "POST",
           dataType:'JSON',
           data: form_data, // serializes the form's elements.
              beforeSend: function () {
                $('[id^=error]').html("");
                $this.button('loading');
               },
              success: function(response) { // your success handler

                if(!response.status){
                    $.each(response.error, function(key, value) {
                    $('#error_' + key).html(value);
                    });
                }else{
                   initDatatable('hostel-list','admin/hostelroom/dthostellist',response.params);
                }
              },
             error: function() { // your error handler
                 $this.button('reset');
             },
             complete: function() {
             $this.button('reset');
             }
         });
        });
    });
</script>