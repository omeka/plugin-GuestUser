<?php
$pageTitle = 'Update Account';
head(array('bodyclass' => 'update-account', 'title' => $pageTitle));
?>
<h1><?php echo $pageTitle; ?></h1>
<div id='primary'>
<?php echo flash(); ?>
<?php echo $this->form; ?>
<?php foot(); ?>
</div>