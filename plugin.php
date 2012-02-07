<?php
require_once(HELPERS);
define('GUEST_USER_PLUGIN_DIR', PLUGIN_DIR . '/GuestUser');
require_once(GUEST_USER_PLUGIN_DIR . '/GuestUserPlugin.php');
$gu = new GuestUser();
$gu->setUp();

?>