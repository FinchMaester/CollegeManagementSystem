<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            <i class="fa fa-book"></i> </h1>
    </section>


    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <!-- Horizontal Form -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $this->lang->line('add_book'); ?></h3>
                        <div class="box-tools pull-right">
                            <?php if ($this->rbac->hasPrivilege('import_book', 'can_view')) {
                                ?>
                                <a class="btn btn-sm btn-primary" href="<?php echo base_url(); ?>admin/book/import" autocomplete="off"><i class="fa fa-plus"></i> <?php echo $this->lang->line('import_book'); ?></a>
                            <?php }
                            ?>
                        </div>
                    </div><!-- /.box-header -->
                    <!-- form start -->
                    <form id="form1" action="<?php echo site_url('admin/book/create') ?>"  id="employeeform" name="employeeform" method="post" accept-charset="utf-8">
                        <div class="box-body row">
                            <?php if ($this->session->flashdata('msg')) { ?>
                                <?php
                                    echo $this->session->flashdata('msg');
                                    $this->session->unset_userdata('msg');
                                ?>
                            <?php } ?>
                            <?php
                            if (isset($error_message)) {
                                echo "<div class='alert alert-danger'>" . $error_message . "</div>";
                            }
                            ?>      
                            <?php echo $this->customlib->getCSRF(); ?>                    
                            <div class="form-group col-md-6">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('book_title'); ?></label><small class="req"> *</small>
                                <input autofocus=""  id="book_title" name="book_title" placeholder="" type="text" class="form-control"  value="<?php echo set_value('book_title'); ?>" />
                                <span class="text-danger"><?php echo form_error('book_title'); ?></span>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('book_number'); ?></label>
                                <input id="book_no" name="book_no" placeholder="" type="text" class="form-control"  value="<?php echo set_value('book_no'); ?>" />
                                <span class="text-danger"><?php echo form_error('book_no'); ?></span>
                            </div>
                            <div class="clearfix"></div>
                            <div class="form-group col-md-6">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('isbn_number'); ?></label>
                                <input id="isbn_no" name="isbn_no" placeholder="" type="text" class="form-control"  value="<?php echo set_value('isbn_no'); ?>" />
                                <span class="text-danger"><?php echo form_error('isbn_no'); ?></span>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('publisher'); ?></label>
                                <input id="publish" name="publish" placeholder="" type="text" class="form-control"  value="<?php echo set_value('publish'); ?>" />
                                <span class="text-danger"><?php echo form_error('publish'); ?></span>
                            </div>
                            <div class="clearfix"></div>
                            <div class="form-group col-md-6">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('author'); ?></label>
                                <input id="author" name="author" placeholder="" type="text" class="form-control"  value="<?php echo set_value('author'); ?>" />
                                <span class="text-danger"><?php echo form_error('author'); ?></span>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('edition'); ?></label>
                                <input id="edition" name="edition" placeholder="" type="text" class="form-control"  value="<?php echo set_value('edition'); ?>" />
                                <span class="text-danger"><?php echo form_error('edition'); ?></span>
                            </div>
                            <div class="clearfix"></div>
                            <div class="form-group col-md-6">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('subject'); ?></label>
                                <input id="subject" name="subject" placeholder="" type="text" class="form-control"  value="<?php echo set_value('subject'); ?>" />
                                <span class="text-danger"><?php echo form_error('subject'); ?></span>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('rack_number'); ?></label>
                                <input id="rack_no" name="rack_no" placeholder="" type="text" class="form-control"  value="<?php echo set_value('rack_no'); ?>" />
                                <span class="text-danger"><?php echo form_error('rack_no'); ?></span>
                            </div>
                            <div class="clearfix"></div>
                            <div class="form-group col-md-6">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('qty'); ?></label>
                                <input id="qty" name="qty" placeholder="" type="text" class="form-control"  value="<?php echo set_value('qty'); ?>" />
                                <span class="text-danger"><?php echo form_error('qty'); ?></span>
                            </div>
                            <div class="clearfix"></div>
                            <div class="form-group col-md-6">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('book_price'); ?> (<?php echo $currency_symbol; ?>)</label>
                                <input id="perunitcost" name="perunitcost" placeholder="" type="text" class="form-control"  value="<?php echo set_value('perunitcost'); ?>" />
                                <span class="text-danger"><?php echo form_error('perunitcost'); ?></span>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('post_date'); ?></label>
                                <input id="postdate" name="postdate"  placeholder="" type="text" class="form-control nepali-datepicker"  value="<?php echo set_value('postdate'); ?>" readonly="readonly" />
                                <span class="text-danger"><?php echo form_error('postdate'); ?></span>
                            </div>
                            <div class="clearfix"></div>
                            <div class="form-group col-md-6">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('description'); ?></label>
                                <textarea class="form-control" id="description" name="description" placeholder="" rows="3" placeholder="Enter ..."><?php echo set_value('description'); ?></textarea>
                                <span class="text-danger"><?php echo form_error('description'); ?></span>
                            </div>
                            <div class="clearfix"></div>
                            <div class="form-group col-md-6">
                                <label for="section_id"><?php echo $this->lang->line('faculty'); ?></label>
                                <select id="section_id" name="section_id" class="form-control">
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="program_id"><?php echo $this->lang->line('program'); ?></label>
                                <select id="program_id" name="program_id" class="form-control">
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                </select>
                            </div>
                            <div class="clearfix"></div>
                            <div class="form-group col-md-6">
                                <label for="class_id"><?php echo $this->lang->line('level'); ?></label>
                                <select id="class_id" name="class_id" class="form-control">
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="lock_for_session"><?php echo $this->lang->line('lock_for_current_session'); ?></label>
                                <div class="checkbox">
                                    <label><input type="checkbox" name="lock_for_session" id="lock_for_session" value="1" <?php echo set_checkbox('lock_for_session', '1'); ?> /> <?php echo $this->lang->line('lock_book_for_session_students'); ?></label>
                                </div>
                            </div>
                        </div><!-- /.box-body -->
                        <div class="box-footer">
                            <button type="submit" class="btn btn-info pull-right"><?php echo $this->lang->line('save'); ?></button>
                        </div>
                    </form>
                </div>
            </div><!--/.col (right) -->
        </div>
        <div class="row">
            <div class="col-md-12">
            </div><!--/.col (right) -->
        </div>   <!-- /.row -->
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->


