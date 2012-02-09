<div class="field">
    <div class="inputs">
        <p class='explanation'>Add some text to the registration screen so people will know what they get for registering.
        As you enable and configure plugins that make use of the guest user, please give them guidance
        about what they can and cannot do.
        </p>
        <textarea name="guest_users_capabilities" ><?php echo get_option('guest_user_capabilities'); ?></textarea>
        <br/>
        <br/>
        <p class='explanation'>Add a shorter version to use as a dropdown from the user bar.
        If empty, no dropdown will appear.
        </p>
        <textarea name="guest_user_short_capabilities" ><?php echo get_option('guest_user_short_capabilities'); ?></textarea>
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
        <p class='explanation'>The text to use for the 'Login' link in the user bar.</p>

        <br/>
        <label for="guest_user_register_text">Register link text</label>
        <input name="guest_user_register_text" type="text" value="<?php echo get_option('guest_user_register_text'); ?>" />
        <p class='explanation'>The text to use for the 'Register' link in the user bar.

        <br/>
        <label for="guest_user_logged_in_text">Logged in text</label>
        <input name="guest_user_logged_in_text" type="text" value="<?php echo get_option('guest_user_logged_in_text'); ?>" />
        <p class='explanation'>The text for the user bar when a user is logged in. If blank, the current username will be shown.</p>
    </div>
</div>