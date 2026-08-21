<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<div class="content-wrapper">
    <section class="content-header"></section>
    <!-- Main content -->
    <section class="content">
        <?php $this->load->view('financereports/_finance');?>
        <div class="row">
            <div class="col-md-12">
                <div class="box removeboxmius">
                    <div class="box-header ptbnull"></div>
                    <div class="box-header ">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                    </div>
                    <form role="form" action="<?php echo site_url('financereports/collection_report') ?>" method="post" class="">
                        <div class="box-body row">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="col-sm-2 col-lg-2 col-md-2">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('search_duration'); ?><small class="req"> *</small></label>
                                    <select class="form-control" name="search_type" onchange="showdate(this.value)">


                                        <?php foreach ($searchlist as $key => $search) {
    ?>
                                            <option value="<?php echo $key ?>" <?php
if ((isset($search_type)) && ($search_type == $key)) {
        echo "selected";
    }
    ?>><?php echo $search ?></option>
                                                <?php }?>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('search_type'); ?></span>
                                </div>
                            </div>


                            <div class="col-sm-2 col-lg-2 col-md-2">
                                <div class="form-group">
                                    <label for="section_id"><?php echo $this->lang->line('faculty'); ?></label>
                                    <select autofocus id="section_id" name="section_id" class="form-control" >
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                                </div>
                            </div>


                            <div class="col-sm-2 col-lg-2 col-md-2">
                                <div class="form-group">
                                    <label for="program_id"><?php echo $this->lang->line('program'); ?></label>
                                    <select id="program_id" name="program_id" class="form-control" >
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('program_id'); ?></span>
                                </div>
                            </div>


                            <div class="col-sm-2 col-lg-2 col-md-2">
                                <div class="form-group">
                                    <label for="class_id"><?php echo $this->lang->line('academic_level'); ?></label>
                                    <select id="class_id" name="class_id" class="form-control" >
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('class_id'); ?></span>
                                </div>
                            </div>


                            <div class="col-sm-2 col-lg-2 col-md-2">
                               <div class="form-group">
                                            <label for="exampleInputEmail1"><?php echo $this->lang->line('fees_type'); ?></label>


                                            <select  id="feetype_id" name="feetype_id" class="form-control" >
                                                <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                <?php
foreach ($feetypeList as $feetype) {
    ?>
                                                    <option value="<?php echo $feetype['id'] ?>"<?php
if (set_value('feetype_id') == $feetype['id']) {
        echo "selected =selected";
    }
    ?>><?php echo $feetype['type'] ?></option>

                                                    <?php
}
?>
                                            </select>
                                            <span class="text-danger"><?php echo form_error('feetype_id'); ?></span>
                                        </div>
                            </div>
                            <div class="col-sm-2 col-lg-2 col-md-2">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('collect_by'); ?></label>
                                    <select class="form-control"  name="collect_by" >
                                        <option value=""><?php echo $this->lang->line('select') ?></option>
                                        <?php
foreach ($collect_by as $key => $value) {
    ?>
                                            <option value="<?php echo $key ?>" <?php
if ((isset($received_by)) && ($received_by == $key)) {
        echo "selected";
    }
    ?> ><?php echo $value ?></option>
                                                <?php }?>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('collect_by'); ?></span>
                                </div>
                            </div>
                            <div id='date_result'>
                            </div>
                            <div class="col-sm-2 col-lg-2 col-md-2">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('group_by'); ?></label>
                                    <select class="form-control" name="group" >
                                        <?php foreach ($group_by as $key => $value) {
    ?>
                                            <option value="<?php echo $key ?>" <?php
if ((isset($group_byid)) && ($group_byid == $key)) {
        echo "selected";
    }
    ?> ><?php echo $value ?></option>
                                                <?php }?>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('group'); ?></span>
                                </div>
                            </div>


                            <div class="form-group">
                                <div class="col-sm-12">
                                    <button type="submit" name="search" value="search_filter" id="search_btn" class="btn btn-primary btn-sm checkbox-toggle pull-right"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                                </div>
                            </div>
                        </div>
                    </form>
 <?php