<script type="text/javascript">
    $(document).ready(function () {
        $("#btnreset").click(function () {
            $("#form1")[0].reset();
        });
        // Cascading: Faculty (Section) → Program → Level
        var baseUrl = '<?php echo base_url(); ?>';
        var csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>';
        var csrfHash = '<?php echo $this->security->get_csrf_hash(); ?>';
        $.ajax({
            url: baseUrl + 'sections/getAll',
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                if (response && response.length > 0) {
                    $.each(response, function (i, section) {
                        html += '<option value="' + section.id + '">' + (section.section || '') + '</option>';
                    });
                }
                $('#section_id').html(html);
            }
        });
        $(document).on('change', '#section_id', function () {
            var section_id = $(this).val();
            $('#program_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
            $('#class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
            if (!section_id) return;
            $('#program_id').html('<option value="">Loading...</option>');
            $.ajax({
                url: baseUrl + 'programs/getBySection',
                method: 'POST',
                data: { section_id: section_id },
                dataType: 'json',
                success: function (response) {
                    var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                    if (response && response.length > 0) {
                        $.each(response, function (i, program) {
                            html += '<option value="' + program.id + '">' + (program.title || '') + '</option>';
                        });
                    }
                    $('#program_id').html(html);
                },
                error: function () { $('#program_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>'); }
            });
        });
        $(document).on('change', '#program_id', function () {
            var section_id = $('#section_id').val();
            var program_id = $(this).val();
            $('#class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
            if (!section_id || !program_id) return;
            $('#class_id').html('<option value="">Loading...</option>');
            $.ajax({
                url: baseUrl + 'classes/getBySectionAndProgram',
                method: 'POST',
                data: { section_id: section_id, program_id: program_id, [csrfName]: csrfHash },
                dataType: 'json',
                success: function (response) {
                    var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                    if (response && response.length > 0) {
                        $.each(response, function (i, cls) {
                            var label = (cls.degree ? cls.degree + ' - ' : '') + (cls.class || '');
                            html += '<option value="' + cls.id + '">' + label + '</option>';
                        });
                    }
                    $('#class_id').html(html);
                },
                error: function () { $('#class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>'); }
            });
        });
    });
</script>
<script>
    $(document).ready(function () {
        $('.detail_popover').popover({
            placement: 'right',
            trigger: 'hover',
            container: 'body',
            html: true,
            content: function () {
                return $(this).closest('td').find('.fee_detail_popover').html();
            }
        });
    });
</script>
<script type="text/javascript">
    $(document).ready(function() {
        $('#postdate').nepaliDatePicker({
            dateFormat: '%Y-%m-%d',
            ndpMonth: true,
            ndpYear: true,
            readOnlyInput: true
        });


        // On form submit, convert Nepali date to English date
        $('#form1').on('submit', function(e) {
            var nepaliDate = $('#postdate').val();
            if (nepaliDate) {
                var engDateObj = NepaliFunctions.ConvertToEnglish(nepaliDate);
                var engDate = engDateObj.year + '-' +
                              ('0' + engDateObj.month).slice(-2) + '-' +
                              ('0' + engDateObj.day).slice(-2);
                $('#postdate').val(engDate);
            }
        });
    });
</script>

