<?php
function getJabzeLoginUrl(){
    global $jabze_org_code;
    $jabze_url = JABZE_RESTFUl_URL."org/wordpressLogin";
    if(strpos($jabze_url,'wpsvc')){
        $prod= true;
    }else{
        $prod= false;
    }
    if(empty($jabze_org_code)){
        if($prod){
            $jabze_url =  str_replace("wpsvc.","",$jabze_url);
        }
    }else{

        if($prod){
            $jabze_url = str_replace("wpsvc",$jabze_org_code,$jabze_url);
        }else{
            //for test
            $jabze_url = str_replace("hf26",$jabze_org_code.".hf26",$jabze_url);
            $jabze_url = str_replace("hf113",$jabze_org_code.".hf113",$jabze_url);
        }

    }
    return $jabze_url;
}
/**
 * Add column to database table, if column doesn't already exist in table.
 *
 * @since 1.0.0
 * @package WordPress
 * @subpackage Plugin
 * @uses $wpdb
 *
 * @param string $table_name Database table name
 * @param string $column_name Table column name
 * @param string $create_ddl SQL to add column to table.
 * @return bool False on failure. True, if already exists or was successful.
 */
function jabze_add_column($table_name, $column_name, $create_ddl)
{
    global $wpdb;
    foreach ($wpdb->get_col("DESC $table_name", 0) as $column) {

        if ($column == $column_name) {
            return true;
        }
    }
    //didn't find it try to create it.
    $wpdb->query($create_ddl);
    // we cannot directly tell that whether this succeeded!
    foreach ($wpdb->get_col("DESC $table_name", 0) as $column) {
        if ($column == $column_name) {
            return true;
        }
    }
    return false;
}

/**
 *
 * Drop column to database table, if column doesn't already exist in table.
 *
 * @since 1.0.0
 * @package WordPress
 * @subpackage Plugin
 * @uses $wpdb
 *
 * @param string $table_name Database table name
 * @param string $column_name Table column name
 * @param string $drop_ddl SQL to add column to table.
 * @return bool False on failure. True, if already exists or was successful.
 */
function jabze_drop_column($table_name, $column_name, $drop_ddl)
{
    global $wpdb;
    foreach ($wpdb->get_col("DESC $table_name", 0) as $column) {
        if ($column == $column_name) {
            //found it try to drop it.
            $wpdb->query($drop_ddl);
            // we cannot directly tell that whether this succeeded!
            foreach ($wpdb->get_col("DESC $table_name", 0) as $column) {
                if ($column == $column_name) {
                    return false;
                }
            }
        }
    }
    // else didn't find it
    return true;
}

function remove_jabze_sync()
{
    global $wpdb;
    if (!jabze_drop_column($wpdb->users, 'jabze_sync_status', 'ALTER TABLE `' . $wpdb->users . '` DROP COLUMN `jabze_sync_status`')) {
        add_option('jabze_setup_error_code', '500');
        add_option('jabze_setup_error_msg', 'Drop jabze_sync_status in ' . $wpdb->users . ' encountered an error .');
        add_action('admin_notices', 'jabze_alert');
        jabze_log('Drop jabze_sync_status in ' . $wpdb->users . ' encountered an error .');
        return false;
    }

}
/**
 * remove jabze option
 */
function remove_jabze_option()
{
    //global $jabze_domain_name,$jabze_access_token_name,$jabze_email_name,$jabze_auth_secret_name;

    $jabze_domain_name = 'jabze_domain';
    $jabze_access_token_name = 'jabze_access_token';
    $jabze_email_name='jabze_email';
    $jabze_auth_secret_name='jabze_auth_secret';
    $jabze_org_code_name = 'jabze_org_code';

    if(get_option($jabze_org_code_name)){
        delete_option($jabze_org_code_name);
    }
    if(get_option($jabze_domain_name)){
        delete_option($jabze_domain_name);
    }
    if(get_option($jabze_access_token_name)){
        delete_option($jabze_access_token_name);
    }

    if(get_option($jabze_email_name)){
        delete_option($jabze_email_name);
    }

    if(get_option($jabze_auth_secret_name)){
        delete_option($jabze_auth_secret_name);
    }

}
/**
 * reset all jabze sync status
 */
function reset_jabze_sync()
{
    global $wpdb;
    $wpdb->query("UPDATE `" . $wpdb->users . "` set jabze_sync_status=0");
}

