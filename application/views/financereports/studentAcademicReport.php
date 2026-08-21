<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
 
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-money"></i> <?php //echo $this->lang->line('fees_collection'); ?> <small> <?php //echo $this->lang->line('filter_by_name1'); ?></small></h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <?php $this->load->view('financereports/_finance'); ?>
        <div class="row">
            <div class="col-md-12">
                <div class="box removeboxmius">
                    <div class="box-header ptbnull"></div>
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                    </div>
                    <form action="<?php echo site_url('financereports/studentacademicreport') ?>"  method="post" accept-charset="utf-8">
                        <div class="box-body">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="section_id"><?php echo $this->lang->line('faculty'); ?></label>
                                        <select autofocus id="section_id" name="section_id" class="form-control" >
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="program_id"><?php echo $this->lang->line('program'); ?></label>
                                        <select id="program_id" name="program_id" class="form-control" >
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('program_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="class_id"><?php echo $this->lang->line('academic_level'); ?></label>
                                        <select id="class_id" name="class_id" class="form-control" >
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('class_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="search_type"><?php echo $this->lang->line('search_type'); ?></label>
                                        <select  id="search_type" name="search_type" class="form-control" >
                                            <?php
                                            foreach ($payment_type as $payment_key => $payment_value) {
                                            ?>
                                             <option value="<?php echo $payment_key; ?>" <?php echo set_select('search_type', $payment_key, set_value('search_type')); ?>><?php echo $payment_value; ?></option>
                                            <?php
                                            }
                                             ?>                                        
                                       </select>
                                        <span class="text-danger"><?php echo form_error('search_type'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">


                            <button type="submit" class="btn btn-primary btn-sm pull-right"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>   </div>
                    </form>




                    <div class="row">


                        <?php
                        if (isset($student_due_fee) && !empty($student_due_fee)) {
                            ?>


                            <div class="" id="transfee">
                                <div class="box-header ptbnull">
                                    <h3 class="box-title titlefix"><i class="fa fa-users"></i> <?php echo $this->lang->line('balance_fees_report'); ?></h3>
                                </div>                              
                                <div class="box-body table-responsive">
                                    <div class="download_label"><?php
                            echo $this->lang->line('balance_fees_report') . "<br>";
                            $this->customlib->get_postmessage();
                            ?></div>
                                    <a class="btn btn-default btn-xs pull-right" id="print" onclick="printDiv()" ><i class="fa fa-print"></i></a> <button class="btn btn-default btn-xs pull-right" id="btnExport" onclick="fnExcelReport();"> <i class="fa fa-file-excel-o"></i> </button>  
                                    <table class="table table-striped table-hover" id="headerTable">
                                        <thead>
                                            <tr>
                                               
                                                <th class="text text-left"><?php echo $this->lang->line('student_name'); ?></th>
                                                <th class="text text-left"><?php echo $this->lang->line('class'); ?></th>


                                                <th class="text text-left"><?php echo $this->lang->line('admission_no'); ?></th>
                                                <?php if ($sch_setting->roll_no) { ?>
                                                    <th class="text text-left"><?php echo $this->lang->line('roll_number'); ?></th>
                                                <?php } if ($sch_setting->father_name) { ?>
                                                    <th class="text text-left"><?php echo $this->lang->line('father_name'); ?></th>
                                                <?php } ?>
                                                <th class="text-right" width="9%"><?php echo $this->lang->line('total_fees'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                                <th class="text-right" width="8%"><?php echo $this->lang->line('paid_fees'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>


                                                <th class="text text-right" width="8%"><?php echo $this->lang->line('discount'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                                <th class="text text-right"><?php echo $this->lang->line('fine'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>


                                                <th class="text-right" width="8%"><?php echo $this->lang->line('balance'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                            </tr>
                                        </thead>  
                                        <tbody>
                                            <?php
                                            if (!empty($resultarray)) {
                                                        $totalfeelabel = 0;
                                                        $depositfeelabel = 0;
                                                        $discountlabel = 0;
                                                        $finelabel = 0;
                                                        $balancelabel = 0;                                      
                                                foreach ($resultarray as $key => $section) {                                                  
                                                     
                                                        foreach ($section['result'] as $students) {                                                            
                                                            $totalfeelabel += $students->totalfee;
                                                            $depositfeelabel += $students->deposit;
                                                            $discountlabel += $students->discount;
                                                            $finelabel += $students->fine;
                                                            $balancelabel += $students->balance;
                                                                    ?>                                            
                                                      <tr>
                                                            <td><?php echo $students->name;?></td>
                                                            <td><?php echo $students->class." (".$students->section.")";?></td>
                                                            <td><?php echo $students->admission_no;?></td>
                                                            <td><?php echo $students->roll_no;?></td>
                                                            <td><?php echo $students->father_name;?></td>


                                                            <td class="text-right"><?php echo amountFormat($students->totalfee);?></td>


                                                            <td class="text-right"><?php echo amountFormat($students->deposit);?></td>


                                                            <td class="text-right"><?php echo amountFormat($students->discount);?></td>


                                                            <td class="text-right"><?php echo amountFormat($students->fine);?></td>


                                                            <td class="text-right"><?php echo amountFormat($students->balance);?></td>
                                                            </tr>
                                                                <?php
                                                        }
                                                            ?>
                                                               <tr class="box box-solid total-bg">
                                                               
                                                                <td></td>
                                                                <td></td>


                                                                <?php if ($sch_setting->roll_no) { ?>
                                                                    <td></td>
                                                                    <?php }


                                                                     if ($sch_setting->father_name) {
                                                                      ?>
                                                                                                                    <td></td>
                                                                    <?php
                                                                     }
                                                                ?>
                                                                <td><?php echo $this->lang->line('total'); ?></td>
                                                                <td class="text-right"><?php echo amountFormat($totalfeelabel);  ?></td>
                                                                <td class="text-right"><?php echo amountFormat($depositfeelabel); ?></td>
                                                                <td class="text-right"><?php echo amountFormat($discountlabel); ?></td>
                                                                <td class="text-right"><?php echo amountFormat($finelabel); ?></td>
                                                                <td class="text-right"><?php echo amountFormat($balancelabel); ?></td>
                                                            </tr>
                                                        <?php                                
                                                          } ?>
                                            </tbody>
                                        </table>
                                    </div>                            
                                </div>                
                            </div>


                            <?php
                        }
                    }
?>






                </div>
            </div>
    </section>
</div>


<script type="text/javascript">
    function removeElement() {
        document.getElementById("imgbox1").style.display = "block";
    }
    $(document).ready(function () {
        var section_id = '<?php echo set_value('section_id') ?>';
        var program_id = '<?php echo set_value('program_id') ?>';
        var class_id = '<?php echo set_value('class_id') ?>';


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


        $(document).on('change', '#section_id', function (e) {
            $('#program_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
            $('#class_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
            var section_id = $(this).val();
            if (section_id) {
                getProgramsBySection(section_id);
            }
        });


        $(document).on('change', '#program_id', function (e) {
            $('#class_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
            var program_id = $(this).val();
            if (program_id) {
                getClassesByProgram(program_id);
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
                    },
                    error: function() {
                        $('#class_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
                    }
                });
            }
        }
    });
    $(document).ready(function () {
        $("ul.type_dropdown input[type=checkbox]").each(function () {
            $(this).change(function () {
                var line = "";
                $("ul.type_dropdown input[type=checkbox]").each(function () {
                    if ($(this).is(":checked")) {
                        line += $("+ span", this).text() + ";";
                    }
                });
                $("input.form-control").val(line);
            });
        });
    });
    $(document).ready(function () {
        $.extend($.fn.dataTable.defaults, {
            ordering: false,
            paging: false,
            bSort: false,
            info: false
        });
    });
</script>
<script>


    document.getElementById("print").style.display = "block";
    document.getElementById("btnExport").style.display = "block";


    function printDiv() {
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
            //tab_text=tab_text+"</tr>";
        }


        tab_text = tab_text + "</table>";
        tab_text = tab_text.replace(/<A[^>]*>|<\/A>/g, "");//remove if u want links in your table
        tab_text = tab_text.replace(/<img[^>]*>/gi, ""); // remove if u want images in your table
        tab_text = tab_text.replace(/<input[^>]*>|<\/input>/gi, ""); // reomves input params


        var ua = window.navigator.userAgent;
        var msie = ua.indexOf("MSIE ");


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

