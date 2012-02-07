<?php
$user = current_user();
$pageTitle =  ucfirst($user->username) . ' Dashboard';
head(array('title' => $pageTitle));
?>
<h1><?php echo $pageTitle; ?></h1>

<div id='primary'>
<?php echo flash(); ?>
<p>Browse and manage your work here.</p>
<?php foreach($widgets as $index=>$widget): ?>
<div class='guest-user-widget <?php if(is_odd($index)): ?>guest-user-widget-odd <?php else:?>guest-user-widget-even<?php endif;?>'>
<?php echo $widget; ?>
</div>
<?php endforeach; ?>

</div>
<?php foot(); ?>