/**
 *
 * jabze unistall
 * @return bool
 *
 */
function jabze_uninstall()
{

    remove_jabze_sync();

    remove_jabze_option();
}

/**
 *
 * jabze activate
 * @return bool
 *
 */
function jabze_activate()
{
    global $wpdb;
    $add_sync_status = jabze_add_column($wpdb->users, 'jabze_sync_status', 'ALTER TABLE `' . $wpdb->users . '` ADD COLUMN `jabze_sync_status`  TINYINT(1) DEFAULT 0;');
    $add_active_status = jabze_add_column($wpdb->users, 'jabze_active_status', 'ALTER TABLE `' . $wpdb->users . '` ADD COLUMN `jabze_active_status`  TINYINT(1) DEFAULT 0;');
    if (!$add_sync_status || !$add_active_status) {
        add_option('jabze_setup_error_code', '500');
        add_option('jabze_setup_error_msg', 'Add jabze_sync_status in ' . $wpdb->users . ' had some error .');
        add_action('admin_notices', 'jabze_alert');
        jabze_log('Add jabze_sync_status and jabze_active_status in ' . $wpdb->users . ' had some error .');
        return false;
    }
}


function jabze_check_version(){
    global $jabze_need_upgrade_msg,$jabze_need_upgrade,$jabze_server_status,$jabze_server_status_msg;
    $json = call(JABZE_CHECK_VERSION_URL, array());

    if($json->success){
        $jabze_need_upgrade=0;
    }else{
        if(isset($json->error) && $json->error=='curl'){
            $jabze_server_status=0;
            $jabze_server_status_msg = $json->msg;
        }else{
            $jabze_need_upgrade=1;
            $jabze_need_upgrade_msg=$json->msg;
        }

    }
}

function jabze_enable_suggestion($flag){
    global $jabze_enable_registration_suggestion_name,$jabze_enable_registration_suggestion;


    if (!add_option($jabze_enable_registration_suggestion_name, $flag))
        update_option($jabze_enable_registration_suggestion_name, $flag);

    $jabze_enable_registration_suggestion=$flag;
}

function jabze_sendCode()
{
    global $jabze_email_name,$jabze_domain;

    if(isset($_POST['getback'])){
        $email=$_POST['uname']."@".$jabze_domain;
    }else{
        $email =$_POST['email'];
    }
    $current_user = wp_get_current_user();
    $current_user_email = $current_user->user_email;
    List(,$user_domain) = explode("@",$current_user_email);
    List(,$code_domain) = explode("@",$email);
    if($user_domain!==$code_domain){
        return array(
            "success" => false,
            "msg" => "your email address does not match the domain"
        );
    }

    if (!add_option($jabze_email_name, $email))
        update_option($jabze_email_name, $email);

    $json = call(JABZE_SEND_CODE_URL, array(), array('email' => $email));

    if ($json->success) {
        $msg = "Thank you for requesting an email address verification, we have just sent you the verification code. ";
        $json->msg=$msg;
    }
    return $json;
}
function jabze_log($message){
    call(JABZE_SEND_LOG_URL, array('name'=>'WP plugin', 'location'=>get_option('siteurl'), 'version'=>JABZE_VERSION,'message'=>$message));
}