if (empty($results)) {
    ?>
<div class="box-header ptbnull">
    <div class="alert alert-info">
       <?php echo $this->lang->line('no_record_found'); ?>
    </div>
</div>
                                        <?php
} else {
    ?>
                    <div class="">
                        <div class="box-header ptbnull"></div>
                        <div class="box-header ptbnull">
                            <h3 class="box-title titlefix"><i class="fa fa-money"></i> <?php ?> <?php echo $this->lang->line('fees_collection_report'); ?></h3>
                        </div>
                        <div class="box-body table-responsive" id="transfee">
                        <div id="printhead"><center><h5><?php ?> <?php echo $this->lang->line('fees_collection_report') . "<br>";
    $this->customlib->get_postmessage();
    ?></h5></center></div>
                            <div class="download_label"><?php ?> <?php echo $this->lang->line('fees_collection_report') . "<br>";
    $this->customlib->get_postmessage();
    ?></div>


                            <a class="btn btn-default btn-xs pull-right" id="print" onclick="printDiv()" ><i class="fa fa-print"></i></a>
                            <a class="btn btn-default btn-xs pull-right" id="btnExport" onclick="exportToExcel();"> <i class="fa fa-file-excel-o"></i> </a>


                            <table class="table table-striped table-bordered table-hover " id="headerTable">
                                <thead class="header">
                                    <tr>
                                        <th><?php echo $this->lang->line('payment_id'); ?></th>
                                        <th><?php echo $this->lang->line('date'); ?></th>
                                        <th><?php echo $this->lang->line('admission_no'); ?></th>
                                        <th><?php echo $this->lang->line('name'); ?></th>
                                        <th><?php echo $this->lang->line('class'); ?></th>
                                        <th><?php echo $this->lang->line('fee_type'); ?></th>
                                        <th><?php echo $this->lang->line('collect_by'); ?></th>
                                        <th><?php echo $this->lang->line('mode'); ?></th>
                                        <th style="mso-number-format:'\@'" class="text text-right"><?php echo $this->lang->line('paid'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                        <th style="mso-number-format:'\@'" class="text text-right"><?php echo $this->lang->line('discount'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                        <th style="mso-number-format:'\@'" class="text text-right"><?php echo $this->lang->line('fine'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                        <th style="mso-number-format:'\@'" class="text text-right"><?php echo $this->lang->line('total'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                    </tr>
                                </thead>
                                <tbody>
                                   <?php


    $count            = 1;
    $grdamountLabel   = array();
    $grddiscountLabel = array();
    $grdfineLabel     = array();
    $grdTotalLabel    = array();


    foreach ($results as $key => $value) {
        $payment_id    = array();
        $date          = array();
        $student_name  = array();
        $student_class = array();
        $fees_type     = array();
        $pay_mode      = array();
        $collection_by = array();
        $amountLabel   = array();
        $discountLabel = array();
        $fineLabel     = array();
        $TotalLabel    = array();
        $admission_no  = array();
        foreach ($value as $collect) {
            $payment_id[]   = $collect['id'] . "/" . $collect['inv_no'];
            $date[]         = date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($collect['date']));
            $student_name[] = $this->customlib->getFullName($collect['firstname'], $collect['middlename'], $collect['lastname'], $sch_setting->middlename, $sch_setting->lastname);


            $admission_no[] = $collect['admission_no'];


            $student_class[] = $collect['class'] . " (" . $collect['section'] . ")";      
         
            if ( $collect['is_system']) {
                $fees_type[]     = $this->lang->line($collect['type']);
            } else {
                $fees_type[]     =$collect['type'];
            }    
           
            $pay_mode[]      = $collect['payment_mode'];
            if (is_array($collect['received_byname'])) {
                $collection_by[] = $collect['received_byname']['name'] . " (" . $collect['received_byname']['employee_id'] . ")";
            }


            $amountLabel[]   = number_format($collect['amount'], 2, '.', '');
            $discountLabel[] = number_format($collect['amount_discount'], 2, '.', '');
            $fineLabel[]     = number_format($collect['amount_fine'], 2, '.', '');
            $t               = $collect['amount'] + $collect['amount_fine'];
            $TotalLabel[]    = number_format($t, 2, '.', '');
        }
        ?>
                                            <tr>
                                                <td>
                                                    <table width="100%"><?php foreach ($payment_id as $p_ides) {
            ?>
                                                            <tr><td style="mso-number-format:'\@'"  class="text-left-md payment_id"><?php echo $p_ides; ?></td></tr>
                                                        <?php }
        ?></table>
                                                </td>
                                                <td >
                                                    <table width="100%"><?php foreach ($date as $date_val) {
            ?>
                                                            <tr><td class="text-left-md"><?php echo $date_val; ?></td></tr>
                                                        <?php }
        ?></table>
                                                </td>
                                                <td >
                                                    <table width="100%"><?php foreach ($admission_no as $admission_no) {
            ?>
                                                            <tr><td class="text-left-md"><?php echo $admission_no; ?></td></tr>
                                                        <?php }
        ?>
                                                    </table>
                                                </td>
                                                <td >
                                                    <table width="100%"><?php foreach ($student_name as $student_name_val) {
            ?>
                                                            <tr><td class="text-left-md"><?php echo $student_name_val; ?></td></tr>
                                                        <?php }
        ?>
                                                    </table>
                                                </td>
                                                <td>
                                                    <table width="100%"><?php
foreach ($student_class as $student_class_val) {
            ?>
                                                            <tr><td class="text-left-md"><?php echo $student_class_val; ?></td></tr>
                                                        <?php }
        ?>
                                                    </table>
                                                </td>
                                                <td >
                                                    <table width="100%"><?php foreach ($fees_type as $fees_type_val) {
            ?>
                                                            <tr><td class="text-left-md"><?php echo $fees_type_val; ?></td></tr>
                                                        <?php }
        ?>
                                                    </table>
                                                </td>
                                                <td >
                                                    <table width="100%">
                                                    <?php foreach ($collection_by as $collection_by_val) {
            ?>
                                                            <tr><td class="text-left-md"><?php echo $collection_by_val; ?></td></tr>
                                                        <?php }
        ?>
                                                    </table>
                                                </td>
                                                <td >
                                                    <table width="100%"><?php foreach ($pay_mode as $pay_mode_val) {
            ?>
                                                            <tr><td class="text-left-md"><?php echo $this->lang->line(strtolower($pay_mode_val)); ?></td></tr>
                                                        <?php }
        ?>
                                                    </table>
                                                </td>
                                                <td class="text text-right">
                                                    <table width="100%"><?php foreach ($amountLabel as $amountLabel_val) {
            ?>
                                                            <tr><td><?php echo amountFormat($amountLabel_val); ?></td></tr>
                                                        <?php }
        ?>
                                                    </table>
                                                </td>
                                                <td class="text text-right">
                                                    <table width="100%"><?php foreach ($discountLabel as $discountLabel_val) {
            ?>
                                                            <tr><td><?php echo amountFormat($discountLabel_val); ?></td></tr>
                                                        <?php }
        ?>
                                                    </table>
                                                </td>
                                                <td class="text text-right">
                                                    <table width="100%">
                                                        <?php foreach ($fineLabel as $fineLabel_val) {
            ?>
                                                            <tr><td><?php echo amountFormat($fineLabel_val); ?></td></tr>
                                                        <?php }
        ?>
                                                    </table>
                                                </td>
                                                <td class="text text-right">
                                                    <table width="100%"><?php foreach ($TotalLabel as $TotalLabel_val) {
            ?>
                                                            <tr><td><?php echo amountFormat($TotalLabel_val); ?></td></tr>
                                                        <?php }
        ?>
                                                    </table>
                                                </td>
                                            </tr>
                                            <?php
$count++;
        if ($subtotal) {
            ?>
                                            <tr>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td style="font-weight:bold"><?php echo $this->lang->line('sub_total'); ?></td>
                                                <td class="text text-right" style="font-weight:bold"><?php echo amountFormat(array_sum($amountLabel)); ?></td>
                                                <td class="text text-right" style="font-weight:bold" ><?php echo amountFormat(array_sum($discountLabel)); ?></td>
                                                <td class="text text-right" style="font-weight:bold" ><?php echo amountFormat(array_sum($fineLabel)); ?></td>
                                                <td class="text text-right " style="font-weight:bold" ><?php echo amountFormat(array_sum($TotalLabel)); ?></td>
                                            </tr>
                                            <?php
}
        $grdamountLabel[]   = array_sum($amountLabel);
        $grddiscountLabel[] = array_sum($discountLabel);
        $grdfineLabel[]     = array_sum($fineLabel);
        $grdTotalLabel[]    = array_sum($TotalLabel);
    }
    ?>
                                        <tr>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td style="font-weight:bold"><?php echo $this->lang->line('grand_total'); ?></td>
                                            <td class="text text-right" style="font-weight:bold"><?php echo amountFormat(array_sum($grdamountLabel)); ?></td>
                                            <td class="text text-right" style="font-weight:bold" ><?php echo amountFormat(array_sum($grddiscountLabel)); ?></td>
                                            <td class="text text-right" style="font-weight:bold" ><?php echo amountFormat(array_sum($grdfineLabel)); ?></td>
                                            <td class="text text-right " style="font-weight:bold" ><?php echo amountFormat(array_sum($grdTotalLabel)); ?></td>
                                        </tr>


                                </tbody>
                            </table>
                        </div>
                    </div>
                     <?php
}
?>
                </div>
            </div>
        </div>
</div>
</section>
</div>
<iframe id="txtArea1" style="display:none"></iframe>


<script>


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
        getClassesByProgram(program_id, class_id, section_id);
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
            var section_id = $('#section_id').val();
            getClassesByProgram(program_id, null, section_id);
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
                        getClassesByProgram(selected_program_id, class_id, section_id);
                    }
                },
                error: function() {
                    $('#program_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
                }
            });
        }
    }


    function getClassesByProgram(program_id, selected_class_id = null, section_id = null) {
        if (program_id != "") {
            var base_url = '<?php echo base_url() ?>';
            var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
            $.ajax({
                type: "POST",
                url: base_url + "admin/timetable/getClassesByProgram",
                data: {'program_id': program_id, 'section_id': section_id},
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


<?php
if ($search_type == 'period') {
    ?>


        $(document).ready(function () {
            showdate('period');
        });


    <?php
}
?>


document.getElementById("print").style.display = "block";
document.getElementById("btnExport").style.display = "block";
document.getElementById("printhead").style.display = "none";


function printDiv() {
    document.getElementById("print").style.display = "none";
    document.getElementById("btnExport").style.display = "none";
     document.getElementById("printhead").style.display = "block";
    var divElements = document.getElementById('transfee').innerHTML;
    var oldPage = document.body.innerHTML;
    document.body.innerHTML =
            "<html><head><title><?php echo $this->lang->line('fee_collection_report'); ?></title></head><body>" +
            divElements + "</body>";
    window.print();
    document.body.innerHTML = oldPage;
    document.getElementById("printhead").style.display = "none";
    location.reload(true);
}


function fnExcelReport(){
    exportToExcel();
}


function exportToExcel(){
var htmls = "";
            var uri = 'data:application/vnd.ms-excel;base64,';
            var template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>';
            var base64 = function(s) {
                return window.btoa(unescape(encodeURIComponent(s)))
            };


            var format = function(s, c) {
                return s.replace(/{(\w+)}/g, function(m, p) {
                    return c[p];
                })
            };
        var tab_text = "<tr >";
                     var textRange;
         var j = 0;
          var val="";
         tab = document.getElementById('headerTable'); // id of table


         for (j = 0; j < tab.rows.length; j++)
         {
             tab_text = tab_text + tab.rows[j].innerHTML + "</tr>";
       }


            var ctx = {
                worksheet : 'Worksheet',
                table : tab_text
            }


            var link = document.createElement("a");
            link.download = "studentfee_collection_report.xls";
            link.href = uri + base64(format(template, ctx));
            link.click();
}


</script>

