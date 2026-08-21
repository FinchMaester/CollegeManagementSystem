<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-bus"></i> <?php //echo $this->lang->line('transport'); ?></h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                    </div>
                    <form role="form" action="<?php echo site_url('admin/route/studenttransportdetails') ?>" method="post" class="">
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
                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('route_list'); ?></label>
                                    <select onchange="get_pickup_point(this.value)" class="form-control" id="transport_route_id" name="transport_route_id">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php
foreach ($vehroutelist as $vehroute) {
    ?>
                                                        <option value="<?php echo $vehroute['id'] ?>" <?php echo set_select('transport_route_id', $vehroute['id']); ?>>
                                                        <?php echo $vehroute['route_title']; ?>
                                                        </option>
                                                        <?php
}

?>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('transport_fees'); ?></span>
                                </div>
                            </div>
                             <div class="col-sm-3 col-md-3">
                                <div class="form-group">
                                    <label for="pickup_point_id"><?php echo $this->lang->line('pickup_point'); ?></label>
                                    <select class="form-control" id="pickup_point_id" name="pickup_point_id">
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    </select>
                                </div>
                            </div>
                               <div class="col-sm-3 col-md-3">
                                <div class="form-group">
                                    <label for="vehicle_id"><?php echo $this->lang->line('vehicle'); ?></label>
                                    <select class="form-control" id="vehicle_id" name="vehicle_id">
                                           <option value=""><?php echo $this->lang->line('select'); ?></option>
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
                            <h3 class="box-title titlefix"><i class="fa fa-users"></i> <?php echo form_error('student'); ?> <?php echo $this->lang->line('student_transport_report'); ?></h3>
                        </div>
                        <div class="box-body table-responsive overflow-visible">
                            <div class="download_label"><?php echo $this->lang->line('student_transport_report') . " " .
$this->customlib->get_postmessage();
?></div>
                            <table class="table table-striped table-bordered table-hover example">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('class'); ?></th>
                                        <th><?php echo $this->lang->line('admission_no'); ?></th>
                                        <th><?php echo $this->lang->line('student_name'); ?></th>
                                        <th><?php echo $this->lang->line('mobile_number'); ?></th>
                                        <th><?php echo $this->lang->line('father_name'); ?></th>
                                        <th><?php echo $this->lang->line('route_title'); ?></th>
                                        <th><?php echo $this->lang->line('vehicle_number'); ?></th>
                                        <th><?php echo $this->lang->line('pickup_point'); ?></th>
                                        <th><?php echo $this->lang->line('driver_name'); ?></th>
                                        <th><?php echo $this->lang->line('driver_contact'); ?></th>
                                        <th class="text-right" width="8%"><?php echo $this->lang->line('fare') . " (" . $currency_symbol . ")"; ?></th>
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
        ?>
                                            <tr>
                                                <td><?php echo $student['class'] . " - " . $student["section"]; ?></td>
                                                <td><?php echo $student['admission_no']; ?></td>
                                                <td>
                                                    <a href="<?php echo base_url(); ?>student/view/<?php echo $student['id']; ?>"><?php echo $this->customlib->getFullName($student['firstname'], $student['middlename'], $student['lastname'], $sch_setting->middlename, $sch_setting->lastname); ?>
                                                    </a>
                                                </td>
                                                <td><?php echo $student['mobileno']; ?></td>
                                                <td><?php echo $student['father_name']; ?></td>
                                                <td><?php echo $student['route_title']; ?></td>
                                                <td><?php echo $student['vehicle_no']; ?></td>
                                                <td><?php echo $student['pickup_name']; ?></td>
                                                <td><?php echo $student['driver_name']; ?></td>
                                                <td><?php echo $student['driver_contact']; ?></td>
                                                <td class="text-right"><?php echo amountFormat($student['fees']); ?></td>
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
                </div><!--./box box-primary-->
            </div><!--./col-md-12-->
        </div>
    </div>
</section>
</div>

<script type="text/javascript">

    $(document).ready(function () {
        var class_id = $('#class_id').val();
        var section_id = '<?php echo set_value('section_id', 0) ?>';
        var pickup_point_id = '<?php echo set_value('pickup_point_id', 0) ?>';
        var vehicle_id = '<?php echo set_value('vehicle_id', 0) ?>';
        get_pickup_point($('#transport_route_id').val(),pickup_point_id,vehicle_id);
    });


    function get_pickup_point(transport_route_id,pickup_point_id,vehicle_id){
           if (transport_route_id != "") {
        $('#pickup_point_id').html('');
        $('#vehicle_id').html('');
         var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
         var vehicle_div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
        $.ajax({
                url: '<?php echo base_url(); ?>admin/pickuppoint/getpickuppointsbyroute/',
                type: "POST",
                data:{transport_route_id:transport_route_id},
                dataType: 'json',
                 beforeSend: function() {
                       $('#pickup_point_id').html('');
                       $('#vehicle_id').html('');
                },
                success: function(res) {

                    $.each(res.vehicle_route_pickups, function (index, value) {
                         let sel = "";
                        if (pickup_point_id == value.pickup_point_id) {
                            sel = "selected";
                        }

                        div_data += "<option value=" + value.pickup_point_id + " " + sel + ">" + value.pickup_point + "</option>";
                    });

                    $('#pickup_point_id').html(div_data);
                      $.each(res.routes_vehicle, function (index, value) {
                         let sel = "";
                        if (vehicle_id == value.id) {
                            sel = "selected";
                        }
                        vehicle_div_data += "<option value=" + value.id  + " " + sel + ">" +  value.vehicle_no + "</option>";
                    });

                    $('#vehicle_id').html(vehicle_div_data);

                },
                   error: function(xhr) { // if error occured
                   alert("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");
            },
            complete: function() {

            }
            });
    }
    }

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