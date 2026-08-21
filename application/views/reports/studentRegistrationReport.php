<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-line-chart"></i> <?php echo $this->lang->line('reports'); ?> 
            <small><?php echo $this->lang->line('filter_by_name1'); ?></small>
        </h1>
    </section>
    
    <!-- Main content -->
    <section class="content">
        <?php $this->load->view('reports/_studentinformation');?>
        <div class="row">
            <div class="col-md-12">
                <div class="box removeboxmius">
                    <div class="box-header ptbnull"></div>
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?>
                        </h3>
                    </div>
                    
                    <div class="box-body">
                        <form role="form" action="<?php echo site_url('report/studentregistrationreportvalidation') ?>" method="post" class="" id="reportform">
                            <div class="row">
                                <?php echo $this->customlib->getCSRF(); ?>
                                <div class="col-sm-6 col-md-3">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('academic_session'); ?></label>
                                        <small class="req"> *</small>
                                        <select id="session_id" name="session_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php foreach ($sessions as $session) { ?>
                                                <option value="<?php echo $session['id']; ?>" 
                                                        <?php if (set_value('session_id') == $session['id']) { echo "selected=selected"; } ?>>
                                                    <?php echo htmlspecialchars($this->customlib->convertSessionToBSIfNeeded($session['session'])); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                        <span class="text-danger" id="error_session_id"></span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <button type="submit" name="search" value="search_filter" 
                                                class="btn btn-primary btn-sm checkbox-toggle pull-right">
                                            <i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?>
                                        </button>
                                    </div>
                                </div>
                            </div><!--./row-->
                        </form>
                    </div><!--./box-body-->
                    
                    <div class="">
                        <div class="box-header ptbnull"></div>
                        <div class="box-header ptbnull">
                            <h3 class="box-title titlefix">
                                <i class="fa fa-users"></i> <?php echo $this->lang->line('student_registration_report'); ?>
                            </h3>
                        </div>
                        
                        <div class="box-body table-responsive">
                            <div class="download_label"><?php echo $this->lang->line('student_registration_report'); ?></div>
                            <div id="printableArea">
                                <table class="table table-striped table-bordered table-hover student-registration-list" 
                                       data-export-title="<?php echo $this->lang->line('student_registration_report'); ?>">
                                    <thead>
                                        <tr>
                                            <th><?php echo $this->lang->line('academic_session'); ?></th>
                                            <th><?php echo $this->lang->line('admission_no'); ?></th>
                                            <th><?php echo $this->lang->line('roll_no'); ?></th>
                                            <th><?php echo $this->lang->line('student_name'); ?></th>
                                            <th><?php echo $this->lang->line('gender'); ?></th>
                                            <th><?php echo $this->lang->line('district'); ?></th>
                                            <th><?php echo $this->lang->line('localLevel'); ?></th>
                                            <th><?php echo $this->lang->line('caste'); ?></th>
                                            <th><?php echo $this->lang->line('faculty'); ?></th>
                                            <th><?php echo $this->lang->line('level'); ?></th>
                                            <th><?php echo $this->lang->line('program'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- DataTable will populate data here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div><!--./box box-primary -->
                </div><!-- ./col-md-12 -->
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">
function initStudentRegistrationTable(session_id) {
    var baseUrl = '<?php echo base_url(); ?>';
    
    $('.student-registration-list').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": baseUrl + "report/dtstudentregistrationreportlist",
            "type": "POST",
            "data": {
                "session_id": session_id
            }
        },
        "columns": [
            { "data": "academic_session" },
            { "data": "admission_no" },
            { "data": "roll_no" },
            { 
                "data": "student_name",
                "render": function(data, type, row) {
                    return '<a href="' + baseUrl + 'student/view/' + row.id + '">' + data + '</a>';
                }
            },
            { "data": "gender" },
            { "data": "district" },
            { "data": "local_level" },
            { "data": "cast" },
            { "data": "faculty" },
            { "data": "level" },
            { "data": "program" }
        ],
        "responsive": true,
        "dom": 'Bfrtip',
        "buttons": [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ],
        "lengthMenu": [10, 25, 50, 100],
        "pageLength": 25
    });
}

$(document).ready(function() {
    // Form submission
    $('#reportform').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var url = form.attr('action');
        
        $.ajax({
            url: url,
            type: "POST",
            dataType: 'json',
            data: form.serialize(),
            success: function(response) {
                if (response.status) {
                    // Destroy existing DataTable if it exists
                    if ($.fn.DataTable.isDataTable('.student-registration-list')) {
                        $('.student-registration-list').DataTable().destroy();
                    }
                    
                    // Initialize new DataTable
                    initStudentRegistrationTable(response.session_id);
                } else {
                    // Show validation errors
                    $.each(response.error, function(key, value) {
                        $('#error_' + key).html(value);
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    });
});
</script>