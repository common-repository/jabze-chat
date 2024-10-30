jQuery(document).ready(function ($) {
    var no_key = $('#jabze-no-key');
    var verify_code = $('#jabze-verify');
    var have_key = $('#jabze-have-key');
    var sync = $('#jabze-sync');
    var msg = $('#jabze-message');
    var finish=$('#jabze-finish');
    var getback_setep1=$('#jabze-get-back-setep1');
    var getback_setep2=$('#jabze-get-back-setep2');
    var progressbar=jQuery('#bcs-progress');
    var int=0;
    var subBox = 'input[name="users[]"]';
    var subBoxChecked='input[name="users[]"]:checked';

    $('.switch-have-key').click(function () {
        no_key.hide();
        have_key.fadeIn('slow');
        return false;
    });

    $('.switch-no-key').click(function () {
        have_key.hide();
        no_key.fadeIn('slow');

        return false;
    });


    $.fn.updateProgress=function(max){
        var progress=document.getElementById("bcs-progress").getAttribute("value");;
        if(progress<max){
	        document.getElementById("bcs-progress").setAttribute("value", progress+Math.random());
        }else{
	        document.getElementById("bcs-progress").setAttribute("value", max);
            clearInterval(int);
        }
    };

    $.fn.syncUser= function(){
        clearInterval(int);
        int=setInterval('jQuery.fn.updateProgress(100)',500);
        msg.children('span').html('Synchronizing users.....');
        var data = {
            action: 'my_action',
            sync: 1
        };

        $.ajax({
            url: ajaxurl,
            data: data,
            type: 'POST',
            dataType: 'json',
            success: function (json) {
                console.log(json);
                if (json.success) {
                      alert('Success');
                      window.location.reload();
                } else {
                    alert(json.msg);
                }
            }
        });
    };
    $('#jabze-sync-user').click(function(){
        var ths=$(this);
        if(ths.hasClass('need_updgrade')){
            alert('Please upgrade Jabze chat.');
            return false;
        }
        if($(subBoxChecked).length==0){
            alert('You need check some user.');
            return false;
        }
        ths.attr("disabled", "disabled");
        var syncUsers=[];
        $(subBoxChecked).each(function(){
            syncUsers.push($(this).val());
        });

        var data = {
            syncUsers:syncUsers,
            action: 'my_action',
            sync: 1
        };

        $.ajax({
            url: ajaxurl,
            data: data,
            type: 'POST',
            dataType: 'json',
            success: function (json) {
                console.log(json);
                if (json.success) {
                    alert('Success');
	                mixpanelHandle("sync");
                    window.location.reload();
                } else {
                    alert(json.msg);
                }
                ths.removeAttr("disabled");
            }
        });
    });

    $('#jabze-send-code').click(function(){
	    if ($.browser.msie && parseFloat($.browser.version) < 9) {
		    alert("Jabze Chat plugin is only compatible with Internet Explorer 9 and above. Please upgrade prior to activating this plugin");
		    return false;
	    }
        var ths=$(this);
        if(ths.hasClass('need_updgrade')){
            alert('Please upgrade Jabze chat.');
            return false;
        }

        var email =$('#jabze-email').val();

        if(!email){
            alert('Please enter your work email address.');
            return false;
        }

	    var pattern = /^[\w\.]+@[a-zA-Z-0-9]+?(\.[a-zA-Z]{2,63}){1,2}$/;
	    if(!email.match(pattern)){
            alert('Your email address is not correct.');
            return false;
        }

        var agreed = $("#agreed").attr('checked');
        if (!agreed) {
            alert('You need agree to share some of your site information with us');
            return false;
        }
        ths.attr("disabled", "disabled");
        var data = {
            action: 'my_action',
            email:email,
            sendCode: 1
        };

        $.ajax({
            url: ajaxurl,
            data: data,
            type: 'POST',
            dataType: 'json',
            success: function (json) {
                console.log(json);
                if (json.success) {
                    alert(json.msg);
                    $('#jabze-verify-code').val("");
                    verify_code.fadeIn();
                    no_key.hide();
	                mixpanelHandle("sendCode");
                } else {
                    alert(json.msg);
                    msg.hide();
                    no_key.show();
                }
                ths.removeAttr("disabled");
            }
        });

        return false;
    });

    $('#jabze-select-all').click(function(){
        $(subBox).attr("checked",this.checked);
        if(this.checked){
            $('#jabze-sync-user').removeAttr("disabled");
        }else{
            $('#jabze-sync-user').attr('disabled',"disabled");
        }
    });

    $(subBox).click(function(){
        $("#jabze-select-all").attr("checked", $(subBox).length ==$(subBoxChecked).length ? true : false);

        if($(subBoxChecked).length>0){
            $('#jabze-sync-user').removeAttr("disabled");
        }else{
            $('#jabze-sync-user').attr('disabled',"disabled");
        }
    });
    $(subBox).click(function(){
        $("#jabze-select-all").attr("checked", $(subBox).length ==$(subBoxChecked).length ? true : false);

        if($(subBoxChecked).length>0){
            $('#jabze-sync-user').removeAttr("disabled");
        }else{
            $('#jabze-sync-user').attr('disabled',"disabled");
        }
    });


    $('#create-new-jabze-key').click(function () {
        var ths=$(this);
        if(ths.hasClass('need_updgrade')){
            alert('Please upgrade Jabze chat.');
            return false;
        }
        var code=$('#jabze-verify-code').val();

        if(!code){
            alert('You need to enter your activation key.');
            return false;
        }

        if(int !=0){
            clearInterval(int);
        }

        progressbar.val(0);
        verify_code.hide();
        have_key.hide();
        no_key.hide();

        msg.show().children('span').html('Please wait a moment, initializing...');

        int=setInterval('jQuery.fn.updateProgress(100)',500);

        var data = {
            action: 'my_action',
            code:code,
            create_key: 1
        };
        $.ajax({
            url: ajaxurl,
            data: data,
            type: 'POST',
            dataType: 'json',
            success: function (json) {
                console.log(json);
                if (json.success) {
	                clearInterval(int);
                    progressbar.val(100);
                    $('#jabze_domain').val(json.domain);
                    $('#jabze-access-token').val(json.accessToken);
                    //$.fn.syncUser('create');
                    msg.hide();
                    finish.show();
	                mixpanelHandle("newKey");
                } else {
	                clearInterval(int);
                    alert(json.msg);

                    msg.hide();
                    no_key.show();
                }
            }
        });

        return false;
    });

    $('#get-back-jabze-key').click(function () {
        var ths=$(this);
        if(ths.hasClass('need_updgrade')){
            alert('Please upgrade Jabze Chat.');
            return false;
        }
        var code=$('#jabze-verify-backcode').val();

        if(!code){
            alert('You need to enter your activation key.');
            return false;
        }

        if(int !=0){
            clearInterval(int);
        }

        progressbar.val(0);
        getback_setep2.hide();

        msg.show().children('span').html('Please wait a moment, retrieving activation key...');

        int=setInterval('jQuery.fn.updateProgress(100)',500);

        var data = {
            action: 'my_action',
            code:code,
            create_key: 1
        };
        $.ajax({
            url: ajaxurl,
            data: data,
            type: 'POST',
            dataType: 'json',
            success: function (json) {
                console.log(json);
                if (json.success) {
                    alert("Successfully retrieved the activation key");
                    progressbar.val(100);
                    $('#jabze_domain').val(json.domain);
                    $('#jabze-access-token').val(json.accessToken);
                    msg.hide();
                    have_key.show();
                } else {
                    alert(json.msg);
                    msg.hide();
                    no_key.show();
                }
            }
        });

        return false;
    });

    $('#jabze-getback-code').click(function(){
        var ths=$(this);
        if(ths.hasClass('need_updgrade')){
            alert('Please upgrade Jabze chat.');
            return false;
        }

        var uname =$('#jabze-uname').val();

        if(!uname){
            alert('Please enter your work email address.');
            return false;
        }


        var pattern =/^[-\w\s_.]*$/;
        if(!uname.match(pattern)){
            alert('Your username is not correct.');
            return false;
        }

        ths.attr("disabled", "disabled");
        var data = {
            action: 'my_action',
            uname:uname,
            sendCode: 1,
            getback:1
        };

        $.ajax({
            url: ajaxurl,
            data: data,
            type: 'POST',
            dataType: 'json',
            success: function (json) {
                console.log(json);
                if (json.success) {
                    alert(json.msg);
                    getback_setep2.fadeIn();
                    getback_setep1.hide();
                } else {
                    alert(json.msg);
                    getback_setep1.hide();
                    have_key.show();
                }
                ths.removeAttr("disabled");
            }
        });
        return false;
    });
    $('#save-changes').click(function(){
        var ths=$(this);
        if(ths.hasClass('need_updgrade')){
            alert('Please upgrade Jabze Chat.');
            return false;
        }
        var checked=$("input[name='enable-registration-suggestion']").attr("checked");
        var enable="checked"==checked?1:0;

        ths.attr("disabled", "disabled");

        var data = {
            action: 'my_action',
            save_changes: enable
        };
        $.ajax({
            url: ajaxurl,
            data: data,
            type: 'POST',
            dataType: 'json',
            success: function () {
                alert("Successfully save changes");
                ths.removeAttr("disabled");
	            mixpanelHandle("saveChange");
            }
        });

        return false;
    });
    $('#update-bcs-key').click(function(){
      var ths=$(this);
      if(ths.hasClass('need_updgrade')){
            alert('Please upgrade Jabze Chat.');
            return false;
      }
      ths.attr("disabled", "disabled");
        var data = {
            action: 'my_action',
            update_key: 1
        };
        $.ajax({
            url: ajaxurl,
            data: data,
            type: 'POST',
            dataType: 'json',
            success: function (json) {
                console.log(json);
                if (json.success) {
                    alert("Successfully verify the activation key");
                    window.location.reload();
	                mixpanelHandle("verifyKey");
                } else {
                    alert(json.msg);
                    msg.hide();
                    have_key.hide();
                    getback_setep1.fadeIn();
                }
                ths.removeAttr("disabled");
            }
        });

        return false;
    });

	$("#jabze-complete-setup").click(function(){
		mixpanelHandle("setUpComplete");
	});

	function mixpanelHandle (type){
		if (!window.mixpanel) {
			return;
		}

		switch (type){
			case "sendCode":
				mixpanel.track("wp: Get verification code.");

				break;
			case "newKey":
				mixpanel.track("wp: Create new Jabze key.");

				break;
			case "setUpComplete":
				mixpanel.track("wp: Jabze set up complete.");

				break;
			case "sync":
				mixpanel.track("wp: Jabze sync user.");

				break;
			case "saveChange":
				mixpanel.track("wp: Save setting changes.");

				break;
			case "verifyKey":
				mixpanel.track("wp: Verify Jabze activation key.");

				break;
			default :
				break;
		}

	}

});
