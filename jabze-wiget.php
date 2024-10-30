<?php
function jabze_check_browser(){
	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	if(strpos($user_agent, 'MSIE') === false){
		return true;
	} else {
		if (preg_match('/MSIE\s([^\s|;]+)/i', $user_agent, $regs)) {
			$browser_ver     = $regs[1];
		} else {
			$browser_ver     = 0;
		}
		if($browser_ver > 8){
			return true;
		}else{
			return false;
		}
	}
}
function jabze_widget() {

    global $jabze_domain,$jabze_auth_secret,$jabze_access_token,$jabze_enable_registration_suggestion;
    $current_user = wp_get_current_user();
    List(,$current_user_domain) = explode("@",$current_user->user_email);
    //if not init ,not show this widget
    if(!$jabze_auth_secret || !$jabze_domain){
        return ;
    }

    if (0 != $current_user->ID):
        $jabzeSync = is_current_user_need_sync();


        $otherLoginUrl=getJabzeLoginUrl();
        $user_login_name = str_replace(" ", "_", strtolower($current_user->user_login));

        $name_valid = preg_match('/[^-\w_\.\s]/',$user_login_name)  == 0 ? true :false;

        $jid= $user_login_name. '@'.$jabze_domain;
        $me = $user_login_name. '@'.$jabze_domain.'/jabze_wp_plugin';
        $pass = sha1($jabze_auth_secret.$jid);
        $timestamp=time();
        $signature= md5($jabze_domain . $user_login_name . $timestamp . $jabze_access_token);
        ?>
        <div id="chatpanel" xmlns="http://www.w3.org/1999/html">
            <div class="box" id="jabze-noti-box">
                <div class="arrow"></div>
                <div class="arrow-border"></div>
                <ul>
                    <li><strong>Jabze Chat!</strong> <a class="close-chat">x</a></li>
                </ul>
                <?php
                if($name_valid):
                    ?>
                    <p>
                        Jabze Chat on this site offers Instant Messaging
                        for every logged-in user.
                        Click on the chat status bar at the VERY BOTTOM of this page, see who's online and chat away!
                    </p>
                    <p>
                        Additional features are also available from our desktop and mobile apps, click below to set up:  <br>
                    <form action="<?php echo $otherLoginUrl;?>" method="post" target="_blank" id="wordpressLogin">
                        <input type="hidden" name="domainName" value="<?php echo $jabze_domain;?>"/>
                        <input type="hidden" name="userName" value="<?php echo $user_login_name;?>"/>

                        <input type="hidden" name="timestamp" value="<?php echo $timestamp;?>"/>
                        <input type="hidden" name="signature" value="<?php echo $signature;?>"/>
                        <input type="hidden" id="loginType" name="loginType" value="<?php echo $signature;?>"/>
                        <div style="display: inline-block;">
                            <button type="submit" class="button button-primary" onclick="jQuery('#loginType').val('webapp')">Web App</button>
                            <button  class="button button-primary" style="margin-left: 10px;" onclick="jQuery('#loginType').val('iphone')">iPhone App</button>
                            <button  class="button button-primary" style="margin-left: 10px;" onclick="jQuery('#loginType').val('android')">Android App</button>
                        </div>
                    </form>
                    </p>
                    <a id="not-show-jabze-noti"  style="font-size: 12px;padding: 3px;">Got it, don't show this message again.</a>
                <?php else:?>
                    <p>
                        Sorry, can't proceed for your account name[<?php echo $user_login_name;?>] contains illegal character.
                        Illegal Characters: @,%,*,& etc.
                    </p>
                <?php endif;?>

            </div>
            <div id="collective-xmpp-chat-data"></div>
            <div id="toggle-controlbox" class="open">
                <a href="#" class="chat toggle-online-users">
                    <strong class="conn-feedback">Jabze Chat</strong>
                    <strong style="display: none" id="online-count">(0)</strong>
                    <strong id="jabze-show-help" style="color: green;float: left;margin-left:6px;">?</strong>
                </a>
            </div>
        </div>

	    <?php
		    if(strpos(JABZE_RESTFUl_URL, 'wpsvc')){
			    $code = JABZE_PROD_CODE;
		    }else{
			    $code = JABZE_DEV_CODE;
		    }
	    ?>
        <script type="text/javascript">
            window.Jabze = {
                config:<?php echo json_encode(array(
                    'jid' => $me,
                    'password' => $pass,
                    'requireConfig' => array('baseUrl' => JABZE_PLUGIN_URL. 'assets/js'),
                    'endpoint' => array(
                        'bosh' => JABZE_CHAT_URL,
                        'websocket' => JABZE_CHAT_URL_WEB_SOCKET,
                    ),
                    'enableConnect'=> $name_valid ? true : false
                ));?>
            };
        </script>
	<?php if(jabze_check_browser()): ?>
	    <script data-main="<?php echo JABZE_PLUGIN_URL;?>assets/js/jabze.js" src="<?php echo JABZE_PLUGIN_URL;?>assets/js/Libraries/require-jquery.js"></script>
    <?php else: ?>
	    <script type="text/javascript">
		    document.getElementById("toggle-controlbox").attachEvent('onclick',function(){
			    alert("Jabze Chat can only be used on IE 9 or above. Please download the latest version to access.");
		    },true);
	    </script>
    <?php endif; ?>

	    <!-- start MixPanel -->
	    <script type="text/javascript">(function(e,b){if(!b.__SV){var a,f,i,g;window.mixpanel=b;a=e.createElement("script");a.type="text/javascript";a.async=!0;a.src=("https:"===e.location.protocol?"https:":"http:")+'//cdn.mxpnl.com/libs/mixpanel-2.2.min.js';f=e.getElementsByTagName("script")[0];f.parentNode.insertBefore(a,f);b._i=[];b.init=function(a,e,d){function f(b,h){var a=h.split(".");2==a.length&&(b=b[a[0]],h=a[1]);b[h]=function(){b.push([h].concat(Array.prototype.slice.call(arguments,0)))}}var c=b;"undefined"!==
		    typeof d?c=b[d]=[]:d="mixpanel";c.people=c.people||[];c.toString=function(b){var a="mixpanel";"mixpanel"!==d&&(a+="."+d);b||(a+=" (stub)");return a};c.people.toString=function(){return c.toString(1)+".people (stub)"};i="disable track track_pageview track_links track_forms register register_once alias unregister identify name_tag set_config people.set people.set_once people.increment people.append people.track_charge people.clear_charges people.delete_user".split(" ");for(g=0;g<i.length;g++)f(c,i[g]);
			    b._i.push([a,e,d])};b.__SV=1.2}})(document,window.mixpanel||[]);
		    mixpanel.init("<?php echo $code; ?>");
		    mixpanel.identify("<?php echo $jid; ?>");
		    mixpanel.people.set({
			    "$email": "<?php echo $current_user->user_email; ?>",
			    "$name" : "<?php echo $current_user->display_name; ?>",
			    "domain": "<?php echo $jabze_domain; ?>",
			    "wp_version": "<?php echo JABZE_VERSION; ?>",
			    "AccountActivated": true
		    });

	        mixpanel.track('wp: Visit Page (user identified): ' +  window.location.pathname + window.location.search + ".");
	    </script>
	    <!-- end MixPanel -->

	    <?php

	    if ($jabzeSync):
		    $ids[] = $current_user->ID;
		    $json = jabze_sync($ids);
		    if ($json->success):
			    ?>
			    <script type="text/javascript">
				    if (window.mixpanel) {
					    mixpanel.track("wp: Auto sync user.");
				    }
			    </script>
		    <?php
		    else:
			    return;
		    endif;
	    endif;
	    ?>

    <?php
    elseif("0" !== $jabze_enable_registration_suggestion):
        ?>
        <div id="chatpanel" xmlns="http://www.w3.org/1999/html">
            <div class="box" id="jabze-noti-box">
                <div class="arrow"></div>
                <div class="arrow-border"></div>
                <ul>
                    <li><strong>Jabze Chat</strong> <a class="close-chat">x</a></li>
                </ul>
                <p id="tipmsg">
                    Jabze Chat enabled on this site. Please sign in (or register for an account)
                    to get connected with other users instantly.

                    <br><br>

                    <a href="<?php echo wp_login_url(); ?>">Sign In</a> &nbsp; &nbsp; &nbsp; &nbsp;
                    <?php if(function_exists(wp_registration_url)){ ?><a href="<?php echo wp_registration_url(); ?>" >Register for an Account</a><?php }?>
                </p>
                <small style="font-size: smaller;">(To enable/disable this pop-up box permanently, please adjust the Jabze Chat plugin options)</small>
            </div>
            <div id="collective-xmpp-chat-data"></div>
            <div id="toggle-controlbox" class="open">
                <a href="javascript:;" class="chat toggle-online-users">
                    <strong class="conn-feedback">Jabze Chat</strong><strong id="jabze-show-help" style="color: green;">?</strong>
                </a>
            </div>

        </div>
	    <?php if(jabze_check_browser()): ?>
	    <script data-main="<?php echo JABZE_PLUGIN_URL;?>assets/js/jabze.js" src="<?php echo JABZE_PLUGIN_URL;?>assets/js/Libraries/require-jquery.js"></script>
        <?php else: ?>
	    <script type="text/javascript">
		    document.getElementById("toggle-controlbox").attachEvent('onclick',function(){
			    alert("Jabze Chat can only be used on IE 9 or above. Please download the latest version to access.");
		    },true);
	    </script>
    <?php endif; ?>

<?php endif; }?>
