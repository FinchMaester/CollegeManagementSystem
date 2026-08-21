<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            <i class="fa fa-book"></i> <?php //echo $this->lang->line('library'); ?> </h1>
    </section>


    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <!-- Horizontal Form -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $this->lang->line('edit_book'); ?></h3>
                    </div><!-- /.box-header -->
                    <!-- form start -->


                    <form id="form1" action="<?php echo site_url('admin/book/edit/' . $id) ?>"  id="employeeform" name="employeeform" method="post" accept-charset="utf-8">
                        <div class="box-body">
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
                            <input  type="hidden" name="id" value="<?php echo set_value('id', $editbook['id']); ?>" >
                            <div class="form-group col-md-6">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('book_title'); ?> <small class="req"> *</small></label>
                                <input autofocus="" id="book_title" name="book_title" placeholder="" type="text" class="form-control"  value="<?php echo set_value('book_title', $editbook['book_title']); ?>" />
                                <span class="text-danger"><?php echo form_error('book_title'); ?></span>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('book_number'); ?></label>
                                <input id="book_no" name="book_no" placeholder="" type="text" class="form-control"  value="<?php echo set_value('book_no', $editbook['book_no']); ?>" />
                                <span class="text-danger"><?php echo form_error('book_no'); ?></span>
                            </div>
                            <div class="clearfix"></div>
                            <div class="form-group col-md-6">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('isbn_number'); ?></label>
                                <input id="isbn_no" name="isbn_no" placeholder="" type="text" class="form-control"  value="<?php echo set_value('isbn_no', $editbook['isbn_no']); ?>" />
                                <span class="text-danger"><?php echo form_error('isbn_no'); ?></span>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('publisher'); ?></label>
                                <input id="amount" name="publish" placeholder="" type="text" class="form-control"  value="<?php echo set_value('publish', $editbook['publish']); ?>" />
                                <span class="text-danger"><?php echo form_error('publish'); ?></span>
                            </div>
                            <div class="clearfix"></div>
                            <div class="form-group col-md-6">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('author'); ?></label>
                                <input id="amount" name="author" placeholder="" type="text" class="form-control"  value="<?php echo set_value('author', $editbook['author']); ?>" />
                                <span class="text-danger"><?php echo form_error('author'); ?></span>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('edition'); ?></label>
                                <input id="edition" name="edition" placeholder="" type="text" class="form-control"  value="<?php echo set_value('edition', isset($editbook['edition']) ? $editbook['edition'] : ''); ?>" />
                                <span class="text-danger"><?php echo form_error('edition'); ?></span>
                            </div>
                            <div class="clearfix"></div>
                            <div class="form-group col-md-6">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('subject'); ?></label>
                                <input id="subject" name="subject" placeholder="" type="text" class="form-control"  value="<?php echo set_value('subject', $editbook['subject']); ?>" />
                                <span class="text-danger"><?php echo form_error('subject'); ?></span>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('rack_number'); ?></label>
                                <input id="rack_no" name="rack_no" placeholder="" type="text" class="form-control"  value="<?php echo set_value('rack_no', $editbook['rack_no']); ?>" />
                                <span class="text-danger"><?php echo form_error('rack_no'); ?></span>
                            </div>
                            <div class="clearfix"></div>
                            <div class="form-group col-md-6">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('qty'); ?></label>
                                <input id="amount" name="qty" placeholder="" type="text" class="form-control"  value="<?php echo set_value('qty', $editbook['qty']); ?>" />
                                <span class="text-danger"><?php echo form_error('qty'); ?></span>
                            </div>
                            <div class="clearfix"></div>
                            <div class="form-group col-md-6">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('book_price'); ?> (<?php echo $currency_symbol; ?>)</label>
                                <input id="amount" name="perunitcost" placeholder="" type="text" class="form-control"  value="<?php echo convertBaseAmountCurrencyFormat($editbook['perunitcost']); ?>" />
                                <span class="text-danger"><?php echo form_error('perunitcost'); ?></span>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('post_date'); ?></label>
                                <input id="postdate" name="postdate"  placeholder="" type="text" class="form-control nepali-datepicker"  value="<?php echo set_value('postdate', $this->customlib->dateformat($editbook['postdate'])); ?>" readonly="readonly" />
                                <input type="hidden" id="postdate_english" value="<?php echo $editbook['postdate']; ?>" />
                                <span class="text-danger"><?php echo form_error('postdate'); ?></span>
                            </div>
                            <div class="clearfix"></div>
                            <div class="form-group col-md-6">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('description'); ?></label>
                                <textarea class="form-control" id="description" name="description" placeholder="" rows="3" placeholder=""><?php echo set_value('description', $editbook['description']); ?></textarea>
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
                                    <?php $lock_checked = !empty($editbook['lock_session_id']); ?>
                                    <label><input type="checkbox" name="lock_for_session" id="lock_for_session" value="1" <?php echo $lock_checked ? 'checked="checked"' : ''; ?> /> <?php echo $this->lang->line('lock_book_for_session_students'); ?></label>
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
            <!-- left column -->
            <!-- right column -->
            <div class="col-md-12">
            </div><!--/.col (right) -->
        </div>   <!-- /.row -->
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

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
        
        // Initialize Nepali Date Picker
        $('#postdate').nepaliDatePicker({
            dateFormat: '%Y-%m-%d',
            ndpMonth: true,
            ndpYear: true,
            readOnlyInput: true
        });
        
        // Convert English date to Nepali date for display
        var englishDate = $('#postdate_english').val();
        if (englishDate && englishDate !== '') {
            var dateParts = englishDate.split('-');
            if (dateParts.length === 3) {
                var nepaliDateObj = NepaliFunctions.ConvertToNepali(
                    parseInt(dateParts[0]), 
                    parseInt(dateParts[1]), 
                    parseInt(dateParts[2])
                );
                var nepaliDate = nepaliDateObj.year + '-' +
                                ('0' + nepaliDateObj.month).slice(-2) + '-' +
                                ('0' + nepaliDateObj.day).slice(-2);
                $('#postdate').val(nepaliDate);
            }
        }
        
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

        // Cascading: Faculty (Section) → Program → Level, with restore on edit
        var baseUrl = '<?php echo base_url(); ?>';
        var csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>';
        var csrfHash = '<?php echo $this->security->get_csrf_hash(); ?>';
        var savedSectionId = '<?php echo isset($editbook["section_id"]) ? (int)$editbook["section_id"] : ""; ?>';
        var savedProgramId = '<?php echo isset($editbook["program_id"]) ? (int)$editbook["program_id"] : ""; ?>';
        var savedClassId = '<?php echo isset($editbook["class_id"]) ? (int)$editbook["class_id"] : ""; ?>';
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
                if (savedSectionId) {
                    $('#section_id').val(savedSectionId).trigger('change');
                }
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
                    if (section_id === savedSectionId && savedProgramId) {
                        $('#program_id').val(savedProgramId).trigger('change');
                    }
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
                    if (section_id === savedSectionId && program_id === savedProgramId && savedClassId) {
                        $('#class_id').val(savedClassId);
                    }
                },
                error: function () { $('#class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>'); }
            });
        });
    });
</script>