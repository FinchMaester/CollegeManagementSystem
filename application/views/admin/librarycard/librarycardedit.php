<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-id-card"></i></h1>
    </section>
    <section class="content">
        <?php
        $can_view_library_card = $this->rbac->hasPrivilege('library_card', 'can_view') || $this->rbac->hasPrivilege('issue_return', 'can_view');
        $can_edit_library_card = $this->rbac->hasPrivilege('library_card', 'can_edit') || $this->rbac->hasPrivilege('issue_return', 'can_edit');
        $can_delete_library_card = $this->rbac->hasPrivilege('library_card', 'can_delete') || $this->rbac->hasPrivilege('issue_return', 'can_delete');
        ?>
        <div class="row">
            <div class="col-md-4">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Edit Library Card Template</h3>
                    </div>
                    <form id="form1" enctype="multipart/form-data" action="<?php echo site_url('admin/librarycard/edit/' . $edit_template[0]->id); ?>" method="post" accept-charset="utf-8">
                        <div class="box-body">
                            <?php if ($this->session->flashdata('msg')) { echo $this->session->flashdata('msg'); $this->session->unset_userdata('msg'); } ?>
                            <?php echo $this->customlib->getCSRF(); ?>
                            <input type="hidden" name="id" value="<?php echo set_value('id', $edit_template[0]->id); ?>">

                            <div class="form-group">
                                <label><?php echo $this->lang->line('background_image'); ?></label>
                                <input type="file" class="filestyle form-control" data-height="40" name="background_image">
                                <span class="text-danger"><?php echo form_error('background_image'); ?></span>
                                <?php if (!empty($edit_template[0]->background)) { ?>
                                    <div class="background_image"><p><a class="uploadclosebtn"><i class="fa fa-trash-o" onclick="removebackground_image()"></i></a> <?php echo $edit_template[0]->background; ?></p></div>
                                <?php } ?>
                            </div>
                            <div class="form-group">
                                <label><?php echo $this->lang->line('logo'); ?></label>
                                <input type="file" class="filestyle form-control" data-height="40" name="logo_img">
                                <span class="text-danger"><?php echo form_error('logo_img'); ?></span>
                                <?php if (!empty($edit_template[0]->logo)) { ?>
                                    <div class="logo_image"><p><a class="uploadclosebtn"><i class="fa fa-trash-o" onclick="removelogo_image()"></i></a> <?php echo $edit_template[0]->logo; ?></p></div>
                                <?php } ?>
                            </div>
                            <div class="form-group">
                                <label><?php echo $this->lang->line('signature'); ?></label>
                                <input type="file" class="filestyle form-control" data-height="40" name="sign_image">
                                <span class="text-danger"><?php echo form_error('sign_image'); ?></span>
                                <?php if (!empty($edit_template[0]->sign_image)) { ?>
                                    <div class="sign_image"><p><a class="uploadclosebtn"><i class="fa fa-trash-o" onclick="removesign_image()"></i></a> <?php echo $edit_template[0]->sign_image; ?></p></div>
                                <?php } ?>
                            </div>
                            <div class="form-group">
                                <label><?php echo $this->lang->line('school_name'); ?></label><small class="req"> *</small>
                                <input name="school_name" type="text" class="form-control" value="<?php echo set_value('school_name', $edit_template[0]->school_name); ?>" />
                                <span class="text-danger"><?php echo form_error('school_name'); ?></span>
                            </div>
                            <div class="form-group">
                                <label><?php echo $this->lang->line('address_phone_email'); ?></label><small class="req"> *</small>
                                <textarea class="form-control" name="address" rows="3"><?php echo set_value('address', $edit_template[0]->school_address); ?></textarea>
                                <span class="text-danger"><?php echo form_error('address'); ?></span>
                            </div>
                            <div class="form-group">
                                <label><?php echo $this->lang->line('id_card_title'); ?></label><small class="req"> *</small>
                                <input name="title" type="text" class="form-control" value="<?php echo set_value('title', $edit_template[0]->title); ?>" />
                                <span class="text-danger"><?php echo form_error('title'); ?></span>
                            </div>
                            <div class="form-group">
                                <label><?php echo $this->lang->line('header_color'); ?></label>
                                <input id="header_color" name="header_color" type="text" class="form-control" value="<?php echo set_value('header_color', $edit_template[0]->header_color); ?>" />
                            </div>
                            <div class="row">
                                <div class="col-xs-6">
                                    <div class="form-group">
                                        <label>Card Width (mm)</label>
                                        <input name="card_width_mm" type="number" step="0.01" min="40" max="150" class="form-control" value="<?php echo set_value('card_width_mm', isset($edit_template[0]->card_width_mm) ? $edit_template[0]->card_width_mm : '86'); ?>" />
                                        <span class="text-danger"><?php echo form_error('card_width_mm'); ?></span>
                                    </div>
                                </div>
                                <div class="col-xs-6">
                                    <div class="form-group">
                                        <label>Card Height (mm)</label>
                                        <input name="card_height_mm" type="number" step="0.01" min="25" max="120" class="form-control" value="<?php echo set_value('card_height_mm', isset($edit_template[0]->card_height_mm) ? $edit_template[0]->card_height_mm : '54'); ?>" />
                                        <span class="text-danger"><?php echo form_error('card_height_mm'); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="box box-default" style="padding:10px;">
                                <h5 style="margin-top:0;"><strong>Easy Layout Controls</strong></h5>
                                <div class="row">
                                    <div class="col-xs-6">
                                        <label>Header Align</label>
                                        <select name="header_align" class="form-control">
                                            <option value="left" <?php echo set_select('header_align', 'left', (set_value('header_align', isset($edit_template[0]->header_align) ? $edit_template[0]->header_align : 'left') == 'left')); ?>>Left</option>
                                            <option value="center" <?php echo set_select('header_align', 'center', (set_value('header_align', isset($edit_template[0]->header_align) ? $edit_template[0]->header_align : '') == 'center')); ?>>Center</option>
                                            <option value="right" <?php echo set_select('header_align', 'right', (set_value('header_align', isset($edit_template[0]->header_align) ? $edit_template[0]->header_align : '') == 'right')); ?>>Right</option>
                                        </select>
                                    </div>
                                    <div class="col-xs-6">
                                        <label>Photo and Details</label>
                                        <select name="content_layout" class="form-control">
                                            <option value="same_row" <?php echo set_select('content_layout', 'same_row', (set_value('content_layout', isset($edit_template[0]->content_layout) ? $edit_template[0]->content_layout : 'same_row') == 'same_row')); ?>>Same Row</option>
                                            <option value="stacked" <?php echo set_select('content_layout', 'stacked', (set_value('content_layout', isset($edit_template[0]->content_layout) ? $edit_template[0]->content_layout : '') == 'stacked')); ?>>Different Rows</option>
                                        </select>
                                        <p class="text-muted" style="font-size:12px;margin:6px 0 0 0;">Different Rows = photo on one row, details on the next row.</p>
                                    </div>
                                </div>
                                <div class="row" style="margin-top:8px;">
                                    <div class="col-xs-4">
                                        <label>Photo Align</label>
                                        <select name="photo_align" class="form-control">
                                            <option value="left" <?php echo set_select('photo_align', 'left', (set_value('photo_align', isset($edit_template[0]->photo_align) ? $edit_template[0]->photo_align : 'left') == 'left')); ?>>Left</option>
                                            <option value="center" <?php echo set_select('photo_align', 'center', (set_value('photo_align', isset($edit_template[0]->photo_align) ? $edit_template[0]->photo_align : '') == 'center')); ?>>Center</option>
                                            <option value="right" <?php echo set_select('photo_align', 'right', (set_value('photo_align', isset($edit_template[0]->photo_align) ? $edit_template[0]->photo_align : '') == 'right')); ?>>Right</option>
                                        </select>
                                    </div>
                                    <div class="col-xs-4">
                                        <label>Details Align</label>
                                        <select name="details_align" class="form-control">
                                            <option value="left" <?php echo set_select('details_align', 'left', (set_value('details_align', isset($edit_template[0]->details_align) ? $edit_template[0]->details_align : 'left') == 'left')); ?>>Left</option>
                                            <option value="center" <?php echo set_select('details_align', 'center', (set_value('details_align', isset($edit_template[0]->details_align) ? $edit_template[0]->details_align : '') == 'center')); ?>>Center</option>
                                            <option value="right" <?php echo set_select('details_align', 'right', (set_value('details_align', isset($edit_template[0]->details_align) ? $edit_template[0]->details_align : '') == 'right')); ?>>Right</option>
                                        </select>
                                    </div>
                                    <div class="col-xs-4">
                                        <label>Signature Align</label>
                                        <select name="signature_align" class="form-control">
                                            <option value="left" <?php echo set_select('signature_align', 'left', (set_value('signature_align', isset($edit_template[0]->signature_align) ? $edit_template[0]->signature_align : '') == 'left')); ?>>Left</option>
                                            <option value="center" <?php echo set_select('signature_align', 'center', (set_value('signature_align', isset($edit_template[0]->signature_align) ? $edit_template[0]->signature_align : '') == 'center')); ?>>Center</option>
                                            <option value="right" <?php echo set_select('signature_align', 'right', (set_value('signature_align', isset($edit_template[0]->signature_align) ? $edit_template[0]->signature_align : 'right') == 'right')); ?>>Right</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row" style="margin-top:8px;">
                                    <div class="col-xs-4">
                                        <label>Barcode Align</label>
                                        <select name="barcode_align" class="form-control">
                                            <option value="left" <?php echo set_select('barcode_align', 'left', (set_value('barcode_align', isset($edit_template[0]->barcode_align) ? $edit_template[0]->barcode_align : '') == 'left')); ?>>Left</option>
                                            <option value="center" <?php echo set_select('barcode_align', 'center', (set_value('barcode_align', isset($edit_template[0]->barcode_align) ? $edit_template[0]->barcode_align : '') == 'center')); ?>>Center</option>
                                            <option value="right" <?php echo set_select('barcode_align', 'right', (set_value('barcode_align', isset($edit_template[0]->barcode_align) ? $edit_template[0]->barcode_align : 'right') == 'right')); ?>>Right</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <details class="box box-default" style="padding:10px;">
                                <summary><strong>Advanced Position Controls (optional)</strong></summary>
                                <p class="text-muted" style="font-size:12px;margin-top:8px;">Use positive/negative values to move blocks. Range: -30 to 30.</p>
                                <div class="row">
                                    <div class="col-xs-4"><label>Header X</label><input name="header_offset_x_mm" type="number" step="0.01" min="-30" max="30" class="form-control" value="<?php echo set_value('header_offset_x_mm', isset($edit_template[0]->header_offset_x_mm) ? $edit_template[0]->header_offset_x_mm : '0'); ?>"></div>
                                    <div class="col-xs-4"><label>Header Y</label><input name="header_offset_y_mm" type="number" step="0.01" min="-30" max="30" class="form-control" value="<?php echo set_value('header_offset_y_mm', isset($edit_template[0]->header_offset_y_mm) ? $edit_template[0]->header_offset_y_mm : '0'); ?>"></div>
                                </div>
                                <div class="row" style="margin-top:8px;">
                                    <div class="col-xs-4"><label>Photo X</label><input name="photo_offset_x_mm" type="number" step="0.01" min="-30" max="30" class="form-control" value="<?php echo set_value('photo_offset_x_mm', isset($edit_template[0]->photo_offset_x_mm) ? $edit_template[0]->photo_offset_x_mm : '0'); ?>"></div>
                                    <div class="col-xs-4"><label>Photo Y</label><input name="photo_offset_y_mm" type="number" step="0.01" min="-30" max="30" class="form-control" value="<?php echo set_value('photo_offset_y_mm', isset($edit_template[0]->photo_offset_y_mm) ? $edit_template[0]->photo_offset_y_mm : '0'); ?>"></div>
                                </div>
                                <div class="row" style="margin-top:8px;">
                                    <div class="col-xs-4"><label>Details X</label><input name="details_offset_x_mm" type="number" step="0.01" min="-30" max="30" class="form-control" value="<?php echo set_value('details_offset_x_mm', isset($edit_template[0]->details_offset_x_mm) ? $edit_template[0]->details_offset_x_mm : '0'); ?>"></div>
                                    <div class="col-xs-4"><label>Details Y</label><input name="details_offset_y_mm" type="number" step="0.01" min="-30" max="30" class="form-control" value="<?php echo set_value('details_offset_y_mm', isset($edit_template[0]->details_offset_y_mm) ? $edit_template[0]->details_offset_y_mm : '0'); ?>"></div>
                                </div>
                                <div class="row" style="margin-top:8px;">
                                    <div class="col-xs-4"><label>Signature X</label><input name="signature_offset_x_mm" type="number" step="0.01" min="-30" max="30" class="form-control" value="<?php echo set_value('signature_offset_x_mm', isset($edit_template[0]->signature_offset_x_mm) ? $edit_template[0]->signature_offset_x_mm : '0'); ?>"></div>
                                    <div class="col-xs-4"><label>Signature Y</label><input name="signature_offset_y_mm" type="number" step="0.01" min="-30" max="30" class="form-control" value="<?php echo set_value('signature_offset_y_mm', isset($edit_template[0]->signature_offset_y_mm) ? $edit_template[0]->signature_offset_y_mm : '0'); ?>"></div>
                                </div>
                                <div class="row" style="margin-top:8px;">
                                    <div class="col-xs-4"><label>Barcode X</label><input name="barcode_offset_x_mm" type="number" step="0.01" min="-30" max="30" class="form-control" value="<?php echo set_value('barcode_offset_x_mm', isset($edit_template[0]->barcode_offset_x_mm) ? $edit_template[0]->barcode_offset_x_mm : '0'); ?>"></div>
                                    <div class="col-xs-4"><label>Barcode Y</label><input name="barcode_offset_y_mm" type="number" step="0.01" min="-30" max="30" class="form-control" value="<?php echo set_value('barcode_offset_y_mm', isset($edit_template[0]->barcode_offset_y_mm) ? $edit_template[0]->barcode_offset_y_mm : '0'); ?>"></div>
                                </div>
                            </details>

                            <?php
                            $switches = array(
                                'is_active_library_card_no' => array('Library Card No', 'enable_library_card_no'),
                                'is_active_member_type'     => array('Member Type', 'enable_member_type'),
                                'is_active_name'            => array($this->lang->line('name'), 'enable_name'),
                                'is_active_admission_no'    => array($this->lang->line('admission_no'), 'enable_admission_no'),
                                'is_active_employee_id'     => array($this->lang->line('staff_id'), 'enable_employee_id'),
                                'is_active_class'           => array($this->lang->line('class'), 'enable_class'),
                                'is_active_section'         => array($this->lang->line('section'), 'enable_section'),
                                'is_active_program'         => array($this->lang->line('program'), 'enable_program'),
                                'is_active_phone'           => array($this->lang->line('phone'), 'enable_phone'),
                                'is_active_dob'             => array($this->lang->line('date_of_birth'), 'enable_dob'),
                                'enable_library_barcode'    => array($this->lang->line('barcode'), 'enable_library_barcode'),
                                'enable_vertical_card'      => array($this->lang->line('design_type'), 'enable_vertical_card'),
                            );
                            foreach ($switches as $field => $meta) { ?>
                                <div class="form-group switch-inline">
                                    <label><?php echo $meta[0]; ?></label>
                                    <div class="material-switch switchcheck">
                                        <input id="<?php echo $field; ?>" name="<?php echo $field; ?>" type="checkbox" class="chk" value="1" <?php echo set_checkbox($field, '1', (set_value($field, $edit_template[0]->{$meta[1]}) == 1)); ?>>
                                        <label for="<?php echo $field; ?>" class="label-success"></label>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-info pull-right"><?php echo $this->lang->line('save'); ?></button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-md-8">
                <div class="box box-primary">
                    <div class="box-header ptbnull"><h3 class="box-title titlefix">Library Card Templates</h3></div>
                    <div class="box-body">
                        <div class="table-responsive overflow-visible">
                            <table class="table table-striped table-bordered table-hover example">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('id_card_title'); ?></th>
                                        <th><?php echo $this->lang->line('background_image'); ?></th>
                                        <th class="text text-center"><?php echo $this->lang->line('design_type'); ?></th>
                                        <th class="text-right noExport"><?php echo $this->lang->line('action'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($template_list as $template) { ?>
                                        <tr>
                                            <td><a data-id="<?php echo $template->id; ?>" class="btn btn-default btn-xs view_data"><?php echo $template->title; ?></a></td>
                                            <td><?php if ($template->background) { ?><img src="<?php echo $this->media_storage->getImageURL('uploads/library_card/background/' . $template->background); ?>" width="40"><?php } else { ?><i class="fa fa-picture-o fa-3x"></i><?php } ?></td>
                                            <td class="text text-center"><?php echo ($template->enable_vertical_card) ? $this->lang->line('vertical') : $this->lang->line('horizontal'); ?></td>
                                            <td class="text-right">
                                                <?php if ($can_view_library_card) { ?>
                                                <a data-id="<?php echo $template->id; ?>" class="btn btn-default btn-xs view_data"><i class="fa fa-reorder"></i></a>
                                                <?php } ?>
                                                <?php if ($can_edit_library_card) { ?>
                                                <a href="<?php echo base_url('admin/librarycard/edit/' . $template->id); ?>" class="btn btn-default btn-xs"><i class="fa fa-pencil"></i></a>
                                                <?php } ?>
                                                <?php if ($can_delete_library_card) { ?>
                                                <a href="<?php echo base_url('admin/librarycard/delete/' . $template->id); ?>" class="btn btn-default btn-xs" onclick="return confirm('<?php echo $this->lang->line('delete_confirm'); ?>');"><i class="fa fa-remove"></i></a>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="certificateModal" role="dialog">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title"><?php echo $this->lang->line('view_id_card'); ?></h4></div>
            <div class="modal-body"><div class="modal-inner-loader"></div><div class="modal-inner-content"></div></div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    $("#header_color").colorpicker();
    $(document).on('click', '.view_data', function () {
        $('#certificateModal').modal("show");
        $.ajax({
            url: "<?php echo base_url('admin/librarycard/view'); ?>",
            method: "post",
            data: {certificateid: $(this).data('id')},
            success: function (data) {
                $('#certificateModal .modal-inner-content').html(data);
                $('#certificateModal .modal-inner-loader').addClass('displaynone');
            }
        });
    });
});

function removebackground_image(){ if (confirm("<?php echo $this->lang->line('delete_confirm'); ?>")) { $('.background_image').html('<input type="hidden" name="removebackground_image" value="1">'); } }
function removelogo_image(){ if (confirm("<?php echo $this->lang->line('delete_confirm'); ?>")) { $('.logo_image').html('<input type="hidden" name="removelogo_image" value="1">'); } }
function removesign_image(){ if (confirm("<?php echo $this->lang->line('delete_confirm'); ?>")) { $('.sign_image').html('<input type="hidden" name="removesign_image" value="1">'); } }
</script>
