<?php
queue_js_file('guest-user-password');
$pageTitle = 'Register';
echo head(array('bodyclass' => 'register', 'title' => $pageTitle));
?>
<h1><?php echo $pageTitle; ?></h1>
<div id='primary'>
<div id='capabilities'>
<?php echo get_option('guest_user_capabilities'); ?>
</div>
<?php echo flash(); ?>
<?php echo $this->form; ?>
<p id='confirm'></p>
<?php echo foot(); ?>
</div>