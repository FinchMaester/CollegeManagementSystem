<style type="text/css">
    @media print {
        .no-print, .no-print * {
            display: none !important;
        }
    }
    .info-box-link {
        text-decoration: none;
        color: inherit;
        display: block;
    }
    .info-box-link:hover {
        text-decoration: none;
        color: inherit;
    }
    .info-box-link:hover .info-box {
        opacity: 0.9;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        transition: all 0.3s ease;
    }
</style>

<div class="content-wrapper" style="min-height: 946px;">
    <section class="content-header">
        <h1><i class="fa fa-book"></i> <?php //echo $this->lang->line('library'); ?> </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <!-- Library Statistics Boxes -->
        <div class="row" style="margin-bottom: 20px;">
            <div class="col-md-4 col-sm-6">
                <a href="<?php echo site_url('admin/book/getall'); ?>" class="info-box-link">
                    <div class="info-box">
                        <span class="info-box-icon bg-blue"><i class="fa fa-book"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Books</span>
                            <span class="info-box-number"><?php echo isset($total_books) ? $total_books : 0; ?></span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4 col-sm-6">
                <a href="<?php echo site_url('admin/member/issuesList/all'); ?>" class="info-box-link">
                    <div class="info-box">
                        <span class="info-box-icon bg-aqua"><i class="fa fa-exchange"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Issues</span>
                            <span class="info-box-number"><?php echo isset($total_issues) ? $total_issues : 0; ?></span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4 col-sm-6">
                <a href="<?php echo site_url('admin/member/issuesList/today'); ?>" class="info-box-link">
                    <div class="info-box">
                        <span class="info-box-icon bg-green"><i class="fa fa-calendar-check-o"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Today's Issues</span>
                            <span class="info-box-number"><?php echo isset($today_issues) ? $today_issues : 0; ?></span>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        <div class="row" style="margin-bottom: 20px;">
            <div class="col-md-12">
                <?php
                $can_view_library_card = $this->rbac->hasPrivilege('library_card', 'can_view') || $this->rbac->hasPrivilege('issue_return', 'can_view');
                $can_generate_library_card = $this->rbac->hasPrivilege('library_card', 'can_add') || $this->rbac->hasPrivilege('issue_return', 'can_add');
                ?>
                <?php if ($this->rbac->hasPrivilege('library_settings', 'can_view')) { ?>
                <a href="<?php echo site_url('admin/librarysettings'); ?>" class="btn btn-info btn-sm">
                    <i class="fa fa-cog"></i> <?php echo $this->lang->line('library_settings'); ?>
                </a>
                <?php } ?>
                <?php if ($can_view_library_card) { ?>
                <a href="<?php echo site_url('admin/librarycard'); ?>" class="btn btn-default btn-sm">
                    <i class="fa fa-id-card"></i> Manage Library Card Template
                </a>
                <?php } ?>
                <?php if ($can_view_library_card || $can_generate_library_card) { ?>
                <a href="<?php echo site_url('admin/generatelibrarycard'); ?>" class="btn btn-primary btn-sm">
                    <i class="fa fa-print"></i> Generate Library Card
                </a>
                <?php } ?>
                <a href="<?php echo site_url('admin/member/student'); ?>" class="btn btn-success btn-sm">
                    <i class="fa fa-user-plus"></i> Register student (library card)
                </a>
                <a href="<?php echo site_url('admin/member/teacher'); ?>" class="btn btn-info btn-sm">
                    <i class="fa fa-user-plus"></i> Register staff (library card)
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-book"></i> Currently issued books</h3>
                        <div class="box-tools pull-right">
                            <a href="<?php echo site_url('admin/member/issuesList/all'); ?>" class="btn btn-default btn-xs"><?php echo $this->lang->line('view_all'); ?></a>
                        </div>
                    </div>
                    <div class="box-body table-responsive" style="max-height: 380px; overflow-y: auto;">
                        <?php
                        $issues_out = isset($outstanding_issues) ? $outstanding_issues : array();
                        if (empty($issues_out)) {
                            ?>
                            <p class="text-muted" style="margin:0;">No books are currently on loan.</p>
                            <?php
                        } else {
                            ?>
                            <table class="table table-striped table-bordered table-condensed" style="margin-bottom:0;">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('book_title'); ?></th>
                                        <th><?php echo $this->lang->line('book_no'); ?></th>
                                        <th><?php echo $this->lang->line('member_name'); ?></th>
                                        <th><?php echo $this->lang->line('member_type'); ?></th>
                                        <th><?php echo $this->lang->line('issue_date'); ?></th>
                                        <th><?php echo $this->lang->line('due') . ' ' . $this->lang->line('return_date'); ?></th>
                                        <th class="text-right noExport"><?php echo $this->lang->line('action'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($issues_out as $issue) {
                                        if ($issue['member_type'] == 'student') {
                                            $member_name = trim(ucwords($issue['fname'] . ' ' . (isset($issue['mname']) ? $issue['mname'] . ' ' : '') . $issue['lname']));
                                        } else {
                                            $member_name = trim(ucwords($issue['fname'] . ' ' . $issue['lname']));
                                        }
                                        $due_raw = isset($issue['duereturn_date']) ? $issue['duereturn_date'] : '';
                                        $is_overdue = $this->customlib->isLibraryDueDateOverdue($due_raw);
                                        $mid = isset($issue['members_id']) ? (int) $issue['members_id'] : 0;
                                        ?>
                                        <tr class="<?php echo $is_overdue ? 'danger' : ''; ?>">
                                            <td><?php echo isset($issue['book_title']) ? htmlspecialchars($issue['book_title']) : ''; ?></td>
                                            <td><?php echo isset($issue['book_no']) ? htmlspecialchars($issue['book_no']) : ''; ?></td>
                                            <td><?php echo htmlspecialchars($member_name); ?></td>
                                            <td><?php echo isset($issue['member_type']) ? ucwords($issue['member_type']) : ''; ?></td>
                                            <td><?php
                                            $issue_raw = isset($issue['issue_date']) ? $issue['issue_date'] : '';
                                            $issue_bs = $this->customlib->formatLibraryDateBsForDisplay($issue_raw);
                                            echo $issue_bs !== '' ? htmlspecialchars($issue_bs) : '-';
                                            ?></td>
                                            <td><?php
                                            $due_bs = $this->customlib->formatLibraryDateBsForDisplay($due_raw);
                                            echo $due_bs !== '' ? htmlspecialchars($due_bs) : '-';
                                            if ($is_overdue) {
                                                echo ' <span class="label label-danger">Overdue</span>';
                                            }
                                            ?></td>
                                            <td class="text-right">
                                                <?php if ($mid > 0) { ?>
                                                <a href="<?php echo base_url('admin/member/issue/' . $mid); ?>" class="btn btn-default btn-xs" title="<?php echo $this->lang->line('issue_return'); ?>"><i class="fa fa-sign-out"></i></a>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                    </div>
                    <div class="box-body">
                        <?php if ($this->session->flashdata('msg')) { ?>
                            <div class="alert alert-success"><?php echo $this->session->flashdata('msg');
                            $this->session->unset_userdata('msg'); ?></div>
                        <?php } ?>
                        
                        <!-- ✅ NEW: Book Number Search Section -->
                        <div class="row" style="margin-bottom: 20px;">
                            <div class="col-md-12">
                                <div style="padding: 15px; background-color: #f9f9f9; border: 1px solid #ddd; border-radius: 4px;">
                                    <h4 style="margin-top: 0; margin-bottom: 15px;"><i class="fa fa-book"></i> Search by Book Number</h4>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group" style="margin-bottom: 10px;">
                                                <label>Book Number</label>
                                                <div class="input-group">
                                                    <input type="text" id="book_number_search" class="form-control" placeholder="Enter book number to find member..." style="height: 34px;">
                                                    <span class="input-group-btn">
                                                        <button type="button" id="search_book_btn" class="btn btn-primary" style="height: 34px; padding-top: 6px; padding-bottom: 6px;">
                                                            <i class="fa fa-search"></i> Search
                                                        </button>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="book_search_result" style="display: none; margin-top: 15px;">
                                        <div class="alert" id="book_search_alert" style="margin-bottom: 0;">
                                            <div id="book_search_content"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <form role="form" action="<?php echo site_url('admin/member'); ?>" method="post" id="lib_member_search_form">
                                <?php echo $this->customlib->getCSRF(); ?>

                                <div class="col-md-12" style="margin-bottom: 15px;">
                                    <p class="text-muted" style="margin-bottom: 8px;"><strong>Issue books</strong> — find a library member below, then use the issue (→) action. New borrowers need a library card first (buttons above).</p>
                                    <label class="radio-inline">
                                        <input type="radio" name="borrower_type" value="student" id="borrower_type_student" <?php echo (!isset($borrower_type) || $borrower_type !== 'staff') ? 'checked' : ''; ?>> <?php echo $this->lang->line('student'); ?>
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" name="borrower_type" value="staff" id="borrower_type_staff" <?php echo (isset($borrower_type) && $borrower_type === 'staff') ? 'checked' : ''; ?>> Staff
                                    </label>
                                </div>

                                <div id="lib-filters-student" class="lib-borrower-filters" style="<?php echo (isset($borrower_type) && $borrower_type === 'staff') ? 'display:none;' : ''; ?>">
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $this->lang->line('faculty'); ?></label>
                                            <select id="section_id" name="section_id" class="form-control">
                                                <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $this->lang->line('program'); ?></label>
                                            <select id="program_id" name="program_id" class="form-control">
                                                <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $this->lang->line('class'); ?></label>
                                            <select id="class_id" name="class_id" class="form-control">
                                                <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $this->lang->line('subject_group'); ?></label>
                                            <select id="subject_group_id" name="subject_group_id" class="form-control">
                                                <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            </select>
                                            <span class="help-block small">Optional — narrows to students in this subject group</span>
                                        </div>
                                    </div>
                                </div>

                                <div id="lib-filters-staff" class="lib-borrower-filters" style="<?php echo (isset($borrower_type) && $borrower_type === 'staff') ? '' : 'display:none;'; ?>">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Role</label>
                                            <select name="role_id" id="role_id" class="form-control">
                                                <option value="0"><?php echo $this->lang->line('all'); ?></option>
                                                <?php
                                                if (!empty($roles_list)) {
                                                    foreach ($roles_list as $r) {
                                                        $rid = isset($r['id']) ? (int) $r['id'] : 0;
                                                        $rname = isset($r['name']) ? $r['name'] : '';
                                                        $sel = (isset($role_id) && (int) $role_id === $rid) ? ' selected' : '';
                                                        echo '<option value="' . $rid . '"' . $sel . '>' . htmlspecialchars($rname) . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                            <span class="help-block small">Filter staff library members by system role (e.g. Teacher, Librarian).</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <button type="submit" name="search" value="search_filter" class="btn btn-primary btn-sm pull-right checkbox-toggle">
                                            <i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>


                    <?php if (isset($memberList)) { ?>
                    <?php if (!empty($library_student_member_list_mode) && $library_student_member_list_mode === 'all_session_students') {
                        $_lib_note = $this->lang->line('library_list_all_students_note');
                        if ($_lib_note === false) {
                            $_lib_note = 'This list includes all session students. Book issue only works after a student is added under Library → Member → Student (library card). Students without a card must be registered first.';
                        }
                        ?>
                        <div class="alert alert-warning" style="margin: 0 15px 15px;">
                            <i class="fa fa-info-circle"></i> <?php echo $_lib_note; ?>
                        </div>
                    <?php } ?>
                    <div class="box box-primary" id="tachelist">
                        <div class="box-header ptbnull">
                            <h3 class="box-title titlefix"><?php echo $this->lang->line('members'); ?></h3>
                            <div class="box-tools pull-right"></div>
                        </div>
                        <div class="box-body">
                            <div class="mailbox-controls"></div>
                            <div class="table-responsive mailbox-messages">
                                <div class="download_label"><?php echo $this->lang->line('members'); ?></div>
                                <table class="table table-striped table-bordered table-hover example" id="members">
                                    <thead>
                                        <tr>
                                            <th><?php echo $this->lang->line('member_id'); ?></th>
                                            <th><?php echo $this->lang->line('library_card_no'); ?></th>
                                            <th><?php echo $this->lang->line('admission_no'); ?></th>
                                            <th><?php echo $this->lang->line('name'); ?></th>
                                            <th><?php echo $this->lang->line('status'); ?></th>
                                            <th><?php echo $this->lang->line('member_type'); ?></th>
                                            <th>Role</th>
                                            <th><?php echo $this->lang->line('phone'); ?></th>
                                            <th><?php echo $this->lang->line('father_name'); ?></th>
                                            <th><?php echo $this->lang->line('date_of_birth'); ?></th>
                                            <th class="text-right noExport"><?php echo $this->lang->line('action'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($memberList)) { ?>
                                            <?php
$count = 1;
foreach ($memberList as $member) {
    if ($member['member_type'] == "student") {
        $name  = $this->customlib->getFullName(
            isset($member['firstname']) ? $member['firstname'] : '',
            isset($member['middlename']) ? $member['middlename'] : '',
            isset($member['lastname']) ? $member['lastname'] : '',
            $sch_setting->middlename,
            $sch_setting->lastname
        );
        $phone = '';
        if (!empty($member['mobileno'])) {
            $phone = $member['mobileno'];
        } elseif (!empty($member['guardian_phone'])) {
            $phone = $member['guardian_phone'];
        }
        $father_name = !empty($member['father_name']) ? $member['father_name'] : '';
        $dob = !empty($member['dob']) ? $member['dob'] : '';
        $student_disabled = isset($member['student_is_active']) && strtolower(trim((string) $member['student_is_active'])) === 'no';
    } else {
        $teacher_name  = !empty($member['teacher_name']) ? $member['teacher_name'] : '';
        $emp_id        = !empty($member['emp_id']) ? $member['emp_id'] : '';
        $teacher_phone = !empty($member['teacher_phone']) ? $member['teacher_phone'] : '';
    
        if ($teacher_name && $emp_id) {
            $name = $teacher_name . " (" . $emp_id . ")";
        } elseif ($teacher_name) {
            $name = $teacher_name;
        } elseif ($emp_id) {
            $name = "(" . $emp_id . ")";
        } else {
            
            $name = "N/A";
        }
    
        $phone = $teacher_phone ?: "N/A";
        $father_name = '';
        $dob = '';
        $student_disabled = false;
    }
    
?>
                                                <tr<?php echo $student_disabled ? ' class="warning"' : ''; ?>>
                                                    <td><?php echo isset($member['lib_member_id']) ? $member['lib_member_id'] : $member['id']; ?></td>
                                                    <td><?php echo $member['library_card_no']; ?></td>
                                                    <td><?php echo $member['admission_no']; ?></td>
                                                    <td><?php echo $name; ?></td>
                                                    <td>
                                                        <?php if ($student_disabled) { ?>
                                                            <span class="label label-danger">Disabled</span>
                                                        <?php } else { ?>
                                                            <span class="label label-success">Active</span>
                                                        <?php } ?>
                                                    </td>
                                                    <td><?php echo $this->lang->line($member['member_type']); ?></td>
                                                    <td><?php echo (!empty($member['role_name'])) ? htmlspecialchars($member['role_name']) : '-'; ?></td>
                                                    <td><?php echo $phone; ?></td>
                                                    <td><?php echo $father_name !== '' ? html_escape($father_name) : '—'; ?></td>
                                                    <td><?php echo $dob !== '' ? $this->customlib->dateformat($dob) : '—'; ?></td>
                                                    <td class="mailbox-date pull-right">
                                                        <?php
                                                        $issue_mid = isset($member['lib_member_id']) ? (int) $member['lib_member_id'] : (isset($member['id']) ? (int) $member['id'] : 0);
                                                        $can_issue = ($issue_mid > 0);
                                                        if ($can_issue) {
                                                            $issue_title = $student_disabled
                                                                ? 'Return books (student disabled — new issues blocked)'
                                                                : $this->lang->line('issue_return');
                                                            ?>
                                                        <a href="<?php echo base_url(); ?>admin/member/issue/<?php echo $issue_mid; ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo htmlspecialchars($issue_title, ENT_QUOTES, 'UTF-8'); ?>">
                                                            <i class="fa fa-sign-out"></i>
                                                        </a>
                                                            <?php
                                                        } else {
                                                            $_add_lm = $this->lang->line('add_library_member');
                                                            if ($_add_lm === false) {
                                                                $_add_lm = 'Add library member (card)';
                                                            }
                                                            ?>
                                                        <a href="<?php echo site_url('admin/member/student'); ?>" class="btn btn-warning btn-xs" data-toggle="tooltip" title="<?php echo htmlspecialchars($_add_lm, ENT_QUOTES, 'UTF-8'); ?>">
                                                            <i class="fa fa-id-card"></i>
                                                        </a>
                                                            <?php
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                            <?php $count++; } ?>
                                        <?php } else { ?>
                                            <tr>
                                                <td colspan="11" class="text-center text-muted" style="padding:15px;">
                                                    No records found for the selected criteria.
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">
    var base_url = '<?php echo base_url(); ?>';

    $(document).ready(function () {
        function libToggleBorrowerFilters() {
            if ($('#borrower_type_staff').is(':checked')) {
                $('#lib-filters-student').hide();
                $('#lib-filters-staff').show();
            } else {
                $('#lib-filters-staff').hide();
                $('#lib-filters-student').show();
            }
        }
        $(document).on('change', 'input[name="borrower_type"]', libToggleBorrowerFilters);
        libToggleBorrowerFilters();

        // Fetch all sections (faculty)
        $.ajax({
            url: base_url + 'sections/getAll',
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                var section_id = '<?php echo isset($section_id) ? html_escape($section_id) : set_value("section_id"); ?>';
                var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                $.each(response, function (index, section) {
                    var selected = (section_id == section.id) ? 'selected' : '';
                    html += '<option value="' + section.id + '" ' + selected + '>' + section.section + '</option>';
                });
                $('#section_id').html(html);
                if (section_id) {
                    $('#section_id').trigger('change');
                } else {
                    libLoadSubjectGroups();
                }
            }
        });

        // ✅ Book Number Search Functionality
        $('#search_book_btn').on('click', function() {
            var book_no = $('#book_number_search').val().trim();
            
            if (book_no === '') {
                alert('Please enter a book number');
                return;
            }

            var $btn = $(this);
            var $result = $('#book_search_result');
            var $alert = $('#book_search_alert');
            var $content = $('#book_search_content');

            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Searching...');
            $result.hide();
            $content.html('');

            $.ajax({
                url: base_url + 'admin/member/searchByBookNumber',
                method: 'POST',
                data: {
                    book_no: book_no,
                    <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'
                },
                dataType: 'json',
                success: function(response) {
                    $btn.prop('disabled', false).html('<i class="fa fa-search"></i> Search');
                    
                    if (response.status === 'issued' && response.data) {
                        var data = response.data;
                        var memberType = data.member_type === 'student' ? 'Student' : 'Teacher';
                        
                        var html = '<h4 style="margin-top: 0;"><i class="fa fa-check-circle"></i> Book is Issued To:</h4>';
                        html += '<table class="table table-bordered" style="margin-bottom: 0;">';
                        html += '<tr><th style="width: 30%;">Book Title</th><td>' + (data.book_title || 'N/A') + '</td></tr>';
                        html += '<tr><th>Book Number</th><td>' + (data.book_no || 'N/A') + '</td></tr>';
                        html += '<tr><th>Author</th><td>' + (data.author || 'N/A') + '</td></tr>';
                        html += '<tr><th>Issued To</th><td><strong>' + (data.member_name || 'N/A') + '</strong></td></tr>';
                        html += '<tr><th>Member Type</th><td>' + memberType + '</td></tr>';
                        html += '<tr><th>Library Card No.</th><td>' + (data.library_card_no || 'N/A') + '</td></tr>';
                        html += '<tr><th>' + (data.member_type === 'student' ? 'Admission No.' : 'Employee ID') + '</th><td>' + (data.admission_emp_no || 'N/A') + '</td></tr>';
                        html += '<tr><th>Phone</th><td>' + (data.phone || 'N/A') + '</td></tr>';
                        html += '<tr><th>Issue Date</th><td>' + (data.issue_date || 'N/A') + '</td></tr>';
                        html += '<tr><th>Due Return Date</th><td>' + (data.duereturn_date || 'N/A') + '</td></tr>';
                        html += '<tr><th>Action</th><td>';
                        html += '<a href="' + base_url + 'admin/member/issue/' + data.member_id + '" class="btn btn-primary btn-sm" style="color: #fff;">';
                        html += '<i class="fa fa-eye"></i> View Member Books</a>';
                        html += '</td></tr>';
                        html += '</table>';
                        
                        $content.html(html);
                        $alert.removeClass('alert-danger alert-warning').addClass('alert-success');
                        $result.slideDown();
                    } else if (response.status === 'not_issued' && response.data) {
                        var data = response.data;
                        var html = '<h4 style="margin-top: 0;"><i class="fa fa-info-circle"></i> Book Found - Available for Issue</h4>';
                        html += '<table class="table table-bordered" style="margin-bottom: 0;">';
                        html += '<tr><th style="width: 30%;">Book Title</th><td><strong>' + (data.book_title || 'N/A') + '</strong></td></tr>';
                        html += '<tr><th>Book Number</th><td>' + (data.book_no || 'N/A') + '</td></tr>';
                        html += '<tr><th>Author</th><td>' + (data.author || 'N/A') + '</td></tr>';
                        html += '<tr><th>ISBN Number</th><td>' + (data.isbn_no || 'N/A') + '</td></tr>';
                        html += '<tr><th>Publisher</th><td>' + (data.publish || 'N/A') + '</td></tr>';
                        html += '<tr><th>Subject</th><td>' + (data.subject || 'N/A') + '</td></tr>';
                        html += '<tr><th>Rack Number</th><td>' + (data.rack_no || 'N/A') + '</td></tr>';
                        html += '<tr><th>Quantity</th><td>' + (data.qty || '0') + '</td></tr>';
                        html += '<tr><th>Available</th><td><span class="label label-success">' + (data.available || '0') + ' Available</span></td></tr>';
                        if (data.description) {
                            html += '<tr><th>Description</th><td>' + (data.description || 'N/A') + '</td></tr>';
                        }
                        html += '<tr><th>Status</th><td><span class="label label-info">Not Issued - Available</span></td></tr>';
                        html += '<tr><th>Action</th><td>';
                        html += '<p class="text-info"><i class="fa fa-info-circle"></i> To issue this book, find a member below using filters and click the arrow (→) button.</p>';
                        html += '</td></tr>';
                        html += '</table>';
                        
                        $content.html(html);
                        $alert.removeClass('alert-danger alert-success').addClass('alert-warning');
                        $result.slideDown();
                    } else if (response.status === 'not_issued') {
                        var html = '<h4 style="margin-top: 0;"><i class="fa fa-info-circle"></i> Book Not Issued</h4>';
                        html += '<p>This book is not issued to any member.</p>';
                        
                        $content.html(html);
                        $alert.removeClass('alert-danger alert-success').addClass('alert-warning');
                        $result.slideDown();
                    } else if (response.status === 'not_found') {
                        var html = '<h4 style="margin-top: 0;"><i class="fa fa-exclamation-triangle"></i> Book Not Found</h4>';
                        html += '<p>Book number not found in system.</p>';
                        
                        $content.html(html);
                        $alert.removeClass('alert-success alert-warning').addClass('alert-danger');
                        $result.slideDown();
                    } else {
                        var html = '<h4 style="margin-top: 0;"><i class="fa fa-exclamation-triangle"></i> Error</h4>';
                        html += '<p>' + (response.message || 'An error occurred') + '</p>';
                        
                        $content.html(html);
                        $alert.removeClass('alert-success alert-warning').addClass('alert-danger');
                        $result.slideDown();
                    }
                },
                error: function(xhr, status, error) {
                    $btn.prop('disabled', false).html('<i class="fa fa-search"></i> Search');
                    console.error('AJAX Error:', status, error);
                    var html = '<h4 style="margin-top: 0;"><i class="fa fa-exclamation-triangle"></i> Error</h4>';
                    html += '<p>An error occurred. Please try again.</p>';
                    $content.html(html);
                    $alert.removeClass('alert-success alert-warning').addClass('alert-danger');
                    $result.slideDown();
                }
            });
        });

        // Allow Enter key to trigger search
        $('#book_number_search').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                $('#search_book_btn').click();
            }
        });
    });

    function libLoadSubjectGroups() {
        var section_id = $('#section_id').val() || '';
        var program_id = $('#program_id').val() || '';
        var class_id = $('#class_id').val() || '';
        var subject_group_id = '<?php echo isset($subject_group_id) ? (int) $subject_group_id : 0; ?>';

        $('#subject_group_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');

        $.ajax({
            url: base_url + 'admin/member/getSubjectGroupsByCriteria',
            method: 'POST',
            data: {
                section_id: section_id,
                program_id: program_id,
                class_id: class_id,
                <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'
            },
            dataType: 'json',
            success: function (response) {
                var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                if (response && response.length > 0) {
                    $.each(response, function (index, group) {
                        var selected = (subject_group_id && String(subject_group_id) === String(group.id)) ? 'selected' : '';
                        html += '<option value="' + group.id + '" ' + selected + '>' + group.name + '</option>';
                    });
                } else if (section_id || program_id || class_id) {
                    html += '<option value="" disabled><?php echo $this->lang->line("no_record_found"); ?></option>';
                }
                $('#subject_group_id').html(html);
            }
        });
    }

    // When section changes, fetch programs
    $(document).on('change', '#section_id', function () {
        var section_id = $(this).val();
        $('#program_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
        $('#class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
        libLoadSubjectGroups();

        if (section_id != '') {
            $('#program_id').html('<option value="">Loading...</option>');
            $.ajax({
                url: base_url + 'programs/getBySection',
                method: 'POST',
                data: {
                    section_id: section_id,
                    <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'
                },
                dataType: 'json',
                success: function (response) {
                    var program_id = '<?php echo isset($program_id) ? html_escape($program_id) : set_value("program_id"); ?>';
                    var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                    if (response.length > 0) {
                        $.each(response, function (index, program) {
                            var selected = (program_id == program.id) ? 'selected' : '';
                            html += '<option value="' + program.id + '" ' + selected + '>' + program.title + '</option>';
                        });
                    } else {
                        html += '<option value="" disabled>No programs found</option>';
                    }
                    $('#program_id').html(html);
                    if (program_id) $('#program_id').trigger('change');
                }
            });
        }
    });

    // When program changes, fetch classes
    $(document).on('change', '#program_id', function () {
        var section_id = $('#section_id').val();
        var program_id = $(this).val();
        $('#class_id').html('<option value="">Loading...</option>');
        libLoadSubjectGroups();
        $.ajax({
            url: base_url + 'classes/getBySectionAndProgram',
            method: 'POST',
            data: {
                section_id: section_id,
                program_id: program_id,
                <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'
            },
            dataType: 'json',
            success: function (response) {
                var class_id = '<?php echo isset($class_id) ? html_escape($class_id) : set_value("class_id"); ?>';
                var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                if (response && response.length > 0) {
                    $.each(response, function (index, classItem) {
                        var selected = (class_id == classItem.id) ? 'selected' : '';
                        html += '<option value="' + classItem.id + '" ' + selected + '>' +
                            (classItem.degree ? classItem.degree + ' - ' : '') + classItem.class + '</option>';
                    });
                } else {
                    html += '<option value="" disabled>No classes found</option>';
                }
                $('#class_id').html(html);
                libLoadSubjectGroups();
            }
        });
    });

    $(document).on('change', '#class_id', function () {
        libLoadSubjectGroups();
    });
</script>
