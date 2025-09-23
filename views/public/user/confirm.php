<?php
$head = array('title' => __('Confirmation Error'));
echo head($head);
?>
<h1><?php echo $head['title']?></h1>

<?php echo flash(); ?>

<?php echo foot(); ?>