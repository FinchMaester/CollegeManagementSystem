<style type="text/css">
    @media print {
        .no-print, .no-print * {
            display: none !important;
        }
    }
</style>

<div class="content-wrapper" style="min-height: 946px;">
    <section class="content-header">
        <h1><i class="fa fa-book"></i> <?php echo $title; ?></h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary" id="tachelist">
                    <div class="box-header ptbnull">
                        <h3 class="box-title titlefix"><?php echo $title_list; ?></h3>
                        <div class="box-tools pull-right">
                            <a href="<?php echo site_url('admin/member'); ?>" class="btn btn-primary btn-sm">
                                <i class="fa fa-arrow-left"></i> Back to Members
                            </a>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="mailbox-controls"></div>
                        <div class="table-responsive mailbox-messages overflow-visible">
                            <div class="download_label"><?php echo $title_list; ?></div>
                            <table class="table table-striped table-bordered table-hover example">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('book_title'); ?></th>
                                        <th><?php echo $this->lang->line('book_no'); ?></th>
                                        <th><?php echo $this->lang->line('author'); ?></th>
                                        <th><?php echo $this->lang->line('issue_date'); ?></th>
                                        <th><?php echo $this->lang->line('due') . " " . $this->lang->line('return_date'); ?></th>
                                        <th><?php echo $this->lang->line('return_date'); ?></th>
                                        <th><?php echo $this->lang->line('member_id'); ?></th>
                                        <th><?php echo $this->lang->line('library_card_no'); ?></th>
                                        <th><?php echo $this->lang->line('admission_no'); ?></th>
                                        <th><?php echo $this->lang->line('member_name'); ?></th>
                                        <th><?php echo $this->lang->line('member_type'); ?></th>
                                        <th><?php echo $this->lang->line('status'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (empty($issues)) {
                                        ?>
                                        <tr>
                                            <td colspan="12" class="text-center text-muted" style="padding:15px;">
                                                No issues found.
                                            </td>
                                        </tr>
                                        <?php
                                    } else {
                                        foreach ($issues as $issue) {
                                            // Format member name
                                            if ($issue['member_type'] == 'student') {
                                                $member_name = trim(ucwords($issue['fname'] . ' ' . (isset($issue['mname']) ? $issue['mname'] . ' ' : '') . $issue['lname']));
                                                $admission_no = isset($issue['admission']) ? $issue['admission'] : '';
                                            } else {
                                                $member_name = trim(ucwords($issue['fname'] . ' ' . $issue['lname']));
                                                $admission_no = isset($issue['admission']) ? $issue['admission'] : '';
                                            }
                                            
                                            // Check if overdue
                                            $is_overdue = false;
                                            if ($issue['is_returned'] == 0 && !empty($issue['duereturn_date'])) {
                                                $is_overdue = $this->customlib->isLibraryDueDateOverdue($issue['duereturn_date']);
                                            }
                                            ?>
                                            <tr <?php if ($is_overdue) { ?>class="danger"<?php } ?>>
                                                <td><?php echo isset($issue['book_title']) ? $issue['book_title'] : 'N/A'; ?></td>
                                                <td><?php echo isset($issue['book_no']) ? $issue['book_no'] : 'N/A'; ?></td>
                                                <td><?php echo isset($issue['author']) ? $issue['author'] : 'N/A'; ?></td>
                                                <td>
                                                    <?php
                                                    if (!empty($issue['issue_date'])) {
                                                        $d = $this->customlib->formatLibraryDateBsForDisplay($issue['issue_date']);
                                                        echo $d !== '' ? htmlspecialchars($d) : 'N/A';
                                                    } else {
                                                        echo 'N/A';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    if (!empty($issue['duereturn_date'])) {
                                                        $d = $this->customlib->formatLibraryDateBsForDisplay($issue['duereturn_date']);
                                                        echo $d !== '' ? htmlspecialchars($d) : 'N/A';
                                                    } else {
                                                        echo 'N/A';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    if (!empty($issue['return_date'])) {
                                                        $d = $this->customlib->formatLibraryDateBsForDisplay($issue['return_date']);
                                                        echo $d !== '' ? htmlspecialchars($d) : '-';
                                                    } else {
                                                        echo '-';
                                                    }
                                                    ?>
                                                </td>
                                                <td><?php echo isset($issue['members_id']) ? $issue['members_id'] : 'N/A'; ?></td>
                                                <td><?php echo isset($issue['library_card_no']) ? $issue['library_card_no'] : 'N/A'; ?></td>
                                                <td><?php echo $admission_no ? $admission_no : '-'; ?></td>
                                                <td><?php echo $member_name ? $member_name : 'N/A'; ?></td>
                                                <td><?php echo isset($issue['member_type']) ? ucwords($issue['member_type']) : 'N/A'; ?></td>
                                                <td>
                                                    <?php
                                                    if ($issue['is_returned'] == 1) {
                                                        echo '<span class="label label-success">Returned</span>';
                                                    } else {
                                                        if ($is_overdue) {
                                                            echo '<span class="label label-danger">Overdue</span>';
                                                        } else {
                                                            echo '<span class="label label-warning">Issued</span>';
                                                        }
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

