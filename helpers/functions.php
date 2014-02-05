<?php

function guest_user_widget($widget)
{
    if(is_array($widget)) {
        $html = "<h2 class='guest-user-widget-label'>" . $widget['label'] . "</h2>";
        $html .= $widget['content'];
        return $html;
    } else {
        return $widget;
    }
}

function guest_user_user_added($user)
{
    $activationTable = get_db()->getTable('UsersActivations');
    $select = $activationTable->getSelect();
    if($user->id == 1) {
        return __("User 1");
    }
    $select->where('user_id = ?', $user->id);
    $activation = $activationTable->fetchObject($select);
    if(empty($activation)) {
        return __("Never activated");
    }
    return $activation->added;
}

?>
