<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#282828" />
        <title>Login : <?php echo $name; ?></title>
        <link href="<?php echo base_url(); ?>uploads/school_content/admin_small_logo/<?php $this->setting_model->getAdminsmalllogo();?>" rel="shortcut icon" type="image/x-icon">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:400,100,300,500">
        <link rel="stylesheet" href="<?php echo base_url(); ?>backend/usertemplate/assets/bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" href="<?php echo base_url(); ?>backend/usertemplate/assets/font-awesome/css/font-awesome.min.css">
        <link rel="stylesheet" href="<?php echo base_url(); ?>backend/usertemplate/assets/css/form-elements.css">
        <link rel="stylesheet" href="<?php echo base_url(); ?>backend/usertemplate/assets/css/style.css">
        <link rel="stylesheet" href="<?php echo base_url(); ?>backend/usertemplate/assets/css/jquery.mCustomScrollbar.min.css">
    </head>
    <style>
        body, html {
    margin: 0;
    padding: 0;
    height: 100%;
    font-family: 'Roboto', sans-serif;
}

.login-page-wrapper {
    height: 100%;
    <?php 
    $user_bg = !empty($school['user_login_page_background']) ? $school['user_login_page_background'] : 'images.png';
    ?>
    background: url('<?php echo base_url(); ?>uploads/school_content/login_image/<?php echo $user_bg; ?>') no-repeat center center fixed;
    background-size: cover;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    
}

.login-container {
    background: rgba(255, 255, 255, 0.97);
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
    padding-right: 40px; /* Changed from left */
    padding-left: 12px;
    border: 1px solid #ccc;
}

.form-group.has-feedback {
    position: relative;
}

.form-group.has-feedback .form-control-feedback {
    position: absolute;
    top: 50%;
    right: 12px;
    transform: translateY(-50%);
    color: #aaa;
    pointer-events: none;
    font-size: 16px;
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

.captcha-group {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.captcha-img {
    flex: 1;
    margin-right: 10px;
    
}

.refresh-captcha {
    cursor: pointer;
    font-size: 18px;
    color: #2575fc;
}

    </style>

    <body>
    <div class="login-page-wrapper">
        <div class="login-container">
            <div class="logo text-center">
                <img src="<?php echo base_url(); ?>uploads/school_content/admin_logo/<?php echo $this->setting_model->getAdminlogo(); ?>" alt="Logo">
            </div>
            <h3><?php echo $this->lang->line('user_login'); ?></h3>

            <?php if (isset($error_message)) { ?>
                <div class='alert alert-danger'><?php echo $error_message; ?></div>
            <?php } ?>

            <?php if ($this->session->flashdata('message')) { ?>
                <div class='alert alert-success'><?php echo $this->session->flashdata('message'); ?></div>
            <?php } ?>

            <form action="<?php echo site_url('site/userlogin') ?>" method="post">
                <?php echo $this->customlib->getCSRF(); ?>

                <div class="form-group has-feedback">
                    <input type="text" name="username" value="<?php echo set_value('username'); ?>" placeholder="<?php echo $this->lang->line('username'); ?>" class="form-control" id="email">
                    <span class="fa fa-envelope form-control-feedback"></span>
                    <span class="text-danger"><?php echo form_error('username'); ?></span>
                </div>

                <div class="form-group has-feedback">
                    <input type="password" name="password" value="<?php echo set_value('password'); ?>" placeholder="<?php echo $this->lang->line('password'); ?>" class="form-control" id="password" autocomplete="current-password">
                    <button type="button" class="toggle-password" data-target="password" aria-label="Show password" title="Show password">
                        <i class="fa fa-eye" aria-hidden="true"></i>
                    </button>
                    <span class="text-danger"><?php echo form_error('password'); ?></span>
                </div>

                <?php if ($is_captcha) { ?>
                    <div class="form-group captcha-group">
                        <div class="captcha-img"><?php echo $captcha_image; ?></div>
                        <span class="fa fa-refresh refresh-captcha" title="<?php echo $this->lang->line('refresh_captcha') ?>" onclick="refreshCaptcha()"></span>
                    </div>
                    <div class="form-group">
                        <input type="text" name="captcha" placeholder="<?php echo $this->lang->line('captcha'); ?>" autocomplete="off" class="form-control">
                        <span class="text-danger"><?php echo form_error('captcha'); ?></span>
                    </div>
                <?php } ?>

                <button type="submit" class="btn btn-primary"><?php echo $this->lang->line('sign_in'); ?></button>
            </form>

            <a href="<?php echo site_url('site/ufpassword') ?>" class="forgot"><i class="fa fa-key"></i> <?php echo $this->lang->line('forgot_password'); ?></a>
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
<script type="text/javascript">
    function refreshCaptcha(){
        $.ajax({
            type: "POST",
            url: "<?php echo base_url('site/refreshCaptcha'); ?>",
            data: {},
            success: function(captcha){
                $("#captcha_image").html(captcha);
            }
        });
    }
</script>
<script>
    function copy(email, password)
    {
        document.getElementById("email").value = email;
        document.getElementById("password").value = password;
    }
</script>

