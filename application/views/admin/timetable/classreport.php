<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            <i class="fa fa-mortar-board"></i> <?php //echo $this->lang->line('academics'); ?> <small><?php //echo $this->lang->line('student_fees1'); ?></small></h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                        <?php if($this->rbac->hasPrivilege('class_timetable', 'can_edit'))  { ?>
                        <div class="box-tools pull-right">
                            <a href="<?php echo site_url('admin/timetable/create') ?>" type="button"  class="btn btn-sm btn-primary" autocomplete="off"><i class="fa fa-plus"></i> <?php echo $this->lang->line('add'); ?></a>
                        </div>
                        <?php } ?>
                    </div>
                    <form action="<?php echo site_url('admin/timetable/classreport') ?>" method="post" accept-charset="utf-8">
                        <div class="box-body">


                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="section_id"><?php echo $this->lang->line('faculty'); ?></label><small class="req"> *</small>
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
                                        <label for="class_id"><?php echo $this->lang->line('academic_level'); ?></label><small class="req"> *</small>
                                        <select id="class_id" name="class_id" class="form-control" >
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('class_id'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary pull-right btn-sm" name="search"><?php echo $this->lang->line('search'); ?></button>
                        </div>
                    </form>


                    <?php
                    if (isset($timetable)) {
                        ?>
                        <div class="box-header ptbnull"></div>
                        <div class="box-body">
                            <?php
                            if (!empty($timetable)) {
                                ?>


                                <div class="table-responsive">    
                                    <table class="table table-stripped">
                                        <thead>
                                            <tr>
                                                <?php
                                                foreach ($timetable as $tm_key => $tm_value) {
                                                    ?>
                                                    <th class="text"><?php echo $this->lang->line(strtolower($tm_key)); ?></th>
                                                    <?php
                                                }
                                                ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <?php
                                                foreach ($timetable as $tm_key => $tm_value) {
                                                    ?>
                                                    <td class="text" width="14%">


                                                        <?php
                                                        if (!$timetable[$tm_key]) {
                                                            ?>
                                                            <div class="attachment-block block-b-noraml clearfix">
                                                                <b class="text text-danger"><i class="fa fa-times-circle text-danger"></i> <?php echo $this->lang->line('not_scheduled'); ?></b><br>
                                                            </div>
                                                            <?php
                                                        } else {
                                                            foreach ($timetable[$tm_key] as $tm_k => $tm_kue) {
                                                                ?>
                                                                <div class="attachment-block attachment-block-normal clearfix">


                                                                    <div class="relative attachment-left-space"><i class="fa fa-book"></i> <?php echo $this->lang->line('subject') ?>: <?php
                                                                        echo $tm_kue->subject_name;
                                                                        if ($tm_kue->code != '') {
                                                                            echo " (" . $tm_kue->code . ")";
                                                                        }
                                                                        ?>


                                                                    </div>


                                                                    <div class="relative attachment-left-space"><i class="fa fa-clock-o"></i> <?php echo $tm_kue->time_from ?>
                                                                    <b class="text text-center">-</b>
                                                                    <strong class=""><?php echo $tm_kue->time_to; ?></strong>
                                                                    </div>
                                                                    <div class="relative attachment-left-space"><i class="fa fa-building"></i> <?php echo $this->lang->line('room_no'); ?>: <?php echo $tm_kue->room_no; ?></div>


                                                                </div>
                                                                <?php
                                                            }
                                                        }
                                                        ?>
                                                    </td>
                                                    <?php
                                                }
                                                ?>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>  
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
    $(document).on('focus', '.time', function () {
        var $this = $(this);
        $this.datetimepicker({
            format: 'LT'
        });
    });
    var tot_count = 0;
    var section_id = '<?php echo set_value('section_id') ?>';
    var program_id = '<?php echo set_value('program_id') ?>';
    var class_id = '<?php echo set_value('class_id') ?>';
    var subject_group_id = '<?php echo set_value('subject_group_id') ?>';
   
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
                    var selected = (section_id == section.id) ? 'selected' : '';
                    html += '<option value="' + section.id + '" ' + selected + '>' + section.section + '</option>';
                });
               
                $('#section_id').html(html);
               
                // If there's a pre-selected section, trigger program load
                if(section_id) {
            getProgramsBySection(section_id, program_id);
        }
            },
            error: function(xhr, status, error) {
                console.error("Error fetching sections:", error);
                $('#section_id').html('<option value="">Error loading sections</option>');
            }
        });


        // Initialize dropdowns with saved values (moved inside section fetch callback for correct order)
        // if (section_id) { // This is now handled after sections are fetched
        //     getProgramsBySection(section_id, program_id);
        // }
        // if (program_id) { // This is now handled after programs are fetched
        //     getClassesByProgram(program_id, class_id);
        // }
        if (class_id && section_id && subject_group_id) {
            getGroupByClassandSection(class_id, section_id, subject_group_id);
        }


        // Faculty/Section change event
        $(document).on('change', '#section_id', function (e) {
            $('#program_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
            $('#class_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
            $('#subject_group_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
           
            var section_id = $(this).val();
            if (section_id) {
                getProgramsBySection(section_id);
            }
        });


        // Program change event
        $(document).on('change', '#program_id', function (e) {
            $('#class_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
            $('#subject_group_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
           
            var program_id = $(this).val();
            if (program_id) {
                getClassesByProgram(program_id);
            }
        });


        // Class change event
        $(document).on('change', '#class_id', function (e) {
            $('#subject_group_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
            var class_id = $(this).val();
            var section_id = $('#section_id').val();
            if (class_id && section_id) {
                getGroupByClassandSection(class_id, section_id, subject_group_id);
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
                        getClassesByProgram(selected_program_id, class_id);
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
                            getGroupByClassandSection(selected_class_id, section_id, subject_group_id);
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
        if (class_id != "" && section_id != "" && subject_group_id != "") {
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


    function getGroupdata(target, target_id, ajax_data) {


        $.ajax({
            type: 'POST',
            url: base_url + "admin/timetable/getBydategroupclasssection",
            data: {'day': ajax_data.day, 'class_id': ajax_data.c, 'section_id': ajax_data.s, 'subject_group_id': ajax_data.group},
            dataType: 'json',
            beforeSend: function () {
                $(target).addClass('show');
            },
            success: function (data) {
                $(target).html(data.html);


                $('.staff', target).select2({
                    dropdownAutoWidth: true,
                    width: '100%'
                });
                $('.subject', target).select2({
                    dropdownAutoWidth: true,
                    width: '100%'
                });
                tot_count = data.total_count + 1;
            },
            error: function (xhr) { // if error occured


            },
            complete: function () {
                $(target).removeClass('show');
            }
        });
    }




    $(document).ready(function () {
        var counter = 0;


        $(document).on("click", ".addrow", function () {


            var newRow = $("<tr>");
            var cols = "";
            cols += '<td><input type="hidden" name="total_row[]" value="' + tot_count + '"><input type="hidden" name="prev_id_' + tot_count + '" value="0"><select class="form-control subject" id="subject_id_' + tot_count + '" name="subject_' + tot_count + '">' + $("#subject_dropdown").text() + '</select></td>';
            cols += '<td><select class="form-control staff" id="staff_id_' + tot_count + '" name="staff_' + tot_count + '">' + $("#staff_dropdown").text() + '</select></td>';


            cols += '<td><div class="input-group"><input type="text" name="time_from_' + tot_count + '" class="form-control time_from time" id="time_from_' + tot_count + '"  aria-invalid="false"><div class="input-group-addon"><span class="glyphicon glyphicon-dashboard"></span></div></div></td>';


            cols += '<td><div class="input-group"><input type="text" name="time_to_' + tot_count + '" class="form-control time_to time" id="time_to_' + tot_count + '"  aria-invalid="false"><div class="input-group-addon"><span class="glyphicon glyphicon-dashboard"></span></div></div></td>';


            cols += '<td><input type="text" class="form-control room_no" name="room_no_' + tot_count + '" id="room_no_' + tot_count + '"/></td>';
            cols += '<td><button type="button" class="ibtnDel btn btn-danger btn-sm btn-danger"><i class="fa fa-trash"></i></button></td>';
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
            $(this).closest("tr").remove();
            counter -= 1
        });


        $(document).on('click', '.submit_subject_group', function () {
            var form_id = $(this).closest("form").attr('id');
            var target = $('.nav-tabs .active a').attr("href"); // activated tab
            var target_id = $('.nav-tabs .active a').attr("id"); // activated tab
            var ajax_data = $('.nav-tabs .active a').data(); // activated tab


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
        ?>
        <option value="<?php echo $subject_value->id; ?>" ><?php echo $subject_value->name . " (" . $subject_value->code . ")"; ?></option>
        <?php
    }
    ?>
   


</script>