function jabze_setup()
{

    global $jabze_domain, $jabze_access_token,$jabze_auth_secret,$jabze_email_name,$jabze_domain_name,$jabze_access_token_name,$jabze_auth_secret_name,$jabze_org_code_name;

    $jabze_email= get_option($jabze_email_name);
    $site_name = get_option('blogname');
    $site_url = get_option('siteurl');
    $admin_email = get_option('admin_email');

    $current_user = wp_get_current_user();
    $intro = array('siteName' => $site_name, 'siteUrl' => $site_url, 'adminEmail' => $admin_email,'admin'=>array('uname'=>$current_user->user_login,'email'=>$current_user->user_email,'name'=>$current_user->display_name));

    if( preg_match('/[^-\w_\.\s]/',$intro['admin']['uname']) !== 0){
        return array(
            "success"=>false,
            'error'=>'invalid',
            'msg'=> 'User Name:'.$current_user->user_login.' is not valid. only alpha-numerical and DOT characters are allowed'
        );

    }
    $code=$_POST['code'];

    //$json =  json_decode('{"domain":"qq.com","accessToken":"unETaqUrybUJyTYtENUJu7ABUPE8a2yp","authSecret":"099f234bf20b3801fccd2903","success":true}');
    $json = call(JABZE_SETUP_URL, array(), array('email'=>$jabze_email,'code'=>$code,'intro' => $intro));

    if ($json->success) {
        $jabze_domain = $json->domain;
        $jabze_access_token = $json->accessToken;
        $jabze_auth_secret=$json->authSecret;
        $jabze_org_code = $json->orgCode;
        //reset sync when set up
        reset_jabze_sync();
        //upate admin sync status
        update_user_sync_status($current_user->ID, 1);

        if (!add_option($jabze_domain_name, $jabze_domain))
            update_option($jabze_domain_name, $jabze_domain);

        if (!add_option($jabze_access_token_name, $jabze_access_token))
            update_option($jabze_access_token_name, $jabze_access_token);

        if (!add_option($jabze_auth_secret_name,$jabze_auth_secret))
            update_option($jabze_auth_secret_name, $jabze_auth_secret);

        if (!add_option($jabze_org_code_name,$jabze_org_code))
            update_option($jabze_org_code_name, $jabze_org_code);
    }

    return $json;

}
function jabze_getSyncUsers(){
    $argc=array(
        //'search' => '*'.$domain,
        //'search_columns' =>array('user_email'),
        'orderby' => 'ID',
        'order' => 'ASC',
    );
    return get_users($argc);
}
function jabze_sync($userIDs,$type="sync")
{
    global $jabze_domain, $jabze_access_token;

    $sync_url = JABZE_SYNC_URL .'/domain/' . $jabze_domain . '/accessToken/' . $jabze_access_token;

    $syncUser=array();
    //$role_groups=jabze_getRoleGroups();
    $argc=array(
        'include'=> $userIDs,
        'orderby' => 'ID',
        'order' => 'ASC',
    );

    $blogusers = get_users($argc);

    if($blogusers){
        foreach($blogusers as $user){

            if($user->jabze_sync_status != 3){
                $syncUser[]=array(
                    'id'=>$user->ID,
                    'name'=>$user->display_name,
                    'email'=>$user->user_email,
                    'uname'=>str_replace(" ", "_", $user->user_login),
                    // 'uname'=>$uname,
                    'role'=>$user->roles[0]
                );
            }
        }
    }
    if(empty($syncUser)){
        return '{"success":false,"error":"syncUser","msg":"no user is selected to sync"}';
    }

    $json = call($sync_url, array(), array('users' => json_encode($syncUser)));

    if ($json->success) {
        foreach ($json->users as $user) {
            if ($user->state == true) {
                update_user_sync_status($user->id, 3);
            } else {
                update_user_sync_status($user->id, 2);
            }
        }
    }else{
        jabze_log($json->msg);
    }
    return $json;

}

/**
 *
 * let role level role had less than role
 * format user roles like this
 * array('adminstrator'=>array(0=>'administartor',1=>'editor',...)
 * @return array
 */
function jabze_getRoleGroups(){
    global $wp_roles;
    $role_groups=$tmp_groups=array();
    foreach ($wp_roles->roles as $role_name => $role) {
        $level = 0;
        for ($i = 0; $i <= 10; $i++)
            if (!empty($role['capabilities']["level_$i"]))
                $level = $i;
        $role_levels[$role_name] = $level;
        $tmp_groups[]=$role_name;
    }
    foreach($tmp_groups as $role_name){
        $level = $role_levels[$role_name];
        $role_groups[$role_name][]=$role_name;
        foreach($role_levels as $name=> $p){
            if($level>$p){
                $role_groups[$role_name][]=$name;
            }
        }

    }
    return $role_groups;
}

