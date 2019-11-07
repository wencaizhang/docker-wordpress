/*function htmlEncode(e) {
    return e.replace(/&/g, "&amp;").replace(/ /g, "&nbsp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/\n/g, "<br />").replace(/"/g, "&quot;")
}

function htmlDecode(e) {
    return e.replace(/&#39;/g, "'").replace(/<br\s*(\/)?\s*>/g, "\n").replace(/&nbsp;/g, " ").replace(/&lt;/g, "<").replace(/&gt;/g, ">").replace(/&quot;/g, '"').replace(/&amp;/g, "&")
}*/


//weixin_data.title	= htmlDecode(weixin_data.title),
//weixin_data.desc	= htmlDecode(weixin_data.desc),

function weixin_close_window() {
	//WeixinJSBridge.call("closeWindow");
	WeixinJSBridge.invoke("closeWindow", {});
}

function weixin_scan_qrcode(){
	WeixinJSBridge.invoke("scanQRCode", {});
}

function weixin_send_mail(title, content){
	WeixinJSBridge.invoke("sendEmail",{
	    "title" : title,
	    "content" : content
	});
}

function weixin_image_preview(curSrc,srcList) {
	if(!curSrc || !srcList || srcList.length == 0) {
		return;
	}
	WeixinJSBridge.invoke('imagePreview', {
		'current' : curSrc,
		'urls' : srcList
	});
}

function weixin_hide_option_menu(){
	WeixinJSBridge.call("hideOptionMenu");
}

function weixin_show_option_menu(){
	WeixinJSBridge.call("showOptionMenu");
}

function weixin_hide_toolbar(){
	WeixinJSBridge.call("hideToolbar");
}

function weixin_show_toolbar(){
	WeixinJSBridge.call("showToolbar");
}

function weixin_open_url_by_ext_browser(url){
	WeixinJSBridge.invoke("openUrlByExtBrowser",{"url" : url});
}

function weixin_jump_to_biz_profile(){
	WeixinJSBridge.invoke(
		"jumpToBizProfile",{
			"tousername" : username
		},
        function(e){
            alert(e.err_msg);
        }
    );
}

//添加微信账号
function weixin_add_contact(username){
    WeixinJSBridge.invoke("addContact", {
    	"webtype": "1",
    	"username": username
    }, function(e) {
	    WeixinJSBridge.log(e.err_msg);
	    //e.err_msg:add_contact:added 已经添加
	    //e.err_msg:add_contact:cancel 取消添加
	    //e.err_msg:add_contact:ok 添加成功
	    if(e.err_msg == 'add_contact:added' || e.err_msg == 'add_contact:ok'){
	            //关注成功，或者已经关注过
	    }
    });
}

function weixin_open_product_view(latitude,longitude,name,address,scale,infoUrl){
	WeixinJSBridge.invoke("openProductView",{
	    "latitude" : latitude, //纬度
	    "longitude" : longitude, //经度
	    "name" : name, //名称
	    "address" : address, //地址
	    "scale" : scale, //地图缩放级别
	    "infoUrl" : infoUrl, //查看位置界面底部的超链接            
	});
}

weixin_data.desc	= weixin_data.desc || weixin_data.link;

function weixin_robot_cancle_share(){
	if(weixin_data.notify == 1 && weixin_data.post_id != 0){
		alert('取消可是没有积分的哦。');
	}
}

function weixin_robot_get_query_string(name){
	var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)"); 
	var r = window.location.search.substr(1).match(reg); 
	if (r!=null){
		return unescape(r[2]);
	}else{
		return ''; 
	}
}

function weixin_robot_set_cookie(c_name,value,expiredays){
	var exdate	= new Date();
	exdate.setDate(exdate.getDate()+expiredays);
	document.cookie	= c_name+ "=" +escape(value)+ ((expiredays==null) ? "" : ";expires="+exdate.toGMTString());
}

function weixin_robot_get_cookie(c_name){
	if (document.cookie.length>0){
		c_start=document.cookie.indexOf(c_name + "=");
		if (c_start != -1){ 
			c_start	= c_start + c_name.length+1;
			c_end	= document.cookie.indexOf(";",c_start);
			if (c_end == -1) c_end = document.cookie.length;
			return unescape(document.cookie.substring(c_start, c_end));
		} 
	}
	return "";
}

function weixin_robot_get_refer(){
	var weixin_refer = weixin_robot_get_query_string('weixin_refer');
	if(weixin_refer != ''){
		weixin_robot_set_cookie('weixin_refer', weixin_refer, 1);
		return weixin_refer;
	}else{
		return weixin_robot_get_cookie('weixin_refer');
	}
}

