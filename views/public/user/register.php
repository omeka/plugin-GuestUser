<?php
$js = "
var guestUserPasswordAgainText = '" . __('Password again for match') . "';
var guestUserPasswordsMatchText = '" . __('Passwords match!') . "';
var guestUserPasswordsNoMatchText = '" . __("Passwords do not match!") . "'; ";

queue_js_string($js);
queue_js_file('guest-user-password');
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
