<div class="field">
    <div class="inputs">
        <p>Add some text to the registration screen so people will know what they get for registering</p>
        <p>As you enable and configure plugins that make use of the guest user, please give them guidance
        about what they can and cannot do.
        </p>
        <textarea name="guest_users_capabilities" ><?php echo get_option('guest_users_capabilities'); ?></textarea>
    </div>
</div>


<div class="field">
    <div class="inputs">
        <p>Allow guest user registration without administrator approval?</p>
        <label for="guest_users_open">Yes</label>
        <?php
            $checked = ( get_option('guest_user_open') == 'on') ? true : false;
        ?>
        <input name="guest_user_open" type="checkbox" <?php if($checked) {echo "checked='checked'"; } ?>  />
        <br/>
        <br/>
        <label for="guest_user_login_text">Login link text</label>
        <input name="guest_user_login_text" type="text" value="<?php echo get_option('guest_user_login_text'); ?>" />


        <br/>
        <label for="guest_user_register_text">Register link text</label>
        <input name="guest_user_register_text" type="text" value="<?php echo get_option('guest_user_register_text'); ?>" />

        <br/>
        <label for="guest_user_logged_in_text">Logged in text</label>
        <input name="guest_user_logged_in_text" type="text" value="<?php echo get_option('guest_user_logged_in_text'); ?>" />
    </div>
</div>