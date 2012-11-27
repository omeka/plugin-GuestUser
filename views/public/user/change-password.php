<?php

queue_js_file('guest-user-password');
$pageTitle = 'Change Password';
echo head(array('bodyclass' => 'change-password', 'title' => $pageTitle));
?>
<h1><?php echo $pageTitle; ?></h1>
<div id='primary'>
<?php echo flash(); ?>
<?php echo $this->form; ?>
<p id='confirm'></p>
<?php echo foot(); ?>
</div>