<?php
$user = current_user();
$pageTitle =  get_option('guest_user_dashboard_label');
echo head(array('title' => $pageTitle, 'bodyClass' => 'user-dashboard guest-user'));
?>
<h1><?php echo $pageTitle; ?></h1>

<?php echo flash(); ?>

<div class="guest-user-widgets">
    <?php foreach($widgets as $index=>$widget): ?>
    <div class='guest-user-widget'>
    <?php echo GuestUserPlugin::guestUserWidget($widget); ?>
    </div>
    <?php endforeach; ?>
</div>
<?php echo foot(); ?>
