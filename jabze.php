<?php
/*
Plugin Name: Jabze Chat
Plugin URI: http://www.jabze.com/site/page/web-based-chat-for-wordpress
Description: Jabze is an industrial strength and IT friendly messenger service, with a great mobile experience. Jabze Chat is the WordPress extension to enable chat function on a WordPress site for signed-in users. It is most useful in an intranet environment.
Author: jabze
Version: 1.6.4
Author URI: http://www.jabze.com
License: GPLv2 or later
*/

/* Copyright 2013 Sumilux Technologies, Inc. (email: info@sumilux.com)

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/


define('JABZE_VERSION', '1.6.4');
define('JABZE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('JABZE_APP_NAME', 'Jabze Chat');
//todo change to https url
define('JABZE_CHAT_URL', 'https://wpsvc.jabze.com/http-bind/');
//define('JABZE_CHAT_URL', 'https://hf113.sumilux.com/http-bind/');

// if is safari use web socket
define('JABZE_CHAT_URL_WEB_SOCKET','ws://www.jabze.com:5290');
//define('JABZE_CHAT_URL_WEB_SOCKET','ws://hf113.sumilux.com:5290');

//TODO use ssl
define('JABZE_RESTFUl_URL', 'https://wpsvc.jabze.com/');
//define('JABZE_RESTFUl_URL', 'https://hf113.sumilux.com/');
define('JABZE_SETUP_URL', JABZE_RESTFUl_URL . 'api/wordpressSetUp/version/'.JABZE_VERSION);
define('JABZE_SYNC_URL', JABZE_RESTFUl_URL . 'api/wordPressSync/version/'.JABZE_VERSION.'/type/sync');
define('JABZE_SEND_CODE_URL', JABZE_RESTFUl_URL . 'api/wordpressSendCode/version/'.JABZE_VERSION);
define('JABZE_CHECK_STATUS_URL', JABZE_RESTFUl_URL . 'api/wordpressCheckStatus/version/'.JABZE_VERSION);
define('JABZE_CHECK_VERSION_URL', JABZE_RESTFUl_URL . 'api/wordpressCheckVersion/version/'.JABZE_VERSION);
define('JABZE_CHECK_iS_USER_ACTIVE_URL', JABZE_RESTFUl_URL . 'api/isUserActive/version/'.JABZE_VERSION);
define('JABZE_SEND_LOG_URL', JABZE_RESTFUl_URL . 'api/log');
define('JABZE_ORG_CODE_URL',JABZE_RESTFUl_URL. 'api/getOrgCode');
define('JABZE_DEV_CODE', '1025b97a3f5f58f553db87fa9ffbf3a4');
define('JABZE_PROD_CODE', 'beb0b0faa4f431b7fe8a1ad686dc6ed0'); //do not change this, wordpress mixpanel is now separated from web app mixpanel

$jabze_domain_name = 'jabze_domain';
$jabze_org_code_name = 'jabze_org_code';
$jabze_access_token_name = 'jabze_access_token';
$jabze_email_name='jabze_email';
$jabze_auth_secret_name='jabze_auth_secret';
$jabze_setup_error_code_name = 'jabze_setup_error_code';
$jabze_setup_error_msg_name = 'jabze_setup_error_msg';
$jabze_enable_registration_suggestion_name = 'jabze_enable_registration_suggestion';

$jabze_domain = get_option($jabze_domain_name);
$jabze_access_token = get_option($jabze_access_token_name);
$jabze_auth_secret= get_option($jabze_auth_secret_name);
$jabze_org_code  = get_option($jabze_org_code_name);

$jabze_enable_registration_suggestion=get_option($jabze_enable_registration_suggestion_name);
$jabze_need_upgrade=0;
$jabze_need_upgrade_msg='';
$jabze_server_status=1;
$jabze_server_status_msg = "";
// Make sure we don't expose any info if called directly
if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}
//jabze functions
require_once dirname(__FILE__) . '/jabze-core.php';

//jabze-wiget
require_once dirname(__FILE__) . '/jabze-wiget.php';


if(empty($jabze_org_code) && !empty($jabze_domain)){
    $json = call(JABZE_ORG_CODE_URL,  array('domainName' => $jabze_domain));
    if ($json->success) {
        $jabze_org_code = $json->orgCode;
        if (!add_option($jabze_org_code_name, $jabze_org_code))
            update_option($jabze_org_code_name, $jabze_org_code);
    }
}

register_uninstall_hook(__FILE__, 'jabze_uninstall');
register_activation_hook(__FILE__, 'jabze_activate');

add_action('wp_enqueue_scripts', 'theme_styles');

add_action('wp_footer', 'jabze_widget');
theme_styles();
if (is_admin())
    require_once dirname(__FILE__) . '/admin.php';
