<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<div class="content-wrapper">
    <section class="content-header">
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
                    <form role="form" action="<?php echo site_url('financereports/onlinefees_report') ?>" method="post" class="">
                        <div class="box-body row">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="col-sm-6 col-md-3" >
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('search_type'); ?><small class="req"> *</small></label>
                                    <select class="form-control" name="search_type" onchange="showdate(this.value)">


                                        <?php foreach ($searchlist as $key => $search) {
                                            ?>
                                            <option value="<?php echo $key ?>" <?php
                                            if ((isset($search_type)) && ($search_type == $key)) {
                                                echo "selected";
                                            }
                                            ?>><?php echo $search ?></option>
                                                <?php } ?>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('search_type'); ?></span>
                                </div>
                            </div>
                            <div id='date_result'>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label for="section_id"><?php echo $this->lang->line('faculty'); ?></label>
                                    <select autofocus id="section_id" name="section_id" class="form-control" >
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label for="program_id"><?php echo $this->lang->line('program'); ?></label>
                                    <select id="program_id" name="program_id" class="form-control" >
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('program_id'); ?></span>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label for="class_id"><?php echo $this->lang->line('academic_level'); ?></label>
                                    <select id="class_id" name="class_id" class="form-control" >
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('class_id'); ?></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <button type="submit" name="search" value="search_filter" class="btn btn-primary btn-sm checkbox-toggle pull-right"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                                </div>
                            </div>
                        </div>
                    </form>
<?php
                                    $grd_total = 0;
                                    $allamount = 0;
                                    $alldiscount = 0;
                                    $finetotal = 0;
                                    $alltotal = 0;
                                    if (empty($collectlist)) { ?>
                                    <br/>
                                    <div class="box-header ptbnull">
                                        <div class="alert alert-info"><?php echo $this->lang->line('no_record_found'); ?></div>
                                    </div>
                                 <?php    } else { ?>
                    <div class="">
                        <div class="box-header ptbnull"></div>
                        <div class="box-header ptbnull">
                            <h3 class="box-title titlefix"><i class="fa fa-money"></i> <?php ?> <?php echo $this->lang->line('online_fees_report'); ?></h3>
                        </div>
                        <div class="box-body table-responsive">
                            <div class="download_label"><?php echo $this->lang->line('online_fees_report');
                                                $this->customlib->get_postmessage();
                                                ?></div>
                            <table class="table table-striped table-bordered table-hover example">
                                <thead class="header">
                                    <tr>
                                        <th><?php echo $this->lang->line('payment_id'); ?></th>
                                        <th><?php echo $this->lang->line('date'); ?></th>
                                        <th><?php echo $this->lang->line('admission_no'); ?></th>
                                        <th><?php echo $this->lang->line('name'); ?></th>
                                        <th><?php echo $this->lang->line('class'); ?></th>
                                        <th><?php echo $this->lang->line('fee_type'); ?></th>
                                        <th><?php echo $this->lang->line('mode'); ?></th>
                                        <th class="text text-right"><?php echo $this->lang->line('amount'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                        <th class="text text-right"><?php echo $this->lang->line('discount'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                        <th class="text text-right"><?php echo $this->lang->line('fine'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                        <th class="text text-right"><?php echo $this->lang->line('total'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        $count = 1;
 
                                        foreach ($collectlist as $key => $collect) {


                                            $amount = 0;
                                            $discount = 0;
                                            $fine = 0;
                                            $total = 0;
                                            $amountLabel = "";
                                            $discountLabel = "";
                                            $fineLabel = "";
                                            $TotalLabel = "";


                                            $amount += $collect['amount'];
                                            $amountLabel .= amountFormat($collect['amount']) . "<br>";
                                            $discount += $collect['amount_discount'];
                                            $discountLabel .= amountFormat($collect['amount_discount']) . "</br>";
                                            $fine += $collect['amount_fine'];
                                            $fineLabel .= amountFormat($collect['amount_fine']) . "</br>";
                                            $t = $collect['amount'] + $collect['amount_fine'];
                                            $TotalLabel .= amountFormat($t) . "</br>";
                                            $amountLabeltot = amountFormat($amount);
                                            $discountLabeltot = amountFormat($discount);
                                            $fineLabeltot = amountFormat($fine);
                                            $TotalLabeltot = amountFormat($t);
                                            $total += ($amount + $fine);
                                            $allamount += $amount;
                                            $alldiscount += $discount;
                                            $finetotal += $fine;
                                            $alltotal += $total;
                                            ?>
                                            <tr>
                                                <td> <?php echo $collect['id'] . "/" . $collect['inv_no']; ?></td>
                                                <td><?php echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($collect['date'])); ?> </td>
                                                <td> <?php echo $collect['admission_no']; ?> </td>
                                                <td> <?php echo $this->customlib->getFullName($collect['firstname'],$collect['middlename'],$collect['lastname'],$sch_setting->middlename,$sch_setting->lastname); ?> </td>
                                                <td><?php echo $collect['class'] . " (" . $collect['section'] . ")"; ?> </td>
                                                <td>  
                                                    <?php
                                                        if ($collect['is_system']) {
                                                            echo $this->lang->line($collect['type']);
                                                        } else {
                                                            echo $collect['type'];
                                                        }
                                                    ?>
                                                </td>
                                                <td><?php echo $this->lang->line(strtolower($collect['payment_mode'])); ?></td>
                                                <td class="text text-right"> <?php echo $amountLabel; ?></td>
                                                <td class="text text-right"><?php echo $discountLabel; ?> </td>
                                                <td class="text text-right"><?php echo $fineLabel; ?></td>
                                                <td class="text text-right"> <?php $t = ($amount + $fine); echo $TotalLabel; ?> </td>
                                            </tr>
                                            <?php
                                            $count++;
                                            ?>


                                            <?php
                                        }
                                        ?>                      
                                   
                                </tbody>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td style="font-weight:bold"><?php echo $this->lang->line('total') ?></td>
                                    <td class="text text-right" style="font-weight:bold" ><?php echo amountFormat($allamount); ?></td>
                                    <td class="text text-right" style="font-weight:bold"><?php echo amountFormat($alldiscount); ?></td>
                                    <td class="text text-right" style="font-weight:bold"><?php echo amountFormat($finetotal); ?></td>
                                    <td class="text text-right" style="font-weight:bold"><?php echo amountFormat($alltotal); ?></td>                                                
                                </tr>      
                            </table>
                        </div>
                    </div>
                </div>
                 <?php
                                    }
                                    ?>
            </div>
        </div>  
</div>  
</section>
</div>
<script>
<?php
if ($search_type == 'period') {
    ?>


        $(document).ready(function () {
            showdate('period');
        });


    <?php
}
?>
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
</script>

