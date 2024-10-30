<?php
add_action('admin_menu', 'jabze_admin_menu');

function jabze_admin_init()
{
    global $wp_version;

    // all admin functions are disabled in old versions
    if (!function_exists('is_multisite') && version_compare($wp_version, '3.0', '<')) {

        function wordpress_version_warning()
        {
            echo '
            <div id="jabze-warning" class="updated fade"><p><strong>' . sprintf(__('jabze %s requires WordPress 3.0 or higher.'), JABZE_VERSION) . '</strong> ' . sprintf(__('Please <a href="%s">upgrade WordPress</a> to a current version'), 'http://codex.wordpress.org/Upgrading_WordPress') . '</p></div>
            ';
        }

        add_action('admin_notices', 'wordpress_version_warning');

        return;
    }
}

add_action('admin_init', 'jabze_admin_init');



function jabze_admin_warnings()
{
    global $jabze_domain, $jabze_access_token,$jabze_need_upgrade,$jabze_server_status,$jabze_server_status_msg;
    jabze_check_version();
    if($jabze_server_status==0 && (!(isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"])=="xmlhttprequest"))){


        function jabze_status_warning()
        {
            global $jabze_server_status_msg;
            echo '<div id="jabze-warning" class="updated fade"><p><strong>'.$jabze_server_status_msg.'</strong></p></div>';
        }

        add_action('admin_notices', 'jabze_status_warning');
    }else if($jabze_need_upgrade){
        function jabze_version_warning()
        {
            global $jabze_need_upgrade_msg;
                echo '<div class="updated" style="padding: 0; margin: 0; border: none; background: none;">
                        <style type="text/css">
    .jabze_activate{min-width:825px;border:1px solid #4F800D;padding:5px;margin:15px 0;background:#83AF24;background-image:-webkit-gradient(linear,0% 0,80% 100%,from(#83AF24),to(#4F800D));background-image:-moz-linear-gradient(80% 100% 120deg,#4F800D,#83AF24);-moz-border-radius:3px;border-radius:3px;-webkit-border-radius:3px;position:relative;overflow:hidden}.jabze_activate .aa_a{position:absolute;top:-5px;right:10px;font-size:140px;color:#769F33;font-family:Georgia, "Times New Roman", Times, serif;z-index:1}.jabze_activate .aa_button{font-weight:bold;border:1px solid #029DD6;border-top:1px solid #06B9FD;font-size:15px;text-align:center;padding:9px 0 8px 0;color:#FFF;background:#029DD6;background-image:-webkit-gradient(linear,0% 0,0% 100%,from(#029DD6),to(#0079B1));background-image:-moz-linear-gradient(0% 100% 90deg,#0079B1,#029DD6);-moz-border-radius:2px;border-radius:2px;-webkit-border-radius:2px}.jabze_activate .aa_button:hover{text-decoration:none !important;border:1px solid #029DD6;border-bottom:1px solid #00A8EF;font-size:15px;text-align:center;padding:9px 0 8px 0;color:#F0F8FB;background:#0079B1;background-image:-webkit-gradient(linear,0% 0,0% 100%,from(#0079B1),to(#0092BF));background-image:-moz-linear-gradient(0% 100% 90deg,#0092BF,#0079B1);-moz-border-radius:2px;border-radius:2px;-webkit-border-radius:2px}.jabze_activate .aa_button_border{border:1px solid #006699;-moz-border-radius:2px;border-radius:2px;-webkit-border-radius:2px;background:#029DD6;background-image:-webkit-gradient(linear,0% 0,0% 100%,from(#029DD6),to(#0079B1));background-image:-moz-linear-gradient(0% 100% 90deg,#0079B1,#029DD6)}.jabze_activate .aa_button_container{cursor:pointer;display:inline-block;background:#DEF1B8;padding:5px;-moz-border-radius:2px;border-radius:2px;-webkit-border-radius:2px;width:266px}.jabze_activate .aa_description{position:absolute;top:22px;left:285px;margin-left:25px;color:#E5F2B1;font-size:15px;z-index:1000}.jabze_activate .aa_description strong{color:#FFF;font-weight:normal}
                        </style>
                            <div class="jabze_activate">
                                <div class="aa_a">J</div>
                                <div class="aa_button_container">
                                    <div class="aa_button_border">
                                        <div class="aa_button"><a href="plugins.php" style="color:#fff;">Upgrade Jabze Chat</a></div>
                                    </div>
                                </div>
                                <div class="aa_description"><strong>Warning</strong> - '.$jabze_need_upgrade_msg .' .</div>
                            </div>
                    </div>
            ';
        }

        add_action('admin_notices', 'jabze_version_warning');
    }else if (!get_option('jabze_domain') && !$jabze_domain && !get_option('jabze_access_token') && !$jabze_access_token) {
        function jabze_warning()
        {
            global $hook_suffix;
            if ($hook_suffix == 'plugins.php' || $hook_suffix==  'jabze-chat_page_jabze_sync_user_conf') {
                $url = admin_url( 'admin.php?page=jabze-chat/jabze.php');
                echo '
                    <div class="updated" style="padding: 0; margin: 0; border: none; background: none;">
                        <style type="text/css">
    .jabze_activate{min-width:825px;border:1px solid #4F800D;padding:5px;margin:15px 0;background:#83AF24;background-image:-webkit-gradient(linear,0% 0,80% 100%,from(#83AF24),to(#4F800D));background-image:-moz-linear-gradient(80% 100% 120deg,#4F800D,#83AF24);-moz-border-radius:3px;border-radius:3px;-webkit-border-radius:3px;position:relative;overflow:hidden}.jabze_activate .aa_a{position:absolute;top:-5px;right:10px;font-size:140px;color:#769F33;font-family:Georgia, "Times New Roman", Times, serif;z-index:1}.jabze_activate .aa_button{font-weight:bold;border:1px solid #029DD6;border-top:1px solid #06B9FD;font-size:15px;text-align:center;padding:9px 0 8px 0;color:#FFF;background:#029DD6;background-image:-webkit-gradient(linear,0% 0,0% 100%,from(#029DD6),to(#0079B1));background-image:-moz-linear-gradient(0% 100% 90deg,#0079B1,#029DD6);-moz-border-radius:2px;border-radius:2px;-webkit-border-radius:2px}.jabze_activate .aa_button:hover{text-decoration:none !important;border:1px solid #029DD6;border-bottom:1px solid #00A8EF;font-size:15px;text-align:center;padding:9px 0 8px 0;color:#F0F8FB;background:#0079B1;background-image:-webkit-gradient(linear,0% 0,0% 100%,from(#0079B1),to(#0092BF));background-image:-moz-linear-gradient(0% 100% 90deg,#0092BF,#0079B1);-moz-border-radius:2px;border-radius:2px;-webkit-border-radius:2px}.jabze_activate .aa_button_border{border:1px solid #006699;-moz-border-radius:2px;border-radius:2px;-webkit-border-radius:2px;background:#029DD6;background-image:-webkit-gradient(linear,0% 0,0% 100%,from(#029DD6),to(#0079B1));background-image:-moz-linear-gradient(0% 100% 90deg,#0079B1,#029DD6)}.jabze_activate .aa_button_container{cursor:pointer;display:inline-block;background:#DEF1B8;padding:5px;-moz-border-radius:2px;border-radius:2px;-webkit-border-radius:2px;width:266px}.jabze_activate .aa_description{position:absolute;top:22px;left:285px;margin-left:25px;color:#E5F2B1;font-size:15px;z-index:1000}.jabze_activate .aa_description strong{color:#FFF;font-weight:normal}
                        </style>
                            <div class="jabze_activate">
                                <div class="aa_a">J</div>
                                <div class="aa_button_container">
                                    <div class="aa_button_border">
                                        <div class="aa_button"><a href="'.$url.'" style="color:#fff;">Activate your Jabze Chat account</a></div>
                                    </div>
                                </div>
                                <div class="aa_description"><strong>Almost done</strong> - activate your account .</div>
                            </div>
                    </div>
                   ';
            }
        }

        add_action('admin_notices', 'jabze_warning');
        return;
    }
}
jabze_admin_warnings();


add_action('admin_enqueue_scripts', 'jabze_load_js_and_css');

function jabze_load_js_and_css()
{
    global $hook_suffix;
    if (in_array($hook_suffix, array(
        //'plugins_page_jabze-key-config',
        //'jetpack_page_jabze-key-config',
        'toplevel_page_jabze-chat/jabze',
        'jabze-chat_page_jabze_sync_user_conf'
    ))
    ) {
        wp_register_style('admin.css', JABZE_PLUGIN_URL . 'admin.css', array(), '2.5.4.4');
        wp_enqueue_style('admin.css');

        wp_register_script('admin.js', JABZE_PLUGIN_URL . 'admin.js', array('jquery'), '2.5.4.6');
        wp_enqueue_script('admin.js');
        wp_localize_script('admin.js', 'WPjabze', array(
            'comment_author_url_nonce' => wp_create_nonce('comment_author_url_nonce')
        ));
    }
}


function jabze_nonce_field($action = -1)
{
    return wp_nonce_field($action);
}

$jabze_nonce = 'jabze-update-key';

function jabze_plugin_action_links($links, $file)
{
    if ($file == plugin_basename(dirname(__FILE__) . '/jabze.php')) {
        $links[] = '<a href="' . admin_url('admin.php?page=jabze-chat/jabze.php') . '">' . __('Settings') . '</a>';
        $links[] = '<a href="' . admin_url('admin.php?page=jabze_sync_user_conf') . '">' . __('Manage synchronization users') . '</a>';
    }

    return $links;
}

add_filter('plugin_action_links', 'jabze_plugin_action_links', 10, 2);

function jabze_sync_user_conf(){

    global  $jabze_domain, $jabze_access_token,$jabze_need_upgrade ,$jabze_auth_secret;

    $current_user = wp_get_current_user();

    if (0 != $current_user->ID){
        $jid=$current_user->user_login. '@'.$jabze_domain;
        $me = $current_user->user_login. '@'.$jabze_domain.'/jabze_wp_plugin';
        $pass = sha1($jabze_auth_secret.$jid);

    }

    if($jabze_domain && $jabze_access_token){

        jabze_load_js_and_css();
        $blogusers =jabze_getSyncUsers();
        $userCount=0;

        foreach($blogusers as $user){
            if($user->jabze_sync_status==1 || $user->jabze_sync_status==3){
                $userCount++;
            }
        }

        $allSync=count($blogusers)==$userCount?true:false;

    ?>
        <div class="wrap jabze">
            <div id="icon-upload" class="icon32"><br></div>
            <h2><?php _e(JABZE_APP_NAME); ?></h2>

            <div id="jabze-message" class="hidden">
                <progress max="100" id="bcs-progress"></progress>
                <br/>
                <span>Please wait a moment, initializing...<span>
            </div>

            <div id="manage-synchronization">
                <h2><?php _e('Synchronizing users with the Jabze Service');?></h2>

                <p><?php _e('Each new user will have an account created automatically with the backend Jabze service.
            But if you have a large number of users, we suggest you synchronize them with our service,
            so everyone shows up as "buddies" for everyone else immediately.
            Currently the following users have been synchronized with Jabze:');?></p>
                <table class="wp-list-table widefat fixed users" cellspacing="0">
                    <thead>
                    <tr>
                        <?php if (!$allSync): ?>
                            <th scope="col" id="cb" class="manage-column column-cb check-column" style=""><label
                                    class="screen-reader-text" for="cb-select-all-1">Select All</label><input
                                    id="jabze-select-all" type="checkbox" autocomplete="off" checked="checked"></th>
                        <?php endif;?>
                        <th scope="col" id="username" class="manage-column column-username" style="">Username</th>
                        <th scope="col" id="name" class="manage-column column-name" style="">Name</th>
                        <th scope="col" id="email" class="manage-column column-email " style="">E-mail</th>
                        <th scope="col" id="role" class="manage-column column-role" style="">Role</th>
                        <th scope="col" id="role" class="manage-column column-role" style="">Status</th>
                    </tr>
                    </thead>
                    <tbody id="the-list" data-wp-lists="list:user">
                    <?php foreach ($blogusers as $user):
                        ?>
                        <tr class="alternate">
                            <?php if (!$allSync): ?>
                                <td>
                                    <?php
                                        if(preg_match('/[^-\w_\.\s]/',trim($user->user_login)) !== 0){
                                            $user_name_valid = false;

                                        }else{
                                            $user_name_valid = true;
                                        }
                                    ?>
                                    <?php if (1 != $user->jabze_sync_status && 3 != $user->jabze_sync_status && $user_name_valid): ?>
                                        <input type="checkbox" autocomplete="off" name="users[]"
                                               value="<?php echo $user->ID; ?>" checked="checked">
                                    <?php endif;?>
                                </td>
                            <?php endif;?>
                            <td class="username column-username">
                                <strong><?php echo $user->user_login;?></strong>
                            </td>
                            <td class="name column-name"><?php echo $user->display_name;?></td>
                            <td class="email column-email"><?php echo $user->user_email;?></td>
                            <td class="role column-role"><?php echo $user->roles[0];?></td>
                            <td class="role column-role" style="width:25%;">
                                <?php
                                    if ( ! $allSync ) {
                                        switch ($user->jabze_sync_status) {
                                            case 0:
                                                $status_text = '<span style="color:red;">Not yet synchrnoized</span>';
                                                break;
                                            case 1:
                                                $status_text = '<span style="color: green;">Synchronized with Jabze</span>';
                                                break;
                                            case 2:
                                                $status_text = '<span style="color: red;">Synchronization error</span>';
                                                break;
                                            case 3:
                                                $status_text = '<span style="color: green;">Synchronized with Jabze</span>';
                                                break;
                                        }
                                        if(!$user_name_valid){
                                            $status_text = '<span style="color: red;">Username has special char (eg: @/#/!), will not sync</span>';
                                        }
                                    } else {
                                        $status_text = '<span style="color: green;">Synchronized with Jabze</span>';
                                    }
                                    echo $status_text;
                                ?>
                            </td>
                        </tr>
                    <?php
                    endforeach;
                    ?>
                    </tbody>
                </table>
                <br/><br/>
                <?php if (!$allSync): ?>
                    <button class="button button-primary <?php echo 1 == $jabze_need_upgrade ? 'need_updgrade' : '' ?>"
                            id="jabze-sync-user">Synchronize users
                    </button>
                <?php endif;?>
            </div>

        </div>



        <div>
            <p>
                For additional assistance in setting up this plugin, or resolving problems in using it. Please feel free to
                contact us at support@jabze.com, or visit our
                <a href="http://support.jabze.com" target="_blank">support site</a>.
            </p>
        </div>
<?php
	    jabze_load_mixpanel(true, "user");
    }
}
/*
 * jabze conf page
 */
function jabze_conf()
{
    global $jabze_nonce, $jabze_domain, $jabze_access_token,$jabze_need_upgrade,$jabze_enable_registration_suggestion;

    $current_user = wp_get_current_user();
    $current_user_email = $current_user->user_email;
    $enable_suggestion=$jabze_enable_registration_suggestion==="0"?false:true;


    jabze_load_js_and_css();
    $show_key_form = $jabze_domain && $jabze_access_token;
    $key_status = '';
    $token_status = '';
    $saved_ok = false;
    $ms = array();
    $messages = array(
        'key_empty' => array('class' => 'updated fade', 'text' => __('Please enter your domain name')),
        'token_empty' => array('class' => 'updated fade', 'text' => __('Please enter your activation key')),
    );

    $jabze_intro = 'Jabze Chat is an IM service allowing all your registered users to easily chat with each other when they are on your site,
     with a smart pop-up window on the lower right corner of the browser. All such users appear as "buddies" to each other automatically. The configuration of the plug-in is quite simple and straightforward:';

    ?>
    <div class="wrap jabze">
        <div id="icon-upload" class="icon32"><br></div>
        <h2><?php _e(JABZE_APP_NAME); ?></h2>
        <div id="jabze-message" class="hidden">
            <progress  max="100" id="bcs-progress"></progress><br/>
            <span>Please wait a moment, initializing...<span>
        </div>
        <div id="jabze-no-key" class="<?php echo $show_key_form ? 'hidden' : ''; ?>">
            <p><?php
                //TODO jabze
                _e($jabze_intro . 'To use Jabze Chat you need to activate this plugin. '); ?></p>
            <p><?php _e('We will gather your site name, site URL and admin email. We use this information only to generate an activation key. We absolutely do not share this information with any other 3rd parties.'); ?></p>
            <p><?php _e('To get started, please verify email address below. We will email you an "email verification code"
               to continue with the configuration steps.
		Please note that a Jabze domain will be created to match your email domain name (e.g., @acme-corp.com), if 
		a Jabze domain already exists for your email domain, we will require that you already have an admin
		account using your email address. To manage your Jabze service, sign on to www.jabze.com.'); ?></p>
            <table class="form-table">
                <tbody>
                <tr>
                    <th><label for="key"><?php _e('Work Email');?></label></th>
                    <td>
                        <input id="jabze-email" name="jabze_domain" type="text" disabled="disabled" value="<?php echo $current_user_email;?>" class="regular-text"><span style="color: red;">*</span>
                        <p class="need-key description"><?php
                            printf(__('You must enter a valid email address'));?></p>
                    </td>
                </tr>
                </tbody>
            </table>
            <p><input  type="checkbox" autocomplete="off" name="agree" id="agreed"/>
		<?php _e('I agree to share site name, URL and admin email information with the Jabze service.'); ?> </p>
            <input type="submit" class="button button-primary <?php echo 1==$jabze_need_upgrade?'need_updgrade':''?>" id="jabze-send-code"  value="<?php echo esc_attr(__('Continue to verify your email')); ?>"/>
            <br/>
            <br/>
           <!-- <a href="javascript:;"  class="switch-have-key"><?php /*_e('I already have an activation key'); */?></a>-->
        </div>
        <div id="jabze-verify" class="hidden">
            <p><?php _e('Please enter the email verification code below (please check your email inbox and spam folder).'); ?></p>
            <table class="form-table">
                <tbody>
                <tr>
                    <th><label for="key"><?php _e('Email Verification Code');?></label></th>
                    <td>
                        <input id="jabze-verify-code"  type="text" class="regular-text <?php echo 1==$jabze_need_upgrade?'need_updgrade':''?>"><span style="color: red;">*</span>
                    </td>
                </tr>
                </tbody>
            </table>

            <input type="submit" class="button button-primary <?php echo 1==$jabze_need_upgrade?'need_updgrade':''?>" id="create-new-jabze-key" name='create-new' value="<?php echo esc_attr(__('Activate this site')); ?>"/>
        </div>

    <div id="jabze-finish" class="hidden">
        <h2><?php _e('Congratulations!'); ?> </h2>
        <p><?php _e('You have finished setting up Jabze Chat, now your registered users will be able to chat with each
        other easily on your site, using the pop-up icon on the lower-right corner.'); ?></p>
        <p><?php _e('If you have a large number of users, we suggest you synchronize them with our service, so everyone shows up as "buddies"
	for everyone else. Otherwise, each user will be added to the collective buddy list when he/she signs into this WordPress site
	for the first time.'); ?></p>
        <button id="jabze-complete-setup" class="button button-primary" onclick="window.location.reload();"><?php _e('Synchronize all registered users'); ?></button>
    </div>
    <div id="jabze-get-back-setep1" class="hidden">
        <h2><?php _e('Retrieving activation key by email'); ?> </h2>
        <table class="form-table">
            <tbody>
            <tr>
                <th><label for="key"><?php _e('Work Email');?></label></th>
                <td>
                    <input id="jabze-uname" name="jabze_uname" type="text" class="regular-text" style="width: 150px;">@<?php echo $jabze_domain;?><span style="color: red;">*</span>
                    <p class="need-key description"><?php printf(__('You must enter a valid work email address'));?></p>
                </td>
            </tr>
            </tbody>
        </table>
        <input type="submit" class="button button-primary <?php echo 1==$jabze_need_upgrade?'need_updgrade':''?>" id="jabze-getback-code"  value="<?php echo esc_attr(__('Continue to verify your email')); ?>"/>
    </div>
    <div id="jabze-get-back-setep2" class="hidden">
        <p><?php _e('Please enter the email verification code below (please check your email inbox and spam folder).'); ?></p>
        <table class="form-table">
            <tbody>
            <tr>
                <th><label for="key"><?php _e('Email Verification Code');?></label></th>
                <td>
                    <input id="jabze-verify-backcode"  type="text" class="regular-text <?php echo 1==$jabze_need_upgrade?'need_updgrade':''?>"><span style="color: red;">*</span>
                </td>
            </tr>
            </tbody>
        </table>
        <input type="submit" class="button button-primary <?php echo 1==$jabze_need_upgrade?'need_updgrade':''?>" id="get-back-jabze-key"  value="<?php echo esc_attr(__('Get activation key back')); ?>"/>
    </div>
    <div id="jabze-have-key" class="<?php echo $show_key_form ? '' : 'hidden'; ?>">
            <?php if (!empty($_POST['update-bcs-key']) && $saved_ok) : ?>
                <div id="message" class="updated fade"><p><strong><?php _e('Settings saved.') ?></strong></p></div>
            <?php endif; ?>
            <?php foreach ($ms as $m) : ?>
                <div class="<?php echo $messages[$m]['class']; ?>"><p>
                        <strong><?php echo $messages[$m]['text']; ?></strong></p></div>
            <?php endforeach; ?>

            <p><?php _e($jabze_intro); ?></p>

            <table class="form-table">
                <tbody>
                <tr>
                    <th><label for="key"><?php _e('Email Domain Name');?></label></th>
                    <td>
                        <input id="jabze_domain" name="jabze_domain" type="text" size="15" maxlength="64"
                               value="<?php echo esc_html($jabze_domain); ?>"
                               class="regular-text code <?php echo $key_status; ?>" disabled="disabled">

                        <div
                            class="under-input key-status <?php echo $key_status; ?>"><?php echo ucfirst($key_status);?></div>
                        <p class="need-key description">The Jabze service uses this domain name to identify your organization, it is derived from the email address of the admin user installing the Jabze Chat plugin.  <br/>
                            If you wish to use a different domain name, please uninstall the plugin, modify your email address, and re-install the plugin
                        </p>
                    </td>
                </tr>
                <tr>
                    <th><label for="key"><?php _e('Encourage registration');?></label></th>
                    <td>
                        <fieldset><legend class="screen-reader-text"><span>Enable registration suggestion</span></legend>
                            <label title="Enable"><input type="radio" name="enable-registration-suggestion" value="1" <?php echo $enable_suggestion?'checked="checked"':"";?>> <span>Enable</span></label><br>
                            <label title="Disable"><input type="radio" name="enable-registration-suggestion" value="0" <?php echo !$enable_suggestion?'checked="checked"':"";?>> <span>Disable</span></label><br>
                        </fieldset>
                        <p class="description">When this is enabled, we will display a small non-intrusive pop-up window, encouraging visitors to sign in or register on this site, so that they can stay connected with other users</p>
                    </td>
                </tr>

                <tr>
                    <th><label for="key"><?php _e('Manage user synchronization');?></label></th>
                    <td>
                        <p class="description">
                            If you have a large number of users, we suggest that you synchronize them with our service, so that everyone becomes available to everyone else immediately.<br/> <a id="jabze-start-to-sync-user" href="<?php echo  admin_url( 'admin.php?page=jabze_sync_user_conf');?>"><?php _e('Start here') ?></a>
                        </p>
                    </td>
                </tr>


                <input id="jabze-access-token" name="jabze_access_token" type="hidden" size="40"
                       maxlength="32" value="<?php echo esc_html($jabze_access_token); ?>"
                       class="regular-text code <?php echo $token_status; ?> ">
                </tbody>
            </table>

             <p class="submit">
                <input type="submit"  id="save-changes" class="button button-primary <?php echo 1==$jabze_need_upgrade?'need_updgrade':''?>"
                       value="<?php _e('Save Changes'); ?>">
             </p>
            <?php jabze_nonce_field($jabze_nonce) ?>
            <p class="submit">
                If the plugin is not working properly, Please verify the local installation against the Jabze service.<br><br>
                <input type="submit"  id="update-bcs-key" class="button button-primary <?php echo 1==$jabze_need_upgrade?'need_updgrade':''?>"
                       value="<?php _e('Verify Jabze Chat installation'); ?>">
            </p>

    </div>

	<div>
        <p>
            For additional assistance in setting up this plugin, or resolving problems in using it. Please feel free to
            contact us at support@jabze.com, or visit our
            <a href="http://support.jabze.com" target="_blank">support site</a>.
        </p>
	</div>

    </div>
<?php
	jabze_load_mixpanel($show_key_form, "setting");
	if (!$show_key_form) {
		jabze_add_mixpanel_track();
	}
}

add_action('wp_ajax_my_action', 'my_action_callback');

function my_action_callback()
{
    if(isset($_POST['sendCode'])){
        $json=  jabze_sendCode();
        echo  json_encode($json);
    }

    if(isset($_POST['create_key'])){
     $json=  jabze_setup();
     echo  json_encode($json);
    }

    if(isset($_POST['update_key'])){
        $json=  jabze_check_key_status();
        echo  json_encode($json);
    }

    if(isset($_POST['sync'])){
        $userIDs=$_POST['syncUsers'];
        $json=  jabze_sync($userIDs,'sync');
        echo  json_encode($json);
    }

    if(isset($_POST['save_changes'])){
       // $userIDs=$_POST['syncUsers'];
        $json = jabze_enable_suggestion($_POST['save_changes']);
        echo  json_encode($json);
    }


   die(); // this is required to return a proper result
}

function jabze_admin_menu()
{
    if (class_exists('Jetpack')) {
        add_action('jetpack_admin_menu', 'jabze_load_menu');
    } else {
        jabze_load_menu();
    }
}

function jabze_load_menu()
{
    $icon =  plugins_url( "icon.png" , __FILE__ ) ;
    add_menu_page(__(JABZE_APP_NAME), __(JABZE_APP_NAME), 10, dirname( __FILE__ ) . '/jabze.php', 'jabze_conf', $icon);
    add_submenu_page(dirname( __FILE__ ) . '/jabze.php', 'Settings', 'Settings', 'manage_options', dirname( __FILE__ ) . '/jabze.php', 'jabze_conf');
    add_submenu_page(dirname( __FILE__ ) . '/jabze.php', 'Manage users', 'Manage users', 'manage_options', 'jabze_sync_user_conf', 'jabze_sync_user_conf');

}

function jabze_load_mixpanel($show, $type) {
	global $jabze_domain;

	if (strpos(JABZE_RESTFUl_URL, 'wpsvc')) {
		$code = JABZE_PROD_CODE;
	} else {
		$code = JABZE_DEV_CODE;
	}

	$current_user = wp_get_current_user();
	$user_login_name = str_replace(" ", "_", strtolower($current_user->user_login));

	if ($jabze_domain) {
		$jid = $user_login_name.'@'.$jabze_domain;
	} else {
		$Jabze_domain_array = explode("@", $current_user->user_email);
		$current_user_domain_name = $Jabze_domain_array[1];
		$jid = $user_login_name.'@'.$current_user_domain_name;
	}
?>
	<!-- start MixPanel -->
	<script type="text/javascript">(function(e,b){if(!b.__SV){var a,f,i,g;window.mixpanel=b;a=e.createElement("script");a.type="text/javascript";a.async=!0;a.src=("https:"===e.location.protocol?"https:":"http:")+'//cdn.mxpnl.com/libs/mixpanel-2.2.min.js';f=e.getElementsByTagName("script")[0];f.parentNode.insertBefore(a,f);b._i=[];b.init=function(a,e,d){function f(b,h){var a=h.split(".");2==a.length&&(b=b[a[0]],h=a[1]);b[h]=function(){b.push([h].concat(Array.prototype.slice.call(arguments,0)))}}var c=b;"undefined"!==
		typeof d?c=b[d]=[]:d="mixpanel";c.people=c.people||[];c.toString=function(b){var a="mixpanel";"mixpanel"!==d&&(a+="."+d);b||(a+=" (stub)");return a};c.people.toString=function(){return c.toString(1)+".people (stub)"};i="disable track track_pageview track_links track_forms register register_once alias unregister identify name_tag set_config people.set people.set_once people.increment people.append people.track_charge people.clear_charges people.delete_user".split(" ");for(g=0;g<i.length;g++)f(c,i[g]);
			b._i.push([a,e,d])};b.__SV=1.2}})(document,window.mixpanel||[]);

		mixpanel.init("<?php echo $code; ?>");
		mixpanel.identify("<?php echo $jid; ?>");
		mixpanel.people.set({
			"$email": "<?php echo $current_user->user_email; ?>",
			"$name" : "<?php echo $current_user->display_name; ?>",
			"domain": "<?php echo $jabze_domain ? $jabze_domain : $current_user_domain_name; ?>",
			"wp_version": "<?php echo JABZE_VERSION; ?>",
			"AccountActivated": true
		});

		if(window.mixpanel){
			<?php if ($show): ?>
				<?php if ($type == "user"): ?>
				mixpanel.track("wp: Open sync user page.");
				<?php elseif ($type == "setting"): ?>
				mixpanel.track("wp: Open setting page.");
				<?php endif; ?>
			<?php endif; ?>
			mixpanel.track_links("#jabze-start-to-sync-user", "wp: Start to sync user.");
		}
	</script>
	<!-- end MixPanel -->
<?php
}

function jabze_add_mixpanel_track() {
?>
	<script type="text/javascript">
		if(window.mixpanel){
			mixpanel.track("wp: Open setting page to set up Jabze.");
		}
	</script>
<?php
}
