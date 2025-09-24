<?php
$head = array('title' => __('Confirmation Error'), 'bodyClass' => 'user-confirm guest-user');
echo head($head);
?>
<h1><?php echo $head['title']?></h1>

<?php echo flash(); ?>

<?php echo foot(); ?>