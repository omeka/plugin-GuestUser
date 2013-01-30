<?php

echo head(array('title' => 'Stale Token'));
?>
<h1>Stale Token</h1>

<div id='primary'>
<?php echo flash(); ?>
<p>Your temporary access to the site has expired. 
Please check your email for the link to follow to confirm your registration.</p>

<p>You have been logged about, but can continue browsing the site.</p>
</div>

<?php echo foot(); ?>