weixin_data.desc	= weixin_data.desc || weixin_data.link;

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
	// alert(weixin_data.screen_width);
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
			// alert(html);
		}
	});

	//jQuery(window).trigger('weixin_view',view_type);
}

function weixin_robot_share(share_type){
	alert(share_type);
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
		document.location.href = weixin_data.refresh_url;	
	}
}

function weixin_robot_cancle_share(){
	if(weixin_data.notify == 1 && weixin_data.post_id != 0){
		alert('取消可是没有积分的哦。');
	}
}


/*微信 JS SDK 封装*/
wx.config({
	debug:		false,
	appId: 		weixin_data.appid,
	timestamp:	weixin_data.timestamp,
	nonceStr:	weixin_data.nonce_str,
	signature:	weixin_data.signature,
	jsApiList:	[
		'checkJsApi',
		'onMenuShareTimeline',
		'onMenuShareAppMessage',
		'onMenuShareQQ',
		'onMenuShareWeibo',
		'hideMenuItems',
		'showMenuItems',
		'hideAllNonBaseMenuItem',
		'showAllNonBaseMenuItem',
		'translateVoice',
		'startRecord',
		'stopRecord',
		'onRecordEnd',
		'playVoice',
		'pauseVoice',
		'stopVoice',
		'uploadVoice',
		'downloadVoice',
		'chooseImage',
		'previewImage',
		'uploadImage',
		'downloadImage',
		'getNetworkType',
		'openLocation',
		'getLocation',
		'hideOptionMenu',
		'showOptionMenu',
		'closeWindow',
		'scanQRCode',
		'chooseWXPay',
		'openProductSpecificView',
		'addCard',
		'chooseCard',
		'openCard'
	]
});

var shareTo = '';

wx.ready(function () {

	if(weixin_data.hide_option_menu == 1){
		wx.hideOptionMenu();
	}

	if(weixin_data.content_wrap){
		var src_list = [];
		jQuery.each(jQuery(weixin_data.content_wrap+' img'),function(i,item){
			if(item.src) {
				src_list.push(item.src);
				jQuery(item).click(function(e){
					wx.previewImage({
						current:	item.src,
						urls:		src_list
					});
				});
			}
		});
	}

	wx.getNetworkType({
		success: function (res) {
			weixin_data.network_type = res.networkType;
			weixin_data.screen_width	= window.screen.width;
			weixin_data.screen_height	= window.screen.height;
			weixin_data.retina			= window.devicePixelRatio;
			weixin_data.view_type		= weixin_robot_get_query_string('from');
			weixin_data.refer			= weixin_robot_get_refer();
			weixin_robot_pageview();
		},
		fail: function (res) {
			//alert(JSON.stringify(res));
			return '';
		}
	});

	wx.onMenuShareTimeline({
		title:	weixin_data.title,	// 分享标题
		link: 	weixin_data.link,	// 分享链接
		imgUrl:	weixin_data.img,	// 分享图标
		trigger: function (res) {
		},
		success: function () { 
			weixin_data.share_type = 'ShareTimeline';
			weixin_robot_share('ShareTimeline');
		},
		cancel: function () { 
			weixin_robot_cancle_share();
		},
		fail: function (res) {
			alert(JSON.stringify(res));
		}
	});

	wx.onMenuShareAppMessage({
		title:	weixin_data.title,	// 分享标题
		desc:	weixin_data.desc,	// 分享描述
		link: 	weixin_data.link,	// 分享链接
		imgUrl:	weixin_data.img, 	// 分享图标
		type:	'link', 			// 分享类型,music、video或link，不填默认为link
		dataUrl: '', 				// 如果type是music或video，则要提供数据链接，默认为空
		trigger: function (res) {
			shareTo = res.shareTo;
			alert(shareTo);
		},
		success: function (res) { 
			if(shareTo == 'friend' || typeof(shareTo) == "undefined"){
				weixin_robot_share('ShareAppMessage');
			}else{
				weixin_robot_share(shareTo);
			}
		},

		cancel: function (res) { 
		    weixin_robot_cancle_share();
		},
		fail: function (res) {
			alert(JSON.stringify(res));
		}
	});

	wx.onMenuShareQQ({
		title:	weixin_data.title,	// 分享标题
		desc:	weixin_data.desc,	// 分享描述
		link: 	weixin_data.link,	// 分享链接
		imgUrl:	weixin_data.img, 	// 分享图标
		trigger: function (res) {
		},
		success: function (res) { 
		   weixin_robot_share('ShareQQ');
		},
		cancel: function (res) { 
			weixin_robot_cancle_share();
		},
		fail: function (res) {
			alert(JSON.stringify(res));
		}
	});

	wx.onMenuShareWeibo({
		title:	weixin_data.title,	// 分享标题
		desc:	weixin_data.desc,	// 分享描述
		link: 	weixin_data.link,	// 分享链接
		imgUrl:	weixin_data.img, 	// 分享图标
		trigger: function (res) {
		},
		success: function (res) { 
		   weixin_robot_share('ShareWeibo');
		},
		cancel: function (res) { 
			weixin_robot_cancle_share();
		},
		fail: function (res) {
			alert(JSON.stringify(res));
		}
	});
});

wx.error(function () {

});



