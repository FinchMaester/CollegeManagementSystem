<div class="content-wrapper" style="min-height: 946px;">
    <section class="content-header">
        <h1>
            <i class="fa fa-map-o"></i> <?php echo $this->lang->line('examinations'); ?>
            <small><?php echo $this->lang->line('student_fee1'); ?></small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                    </div>

                    <div class="box-body">
                        <form role="form" action="<?php echo site_url('admin/examresult/admitcard') ?>" method="post" class="row">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <?php
                            $session_selected = set_value('session_id');
                            if ($session_selected === '' || $session_selected === false) {
                                $session_selected = isset($sch_setting->session_id) ? $sch_setting->session_id : '';
                            }
                            ?>

                            <div class="col-sm-6 col-lg-4 col-md-4">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('exam_group'); ?></label><small class="req"> *</small>
                                    <select autofocus id="exam_group_id" name="exam_group_id" class="form-control">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php foreach ($examgrouplist as $ex_group_value) { ?>
                                            <option value="<?php echo $ex_group_value->id ?>" <?php echo set_value('exam_group_id') == $ex_group_value->id ? 'selected' : ''; ?>>
                                                <?php echo $ex_group_value->name; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('exam_group_id'); ?></span>
                                </div>
                            </div>

                            <div class="col-sm-6 col-lg-4 col-md-4">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('exam') ?></label><small class="req"> *</small>
                                    <select id="exam_id" name="exam_id" class="form-control">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('exam_id'); ?></span>
                                </div>
                            </div>

                            <div class="col-sm-6 col-lg-4 col-md-4">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('session'); ?><small class="req"> *</small></label>
                                    <select id="session_id" name="session_id" class="form-control">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php foreach ($sessionlist as $session) { ?>
                                            <option value="<?php echo $session['id']; ?>" <?php echo ((string) $session_selected === (string) $session['id']) ? 'selected' : ''; ?>>
                                                <?php echo $session['session']; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('session_id'); ?></span>
                                </div>
                            </div>

                            <div class="col-sm-6 col-lg-4 col-md-4">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('section'); ?></label><small class="req"> *</small>
                                    <select id="section_id" name="section_id" class="form-control">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                                </div>
                            </div>

                            <div class="col-sm-6 col-lg-4 col-md-4">
                                <div class="form-group">
                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('program'); ?></label><small class="req"> *</small>
                                    <select id="program_id" name="program_id" class="form-control">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('program_id'); ?></span>
                                </div>
                            </div>

                            <div class="col-sm-6 col-lg-4 col-md-4">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('class'); ?></label> <small class="req"> *</small>
                                    <select id="class_id" name="class_id" class="form-control">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    </select>
                                    <span class="text-danger" id="error_class_id"></span>
                                </div>
                            </div>

                            <div class="col-sm-6 col-lg-4 col-md-4">
                                <div class="form-group">
                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('admit_card_template'); ?></label><small class="req"> *</small>
                                    <select id="admitcard" name="admitcard" class="form-control">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php foreach ($admitcardlist as $admitcard) { ?>
                                            <option value="<?php echo $admitcard->id ?>" <?php echo set_value('admitcard') == $admitcard->id ? 'selected' : ''; ?>>
                                                <?php echo $admitcard->template; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('admitcard'); ?></span>
                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <button type="submit" name="search" value="search_filter" class="btn btn-primary pull-right btn-sm checkbox-toggle">
                                        <i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <?php if (isset($studentList)) { ?>
                        <form method="post" action="<?php echo base_url('admin/examresult/printCard') ?>" id="printCard">
                            <input type="hidden" name="admitcard_template" value="<?php echo $admitcard_template; ?>">
                            <input type="hidden" name="post_exam_id" value="<?php echo $exam_id; ?>">
                            <input type="hidden" name="post_exam_group_id" value="<?php echo $exam_group_id; ?>">
                            <!-- CSRF hidden that we refresh from cookie before submit -->
                            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">

                            <div class="">
                                <div class="box-header ptbnull"></div>
                                <div class="box-header ptbnull">
                                    <h3 class="box-title titlefix"><i class="fa fa-users"></i> <?php echo $this->lang->line('student_list'); ?></h3>
                                    <button class="btn btn-info btn-sm printSelected pull-right" type="submit" name="generate" title="<?php echo $this->lang->line('generate_multiple_admit_card'); ?>">
                                        <?php echo $this->lang->line('generate'); ?>
                                    </button>
                                </div>

                                <div class="box-body">
                                    <div class="tab-pane active table-responsive no-padding" id="tab_1">
                                        <div class="download_label"><?php echo $this->lang->line('student'); ?> <?php echo $this->lang->line('list'); ?></div>
                                        <table class="table table-striped table-bordered table-hover table-student" cellspacing="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <th><input type="checkbox" id="select_all" /></th>
                                                    <th><?php echo $this->lang->line('admission_no'); ?></th>
                                                    <th><?php echo $this->lang->line('student_name'); ?></th>
                                                    <th><?php echo $this->lang->line('father_name'); ?></th>
                                                    <th><?php echo $this->lang->line('date_of_birth'); ?></th>
                                                    <th><?php echo $this->lang->line('gender'); ?></th>
                                                    <th><?php echo $this->lang->line('category'); ?></th>
                                                    <th class=""><?php echo $this->lang->line('mobile_number'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($studentList)) { foreach ($studentList as $student_value) { ?>
                                                    <tr>
                                                        <td class="text-center">
                                                            <input type="checkbox" class="checkbox center-block"
                                                                   name="exam_group_class_batch_exam_student_id[]"
                                                                   data-student_id="<?php echo $student_value->student_id; ?>"
                                                                   value="<?php echo $student_value->student_id; ?>">
                                                        </td>
                                                        <td><?php echo $student_value->admission_no; ?></td>
                                                        <td>
                                                            <a href="<?php echo base_url(); ?>student/view/<?php echo $student_value->student_id; ?>">
                                                                <?php echo $this->customlib->getFullName($student_value->firstname, $student_value->middlename, $student_value->lastname, $sch_setting->middlename, $sch_setting->lastname); ?>
                                                            </a>
                                                        </td>
                                                        <td><?php echo $student_value->father_name; ?></td>
                                                        <td><?php echo date($student_value->dob); ?></td>
                                                        <td><?php echo $this->lang->line(strtolower($student_value->gender)); ?></td>
                                                        <td><?php echo $student_value->category; ?></td>
                                                        <td><?php echo $student_value->mobileno; ?></td>
                                                    </tr>
                                                <?php } } else { ?>
                                                    <tr>
                                                        <td colspan="8">
                                                            <div class="alert alert-danger text-center">
                                                                <?php echo $this->lang->line('no_record_found'); ?>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </form>
                    <?php } ?>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- ***** SAFETY SHIMS: prevent upstream crashes from missing plugins ***** -->
<script>
(function () {
    if (window.jQuery) {
        var $ = window.jQuery;
        if (!$.fn.mCustomScrollbar) { $.fn.mCustomScrollbar = function () { return this; }; }
        if (!$.widget) { $.widget = { bridge: function () {} }; }
        else if (!$.widget.bridge) { $.widget.bridge = function () {}; }
        if (!$.fn.button) { $.fn.button = function(){ return this; }; }
    }
})();
</script>

<script type="text/javascript">
(function($){
    // ---- CSRF helpers ----
    var CSRF_TOKEN_NAME  = '<?php echo $this->security->get_csrf_token_name(); ?>';
    var CSRF_COOKIE_NAME = '<?php echo $this->config->item('csrf_cookie_name'); ?>' || 'csrf_cookie_name';

    function getCookie(name){
        var m = document.cookie.match('(^|;)\\s*' + name + '\\s*=\\s*([^;]+)');
        return m ? decodeURIComponent(m.pop()) : '';
    }
    function latestCsrfPair() {
        var o = {}; o[CSRF_TOKEN_NAME] = getCookie(CSRF_COOKIE_NAME); return o;
    }
    function refreshFormCsrf($form){
        var token = getCookie(CSRF_COOKIE_NAME);
        if (token) { $form.find('input[name="'+CSRF_TOKEN_NAME+'"]').val(token); }
    }

    // ---- initial state / persisted selections ----
    var selectedSection = '<?php echo set_value("section_id") ?>';
    var selectedProgram = '<?php echo set_value("program_id") ?>';
    var selectedClass   = '<?php echo set_value("class_id") ?>';

    $(document).ready(function () {
        loadSections();

        $('#section_id').on('change', function(){
            var sectionId = $(this).val();
            selectedSection = sectionId; selectedProgram = ''; selectedClass = '';
            if (sectionId) loadPrograms(sectionId);
            else {
                $('#program_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
                $('#class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
            }
        });

        $('#program_id').on('change', function(){
            var programId = $(this).val();
            var sectionId = $('#section_id').val();
            selectedProgram = programId; selectedClass = '';
            if (sectionId && programId) loadClasses(sectionId, programId);
            else $('#class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
        });
    });

    function loadSections() {
        $.ajax({
            url: '<?php echo base_url(); ?>sections/getAll',
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                $.each(response || [], function (i, section) {
                    var sel = (selectedSection == section.id) ? 'selected' : '';
                    html += '<option value="'+section.id+'" '+sel+'>'+section.section+'</option>';
                });
                $('#section_id').html(html);
                if (selectedSection) loadPrograms(selectedSection);
            },
            error: function(xhr, status, error) {
                console.error("Error loading sections:", error);
            }
        });
    }

    function loadPrograms(sectionId) {
        if (!sectionId) return;
        $('#program_id').html('<option value="">Loading...</option>');
        $('#class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');

        var payload = { section_id: sectionId };
        $.extend(payload, latestCsrfPair());

        $.ajax({
            url: '<?php echo base_url(); ?>programs/getBySection',
            method: 'POST',
            data: payload,
            dataType: 'json',
            success: function (response) {
                var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                if (response && response.length) {
                    $.each(response, function (i, program) {
                        var sel = (selectedProgram == program.id) ? 'selected' : '';
                        html += '<option value="'+program.id+'" '+sel+'>'+program.title+'</option>';
                    });
                } else {
                    html += '<option value="" disabled>No programs found</option>';
                }
                $('#program_id').html(html);
                if (selectedProgram) loadClasses(sectionId, selectedProgram);
            },
            error: function(xhr, status, error) {
                console.error("Error fetching programs:", error);
                $('#program_id').html('<option value="">Error loading programs</option>');
            }
        });
    }

    function loadClasses(sectionId, programId) {
        if (!sectionId || !programId) return;
        $('#class_id').html('<option value="">Loading...</option>');

        var payload = { section_id: sectionId, program_id: programId };
        $.extend(payload, latestCsrfPair());

        $.ajax({
            url: '<?php echo base_url(); ?>classes/getBySectionAndProgram',
            method: 'POST',
            data: payload,
            dataType: 'json',
            success: function (response) {
                var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                if (response && response.length) {
                    $.each(response, function (i, c) {
                        var sel = (selectedClass == c.id) ? 'selected' : '';
                        html += '<option value="'+c.id+'" '+sel+'>'+(c.degree?c.degree+' - ':'')+c.class+'</option>';
                    });
                } else {
                    html += '<option value="" disabled>No classes found for this combination</option>';
                }
                $('#class_id').html(html);
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error (classes):", status, error);
                $('#class_id').html('<option value="">Error loading classes</option>');
            }
        });
    }

    // exam group -> exams
    var date_format = '<?php echo strtr($this->customlib->getSchoolDateFormat(), ['d' => 'dd','m' => 'mm','Y' => 'yyyy']) ?>';
    var class_id    = '<?php echo set_value('class_id') ?>';
    var section_id  = '<?php echo set_value('section_id') ?>';
    var session_id  = '<?php echo htmlspecialchars((string) $session_selected, ENT_QUOTES, 'UTF-8'); ?>';
    var exam_group_id_init = '<?php echo set_value('exam_group_id') ?>';
    var exam_id_init       = '<?php echo set_value('exam_id') ?>';

    getExamByExamgroup(exam_group_id_init, exam_id_init);

    $(document).on('change', '#exam_group_id', function(){
        $('#exam_id').html("");
        getExamByExamgroup($(this).val(), 0);
    });

    function getExamByExamgroup(exam_group_id, exam_id) {
        if (!exam_group_id) return;
        var base_url = '<?php echo base_url() ?>';
        var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';

        var payload = { exam_group_id: exam_group_id };
        $.extend(payload, latestCsrfPair());

        $.ajax({
            type: "POST",
            url: base_url + "admin/examgroup/getExamByExamgroup",
            data: payload,
            dataType: "json",
            beforeSend: function () {
                $('#exam_id').addClass('dropdownloading');
            },
            success: function (data) {
                $.each(data || [], function (i, obj) {
                    var sel = (String(exam_id) === String(obj.id)) ? "selected" : "";
                    div_data += "<option value='"+obj.id+"' "+sel+">"+obj.exam+"</option>";
                });
                $('#exam_id').html(div_data);
            },
            complete: function () {
                $('#exam_id').removeClass('dropdownloading');
            }
        });
    }

    // Select-all
    $(document).on('click', '#select_all', function () {
        $(this).closest('table').find('td input:checkbox').prop('checked', this.checked);
    });

    // Print (multiple)
    $(document).on('submit', 'form#printCard', function (e) {
        e.preventDefault();

        var $form = $(this);
        var $btn  = $form.find(':submit');
        var list_selected = $('form#printCard input[name="exam_group_class_batch_exam_student_id[]"]:checked').length;

        if (list_selected <= 0) {
            confirm("<?php echo $this->lang->line('please_select_student'); ?>");
            return false;
        }

        // Refresh CSRF just-in-time
        refreshFormCsrf($form);

        $.ajax({
            type: "POST",
            url: $form.attr('action'),
            data: $form.serialize(),
            dataType: "JSON",
            beforeSend: function () { if ($.fn.button) $btn.button('loading'); },
            success: function (response) {
                if (response && response.page) {
                    Popup(response.page);
                } else {
                    console.error('Unexpected response:', response);
                    alert("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");
                }
            },
            error: function (xhr) {
                console.error('printCard AJAX error:', xhr.status, xhr.responseText);
                alert("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");
            },
            complete: function () { if ($.fn.button) $btn.button('reset'); }
        });
    });

    // printer
    window.Popup = function(data) {
        var frame1 = $('<iframe />');
        frame1[0].name = "frame1";
        frame1.css({ position: "absolute", top: "-10000px" });

        $("body").append(frame1);
        var frameDoc = frame1[0].contentWindow
            ? frame1[0].contentWindow
            : frame1[0].contentDocument.document
                ? frame1[0].contentDocument.document
                : frame1[0].contentDocument;

        frameDoc.document.open();
        frameDoc.document.write('<html><head><title></title></head><body>');
        frameDoc.document.write(data);
        frameDoc.document.write('</body></html>');
        frameDoc.document.close();

        setTimeout(function () {
            window.frames["frame1"].focus();
            window.frames["frame1"].print();
            frame1.remove();
        }, 500);

        return true;
    };
})(jQuery);
</script>
