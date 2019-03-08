jQuery(function($){
	window.tb_position = function(){

		var tbWindow	= $('#TB_window');

		if (tbWindow.length) {

			var tbIframeContent	= $('#TB_iframeContent');
			var tbAjaxContent	= $('#TB_ajaxContent');

			var width	= $(window).width();
			var	height	= $(window).height();

			if( tbIframeContent.length ){
				var H, W;

				if( 804 < width ) {
					W	= 784;
					H	= ( 720 < height) ? 600 : height - 120;
					H	= (TB_HEIGHT < H)?TB_HEIGHT:H;
				}else{
					W	= width - 20;
					H	= height - 80;;
				}

				tbIframeContent.width( W ).height( H );
				tbWindow.width( W );
				tbWindow.css({
					marginLeft: '-' + parseInt( ( W / 2 ), 10 ) + 'px',
					marginTop:	'-' + parseInt( ( H / 2 ), 10 ) + 'px',
				});
			}else if( tbAjaxContent.length ){
				if( 804 < width ) {
					height		= (height > 800)?700:height-100;
					TB_WIDTH	= 720;
				}else{
					height		= height - 80;
					TB_WIDTH	= width - 20;
				}

				if(tbWindow.css("visibility") != 'hidden') {
					TB_HEIGHT	= tbAjaxContent.prop("scrollHeight")+40;
					TB_HEIGHT	= (TB_HEIGHT > 200) ? TB_HEIGHT : 200;
				}

				TB_HEIGHT	= (TB_HEIGHT > height) ? height : TB_HEIGHT;

				tbAjaxContent.width(TB_WIDTH-50).height(TB_HEIGHT-57);

				tbWindow.width(TB_WIDTH).height(TB_HEIGHT);

				tbWindow.css({
					marginLeft: '-' + parseInt( ( TB_WIDTH / 2 ), 10 ) + 'px',
					marginTop:	'-' + parseInt( ( TB_HEIGHT / 2 ), 10 ) + 'px',
				});
			}else{
				// 默认图片效果
				tbWindow.css({marginLeft: '-' + parseInt((TB_WIDTH / 2),10) + 'px', width: TB_WIDTH + 'px'});
				tbWindow.css({marginTop: '-' + parseInt((TB_HEIGHT / 2),10) + 'px'});
			}
		}
	};

	var old_tb_remove = window.tb_remove;
	window.tb_remove = function(){
		old_tb_remove();

		if(isset(wpjam_page_setting.current_list_table)){
			var params_pairs	= window.location.search.substring(1).split('&');

			for(var i = params_pairs.length; i-- > 0;) {  
				if(params_pairs[i].lastIndexOf('action=', 0) !== -1 || params_pairs[i].lastIndexOf('id=', 0) !== -1){
					params_pairs.splice(i, 1);
				}
			}

			var replace_url	= window.location.origin + window.location.pathname + '?' + params_pairs.join('&');
			
			window.history.replaceState(null,null,replace_url);
		}
	}

	$(window).resize( function() {
		tb_position();
	});

	window.onpopstate = function(e) {
		if(isset(wpjam_page_setting.current_list_table)){
			var params_pairs	= window.location.search.substring(1).split('&');
			var params			= {};

			$.each(params_pairs, function() { 
				if($.inArray(this.split('=')[0], ['page', 'tab', '_wp_http_referer', '_wpnonce', 'action', 'action2']) == -1){
					params[this.split('=')[0]] = this.split('=')[1];
				}
			});

			wpjam_page_setting.params	= params;
			
			return $.wpjam_list_table_query_items(false);
		}
	};

	$.extend({
		wpjam_list_table_action: function(args){
			if(isset(args.post_type) && args.post_type){
				args.action 		= 'post-list-table-action';
			}else{
				args.action 		= 'list-table-action';
				args.plugin_page	= wpjam_page_setting.plugin_page;
				args.current_tab	= wpjam_page_setting.current_tab;
			}

			var bulk	= args.bulk;
			if(bulk){
				var	ids	= args.ids;			
			}else{
				var id	= args.id;
			}

			var item_prefix	= 'tr-';
			if(isset(args.post_type) && args.post_type){
				item_prefix	= 'post-';
			}

			var list_action_type	= args.list_action_type;
			var list_action			= args.list_action;
			
			if(list_action_type == 'submit'){
				$('.spinner').addClass('is-active');
				$('.list-table-action-notice').fadeOut(400);
			}else{
				// $('.list-table-notice').hide();

				$("body").append("<div id='TB_load'><img src='"+imgLoader.src+"' width='208' /></div>");
				$('#TB_load').show();
			}

			$.post(ajaxurl, args, function(data, status){
				var response = JSON.parse(data);

				if(response.errcode != 0){
					if(list_action_type == 'submit'){
						$('.spinner').removeClass('is-active');

						$('#TB_ajaxContent').scrollTop(0);
						
						$('.list-table-action-notice').removeClass('notice-success').addClass('notice-error').html('<p>'+response.errmsg+'</p>').fadeIn(400,function(){
							tb_position();
						});
					}else{
						$('#TB_load').remove();
						
						$('.list-table-notice').removeClass('notice-success').addClass('notice-error').html('<p>'+response.errmsg+'</p>').fadeIn(400,function(){
							alert(response.errmsg);
						});
					}
				}else{
					var response_type	= response.type;

					if(list_action_type == 'list'){
						$('#TB_load').remove();
						$('div.list-table').html(response.data);

						$('body').trigger('list_table_loaded');
					}else if(list_action_type == 'form'){
						$('#TB_load').remove();

						$('#tb_modal').html(response.form);
						tb_show(response.page_title, "#TB_inline?inlineId=tb_modal&height=200");
					}else if(list_action_type == 'direct'){
						$('#TB_load').remove();

						if(response_type != 'append'){
							$('.list-table-notice').removeClass('notice-error').addClass('notice-success').html('<p>'+response.errmsg+'</p>').fadeIn(400);
						}

						if(response_type == 'append'){
							$('#tb_modal').html(response.data);
							tb_show(response.page_title, "#TB_inline?inlineId=tb_modal&height=200");
						}else if(response_type == 'list'){
							$('div.list-table').html(response.data);
						}else if(response_type == 'add' || response_type == 'duplicate'){
							if(response.data){
								$('.wp-list-table tbody tr').first().before(response.data);
								$('.wp-list-table tbody tr').first().hide().css('background-color','#ffffee').fadeIn(400);
								$('.no-items').remove();
							}
						}else if(response_type == 'delete'){
							if(bulk){
								$.each(ids, function(index, id){
									if(id){
										$('.'+item_prefix+id).remove();
									}
								});
							}else{
								$('.'+item_prefix+id).remove();
							}
						}else{
							if(bulk){
								$.each(response.data, function(id, item){
									$.wpjam_list_table_update_item(id, item, item_prefix);
								});
							}else{
								$.wpjam_list_table_update_item(id, response.data, item_prefix);
							}
						}
					}else if(list_action_type == 'submit'){
						$(".spinner").removeClass('is-active');

						if(response_type == 'append'){
							var scrollto = $('#TB_ajaxContent')[0].scrollHeight;
							$('#TB_ajaxContent').scrollTop(scrollto-50);

							$('.response').html(response.data).fadeIn(400);
						}else{
							$('#TB_ajaxContent').html(response.form);
							$('#TB_ajaxContent').scrollTop(0);

							$('.list-table-action-notice').removeClass('notice-error').addClass('notice-success').html('<p>'+response.errmsg+'</p>').fadeIn(400);
							
							if(response_type == 'list'){
								$('div.list-table').html(response.data);
							}else if(response_type == 'add' || response_type == 'duplicate'){
								if(response.data){
									$('.wp-list-table tbody tr').first().before(response.data);
									$('.wp-list-table tbody tr').first().hide().css('background-color','#ffffee').fadeIn(400);
									$('.no-items').remove();
								}
							}else{
								if(bulk){
									$.each(response.data, function(id, item){
										$.wpjam_list_table_update_item(id, item, item_prefix);
									});
								}else{
									$.wpjam_list_table_update_item(id, response.data, item_prefix);
								}		
							}
						}
						// makeNoticesDismissible();
					}

					response.list_action = list_action
					$('body').trigger('list_table_action_success', response);

					if($('#TB_ajaxContent').length > 0){
						tb_position();
					}
				}
			});

			return false;
		},

		wpjam_list_table_update_item: function(id, item, item_prefix){
			if(id){
				if(item){
					$('.'+item_prefix+id).last().after('<span class="edit-'+item_prefix+id+'"></span>');
					$('.'+item_prefix+id).remove();
					$('.edit-'+item_prefix+id).before(item).remove();
				}
				
				$('.'+item_prefix+id).hide().css('background-color','#ffffee').fadeIn(400);
			}
		},

		wpjam_list_table_form: function(args){
			args.list_action_type	= 'form';
			
			$.wpjam_list_table_action(args);

			var replace_url = window.location.href+'&action='+args.list_action;

			if(args.list_action != 'add'){
				replace_url	+= '&id='+args.id;
			}

			if(args.data){
				replace_url	+= 	'&data='+encodeURIComponent(args.data);
			}

			window.history.replaceState(null, null, replace_url);
		},

		wpjam_list_table_query_items: function(pushState){
			$.wpjam_list_table_action({
				list_action_type:	'list',
				_ajax_nonce:		$('#_wpnonce').val(),
				data:				$.param(wpjam_page_setting.params)
			});

			if(pushState){
				var push_url = wpjam_page_setting.current_admin_url+'&'+$.param(wpjam_page_setting.params);

				if(window.location.href != push_url){
					window.history.pushState(null, null, push_url);
				}
			}

			return false;
		},

		wpjam_list_table_filter_action: function(params){
			wpjam_page_setting.params = {};

			$.each(params, function(index, param){
				if($.inArray(param.name, ['page', 'tab', 's', 'paged', '_wp_http_referer', '_wpnonce', 'action', 'action2']) == -1){
					wpjam_page_setting.params[param.name]	= param.value;	
				}
			});

			return $.wpjam_list_table_query_items(true);
		},

		wpjam_list_table_search_action: function(){
			wpjam_page_setting.params	= {};
			wpjam_page_setting.params.s	= $('#wpjam-search-input').val();

			return $.wpjam_list_table_query_items(true);
		},

		wpjam_list_table_sort_action: function(orderby, order){
			wpjam_page_setting.params.orderby	= orderby;
			wpjam_page_setting.params.order		= order;
			wpjam_page_setting.params.paged		= 1;

			return $.wpjam_list_table_query_items(true);
		},

		wpjam_list_table_pagination:function(paged){
			wpjam_page_setting.params.paged	= paged;

			$('#current-page-selector').val(paged);

			return $.wpjam_list_table_query_items(true);
		},

		wpjam_list_table_bulk_form: function(bulk_action, bulk_option, post_type){
			var ids		= new Array();
			post_type	= post_type || '';

			$('#the-list input[type="checkbox"]:checked').each(function(index, element){
				ids[index] = $(this).val();
			});

			if(ids.length == 0){
				alert('请至少选择一项！');
				return false;
			}

			$.wpjam_list_table_action({
				bulk:				true,
				post_type:			post_type,
				list_action_type:	bulk_option.data('direct')?'direct':'form',
				list_action:		bulk_action,
				ids:				ids,
				data:				bulk_option.data('data'),
				_ajax_nonce: 		bulk_option.data('nonce')
			});

			return false;
		},
		
		wpjam_page_action: function (args){
			args.action				= 'wpjam-page-action';
			args.plugin_page		= wpjam_page_setting.plugin_page;
			args.current_tab		= wpjam_page_setting.current_tab;
			
			var page_action_type	= args.page_action_type;
			var page_action			= args.page_action;
			var action_title		= args.action_title;

			if(page_action_type == 'submit'){
				$('.spinner').addClass('is-active');
			}

			$.post(ajaxurl, args, function(data, status){
				var response = JSON.parse(data);

				if(response.errcode != 0){
					if(page_action_type == 'submit'){
						$('.spinner').removeClass('is-active');
						$('.notice').removeClass().addClass('notice notice-error').html('<p>'+action_title+'失败：'+response.errmsg+'</p>').fadeIn(400);
					}else{
						alert(response.errmsg);
					}
				}else{
					if(page_action_type == 'submit'){
						var response_type	= response.type;

						$('.spinner').removeClass('is-active');
						$('.response').hide();

						if(response_type == 'append'){
							if($('#TB_ajaxContent').length > 0){
								var scrollto = $('#TB_ajaxContent')[0].scrollHeight;
							}
							
							$('.response').html(response.data);
							$('.response').fadeIn(400);

							if($('#TB_ajaxContent').length > 0){
								$('#TB_ajaxContent').scrollTop(scrollto);
							}
						}else{
							if($('#TB_ajaxContent').length > 0){
								$('#TB_ajaxContent').scrollTop(0);
							}
							
							if(isset(response.errmsg) && response.errmsg){
								$('.notice').removeClass().addClass('notice notice-info').html('<p>'+response.errmsg+'</p>').fadeIn(400);
							}else{
								$('.notice').removeClass().addClass('notice notice-success').html('<p>'+action_title+'成功</p>').fadeIn(400);
							}
						}

						if($('#TB_ajaxContent').length > 0){
							tb_position();
						}
						
						// makeNoticesDismissible();
					}else if(page_action_type == 'form'){
						$('#tb_modal').html(response.data);
						tb_show(action_title, "#TB_inline?inlineId=tb_modal&height=200");
					}
				
					response.page_action		= page_action;
					response.page_action_type	= page_action_type;

					$('body').trigger('page_action_success', response);
				}
			});

			return false;
		},

		wpjam_option_action: function(args){
			args.action	= 'wpjam-option-action';

			args.plugin_page		= wpjam_page_setting.plugin_page;
			args.current_tab		= wpjam_page_setting.current_tab;

			$('.spinner').addClass('is-active');

			$.post(
				ajaxurl, 
				args,
				function(data, status){
					var response = JSON.parse(data);
					if(response.errcode != 0){
						$('.spinner').removeClass('is-active');
						$('.notice').removeClass().addClass('notice notice-error').html('<p>保存失败：'+response.errmsg+'</p>').fadeIn(400);
					}else{
						var notice_msg	= '设置已保存。';
						if(isset(response.errmsg) && response.errmsg){
							notice_msg	= response.errmsg;	
						}

						$('.spinner').removeClass('is-active');
						$('.notice').removeClass().addClass('notice notice-success').html('<p>'+notice_msg+'</p>').fadeIn(400);

						$('body').trigger('option_action_success', response);
					}
				}
			);

			return false;
		},

		wpjam_query_posts:function (args, datalist){
			args.action 		= 'query_posts';
			args.post_status 	= 'publish';
			args.posts_per_page = 10;

			$.post(
				ajaxurl, 
				args,
				function(data, status){
					$('datalist#'+args.datalist).empty();
					$.each(JSON.parse(data), function(index, value){
						$('datalist#'+args.datalist).append('<option value="'+value.ID+'" label="'+value.post_title+'"></option>');
					});
				}
			)
		}
	});
	
	$('body').on('click', '.list-table-action', function(){
		if($(this).data('confirm')){
			if(confirm('确定要'+$(this).attr('title')+'吗?') == false){
				return false;
			}
		}

		var list_action_type = $(this).data('direct')?'direct':'form';

		var args = {
			list_action_type:	'direct',
			list_action:		$(this).data('action'),
			id:					$(this).data('id'),
			data:				$(this).data('data'),
			_ajax_nonce: 		$(this).data('nonce')
		};

		if(list_action_type == 'form'){
			$.wpjam_list_table_form(args);
		}else{
			$.wpjam_list_table_action(args);
		}
		
	});

	$('body').on('click', '.post-list-table-action', function(){
		if($(this).data('confirm')){
			if(confirm('确定要'+$(this).attr('title')+'吗?') == false){
				return false;
			}
		}

		$.wpjam_list_table_action({
			post_type:			$('input[name=post_type]').val(),
			screen:				$('input[name=screen]').val(),
			list_action_type:	$(this).data('direct')?'direct':'form',
			list_action:		$(this).data('action'),
			id:					$(this).data('id'),
			data:				$(this).data('data'),
			_ajax_nonce: 		$(this).data('nonce')
		});
	});

	$('body').on('submit', "#list_table_form", function(e){

		var active_element_id	= $(document.activeElement).attr('id');

		if(active_element_id == 'current-page-selector'){
			return $.wpjam_list_table_pagination($('#current-page-selector').val());
		}else if(active_element_id == 'search-submit' || active_element_id == 'wpjam-search-input'){
			return $.wpjam_list_table_search_action();
		}else if(active_element_id == 'export_action'){
			return;
		}else if(active_element_id == 'filter_action'){
			return $.wpjam_list_table_filter_action($(this).serializeArray());
		}else if(active_element_id == 'doaction'){
			var bulk_action	= $('select[name="action"]').val();
			var bulk_option	= $('select[name="action"]').find("option:selected");

			return $.wpjam_list_table_bulk_form(bulk_action, bulk_option);
		}else if(active_element_id == 'doaction2'){
			var bulk_action	= $('select[name="action2"]').val();
			var bulk_option	= $('select[name="action2"]').find("option:selected");

			return $.wpjam_list_table_bulk_form(bulk_action, bulk_option);
		}
	});

	$('body').on('submit', "#posts-filter", function(e){

		var active_element_id	= $(document.activeElement).attr('id');

		if(active_element_id == 'doaction'){
			var bulk_action	= $('select[name="action"]').val();
			var bulk_option	= $('select[name="action"]').find("option:selected");

			var post_type	= $('input[name=post_type]').val();

			if(bulk_option.data('action')){
				return $.wpjam_list_table_bulk_form(bulk_action, bulk_option, post_type);
			}
		}else if(active_element_id == 'doaction2'){
			var bulk_action	= $('select[name="action2"]').val();
			var bulk_option	= $('select[name="action2"]').find("option:selected");

			if(bulk_option.data('action')){
				return $.wpjam_list_table_bulk_form(bulk_action, bulk_option, post_type);
			}
		}
	});

	$('body').on('click', '#list_table_form .list-table-filter', function(){
		return $.wpjam_list_table_filter_action($(this).data('filter'));
	});

	$('body').on('click', '#list_table_form .first-page', function(){
		return $.wpjam_list_table_pagination(1);
	});

	$('body').on('click', '#list_table_form .prev-page', function(){
		var current_page	= parseInt($('#current-page-selector').val());
		return $.wpjam_list_table_pagination(current_page-1);
	});

	$('body').on('click', '#list_table_form .next-page', function(){
		var current_page	= parseInt($('#current-page-selector').val());
		return $.wpjam_list_table_pagination(current_page+1);
	});

	$('body').on('click', '#list_table_form .last-page', function(){
		var total_page	= $('.total-pages').html().replace(/,/,'');
		return $.wpjam_list_table_pagination(total_page);
	});

	$('body').on('click', '#list_table_form .list-table-sort', function(){
		return $.wpjam_list_table_sort_action($(this).data('orderby'), $(this).data('order'));
	});

	$('body').on('submit', '#list_table_action_form', function(e){
		e.preventDefault();	// 阻止事件默认行为。

		var args	= {	
			list_action_type:	'submit',
			bulk: 				$(this).data('bulk'),
			data: 				$(this).serialize(),
			list_action:		$(this).data('action'),
			_ajax_nonce: 		$(this).data('nonce')
		};

		if($(this).data('bulk')){
			args.ids	= $(this).data('ids');
		}else{
			args.id		= $(this).data('id');
		}

		$.wpjam_list_table_action(args);
	});

	$('body').on('submit', '#post_list_table_action_form', function(e){

		e.preventDefault();	// 阻止事件默认行为。

		var args	= {	
			post_type:			$('input[name=post_type]').val(),
			screen:				$('input[name=screen]').val(),
			list_action_type:	'submit',
			bulk: 				$(this).data('bulk'),
			data: 				$(this).serialize(),
			list_action:		$(this).data('action'),
			_ajax_nonce: 		$(this).data('nonce')
		};

		if($(this).data('bulk')){
			args.ids	= $(this).data('ids');
		}else{
			args.id		= $(this).data('id');
		}

		$.wpjam_list_table_action(args);
	});

	$('body').on('click', '.wpjam-button', function(e){
		e.preventDefault();	// 阻止事件默认行为。

		if($(this).data('confirm')){
			if(confirm('确定要'+$(this).data('title')+'吗?') == false){
				return false;
			}
		}

		$.wpjam_page_action({
			page_action_type:	$(this).data('direct')?'direct':'form',
			data:				$(this).data('data'),
			page_action:		$(this).data('action'),
			action_title:		$(this).data('title'),
			_ajax_nonce:		$(this).data('nonce')
		});
	});

	$('body').on('submit', '#wpjam_form', function(e){
		e.preventDefault();	// 阻止事件默认行为。

		$.wpjam_page_action({
			page_action_type:	'submit',
			data: 				$(this).serialize(),
			page_action:		$(this).data('action'),
			action_title:		$(this).data('title'),
			_ajax_nonce:		$(this).data('nonce')
		});
	});

	$('body').on('submit', '#wpjam_option', function(e){
		e.preventDefault();	// 阻止事件默认行为。

		$.wpjam_option_action({
			data:	$(this).serialize()
		});
	});

	$('body').on('input', 'input.post_id', function(){
		var search_term = $(this).val();

		if($.isNumeric(search_term) == false){
			$.wpjam_query_posts({
				datalist:	$(this).attr('list'),
				post_type:	$(this).data('post_type'),
				s: search_term
			});
		}
	});

	$('body').on('focus', 'input.post_id', function(){
		if($('datalist#'+$(this).attr('list')+' option').length == 0){
			$.wpjam_query_posts({
				datalist:	$(this).attr('list'),
				post_type:	$(this).data('post_type')
			});
		}
	});
});

		
function show_wx_img(src, iframe_width=0, iframe_height=0, url='') {
	if(iframe_width){
		var img_html	= '<img id="img" src=\'' + src + '?' + Math.random() + '\' />';
	}else{
		var img_html	= '<img id="img" style="max-width:100%;" src=\'' + src + '?' + Math.random() + '\' />';
	}
	
	if(url){
		img_html	= '<a href="'+url+'" target="_blank">'+img_html+'</a>';
	}

	var frameid		= 'frameimg' + Math.random();

	if(iframe_width){
		window.iframe_html = '<body style="margin:0;padding:0;">'+img_html+'<script>window.onload = function() {wx_iframe=parent.document.getElementById(\'' + frameid + '\'); wx_img = document.getElementById(\'img\'); iframe_width=wx_iframe.width; iframe_height=wx_iframe.height; img_width = wx_img.width; img_height = wx_img.height; if((img_width/img_height)>(iframe_width/iframe_height)){ wx_img.style.height=\'100%\'; img_width=Math.ceil(iframe_height/img_height*img_width); wx_img.style.marginLeft=(iframe_width - img_width) / 2+\'px\'; }else{ wx_img.style.width=\'100%\'; img_height=Math.ceil(iframe_width/img_width*img_height); wx_img.style.marginTop=(iframe_height - img_height) / 2+\'px\'; } }<' + '/script></body>';

		return '<iframe id="' + frameid + '" src="javascript:parent.iframe_html;" width="'+iframe_width+'" height="'+iframe_height+'" frameBorder="0" scrolling="no"></iframe>';
		
		// document.write('<iframe id="' + frameid + '" src="javascript:parent.iframe_html;" width="'+iframe_width+'" height="'+iframe_height+'" frameBorder="0" scrolling="no"></iframe>');
	}else{
		window.iframe_html = '<body style="margin:0;padding:0;">'+img_html+'<script>window.onload = function() { parent.document.getElementById(\'' + frameid + '\').height = document.getElementById(\'img\').height+\'px\'; }<' + '/script></body>';

		return '<iframe id="' + frameid + '" src="javascript:parent.iframe_html;" width="100%" frameBorder="0" scrolling="no"></iframe>';
		// document.write('<iframe id="' + frameid + '" src="javascript:parent.iframe_html;" width="100%" frameBorder="0" scrolling="no"></iframe>');
	}
}

