/**
 * Created by yaowenh on 14-4-25.
 */

require(["jquery", "converse"], function ($, converse) {

    var notiBox = $('#jabze-noti-box');

    if(!window.Jabze){
        if ($.browser.msie && parseFloat($.browser.version) < 9) {
            $("#tipmsg").html('<p style="color:red">Sorry your browser is not supported, please use Microsoft IE 9 or above, or a recent version of Firefox, Chrome or Safari browsers</p>');
            return false;
        }
        notiBox.delay(10*1000).fadeIn('slow');

        $('div#toggle-controlbox').click(function(){
            notiBox.toggle();
        });
        $(".close-chat").click(function(){
            notiBox.hide();
        });
        return;
    }

	function JabzeMixpanelHandle (type){
		if (!window.mixpanel) {
			return;
		}

		switch (type){
			case "showHelp":
				mixpanel.track("wp: See the help.");

				break;
			case "connect":
				mixpanel.track("wp: Connect to Jabze.");

				break;
			case "autoOpen":
				mixpanel.track("wp: Auto connect to Jabze.");
			default :
				break;
		}

	}

    $.browser.msie === !0 && $.browser.version < 10 && Strophe.addConnectionPlugin("xdomainrequest", {
        init: function() {
            window.XDomainRequest ? (Strophe.debug("using XdomainRequest for IE"), XDomainRequest.prototype.oldsend = XDomainRequest.prototype.send, XDomainRequest.prototype.send = function() {
                XDomainRequest.prototype.oldsend.apply(this, arguments),
                    this.readyState = 2;
                try {
                    this.onreadystatechange()
                } catch(e) {console.log(e)}
            },
                Strophe.Request.prototype._newXHR = function() {
                    var e = function(e, t) {
                            e.status = t,
                                e.readyState = 4;
                            try {
                                e.onreadystatechange()
                            } catch(n) {console.log(n)}
                        },
                        t = new XDomainRequest;
                    return t.readyState = 0,
                        t.onreadystatechange = this.func.bind(null, this),
                        t.onload = function() {
                            xmlDoc = new ActiveXObject("Microsoft.XMLDOM"),
                                xmlDoc.async = "false",
                                xmlDoc.loadXML(t.responseText),
                                t.responseXML = xmlDoc,
                                e(t, 200)
                        },
                        t.onerror = function() {
                            e(t, 500)
                        },
                        t.ontimeout = function() {
                            e(t, 500)
                        },
                        t
                }) : Strophe.error("XDomainRequest not found. Falling back to native XHR implementation.")
        }
    });
    var config = Jabze.config;
    Jabze.converse = converse;
    // Set up converse.js
    converse.initialize({
        auto_list_rooms: false,
        auto_subscribe: true,
        bosh_service_url: (window.WebSocket && $.browser.safari) ? config.endpoint.websocket : config.endpoint.bosh,

        hide_muc_server: true,
        i18n: locales.en, // Refer to ./locale/locales.js to see which locales are supported
        prebind: true,
        xhr_user_search: false,
        animate: true
    });


    $('#not-show-jabze-noti').click(function () {
        window.localStorage.notShowJabezeNoti = 1;
        notiBox.fadeOut();
    });
    $("#why-disabled").click(function () {
        $(this).parent().hide();
        $("#info-disabled").show();
    });
    $(".close-chat").click(function () {
        notiBox.hide();
    });

    if (window.localStorage.notShowJabezeNoti == 0) {
        notiBox.delay(60 * 1000).fadeIn('slow');
    }

    var jabze_isConnecting = 0;




    var __ = function (str) {
        var t = converse.i18n.translate(str);
        if (arguments.length > 1) {
            return t.fetch.apply(t, [].slice.call(arguments, 1));
        } else {
            return t.fetch();
        }
    };

    var lazyLogin = setTimeout(function () {
        $('div#toggle-controlbox').click();

	    JabzeMixpanelHandle("autoOpen");
    }, 90 * 1000);

    $('.toggle-online-users').bind(
        'click',
        $.proxy(function (e) {
            e.preventDefault();
            if (converse.connection && converse.connection.connected === true) {
                converse.toggleControlBox();
            } else {
	            JabzeMixpanelHandle("connect");
            }
        }, this)
    );
    $('#jabze-show-help').click(function (e) {
        notiBox.toggle();
	    JabzeMixpanelHandle("showHelp");
        return false;
    });





    var ConnectionManager = (function(){
        "use strict";

        var endpoint = "";
        var jid = "";
        var password = "";
        var statusCallback = function(){};
        var reconnectAfterDisconnected = false;
        var pingData = {
            started: false,
            tid: 0,
            failure: 0,
            config: {
                duration: 25000,
                timeout: 3000,
                retryDelay: 100,
                maxRetryCount: 2
            }
        };
        var connecttionTimer = 0;
        var connection = {
            instance: null, // instance of Strophe.Connection
            status: {
                _no: -1,
                _text: "",
                set no(val){
                    for (var text in Strophe.Status) {
                        if (Strophe.Status.hasOwnProperty(text) && Strophe.Status[text] == val) {
                            this._no = val;
                            this._text = text.toLowerCase();
                            return;
                        }
                    }
                    throw new RangeError("Unknown status value: " + val + ". Please refer to Strophe.Status for valid status.");
                },
                get no(){
                    return this._no;
                },
                get text(){
                    return this._text;
                },
                get isError(){
                    return this._text == "connfail" || this._text == "authfail";
                },
                is: function(text){
                    return this._text == text;
                },
                reset: function(){
                    this._no = -1;
                    this._text = "";
                }
            },

            get isConnecting(){
                return this.instance
                    && !this.instance.connected
                    && !this.instance.disconnecting
                    && this.status.is("connecting");
            },
            get isConnected(){
                return this.instance
                    && this.instance.connected;
            },
            get isDisconnecting(){
                return this.instance
                    && this.instance.disconnecting;
            },
            get isDisconnected(){
                return !this.instance
                    || !this.instance.connected;
            },
            get isWebsocket(){
                return endpoint.indexOf("ws:") === 0 || endpoint.indexOf("wss:") === 0;
            },
            reset: function(){
                this.resetInstance();
                this.status.reset();
            },
            resetInstance: function(){
                this.instance.reset();
                this.instance.connectCount++;
            }
        };

        function connectionCallback(status, condition){
            condition = condition ? condition : "";
            connection.status.no = status;
            var msg = 'Connection state changed  >>> ' + status + '  --  ' + connection.status.text;
            if (condition) msg += '  ----  ' + condition;
            console.log(msg);

            switch (status) {
                case Strophe.Status.CONNECTED:
                    converse.showControlBox();
                    converse.onConnected(connection.instance);

                    connection.instance.vcard = {
                        'get': function (callback, jid) {
                        }
                    };
                    $('#toggle-controlbox').addClass('controlbox-online');
                    startPingLoop(function(){
                        if (connection.isConnected) {
                            connection.instance.disconnect();
                        }
                        endPingLoop();
                    });
                    break;
                case Strophe.Status.DISCONNECTED:
                    converse.giveFeedback(__('Disconnected'), 'error');
                    // reset the connection.
                    connection.resetInstance();
                    if (reconnectAfterDisconnected) {
                        setTimeout(function(){
                            console.log("Reconnecting after disconnected.");
                            reconnectAfterDisconnected = false;
                            doConnect();
                        }, 10);
                    }
                    endPingLoop();
                    break;
                case Strophe.Status.CONNECTING:
                    converse.giveFeedback(__('Connecting'));
                    checkConnectTimeOut();
                    break;
                case Strophe.Status.DISCONNECTING:
                    converse.giveFeedback(__('Disconnecting'), 'error');
                    break;
                case Strophe.Status.CONNFAIL:
                    converse.giveFeedback(__('Connection Failed'), 'error');
                    break;
                case Strophe.Status.AUTHFAIL:
                    converse.giveFeedback(__('Authentication Failed'), 'error');
                    break;
                case Strophe.Status.ATTACHED:
                    console.log(__('Attached'));
                    break;
                default:
                    break;
            }
        }

        function doConnect(){

            switch (true) {
                case !connection.instance:
                    console.log("Creating Strophe.Connection instance.", "debug");
                    connection.instance = new Strophe.Connection(endpoint);
                    break;
                case connection.isDisconnecting: // this should be ahead of "isConnected".
                    console.log("Strophe is disconnecting. Will do reconnect after disconnected.", "debug");
                    reconnectAfterDisconnected = true;
                    return;
                case connection.isConnecting:
                    console.log("Duplicated call. Strophe is connecting.");
                    return;
                case connection.isConnected:
                    console.log("Duplicated call. Strophe is already connected.");
                    return;
            }

            connection.instance.connect(jid, password, connectionCallback);
        }

        function doDisconnect(){

            switch (true) {
                case connection.isConnecting:
                    console.log("Strophe is connecting. Canceling it.");
                    connection.reset();
                    return;
                case connection.isDisconnected:
                    console.log("Duplicated call. Strophe is already disconnected.");
                    return;
                case connection.isDisconnecting:
                    console.log("Duplicated call. Strophe is disconnecting.");
                    return;
                case !connection.instance:
                    console.log("No connection is created yet. Why are you calling this?");
                    return;
            }

            console.log("Disconnecting....");
            connection.instance.disconnect();
        }

        function startPingLoop(onFailure){
            if (pingData.started) return;
            pingData.started = true;
            var server = bare(jid).split("@")[1];
            loop();

            function loop(timeout){
                pingData.tid = setTimeout(function(){
                    connection.instance.ping.ping(server, function(){
                        // don't clean the failure count, so if the user's
                        // network is terribly bad, we will also disconnect
                        if (pingData.failure) pingData.failure--;
                        loop();
                    }, function(){
                        if (++pingData.failure > pingData.config.maxRetryCount) {
                            onFailure();
                        } else {
                            loop(pingData.config.retryDelay);
                        }
                    }, pingData.config.timeout);
                }, timeout ? timeout : pingData.config.duration);
            }
        }
        function endPingLoop(){
            clearTimeout(pingData.tid);
            $.extend(pingData, {
                started: false,
                tid: 0,
                failure: 0
            });
        }
        function checkConnectTimeOut(){
            clearTimeout(connecttionTimer);
            connecttionTimer = setTimeout(
                function(){
                    if(converse.connection && converse.connection.connected === false){
                      //  jabze_isConnecting = 0;
                        console.log("connect time out");
                        converse.connection.disconnect();
                    }
                },20000);

        }
        function getServerUrl(){
            var server_url = converse.bosh_service_url;
            //for ie 9 set server url ,use ssl or not
            if ($.browser.msie === !0 && $.browser.version < 10 && window.location.protocol != "https:") {
                server_url = server_url.replace('https','http');
            }
            return server_url;
        }
        function bare(jid){
            if (typeof jid == "string") {
                return jid ? Strophe.getBareJidFromJid(jid).toLowerCase() : "";
            } else {
                return jid;
            }
        }
        return {

            init: function( j, pwd, onStatusChange){
                var ep = getServerUrl();
                endpoint = ep;
                jid = j;
                password = pwd;
                statusCallback = onStatusChange;
                console.log("Setup: endpoint = " + ep + ", jid = " + j);
            },
            connect: function(){
                doConnect();
            },
            disconnect: function(){
                doDisconnect();
            },
            get connection(){
                if (connection.instance) {
                    return connection.instance;
                }
                throw new Error("No connection is created yet.");
            },
            get status(){
                return {
                    no: connection.status.no,
                    text: connection.status.text,
                    toString: function(){ return connection.status.toString(); }
                }
            },
            get isConnected(){
                return connection.isConnected;
            },
            get hasError(){
                return connection.status.isError;
            }
        };
    })();

    ConnectionManager.init(config.jid,config.password,function(){});
    $('div#toggle-controlbox').click(function () {
        if(!config.enableConnect){
            notiBox.show();
            return;
        }
        notiBox.fadeOut();

        if ($.browser.msie && 9 > $.browser.version) {
            alert('The Jabze Chat plugin only supports Internet Explorer version 9 or above, your version of IE ' + $.browser.version + ' is not supported.');
            return false;
        }
        clearTimeout(lazyLogin);
        if (!ConnectionManager.isConnected && !ConnectionManager.isConnecting) {
           ConnectionManager.connect();
        }

    });
});
