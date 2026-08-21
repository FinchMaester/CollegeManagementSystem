

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#282828" />
        <title>Login : <?php echo $name; ?></title>        
        <link href="<?php echo base_url(); ?>uploads/school_content/admin_small_logo/<?php $this->setting_model->getAdminsmalllogo(); ?>" rel="shortcut icon" type="image/x-icon">
        <!-- CSS -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:400,100,300,500">
        <link rel="stylesheet" href="<?php echo base_url(); ?>backend/usertemplate/assets/bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" href="<?php echo base_url(); ?>backend/usertemplate/assets/font-awesome/css/font-awesome.min.css">
        <link rel="stylesheet" href="<?php echo base_url(); ?>backend/usertemplate/assets/css/form-elements.css">
        <link rel="stylesheet" href="<?php echo base_url(); ?>backend/usertemplate/assets/css/style.css">
        <link rel="stylesheet" href="<?php echo base_url(); ?>backend/usertemplate/assets/css/jquery.mCustomScrollbar.min.css">
    </head>
    <style>
        html, body {
    height: 100%;
    margin: 0;
    padding: 0;
    font-family: 'Roboto', sans-serif;
}

.login-admin-wrapper {
    height: 100%;
    <?php 
    $admin_bg = !empty($school['admin_login_page_background']) ? $school['admin_login_page_background'] : 'images.png';
    ?>
    background: url('<?php echo base_url(); ?>uploads/school_content/login_image/<?php echo $admin_bg; ?>') no-repeat center center fixed;
    background-size: cover;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.login-container {
    background: rgba(255, 255, 255, 0.95);
    padding: 40px 30px;
    border-radius: 12px;
    max-width: 400px;
    width: 100%;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.25);
    position: relative;
}

.form-group {
    position: relative;
    margin-bottom: 20px;
}

.form-control {
    width: 100%;
    height: 45px;
    border-radius: 8px;
    padding-right: 40px;
    padding-left: 12px;
    border: 1px solid #ccc;
}

.form-control-feedback {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #999;
    pointer-events: none;
}

.toggle-password {
    position: absolute;
    right: 12px;
    top: 22px;
    transform: translateY(-50%);
    color: #666;
    cursor: pointer;
    z-index: 2;
    font-size: 16px;
    background: none;
    border: none;
    padding: 0;
    line-height: 1;
}

.toggle-password:hover,
.toggle-password:focus {
    color: #2575fc;
    outline: none;
}

.btn-primary {
    width: 100%;
    padding: 12px;
    background-color: #2575fc;
    border: none;
    color: white;
    border-radius: 6px;
    font-size: 16px;
}

.btn-primary:hover {
    background-color: #1a5de8;
}

.forgot {
    display: block;
    margin-top: 15px;
    text-align: center;
    color: #2575fc;
    font-weight: 500;
}


    </style>
    
    <body class="login-admin-body">
    <div class="login-admin-wrapper">
        <div class="login-container">
            <!-- Insert logo -->
            <div class="text-center mb-3">
                <img src="<?php echo base_url(); ?>uploads/school_content/admin_logo/<?php echo $this->setting_model->getAdminlogo(); ?>" alt="Admin Logo" class="img-fluid" style="max-height: 80px;">
            </div>

            <h3 class="text-center bolds mb-4"><?php echo $this->lang->line('admin_login'); ?></h3>
            <h4 class="text-center"><?php echo $name; ?></h4>
            <h5 class="text-center">HEMIS System</h5>

            <!-- Alert messages -->
            <?php if (isset($error_message)) echo "<div class='alert alert-danger'>$error_message</div>"; ?>
            <?php if ($this->session->flashdata('message')): ?>
                <div class="alert alert-success"><?php echo $this->session->flashdata('message'); ?></div>
                <?php $this->session->unset_userdata('message'); ?>
            <?php endif; ?>
            <?php if ($this->session->flashdata('disable_message')): ?>
                <div class="alert alert-danger"><?php echo $this->session->flashdata('disable_message'); ?></div>
                <?php $this->session->unset_userdata('disable_message'); ?>
            <?php endif; ?>

            <!-- Login form -->
            <form action="<?php echo site_url('site/login') ?>" method="post">
                <?php echo $this->customlib->getCSRF(); ?>

                <div class="form-group has-feedback">
                    <input type="text" name="username" value="<?php echo set_value('username') ?>" class="form-control" placeholder="<?php echo $this->lang->line('username'); ?>">
                    <span class="fa fa-envelope form-control-feedback"></span>
                    <span class="text-danger"><?php echo form_error('username'); ?></span>
                </div>

                <div class="form-group has-feedback">
                    <input type="password" name="password" id="admin-password" value="<?php echo set_value('password') ?>" class="form-control" placeholder="<?php echo $this->lang->line('password'); ?>" autocomplete="current-password">
                    <button type="button" class="toggle-password" data-target="admin-password" aria-label="Show password" title="Show password">
                        <i class="fa fa-eye" aria-hidden="true"></i>
                    </button>
                    <span class="text-danger"><?php echo form_error('password'); ?></span>
                </div>


                <button type="submit" class="btn btn-primary"><?php echo $this->lang->line('sign_in'); ?></button>
            </form>

            <a href="<?php echo site_url('site/forgotpassword') ?>" class="forgot"><i class="fa fa-key"></i> <?php echo $this->lang->line('forgot_password'); ?>?</a>
        </div>
    </div>
</body>

</html>
<script type="text/javascript">
(function () {
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.toggle-password');
        if (!btn) {
            return;
        }
        var input = document.getElementById(btn.getAttribute('data-target'));
        if (!input) {
            return;
        }
        var icon = btn.querySelector('i');
        var showing = input.type === 'text';
        input.type = showing ? 'password' : 'text';
        if (icon) {
            icon.className = showing ? 'fa fa-eye' : 'fa fa-eye-slash';
        }
        btn.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
        btn.setAttribute('title', showing ? 'Show password' : 'Hide password');
    });
})();
</script>
