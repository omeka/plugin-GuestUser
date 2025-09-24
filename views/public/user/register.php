<?php
queue_js_file('guest-user-password');
queue_css_file('skeleton');
$pageTitle = get_option('guest_user_register_text') ? get_option('guest_user_register_text') : __('Register');
echo head(array('bodyclass' => 'register guest-user', 'title' => $pageTitle));
?>
<h1><?php echo $pageTitle; ?></h1>
<?php if ($capabilities = get_option('guest_user_capabilities')): ?>
<div id='capabilities'>
<p>
<?php echo $capabilities; ?>
</p>
</div>
<?php endif; ?>
<?php echo flash(); ?>
<?php echo $this->form; ?>
<p id='confirm'></p>
<?php echo foot(); ?>
