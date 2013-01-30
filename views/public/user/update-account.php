<?php

queue_js_file('guest-user-password');
queue_css_file('skeleton');
$css = "form > div { clear: both; padding-top: 10px;} .two.columns {width: 30%;}";
queue_css_string($css);
$pageTitle = 'Update Account';
echo head(array('bodyclass' => 'update-account', 'title' => $pageTitle));
?>
<h1><?php echo $pageTitle; ?></h1>
<div id='primary'>
<?php echo flash(); ?>
<?php echo $this->form; ?>
<?php echo foot(); ?>
</div>