<?php $view = get_view(); ?>

<div class="field">
    <div class="two columns alpha">
        <label>Registration Features</label>    
    </div>
    <div class="inputs five columns omega" >
        <p class='explanation'>Add some text to the registration screen so people will know what they get for registering.
        As you enable and configure plugins that make use of the guest user, please give them guidance
        about what they can and cannot do.
        </p>
        <div class="input-block">
            <textarea name="guest_user_capabilities" ><?php echo get_option('guest_user_capabilities'); ?></textarea>
        </div>

    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label>Short Registration Features</label>    
    </div>
    <div class="inputs five columns omega" >
        <p class='explanation'>Add a shorter version to use as a dropdown from the user bar.
        If empty, no dropdown will appear.
        </p>        
        <div class="input-block">
            <textarea name="guest_user_short_capabilities" ><?php echo get_option('guest_user_short_capabilities'); ?></textarea>
        </div>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label>Dashboard Label</label>    
    </div>    
    <div class="inputs five columns omega">
        <p class='explanation'>The text to use for the label on the user's dashboard</p>
        <div class="input-block">        
            <input name="guest_user_dashboard_label" type="text" value="<?php echo get_option('guest_user_dashboard_label'); ?>" />        
        </div>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label>Login Text</label>    
    </div>    
    <div class="inputs five columns omega">
        <p class='explanation'>The text to use for the 'Login' link in the user bar.</p>
        <div class="input-block">        
        <input name="guest_user_login_text" type="text" value="<?php echo get_option('guest_user_login_text'); ?>" />        
        </div>
    </div>
</div>
        
<div class="field">
    <div class="two columns alpha">
        <label>Register Text</label>    
    </div>    
    <div class="inputs five columns omega">
        <p class='explanation'>The text to use for the 'Register' link in the user bar.
        <div class="input-block">        
            <input name="guest_user_register_text" type="text" value="<?php echo get_option('guest_user_register_text'); ?>" />        
        </div>
    </div>
</div>


<div class="field">
    <div class="two columns alpha">
        <label>Allow open registration?</label>    
    </div>
    <div class="inputs five columns omega">
        <p class="explanation">Allow guest user registration without administrator approval?</p>
        <div class="input-block">        
            <?php
                $checked = ( get_option('guest_user_open') == 1) ;
                if($checked) {
                    $options = array('checked'=>'checked');
                } else {
                    $options = array();
                }
                echo $view->formCheckbox('guest_user_open', null, $options);                
            ?>
        </div>
    </div>
</div>
        
        
<div class="field">
    <div class="two columns alpha">
        <label>Allow instant access?</label>    
    </div>
    <div class="inputs five columns omega">
        <p class="explanation">Allow instant access for 20 minutes for new users</p>
        <div class="input-block">        
            <?php
                $checked = ( get_option('guest_user_instant_access') == 1 );
                if($checked) {
                    $options = array('checked'=>'checked');
                } else {
                    $options = array();
                }            
                echo $view->formCheckbox('guest_user_instant_access', null, $options);
            ?>
        </div>
    </div>
</div>


<div class="field">
    <div class="two columns alpha">
        <label>Require ReCaptcha?</label>    
    </div>
    <div class="inputs five columns omega">
        <p class="explanation">Check this to require passing a ReCaptcha test when registering</p>
        <div class="input-block">        
            <?php
                $checked = ( get_option('guest_user_recaptcha') == 1);
                if($checked) {
                    $options = array('checked'=>'checked');
                } else {
                    $options = array();
                }
                echo $view->formCheckbox('guest_user_recaptcha', null, $options);
            ?>           
        </div>
    </div>
</div>

