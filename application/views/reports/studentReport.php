<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-line-chart"></i> <?php //echo $this->lang->line('reports'); ?> <small> <?php //echo $this->lang->line('filter_by_name1'); ?></small></h1>
    </section>
    <!-- Main content -->
    <section class="content" >
        <?php $this->load->view('reports/_studentinformation');?>
        <div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-users"></i> <?php echo $this->lang->line('total_students'); ?></h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="info-box">
                            <span class="info-box-icon bg-aqua"><i class="fa fa-users"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text"><?php echo $this->lang->line('total_students'); ?></span>
                                <span class="info-box-number"><?php echo $totalStudents; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
        <div class="row">
            <div class="col-md-12">
                <div class="box removeboxmius">
                    <div class="box-header ptbnull"></div>
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                    </div>
                    <div class="box-body">
                        <form role="form" action="<?php echo site_url('report/studentreportvalidation') ?>" method="post" class="" id="reportform">
                            <div class="row">
                                <?php echo $this->customlib->getCSRF(); ?>
                                <div class="col-sm-6 col-md-3">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('section'); ?></label>
                                        <select id="section_id" name="section_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('program'); ?></label>
                                        <select id="program_id" name="program_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('program_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('class'); ?></label>
                                        <select id="class_id" name="class_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger" id="error_class_id"></span>
                                    </div>
                                </div>
                                <?php if ($sch_setting->category) {
    ?>
                                    <div class="col-sm-3 col-md-2">
                                        <div class="form-group">
                                            <label><?php echo $this->lang->line('category'); ?></label>
                                            <select  id="category_id" name="category_id" class="form-control" >
                                                <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                <?php
foreach ($categorylist as $category) {
        ?>
                                                    <option value="<?php echo $category['id'] ?>" <?php if (set_value('category_id') == $category['id']) {
            echo "selected=selected";
        }
        ?>><?php echo $category['category'] ?></option>
                                                    <?php
$count++;
    }
    ?>
                                            </select>
                                        </div>
                                    </div>
                                <?php }?>
                                <div class="col-sm-3 col-md-2">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('gender'); ?></label>
                                        <select class="form-control" name="gender">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php