function weixin_robot_pageview(){
	jQuery.ajax({
		type: "post",
		url: weixin_data.ajax_url,
		data: { 
			action:			'weixin_view', 
			sub_type:		weixin_data.view_type,
			post_id: 		weixin_data.post_id,
			link: 			weixin_data.link, 
			refer:			weixin_data.refer,	
			network_type:   weixin_data.network_type,
			screen_width:	weixin_data.screen_width,
			screen_height:	weixin_data.screen_height,
			retina:			weixin_data.retina,
			_ajax_nonce: 	weixin_data.nonce
		},
		success: function(html){
			// do nothing
		}
	});

	//jQuery(window).trigger('weixin_view',view_type);
}

function weixin_robot_share(share_type){
	jQuery.ajax({
		type: "post",
		url: weixin_data.ajax_url,
		data: { 
			action:			'weixin_share', 
			sub_type:		share_type,
			post_id:		weixin_data.post_id, 
			link:			weixin_data.link, 
			refer:			weixin_data.refer,
			network_type:	weixin_data.network_type,
			screen_width:	weixin_data.screen_width,
			screen_height:	weixin_data.screen_height,
			retina:			weixin_data.retina,
			_ajax_nonce: 	weixin_data.nonce
		},
		success: function(html){
			if(weixin_data.notify == 1 && html != false){
				alert(html);
			}
		}
	});

	jQuery(window).trigger('weixin_share',share_type);

	if(weixin_data.refresh_url != ''){
		alert(weixin_data.refresh_url);
		document.location.href = weixin_data.refresh_url;	
	}
}


	
(function(){
	var onBridgeReady=function(){

		if(weixin_data.hide_option_menu == 1){
			weixin_hide_option_menu();
		}

		if(weixin_data.hide_toolbar == 1){
			weixin_hide_toolbar();
		}

		WeixinJSBridge.invoke('getNetworkType', {}, function (e) {
			weixin_data.network_type	= e.err_msg;
			weixin_data.screen_width	= window.screen.width;
			weixin_data.screen_height	= window.screen.height;
			weixin_data.retina			= window.devicePixelRatio;
			weixin_data.view_type		= weixin_robot_get_query_string('from');
			weixin_data.refer			= weixin_robot_get_refer();
			weixin_robot_pageview();
		});

		var srcList = [];
		jQuery.each(jQuery(weixin_data.content_wrap+' img'),function(i,item){
			if(item.src) {
				srcList.push(item.src);
				jQuery(item).click(function(e){
					// 通过这个API就能直接调起微信客户端的图片播放组件了
					weixin_image_preview(item.src,srcList);
				});
			}
		});

		WeixinJSBridge.on('menu:share:appmessage', function(argv){
			WeixinJSBridge.invoke('sendAppMessage',{
				"appid":		weixin_data.appid,
				"img_url":		weixin_data.img,
				"img_width":	"120",
				"img_height":	"120",
				"link":			weixin_data.link,
				"desc":			weixin_data.desc,
				"title":		weixin_data.title
			}, function(res){
				if(argv.shareTo == 'friend'){
					if(res.err_msg == 'send_app_msg:cancel'){
						weixin_robot_cancle_share();
					}else {
						weixin_robot_share('SendAppMessage');
					}
				}else{
					weixin_robot_share(argv.shareTo)
				}
			});
		});
		// 分享到朋友圈;
		WeixinJSBridge.on('menu:share:timeline', function(argv){
			WeixinJSBridge.invoke('shareTimeline',{
				"appid":		weixin_data.appid,
				"img_url":		weixin_data.img,
				"img_width":	"120",
				"img_height": 	"120",
				"link":			weixin_data.link,
				"desc":			weixin_data.desc,
				"title":		weixin_data.title
			}, function(res){
				if(res.err_msg == 'share_timeline:cancel'){
					weixin_robot_cancle_share();
				}else {
					weixin_robot_share('ShareTimeline');
				}
			});
		});
		// 分享到微博;
		WeixinJSBridge.on('menu:share:weibo', function(argv){
			WeixinJSBridge.invoke('shareWeibo',{
				"content":		weixin_data.title+' '+weixin_data.link,
				"url":			weixin_data.link
			}, function(res){
				if(res.err_msg == 'share_weibo:cancel'){
					weixin_robot_cancle_share();
				}else {
					weixin_robot_share('ShareWeibo');
				}
			});
		});
		// 分享到Facebook
		WeixinJSBridge.on('menu:share:facebook', function(argv){
			WeixinJSBridge.invoke('shareFB',{
				"img_url":		weixin_data.img,
				"img_width":	"120",
				"img_height":	"120",
				"link":			weixin_data.link,
				"desc":			weixin_data.desc,
				"title":		weixin_data.title
			}, function(res){
				if(res.err_msg == 'share_fb:cancel'){
					weixin_robot_cancle_share();
				}else {
					weixin_robot_share('ShareFB');
				}
			});
		});
	};
	if(document.addEventListener){
		document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
	}else if(document.attachEvent){
		document.attachEvent('WeixinJSBridgeReady',		onBridgeReady);
		document.attachEvent('onWeixinJSBridgeReady',	onBridgeReady);
	}
})();