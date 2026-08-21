<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<div class="content-wrapper" style="min-height: 946px;">
    <section class="content-header">
        <h1>
            <i class="fa fa-book"></i> <?php echo $this->lang->line('assign_subject_groups'); ?></h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-book"></i> <?php echo $this->lang->line('assign_subject_groups_to_exam_group'); ?></h3>
                    </div>
                    <div class="box-body">
                        <form method="post" action="<?php echo site_url('admin/examgroup/addsubjectgroups') ?>" id="assign_form">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <input type="hidden" name="exam_group" value="<?php echo $examgroup->id; ?>">
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="col-md-4">
                                        <div class="table-responsive">
                                            <h4>
                                                <a href="#" data-toggle="popover" class="detail_popover"><?php echo $examgroup->name; ?></a>
                                            </h4>
                                            <p><strong><?php echo $this->lang->line('exam_type'); ?>:</strong> <?php echo $examgroup->exam_type; ?></p>
                                            <?php if (!empty($examgroup->description)) { ?>
                                                <p><strong><?php echo $this->lang->line('description'); ?>:</strong> <?php echo $examgroup->description; ?></p>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="table-responsive ptt10">
                                            <table class="table table-striped">
                                                <tbody>
                                                    <tr>
                                                        <th><input style="vertical-align: inherit;" type="checkbox" id="select_all"/> <?php echo $this->lang->line('all'); ?></th>
                                                        <th><?php echo $this->lang->line('subject_group'); ?></th>
                                                        <th><?php echo $this->lang->line('status'); ?></th>
                                                    </tr>
                                                    <?php
                                                    if (empty($subject_groups)) {
                                                        ?>
                                                        <tr>
                                                            <td colspan="3" class="text-danger text-center"><?php echo $this->lang->line('no_subject_groups_found'); ?></td>
                                                        </tr>
                                                        <?php
                                                    } else {
                                                        foreach ($subject_groups as $subject_group) {
                                                            ?>
                                                            <tr>
                                                                <td>
                                                                    <?php
                                                                    if ($subject_group['exam_group_subject_group_id'] != 0) {
                                                                        $sel = "checked='checked'";
                                                                    } else {
                                                                        $sel = "";
                                                                    }
                                                                    ?>
                                                                    <input type="hidden" name="all_subject_groups[]" value="<?php echo $subject_group['subject_group_id']; ?>">
                                                                    <input class="checkbox" type="checkbox" name="subject_groups_id[]" value="<?php echo $subject_group['subject_group_id']; ?>" <?php echo $sel; ?>/>
                                                                </td>
                                                                <td><?php echo htmlspecialchars($subject_group['subject_group_name']); ?></td>
                                                                <td>
                                                                    <?php if ($subject_group['exam_group_subject_group_id'] != 0) { ?>
                                                                        <span class="label label-success"><?php echo $this->lang->line('assigned'); ?></span>
                                                                    <?php } else { ?>
                                                                        <span class="label label-default"><?php echo $this->lang->line('not_assigned'); ?></span>
                                                                    <?php } ?>
                                                                </td>
                                                            </tr>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <button type="submit" class="allot-fees btn btn-primary btn-sm pull-right" id="load" data-loading-text="<i class='fa fa-spinner fa-spin '></i> <?php echo $this->lang->line('saving'); ?>.."><?php echo $this->lang->line('save'); ?>
                                        </button>
                                        <br/>
                                        <br/>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">
    //select all checkboxes
    $("#select_all").change(function () {
        $(".checkbox").prop('checked', $(this).prop("checked"));
    });

    //".checkbox" change
    $('.checkbox').change(function () {
        //uncheck "select all", if one of the listed checkbox item is unchecked
        if (false == $(this).prop("checked")) {
            $("#select_all").prop('checked', false);
        }
        //check "select all" if all checkbox items are checked
        if ($('.checkbox:checked').length == $('.checkbox').length) {
            $("#select_all").prop('checked', true);
        }
    });
    
    $("#assign_form").submit(function (e) {
        if (confirm('Are you sure you want to save the subject group assignments?')) {
            var $this = $('.allot-fees');
            $.ajax({
                type: "POST",
                dataType: 'Json',
                url: $("#assign_form").attr('action'),
                data: $("#assign_form").serialize(),
                beforeSend: function () {
                    $this.button('loading');
                },
                success: function (data) {
                    if (data.status == "fail") {
                        var message = "";
                        $.each(data.error, function (index, value) {
                            message += value;
                        });
                        errorMsg(message);
                    } else {
                        successMsg(data.message);
                        setTimeout(function() {
                            window.location.href = '<?php echo site_url("admin/examgroup/addexam/" . $examgroup->id); ?>';
                        }, 1500);
                    }
                    $this.button('reset');
                },
                complete: function () {
                    $this.button('reset');
                }
            });
        }
        e.preventDefault();
    });
</script>