foreach ($genderList as $key => $value) {
    ?>
                                                <option value="<?php echo $key; ?>" <?php if (set_value('gender') == $key) {
        echo "selected";
    }
    ?>><?php echo $value; ?></option>
                                                <?php
}
?>
                                        </select>
                                    </div>
                                </div>
                                <?php if ($sch_setting->rte) {
    ?>
                                    <div class="col-sm-3 col-md-2">
                                        <div class="form-group">
                                            <label><?php echo $this->lang->line('rte'); ?></label>
                                            <select  id="rte" name="rte" class="form-control" >
                                                <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                <?php
foreach ($RTEstatusList as $k => $rte) {
        ?>
                                                    <option value="<?php echo $k; ?>" <?php if (set_value('rte') == $k) {
            echo "selected";
        }
        ?>><?php echo $rte; ?></option>




                                                    <?php
$count++;
    }
    ?>
                                            </select>
                                        </div>
                                    </div>
                                <?php }?>




                                <div class="col-sm-3 col-md-2">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('ethnicity'); ?></label>
                                        <select id="ethnicity_id" name="ethnicity_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php
                                            foreach ($ethnicitylist as $ethnicity) {
                                                ?>
                                                <option value="<?php echo $ethnicity['id'] ?>" <?php if (set_value('ethnicity_id') == $ethnicity['id']) {
                                                    echo "selected=selected";
                                                }
                                                ?>><?php echo $ethnicity['name'] ?></option>
                                                <?php
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('province'); ?> <small class="text-muted">(HEMIS)</small></label>
                                        <select id="province_id" name="province_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php
                                            foreach ($provincelist as $province) {
                                                $pname = isset($province['province']) ? $province['province'] : (isset($province['name']) ? $province['name'] : '');
                                                if ($pname === '') {
                                                    continue;
                                                }
                                                ?>
                                                <option value="<?php echo htmlspecialchars($pname, ENT_QUOTES, 'UTF-8'); ?>" <?php if (set_value('province_id') == $pname) {
                                                    echo "selected=selected";
                                                }
                                                ?>><?php echo htmlspecialchars($pname, ENT_QUOTES, 'UTF-8'); ?></option>
                                                <?php
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>




                                <div class="col-sm-6 col-md-3">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('district'); ?></label>
                                        <select id="district_id" name="district_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                    </div>
                                </div>




                                <div class="col-sm-6 col-md-3">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('localLevel'); ?></label>
                                        <select id="municipality_id" name="municipality_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                    </div>
                                </div>




                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <span class="text-danger" id="error_filters"></span>
                                        <button type="submit" name="search" value="search_filter" class="btn btn-primary btn-sm checkbox-toggle pull-right"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                                    </div>
                                </div>
                            </div><!--./row-->
                        </form>
                    </div><!--./box-body-->
                        <div class="">
                            <div class="box-header ptbnull"></div>
                            <div class="box-header ptbnull">
                                <h3 class="box-title titlefix"><i class="fa fa-users"></i> <?php echo form_error('student'); ?> <?php echo $this->lang->line('student_report'); ?></h3>
                            </div>
                            <div class="box-body table-responsive">
                                    <div class="download_label"> <?php echo $this->lang->line('student_report'); ?></div>
                            <div >
                                <table class="table table-striped table-bordered table-hover student-list" data-export-title="<?php echo $this->lang->line('student_report'); ?>">
                                    <thead>
                                        <tr>
                                            <th><?php echo $this->lang->line('section'); ?></th>
                                            <th><?php echo $this->lang->line('admission_no'); ?></th>
                                            <th><?php echo $this->lang->line('student_name'); ?></th>
                                            <?php if ($sch_setting->father_name) {?>
                                                <th><?php echo $this->lang->line('father_name'); ?></th>
                                                <?php }?>
                                            <th><?php echo $this->lang->line('date_of_birth'); ?></th>
                                            <th><?php echo $this->lang->line('gender'); ?></th>
                                            <?php if ($sch_setting->category) {?>
                                                <th><?php echo $this->lang->line('category'); ?></th>
                                            <?php }if ($sch_setting->mobile_no) {?>
                                                <th><?php echo $this->lang->line('mobile_number'); ?></th>
                                            <?php
}
if ($sch_setting->local_identification_no) {
    ?>
                                                <th><?php echo $this->lang->line('local_identification_number'); ?></th>
                                            <?php }if ($sch_setting->national_identification_no) {?>
                                                <th><?php echo $this->lang->line('national_identification_number'); ?></th>
                                            <?php }if ($sch_setting->rte) {?>
                                                <th><?php echo $this->lang->line('rte'); ?></th>
                                            <?php }?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div><!--./box box-primary -->
            </div><!-- ./col-md-12 -->
        </div>
</div>
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




    $(document).on('change', '#province_id', function (e) {
        var province_id = $(this).val();
        $('#district_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
        $('#municipality_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
        getDistrictsByProvince(province_id, '');
    });

    $(document).on('change', '#district_id', function (e) {
        var district_id = $(this).val();
        $('#municipality_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
        getMunicipalitiesByDistrict(district_id, '');
    });

    function getDistrictsByProvince(province_id, district_id) {
        if (province_id != "") {
            var base_url = '<?php echo base_url() ?>';
            var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
            $.ajax({
                type: "POST",
                url: base_url + "report/getDistrictsByProvince",
                data: {'province_id': province_id},
                dataType: "json",
                success: function (data) {
                    $.each(data, function (i, obj) {
                        var sel = "";
                        if (district_id == obj.id) {
                            sel = "selected";
                        }
                        div_data += '<option value="' + $('<div/>').text(obj.id).html() + '" ' + sel + '>' + obj.name + '</option>';
                    });
                    $('#district_id').html(div_data);
                }
            });
        }
    }

    function getMunicipalitiesByDistrict(district_id, municipality_id) {
        var province_id = $('#province_id').val();
        if (district_id != "" && province_id != "") {
            var base_url = '<?php echo base_url() ?>';
            var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
            $.ajax({
                type: "POST",
                url: base_url + "report/getMunicipalitiesByDistrict",
                data: {
                    'province_id': province_id,
                    'district_id': district_id
                },
                dataType: "json",
                success: function (data) {
                    $.each(data, function (i, obj) {
                        var sel = "";
                        if (municipality_id == obj.id) {
                            sel = "selected";
                        }
                        div_data += '<option value="' + $('<div/>').text(obj.id).html() + '" ' + sel + '>' + obj.name + '</option>';
                    });
                    $('#municipality_id').html(div_data);
                }
            });
        }
    }
});




   
   
</script>




<script>
$(document).ready(function() {
    emptyDatatable('student-list','data');
});
</script>




<script type="text/javascript">
$(document).ready(function(){
$(document).on('submit','#reportform',function(e){
    e.preventDefault(); // avoid to execute the actual submit of the form.
    var $this = $(this).find("button[type=submit]:focus");
    var form = $(this);
    var url = form.attr('action');
    var form_data = form.serializeArray();
    form_data.push({name: 'search_type', value: $this.attr('value')});




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
                        if (value) {
                            $('#error_' + key).html(value);
                        }
                    });
                }else{




                   initDatatable('student-list','report/dtstudentreportlist',response.params,[],100);
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