function update_user_sync_status($id, $value)
{
    global $wpdb;
    $wpdb->update($wpdb->users, array('jabze_sync_status' => $value), array('ID' => $id));
}
function update_user_jabze_status($id, $value){
    global $wpdb;
    $wpdb->update($wpdb->users, array('jabze_active_status' => $value), array('ID' => $id));
}
function add_column_active_status(){
    global $wpdb;
    $add_active_status = jabze_add_column($wpdb->users, 'jabze_active_status', 'ALTER TABLE `' . $wpdb->users . '` ADD COLUMN `jabze_active_status`  TINYINT(1) DEFAULT 0;');
    if ( !$add_active_status) {
        add_option('jabze_setup_error_code', '500');
        add_option('jabze_setup_error_msg', 'Add jabze_sync_status in ' . $wpdb->users . ' had some error .');
        add_action('admin_notices', 'jabze_alert');
        jabze_log('Add  jabze_active_status in ' . $wpdb->users . ' had some error .');
        return false;
    }
}
//check current user active status in wordpress
function is_current_user_active_in_jabze(){
    $current_user = wp_get_current_user();
    if(!isset($current_user->jabze_active_status)){
        add_column_active_status();
    }
    $active_in_jabze = $current_user->jabze_active_status;
    if(!$active_in_jabze){//not active in jabze server
        return false;
    }else{
        return true;
    }
}
//get user active status from jabze server ,and update in wordpress
function get_current_user_status_in_jabze(){
    global $jabze_domain, $jabze_access_token;
    $current_user = wp_get_current_user();
    $email = strtolower($current_user->user_login). '@'.$jabze_domain;
    $isUserActiveUrl = JABZE_CHECK_iS_USER_ACTIVE_URL .'/email/'.$email.'/domainName/' . $jabze_domain . '/accessToken/' . $jabze_access_token;
    $json = call($isUserActiveUrl, array());

    if($json->success){
        $isUserActive =  $json->isActive;
    }else{
        $isUserActive = false;
    }
    if($isUserActive){
        update_user_jabze_status($current_user->id,1);
    }
    return $isUserActive;

}
function jabze_check_key_status()
{
    global  $jabze_access_token,$jabze_auth_secret,$jabze_domain,$jabze_auth_secret_name;

    //$domain=$_POST['domain'];
    //$accessToken=$_POST['accessToken'];

    //TODO need check status api
    $json = call(JABZE_CHECK_STATUS_URL, array(), array('accessToken'=>$jabze_access_token,'domain'=>$jabze_domain));

    if ($json->success) {

        $jabze_auth_secret=$json->authSecret;

        /*if (!add_option($jabze_domain_name, $jabze_domain_name))
            update_option($jabze_domain_name, $jabze_domain_name);

        if (!add_option($jabze_access_token_name, $jabze_access_token))
            update_option($jabze_access_token_name, $jabze_access_token);*/

        if (!add_option($jabze_auth_secret_name,$jabze_auth_secret))
            update_option($jabze_auth_secret_name, $jabze_auth_secret);

        //reset_jabze_sync();
        //jabze_sync();
    }else{
        $json->msg="Your domain name does not match the activation key. Please retrieve the activation key, and reconfigure the Jabze Chat plugin for domain: ".$jabze_domain;
    }

    return $json;

}

/**
 * call reset-ful url add get json object
 * @param $url
 * @param $params
 * @param array $posts
 * @return array|mixed
 */
function call($url, $params, $posts = array())
{
    //$url=$url.'/version/'.JABZE_VERSION;
    $query = http_build_query($params);

    if ($query)
        $url = $url . '&' . $query;

    $data = getResponse($url, $posts);

    return json_decode(trim($data));
}

/**
 * @param string $url
 * @param array $posts defalut empty array, then it will use GET method to obtain data
 * @return mixed
 */
function getResponse($url, $posts = array())
{
    if(!function_exists('curl_init')) {
        return '{"success":false,"error":"curl","msg":"curl extension is unavailable."}';
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);

    if (!empty($posts)) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($posts));
    }

    $data = curl_exec($ch);

    if (curl_errno($ch)) {
        curl_close($ch);
        return '{"success":false,"error":"curl","msg":"Unable to connect to the Jabze server"}';
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if($httpCode!= 200){
        return '{"success":false,"error":"curl","msg":"Unable to connect to the Jabze server"}';
    }

    curl_close($ch);
    return $data;
}
function is_current_user_need_sync(){
    $current_user = wp_get_current_user();

    $jabzeSync = $current_user->jabze_sync_status;//to do.check

    //only alpha-numerical and DOT characters are allowed in uid
    if( preg_match('/[^-\w_\.\s]/',$current_user->user_login) !== 0){
        return false;
    }
    if($jabzeSync==1||$jabzeSync==3){//has sync, for plugin updae check user email_name match uname?
        return false;
    }else{
        return true;
    }
}

function theme_styles()
{
    wp_register_style('converse', JABZE_PLUGIN_URL . 'assets/css/converse.css', array(), '1.0.0');
    wp_enqueue_style('converse');
}

?>