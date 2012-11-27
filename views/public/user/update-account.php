<?php

queue_js_file('guest-user-password');
$pageTitle = 'Update Account';
echo head(array('bodyclass' => 'update-account', 'title' => $pageTitle));
?>
<h1><?php echo $pageTitle; ?></h1>
<div id='primary'>
<?php echo flash(); ?>
<?php echo $this->form; ?>
<?php echo foot(); ?>
</div>