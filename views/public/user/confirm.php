<?php
$head = array('title' => __('Confirmation Error'));
echo head($head);
?>
<h1><?php echo $head['title']?></h1>

<div id='primary'>
<?php echo flash(); ?>
</div>

<?php echo foot(); ?>