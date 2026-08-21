<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>


<div class="content-wrapper">
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                 
                <div class="box box-info" style="padding:5px;">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $this->lang->line('import_book') ?></h3>
                        <div class="pull-right box-tools">
                            <a href="<?php echo site_url('admin/book/exportformat') ?>">
                                <button class="btn btn-primary btn-sm"><i class="fa fa-download"></i> <?php echo $this->lang->line('download_sample_import_file'); ?></button>
                            </a>
                        </div>
                    </div>
                    <div class="box-body">
                        <?php if ($this->session->flashdata('msg')) {?> <div>  <?php echo $this->session->flashdata('msg'); $this->session->unset_userdata('msg'); ?> </div> <?php }?>
                        <br/>
                        1. <?php echo $this->lang->line('book_instruction_one'); ?><br/>
                        2. <?php echo $this->lang->line('book_instruction_two'); ?><br/>
                        <hr/></div>
                    <div class="box-body table-responsive overflow-visible">
                        <table class="table table-striped table-bordered table-hover" id="sampledata">
                            <thead>
                                <tr>
                                    <?php


foreach ($fields as $key => $value) {


    if ($value == 'book_title') {
        $value = "book_title";
    }
    if ($value == 'book_no') {
        $value = "book_number";
    }
    if ($value == 'isbn_no') {
        $value = "isbn_number";
    }
    if ($value == 'subject') {
        $value = "subject";
    }
    if ($value == 'rack_no') {
        $value = "rack_number";
    }
    if ($value == 'publish') {
        $value = "publisher";
    }
    if ($value == 'author') {
        $value = "author";
    }
    if ($value == 'edition') {
        $value = "edition";
    }
    if ($value == 'qty') {
        $value = "qty";
    }
    if ($value == 'perunitcost') {
       
        $value = "book_price";
    }
    if ($value == 'postdate') {
        $value = "post_date";
    }
    if ($value == 'description') {
        $value = "description";
    }
    if ($value == 'available') {
        $value = "available";
    }
    if ($value == 'is_active') {
        $value = "is_active";
    }
    $add = "";
    if (($value == "book_title")) {
        $add = "<span class='text-red d-inline'>* </span>";
    }
    ?>
                                        <th><?php echo $add . "<span>" . $this->lang->line($value) . "</span>"; ?></th>
                                    <?php }?>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <?php foreach ($fields as $key => $value) {
    ?>
                                        <td><?php echo "Sample Data" ?></td>
                                    <?php }?>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <hr/>
                    <form action="<?php echo site_url('admin/book/import') ?>"  id="employeeform" name="employeeform" method="post" enctype="multipart/form-data">
                        <div class="box-body">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="section_id"><?php echo $this->lang->line('faculty'); ?></label>
                                        <select id="section_id" name="section_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <small class="text-muted">Optional — applies to all imported books</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="program_id"><?php echo $this->lang->line('program'); ?></label>
                                        <select id="program_id" name="program_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="class_id"><?php echo $this->lang->line('level'); ?></label>
                                        <select id="class_id" name="class_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="lock_for_session"><?php echo $this->lang->line('lock_for_current_session'); ?></label>
                                        <div class="checkbox pt8">
                                            <label><input type="checkbox" name="lock_for_session" id="lock_for_session" value="1" /> <?php echo $this->lang->line('lock_book_for_session_students'); ?></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="exampleInputFile"><?php echo $this->lang->line('select_csv_file'); ?></label><small class="req"> *</small>
                                        <div><input class="filestyle form-control" type='file' name='file' id="file" size='20' />
                                            <span class="text-danger"><?php echo form_error('file'); ?></span></div>
                                    </div>
                                </div>
                                <div class="col-md-6 pt20">
                                    <button type="submit" class="btn btn-info pull-right"><?php echo $this->lang->line('import_book'); ?></button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div>
                    </div>
                </div>
                </section>
            </div>

<script type="text/javascript">
    $(document).ready(function () {
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

