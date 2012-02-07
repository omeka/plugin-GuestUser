<?php
$pageTitle = 'Change Password';
head(array('bodyclass' => 'change-password', 'title' => $pageTitle));
?>
<h1><?php echo $pageTitle; ?></h1>
<div id='primary'>
<?php echo flash(); ?>
<?php echo $this->form; ?>
<p id='confirm'></p>
<?php foot(); ?>
</div>