<style type="text/css">
    /*.table .pull-right {text-align: initial; width: auto}*/
    .chart canvas {}​
    canvas#1_1001{fill: #000 !important}
</style>
<?php
$language = $this->customlib->getLanguage();
$language_name = $language["short_code"];
?>
<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/Chart.js/1.0.2/Chart.min.js"></script>


<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
    </section>
    <section class="content">
        <?php $this->load->view('reports/_lesson_plan'); ?>
        <div class="box removeboxmius">
            <div class="box-header ptbnull">
                <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
            </div>
            <form  class="assign_teacher_form" action="<?php echo base_url(); ?>report/lesson_plan/" method="post" enctype="multipart/form-data">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-12">
                            <?php if ($this->session->flashdata('msg')) { ?>
                                <?php
                                    echo $this->session->flashdata('msg');
                                    $this->session->unset_userdata('msg');
                                ?>
                            <?php } ?>
                            <?php echo $this->customlib->getCSRF(); ?>
                        </div>
                        <div class="col-md-3 col-lg-3 col-sm-6">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('faculty'); ?></label><small class="req"> *</small>
                                <select id="section_id" name="section_id" class="form-control" >
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                </select>
                                <span class="class_id_error text-danger"><?php echo form_error('section_id'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3 col-lg-3 col-sm-6">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('program'); ?></label><small class="req"> *</small>
                                <select id="program_id" name="program_id" class="form-control" >
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                </select>
                                <span class="class_id_error text-danger"><?php echo form_error('program_id'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3 col-lg-3 col-sm-6">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('academic_level'); ?></label><small class="req"> *</small>
                                <select id="class_id" name="class_id" class="form-control" >
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                </select>
                                <span class="class_id_error text-danger"><?php echo form_error('class_id'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3 col-lg-3 col-sm-6">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('subject_group'); ?></label><small class="req"> *</small>
                                <select id="subject_group_id" name="subject_group_id" class="form-control" >
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                </select>
                                <span class="class_id_error text-danger"><?php echo form_error('subject_group_id'); ?></span>
                            </div>
                        </div>
                    </div>
                    <button type="submit" id="search_filter" name="search" value="search_filter" class="btn btn-primary btn-sm checkbox-toggle pull-right"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                </div>
            </form>
            <?php if (!empty($subjects_data)) {
                ?>


                <div class="row">
                    <div class="col-md-12">
                        <div class="box-header ptbnull"></div>
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-users"></i> <?php echo $this->lang->line('syllabus_status_report'); ?></h3>
                            <?php if ($this->rbac->hasPrivilege('homework', 'can_add')) { ?>
                                <div class="box-tools pull-right"></div>
                            <?php } ?>
                        </div>
                        <div class="box-body">
                            <div class="download_label"> <?php echo $this->lang->line('syllabus_status_report'); ?></div>
                            <div class="row">
                                <div class="text-center">
                                    <?php foreach ($subjects_data as $key => $value) {
                                        ?>
                                        <div class="col-md-2 col-xs-6 systatus">
                                            <b><?php echo $value['lebel'] ?></b>
                                            <div class="chart ptt10">
                                                <canvas id="<?php echo $value['id'] ?>" class="" style="width: 100%; height: 100%;"></canvas>
                                            </div>
                                             <span class="label lbcolor"><?php echo $this->lang->line('complete') . " " . $value['complete'] . " %"; ?></span>  
                                        </div>
                                    <?php }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!--./row-->


                <div class="box-body">
                    <div class="table-responsive" id="transfee">
                        <div class="hide" id="visible"><center><b><?php echo $this->lang->line('syllabus_status_report'); ?></b></center></div>
                        <a class="btn btn-default btn-xs pull-right mr0-3" id="print" onclick="printDiv()" ><i class="fa fa-print"></i></a> <a class="btn btn-default btn-xs pull-right" id="btnExport" onclick="fnExcelReport();"> <i class="fa fa-file-excel-o"></i></a>
                        <table class="table table-bordered topicstaus" id="headerTable">
                            <tr id="noprint"><td class="pull-right"><i><?php echo $this->lang->line('note') . " : " . $this->lang->line('subject_percentage_based_on_topic'); ?></i></td></tr>
                            <tr class="hide" id="visible">
                                <td colspan="2"><?php echo $this->lang->line('syllabus_status_report'); ?></td>
                            </tr>
                            <tr>
                                <th width="100%"><?php echo $this->lang->line('subject_lesson_topic'); ?>
                                    <span class="pull-right"><?php echo $this->lang->line('status'); ?></span></th>
                            </tr>
                            <?php
                            $s = 1;
                            foreach ($subjects_data as $key => $value) {
                                ?>
                                <tr>
                                    <td>
                                        <h4><?php echo $value['lebel']; ?><span><?php echo $value['complete']; ?>% <?php echo $this->lang->line('complete'); ?></span></h4>
                                        <ul class="topicstaus"><li>
                                                <?php
                                                if ($value['total'] > 0) {
                                                    $l = 1;
                                                    foreach ($value['lesson_summary'] as $teachers_summarykey => $teachers_summaryvalue) {
                                                        ?>
                                                        <ul class="topicstaus">
                                                            <li><h4><?php echo $l; ?> &nbsp;&nbsp;&nbsp; <?php echo $teachers_summaryvalue['name'] ?> <span><?php
                                                                        if ($teachers_summaryvalue['complete_percent'] == 0 && $teachers_summaryvalue['incomplete_percent'] == 0) {
                                                                            echo $this->lang->line('no_status');
                                                                        } else {
                                                                            echo $teachers_summaryvalue['complete_percent'];
                                                                            ?>% <?php echo $this->lang->line('complete');
                                                    }
                                                                        ?></span></h4>
                                                                <ul class="topicstaus">
                                                                    <?php
                                                                    $t = 1;
                                                                    foreach ($teachers_summaryvalue['topics'] as $topicskey => $topicsvalue) {
                                                                        ?>
                                                                        <li><h5><?php echo $l . "." . $t; ?> &nbsp;&nbsp;&nbsp; <?php echo $topicsvalue['name']; ?><i><?php
                                                                                    echo $status[$topicsvalue['status']];
                                                                                    if ($topicsvalue['status'] == 1) {
                                                                                        echo " (" . date($this->customlib->getSchoolDateFormat(), strtotime($topicsvalue['complete_date'])) . ")";
                                                                                    }
                                                                                    ?></i></h5></li>
                    <?php $t++;
                }
                ?>
                                                                </ul>
                                                            </li>
                                                        </ul>
                <?php
                $l++;
            }
        }
        ?>
                                            </li>
                                        </ul>
                                    </td>
                                </tr>
                    <?php $s++;
                }
                ?>


                        </table>
                    </div><!--./table-responsive-->  
                </div>
    <?php
} else {
    ?>
                <div class="active table-responsive box-body" >
                    <div class="download_label"> <?php echo $this->lang->line('syllabus_status_report'); ?></div>
                    <table class="table table-striped table-bordered table-hover example" cellspacing="0" >
                        <thead>
                            <tr>
                                <th ><?php echo $this->lang->line('subject_lesson_topic'); ?>
                                </th>
                                <th>  <?php echo $this->lang->line('status'); ?></th>  
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
<?php }
?>


        </div>
    </section>
</div>


<script>
<?php foreach ($subjects_data as $key => $value) { ?>
        Chart.types.Doughnut.extend({
            name: "DoughnutTextInside",
            showTooltip: function () {
                this.chart.ctx.save();
                Chart.types.Doughnut.prototype.showTooltip.apply(this, arguments);
                this.chart.ctx.restore();
            },
            draw: function () {
                Chart.types.Doughnut.prototype.draw.apply(this, arguments);


                var width = this.chart.width,
                        height = this.chart.height;


                var fontSize = (height / 190).toFixed(2);
                this.chart.ctx.font = fontSize + "em Verdana";
                this.chart.ctx.textBaseline = "middle";


                var text = "",
                        textX = Math.round((width - this.chart.ctx.measureText(text).width) / 2),
                        textY = height / 2;


                this.chart.ctx.fillText(text, textX, textY);
            }
        });
    <?php
    $complete_percent = $value['complete'];
    $incomplete_percent = $value['incomplete'];
    if ($complete_percent == 0 && $incomplete_percent == 0) {
        $complete_percent = 0;
        $incomplete_percent = 100;
    }
    ?>


        var data = [{
                lebel: 'complete',
                value: <?php echo $complete_percent ?>,
                color: "#4CAF50"
            }, {
                value: <?php echo $incomplete_percent ?>,
                color: "#cfcfcf"
            }
        ];


        var DoughnutTextInsideChart = new Chart($('#<?php echo $value['id'] ?>')[0].getContext('2d')).DoughnutTextInside(data, {
            responsive: true
        });
<?php } ?>
</script>


<script>
    $(document).ready(function () {
        $(document).on('click', '.chk', function () {
            var checked = $(this).is(':checked');
            var rowid = $(this).data('rowid');
            var role = $(this).data('role');
            if (checked) {
                if (!confirm("<?php echo $this->lang->line('are_you_sure_you_active_account'); ?>")) {
                    $(this).removeAttr('checked');
                } else {
                    var status = "yes";
                }
            } else if (!confirm("<?php echo $this->lang->line('are_you_sure_you_deactive_account'); ?>")) {
                $(this).prop("checked", true);
            } else {
                var status = "no";
            }
        });
    });
</script>


<script>
    $(document).ready(function (e) {
        var section_id = '<?php echo set_value('section_id') ?>';
        var program_id = '<?php echo set_value('program_id') ?>';
        var class_id = '<?php echo set_value('class_id') ?>';
        var subject_group_id = '<?php echo set_value('subject_group_id') ?>';


        // Fetch all sections (faculty)
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
                if(section_id) {
                    getProgramsBySection(section_id, program_id);
                }
            },
            error: function(xhr, status, error) {
                $('#section_id').html('<option value="">Error loading sections</option>');
            }
        });


        if (class_id && section_id && program_id) {
            getClassesByProgram(program_id, class_id);
        }


        if (class_id) {
            getSubjectGroupsByClass(class_id, subject_group_id);
        }


        $(document).on('change', '#section_id', function (e) {
            $('#program_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
            $('#class_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
            $('#subject_group_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
            var section_id = $(this).val();
            if (section_id) {
                getProgramsBySection(section_id);
            }
        });


        $(document).on('change', '#program_id', function (e) {
            $('#class_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
            $('#subject_group_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
            var program_id = $(this).val();
            if (program_id) {
                getClassesByProgram(program_id);
            }
        });


        $(document).on('change', '#class_id', function (e) {
            $('#subject_group_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
            var class_id = $(this).val();
            if (class_id) {
                getSubjectGroupsByClass(class_id);
            }
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
                        if (selected_class_id) {
                            getSubjectGroupsByClass(selected_class_id, subject_group_id);
                        }
                    },
                    error: function() {
                        $('#class_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
                    }
                });
            }
        }


        function getSubjectGroupsByClass(class_id, selected_subject_group_id = null) {
            var section_id = $('#section_id').val();
            if (class_id != "" && section_id != "") {
                var base_url = '<?php echo base_url() ?>';
                var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
                $.ajax({
                    type: 'POST',
                    url: base_url + 'admin/subjectgroup/getGroupByClassandSection',
                    data: {'class_id': class_id, 'section_id': section_id},
                    dataType: 'JSON',
                    success: function (data) {
                        $.each(data, function (i, obj) {
                            var sel = "";
                            if (selected_subject_group_id == obj.subject_group_id) {
                                sel = "selected";
                            }
                            div_data += "<option value=" + obj.subject_group_id + " " + sel + ">" + obj.name + "</option>";
                        });
                        $('#subject_group_id').html(div_data);
                    },
                    error: function () {
                        $('#subject_group_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
                    }
                });
            }
        }
    });
</script>


<script>


    document.getElementById("print").style.display = "block";
    document.getElementById("btnExport").style.display = "block";


    function printDiv() {


        document.getElementById("noprint").style.display = "none";


        $("#visible").removeClass("hide");
        $(".download_label").addClass("hide");


        document.getElementById("print").style.display = "none";
        document.getElementById("btnExport").style.display = "none";
        var divElements = document.getElementById('transfee').innerHTML;
        var oldPage = document.body.innerHTML;
        document.body.innerHTML =
                "<html><head><title></title></head><body>" +
                divElements + "</body>";
        window.print();
        document.body.innerHTML = oldPage;


        location.reload(true);
    }


    function fnExcelReport()
    {
        var tab_text = "<table border='2px'><tr >";
        var textRange;
        var j = 0;
        tab = document.getElementById('headerTable'); // id of table


        for (j = 0; j < tab.rows.length; j++)
        {
            tab_text = tab_text + tab.rows[j].innerHTML + "</tr>";            
        }
        $("#visible").removeClass("hide");


        tab_text = tab_text + "</table>";
        tab_text = tab_text.replace(/<A[^>]*>|<\/A>/g, "");//remove if u want links in your table
        tab_text = tab_text.replace(/<img[^>]*>/gi, ""); // remove if u want images in your table
        tab_text = tab_text.replace(/<input[^>]*>|<\/input>/gi, ""); // reomves input params


        var ua = window.navigator.userAgent;
        var msie = ua.indexOf("MSIE ");
        $("#visible").addClass("hide");
        if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./))      // If Internet Explorer
        {
            txtArea1.document.open("txt/html", "replace");
            txtArea1.document.write(tab_text);
            txtArea1.document.close();
            txtArea1.focus();
            sa = txtArea1.document.execCommand("SaveAs", true, "Say Thanks to Sumit.xls");
        } else                 //other browser not tested on IE 11
            sa = window.open('data:application/vnd.ms-excel,' + encodeURIComponent(tab_text));


        return (sa);
    }
</script>

