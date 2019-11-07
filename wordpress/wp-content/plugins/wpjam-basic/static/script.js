jQuery(function($){
	window.tb_position = function(){

		var tbWindow	= $('#TB_window');

		if (tbWindow.length) {

			var tbIframeContent	= $('#TB_iframeContent');
			var tbAjaxContent	= $('#TB_ajaxContent');

			var win_width	= $(window).width();
			var	win_height	= $(window).height();

			if( tbIframeContent.length ){
				var H, W;

				if( 804 < win_width ) {
					W	= 784;
					H	= ( 720 < win_height) ? 600 : win_height - 120;
					H	= (TB_HEIGHT < H)?TB_HEIGHT:H;
				}else{
					W	= win_width - 20;
					H	= win_height - 80;;
				}

				tbIframeContent.width( W ).height( H );
				tbWindow.width( W );
				tbWindow.css({
					marginLeft: '-' + parseInt( ( W / 2 ), 10 ) + 'px',
					marginTop:	'-' + parseInt( ( H / 2 ), 10 ) + 'px',
				});
			}else if( tbAjaxContent.length ){
				var H, W;

				if( 804 < win_width ) {
					win_height	= win_height-100;
					win_height	= Math.min(win_height, 700);

					W	= Math.min(TB_WIDTH, 720);
				}else{
					win_height	= win_height - 80;

					win_width	= win_width - 20;
					W	= Math.min(TB_WIDTH, win_width);
				}

				if(tbWindow.css('visibility') != 'hidden') {
					H	= Math.max(tbAjaxContent.prop('scrollHeight')+40, TB_HEIGHT);
				}else{
					H	= TB_HEIGHT;
				}

				H	= Math.min(H, win_height);

				tbAjaxContent.width(W-50).height(H-57);

				tbWindow.width(W).height(H);

				tbWindow.css({
					marginLeft: '-' + parseInt( ( W / 2 ), 10 ) + 'px',
					marginTop:	'-' + parseInt( ( H / 2 ), 10 ) + 'px',
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

		if(isset(wpjam_page_setting) && isset(wpjam_page_setting.current_list_table)){
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
			args.action 		= 'wpjam-list-table-action';
			args.screen_id		= wpjam_page_setting.screen_id;
			args.plugin_page	= wpjam_page_setting.plugin_page;
			args.current_tab	= wpjam_page_setting.current_tab;

			var list_action_type	= args.list_action_type;
			var list_action			= args.list_action;
			var item_prefix			= $.wpjam_list_table_item_prefix();
			
			if(list_action_type == 'submit'){
				$('.spinner').addClass('is-active');
				$('.list-table-action-notice').fadeOut(400);
			}else{
				$('body').append("<div id='TB_load'><img src='"+imgLoader.src+"' width='208' /></div>");
				$('#TB_load').show();

				if(args.bulk){
					$.each(args.ids, function(index, id){
						var tr_id	= $.wpjam_list_table_tr_id(id);
						$(item_prefix+tr_id+' .check-column input').after('<span class="spinner is-active"></span>').hide();	
					});
				}else{
					if(args.id){
						var tr_id	= $.wpjam_list_table_tr_id(args.id);
						$(item_prefix+tr_id+' .check-column input').after('<span class="spinner is-active"></span>').hide();
					}
				}

				$('.list-table-notice').fadeOut(400);
			}

			$.post(ajaxurl, args, function(data, status){
				var response	= (typeof data == 'object') ? data : JSON.parse(data);

				if(list_action_type == 'submit'){
					$('.spinner').removeClass('is-active');
				}else{
					$('#TB_load').remove();

					if(args.bulk){
						$.each(args.ids, function(index, id){
							var tr_id	= $.wpjam_list_table_tr_id(id);
							$(item_prefix+tr_id+' .check-column input').show();
							$(item_prefix+tr_id+' .check-column .spinner').remove();
						});
					}else{
						if(args.id){
							var tr_id	= $.wpjam_list_table_tr_id(args.id);

							$(item_prefix+tr_id+' .check-column input').show();
							$(item_prefix+tr_id+' .check-column .spinner').remove();
						}
					}
				}
				
				if($('.list-table-notice').length < 1){
					$('hr.wp-header-end').after('<div class="list-table-notice notice inline is-dismissible hidden"></div>');	
				}
				
				if(response.errcode != 0){
					if(list_action_type == 'submit'){
						$('#TB_ajaxContent').scrollTop(0);
						$('.list-table-action-notice').removeClass('notice-success').addClass('notice-error').html('<p>'+response.errmsg+'</p>').fadeIn(400,function(){
							tb_position();
						});
					}else{
						
						$('.list-table-notice').removeClass('notice-success').addClass('notice-error').html('<p>'+response.errmsg+'</p>').fadeIn(400,function(){
							alert(response.errmsg);
						});
					}
				}else{
					var response_type	= response.type;

					args.tb_width	= args.tb_width || 720;
					args.tb_height	= args.tb_height || 200;

					$('.wp-list-table tbody tr').css('background-color', '');

					if(list_action_type == 'list'){
						$('div.list-table').html(response.data);

						$(document).scrollTop(0);

						$('body').trigger('list_table_loaded');
					}else if(list_action_type == 'form'){
						$('#tb_modal').html(response.form);
						tb_show(response.page_title, '#TB_inline?inlineId=tb_modal&width='+args.tb_width+'&height='+args.tb_height);
					}else if(list_action_type == 'direct'){
						if(response_type == 'append'){
							$('#tb_modal').html(response.data);
							tb_show(response.page_title, '#TB_inline?inlineId=tb_modal&width='+args.tb_width+'&height='+args.tb_height);
						}else if(response_type == 'list'){
							$('div.list-table').html(response.data);
						}else if(response_type == 'add' || response_type == 'duplicate'){
							if(response.data){
								if(isset(response.last)){
									$('.wp-list-table tbody tr').last().after(response.data);
									$('.wp-list-table tbody tr').last().hide().css('background-color','#ffffee').fadeIn(400);

									$(document).scrollTop($('.wp-list-table tbody tr').last().offset().top-100);
								}else{
									$('.wp-list-table tbody tr').first().before(response.data);
									$('.wp-list-table tbody tr').first().hide().css('background-color','#ffffee').fadeIn(400);
								}
								
								$('.no-items').remove();
							}
						}else if(response_type == 'up' || response_type == 'down'){
							tr_id	= $.wpjam_list_table_tr_id(args.id);

							if(response_type == 'up'){
								tr_next	= $.wpjam_list_table_tr_id(args.next);
								$(item_prefix+tr_next).insertAfter(item_prefix+tr_id);	
							}else{
								tr_prev	= $.wpjam_list_table_tr_id(args.prev);
								$(item_prefix+tr_id).insertAfter(item_prefix+tr_prev);
							}
							$.wpjam_list_table_update_item(args.id, '', '#eeffff');
							$.wpjam_init_list_table_sortable();
						}else if(response_type == 'move'){
							$.wpjam_list_table_update_item(args.id, response.data, '#eeffee');
							$('.wp-list-table tbody').sortable('enable');
							$.wpjam_init_list_table_sortable();
						}else if(response_type == 'delete'){
							$.wpjam_list_table_delete_items(args);
						}else{
							if(args.bulk){
								var bg_color	= '#ffffdd';
								$.each(response.data, function(id, item){
									bg_color 	= bg_color == '#ffffdd' ? '#ffffee' : '#ffffdd';
									$.wpjam_list_table_update_item(id, item, bg_color);
								});
							}else{
								$.wpjam_list_table_update_item(args.id, response.data);
							}
						}

						if($.inArray(response_type, ['append', 'up', 'down', 'move']) == -1){
							$('.list-table-notice').removeClass('notice-error').addClass('notice-success').html('<p>'+response.errmsg+'</p>').fadeIn(400);
						}
					}else if(list_action_type == 'submit'){
						$(".spinner").removeClass('is-active');

						if(response_type == 'append'){
							var scrollto = $('#TB_ajaxContent')[0].scrollHeight;
							$('#TB_ajaxContent').scrollTop(scrollto-50);

							$('.response').html(response.data).fadeIn(400);
						}else if(response_type == 'delete'){
							tb_remove();

							$('.list-table-notice').removeClass('notice-error').addClass('notice-success').html('<p>'+response.errmsg+'</p>').fadeIn(400);

							$wpjam_list_table_delete_items(args);
						}else{
							$('#TB_ajaxContent').html(response.form);
							$('#TB_ajaxContent').scrollTop(0);
							
							if(response_type == 'list'){
								$('div.list-table').html(response.data);
							}else if(response_type == 'add' || response_type == 'duplicate'){
								if(response.data){
									if(isset(response.last)){
										$('.wp-list-table tbody tr').last().after(response.data);
										$('.wp-list-table tbody tr').last().hide().css('background-color','#ffffee').fadeIn(400);
										$(document).scrollTop($('.wp-list-table tbody tr').last().offset().top);
									}else{
										$('.wp-list-table tbody tr').first().before(response.data);
										$('.wp-list-table tbody tr').first().hide().css('background-color','#ffffee').fadeIn(400);
									}
									
									$('.no-items').remove();
								}
							}else{
								if(args.bulk){
									var bg_color	= '#ffffdd';
									$.each(response.data, function(id, item){
										bg_color 	= bg_color == '#ffffdd' ? '#ffffee' : '#ffffdd';
										$.wpjam_list_table_update_item(id, item, bg_color);
									});
								}else{
									$.wpjam_list_table_update_item(args.id, response.data);
								}		
							}

							$('.list-table-action-notice').removeClass('notice-error').addClass('notice-success').html('<p>'+response.errmsg+'</p>').fadeIn(400);
						}
						// makeNoticesDismissible();
					}

					response.list_action		= list_action;
					response.list_action_type	= list_action_type;
					
					$('body').trigger('list_table_action_success', response);

					if($('#TB_ajaxContent').length > 0){
						tb_position();
					}
				}
			});

			return false;
		},

		wpjam_list_table_item_prefix: function(){
			if(isset(wpjam_page_setting.item_prefix) && wpjam_page_setting.item_prefix){
				return wpjam_page_setting.item_prefix;
			}else{
				return '.tr-';
			}
		},

		wpjam_list_table_update_item: function(id, item, bg_color){
			bg_color	= bg_color || '#ffffee';
			var item_prefix	= $.wpjam_list_table_item_prefix();

			if(id){
				tr_id	 = $.wpjam_list_table_tr_id(id);

				if(item){
					$(item_prefix+tr_id).last().after('<span class="edit-'+tr_id+'"></span>');
					$(item_prefix+tr_id).remove();
					$('.edit-'+tr_id).before(item).remove();
				}
				
				$(item_prefix+tr_id).hide().css('background-color', bg_color).fadeIn(400);
			}
		},

		wpjam_list_table_delete_items: function(args){
			var item_prefix	= $.wpjam_list_table_item_prefix();
			if(args.bulk){
				$.each(args.ids, function(index, id){
					tr_id	= $.wpjam_list_table_tr_id(id);
					$(item_prefix+tr_id).remove();
				});
			}else{
				tr_id	= $.wpjam_list_table_tr_id(args.id);
				$(item_prefix+tr_id).remove();
			}
		},

		wpjam_list_table_tr_id: function(id){
			if(typeof(id) == "string"){
				return id.replace(/\./g, '-');	
			}else{
				return id;
			}
		},

		wpjam_list_table_form: function(args){
			args.list_action_type	= 'form';
			
			$.wpjam_list_table_action(args);

			var replace_url = window.location.href+'&action='+args.list_action;

			if(args.list_action != 'add' && isset(args.id)){
				replace_url	+= '&id='+args.id;
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
			wpjam_page_setting.params	= {};

			$.each(params, function(index, param){
				if($.inArray(param.name, ['page', 'tab', 's', 'paged', '_wp_http_referer', '_wpnonce', 'action', 'action2']) == -1){
					wpjam_page_setting.params[param.name]	= param.value;	
				}
			});

			return $.wpjam_list_table_query_items(true);
		},

		wpjam_list_table_search_action: function(){
			if($('#wpjam_query_data').length > 0){
				wpjam_page_setting.params	= JSON.parse($('#wpjam_query_data').val());
			}else{
				wpjam_page_setting.params	= {};
			}
			
			wpjam_page_setting.params.s	= $('#wpjam-search-input').val();

			return $.wpjam_list_table_query_items(true);
		},

		wpjam_init_list_table_sortable: function(){
			var items = $('.wp-list-table tbody').sortable('option', 'items');

			$('table.wp-list-table tbody .up').show();
			$('table.wp-list-table tbody .down').show();
			$('table.wp-list-table tbody'+items).first().find('.up').hide();
			$('table.wp-list-table tbody'+items).last().find('.down').hide();
		},

		wpjam_list_table_sortable: function(items){
			items	= items || '> tr';

			$('.wp-list-table tbody').sortable({
				items:		items,
				axis:		'y',
				containment:'table.wp-list-table',
				cursor:		'move',
				handle:		'.list-table-move-action',

				create: function(e, ui){
					if($('table.wp-list-table tbody'+items).length < 3){
						$('table.wp-list-table tbody .move').hide();
					}

					$.wpjam_init_list_table_sortable();
				},

				start: function(e, ui){
					$('.wp-list-table tbody tr').css('background-color', '');
					ui.placeholder.height(ui.item.height()).css({'visibility':'visible'});
				},

				helper: function(e, ui) {
					var children = ui.children();
					for (var i=0; i<children.length; i++){
						var selector = $(children[i]);
						selector.width( selector.width() );
					};
					return ui;
				},
				
				update:		function(e, ui) {
					$(this).sortable('disable');
					// $(this).sortable('serialize');
					// $(this).sortable('toArray');

					var handle	= ui.item.find('.row-actions .move a');
					var data	= handle.data('data');
					data		= data ? data + '&type=drag' : 'type=drag';

					var	next	= ui.item.next().find('.ui-sortable-handle');
					var	prev	= ui.item.prev().find('.ui-sortable-handle');

					if(next.length > 0) {
						data	= data + '&next='+next.data('id');
					}else{
						data	= data + '&next=0';	// 最后
					}

					if(prev.length > 0) {
						data	= data + '&prev='+prev.data('id');
					}else{
						data	= data + '&prev=0';	// 最前
					}
					
					$.wpjam_list_table_action({
						list_action_type:	'direct',
						list_action:		'move',
						id:					handle.data('id'),
						data:				data,
						_ajax_nonce: 		handle.data('nonce')
					});
				}
			});
		},

		wpjam_list_table_pagination:function(paged){
			wpjam_page_setting.params.paged	= paged;

			$('#current-page-selector').val(paged);

			return $.wpjam_list_table_query_items(true);
		},

		wpjam_list_table_bulk_action: function(active_element_id){
			if(active_element_id == 'doaction'){
				var bulk_action	= $('select#bulk-action-selector-top').val();
				var bulk_option	= $('select#bulk-action-selector-top').find("option:selected");
			}else if(active_element_id == 'doaction2'){
				var bulk_action	= $('select#bulk-action-selector-bottom').val();
				var bulk_option	= $('select#bulk-action-selector-bottom').find("option:selected");
			}else{
				return false;
			}

			if(bulk_action == '-1'){
				alert('请选择要进行的批量操作！');
				return false;
			}

			var ids	= new Array();

			$('tbody .check-column input[type="checkbox"]:checked').each(function(index, element){
				ids.push($(this).val());
			});

			if(ids.length == 0){
				alert('请至少选择一项！');
				return false;
			}

			if(bulk_option.data('confirm')){
				if(confirm('确定要'+bulk_option.text()+'吗?') == false){
					return false;
				}
			}

			if(bulk_option.data('action')){
				$.wpjam_list_table_action({
					bulk:				true,
					ids:				ids,
					list_action_type:	bulk_option.data('direct') ? 'direct' : 'form',
					list_action:		bulk_action,
					data:				bulk_option.data('data'),
					_ajax_nonce: 		bulk_option.data('nonce')
				});
				return false;
			}
		},
		
		wpjam_page_action: function (args){
			args.action			= 'wpjam-page-action';
			args.screen_id		= wpjam_page_setting.screen_id;
			args.plugin_page	= wpjam_page_setting.plugin_page;
			args.current_tab	= wpjam_page_setting.current_tab;

			var page_action_type	= args.page_action_type;
			var page_action			= args.page_action;
			var action_title		= args.action_title;

			if(page_action_type == 'submit'){
				$('.spinner').addClass('is-active');
				$('.notice').fadeOut(400);
			}else{
				$("body").append("<div id='TB_load'><img src='"+imgLoader.src+"' width='208' /></div>");
				$('#TB_load').show();
			}

			$.post(ajaxurl, args, function(data, status){
				var response	= (typeof data == 'object') ? data : JSON.parse(data);

				if(response.errcode != 0){
					if(page_action_type == 'submit'){
						$('.spinner').removeClass('is-active');
						$('.notice').removeClass().addClass('notice notice-error').html('<p>'+action_title+'失败：'+response.errmsg+'</p>').fadeIn(400);
					}else{
						$('#TB_load').remove();
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
							
							if(isset(response.errmsg)){
								if(response.errmsg){
									$('.notice').removeClass().addClass('notice notice-info').html('<p>'+response.errmsg+'</p>').fadeIn(400);
								}
							}else{
								$('.notice').removeClass().addClass('notice notice-success').html('<p>'+action_title+'成功</p>').fadeIn(400);
							}
						}

						// makeNoticesDismissible();
					}else if(page_action_type == 'form'){
						$('#TB_load').remove();
						$('#tb_modal').html(response.data);

						args.tb_width	= args.tb_width || 720;
						args.tb_height	= args.tb_height || 200;
						tb_show(action_title, '#TB_inline?inlineId=tb_modal&width='+args.tb_width+'&height='+args.tb_height);
					}else{
						$('#TB_load').remove();
					}

					if($('#TB_ajaxContent').length > 0){
						tb_position();
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

			$.post(ajaxurl, args, function(data, status){
				var response	= (typeof data == 'object') ? data : JSON.parse(data);
				
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
			});

			return false;
		},

		wpjam_tab_nav: function(current_tab){
			var prev_tab	= $('.nav-tab-active').data('tab_id');
			
			if(isset(prev_tab)){
				$('#tab_title_'+prev_tab).removeClass('nav-tab-active');
				$('#tab_'+prev_tab).hide();
			}

			if(current_tab == ''){ //设置第一个为当前 tab显示
				current_tab	= $('a.nav-tab').first().data('tab_id');
				if(isset(wpjam_page_setting.current_option) && !isset(wpjam_page_setting.current_tab)){
					wpjam_page_setting.params.option_tab	= current_tab;
				}
			}

			$('#tab_title_'+current_tab).addClass('nav-tab-active');
			$('#tab_'+current_tab).show();

			if(isset(wpjam_page_setting.current_option) && !isset(wpjam_page_setting.current_tab)){
				if(current_tab != wpjam_page_setting.params.option_tab){
					wpjam_page_setting.params.option_tab	= current_tab;
					window.history.replaceState(null, null, wpjam_page_setting.current_admin_url+'&option_tab='+current_tab);
					$('input[name="_wp_http_referer"]').val(wpjam_page_setting.current_admin_url+'&option_tab='+current_tab);
				}
			}	
		}
	});

	$('body').on('submit', '#list_table_action_form', function(e){
		e.preventDefault();

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
	
	$('body').on('click', '.list-table-action', function(){
		if($(this).data('confirm')){
			if(confirm('确定要'+$(this).attr('title')+'吗?') == false){
				return false;
			}
		}

		var list_action_type	= $(this).data('direct') ? 'direct' : 'form';
		var list_action			= $(this).data('action');
		var item_prefix			= $.wpjam_list_table_item_prefix();

		var id		= $(this).data('id');
		var data	= $(this).data('data');
		
		var	next	= '';
		var	prev	= '';

		var tr_id = $.wpjam_list_table_tr_id(id);

		if(list_action == 'up'){
			next	= $(item_prefix+tr_id).prev().find('.ui-sortable-handle');
			
			if(next.length > 0){
				next	= next.data('id');
				data	= data ? data + '&' : '';
				data	= data + 'type=up&next='+next;
			}else{
				alert('已经是第一个了，不可上移了。');
				return false;
			}
		}else if(list_action == 'down'){
			prev	= $(item_prefix+tr_id).next().find('.ui-sortable-handle');
			
			if(prev.length > 0){
				prev	= prev.data('id');
				data	= data ? data + '&' : '';
				data	= data + 'type=up&prev='+prev;
			}else{
				alert('已经最后一个了，不可下移了。');
				return false;
			}
		}

		var args = {
			list_action_type:	list_action_type,
			list_action:		list_action,
			id:					id,
			data:				data,
			prev:				prev,
			next:				next,
			tb_width:			$(this).data('tb_width'),
			tb_height:			$(this).data('tb_height'),
			_ajax_nonce: 		$(this).data('nonce')
		};

		if(list_action_type == 'form' && isset(wpjam_page_setting.plugin_page)){
			$.wpjam_list_table_form(args);
		}else{
			$.wpjam_list_table_action(args);
		}
		
		$(this).blur();
	});

	$('body').on('submit', "#list_table_form", function(e){

		var active_element_id	= $(document.activeElement).attr('id');

		if(active_element_id == 'export_action'){
			return;
		}else if(active_element_id == 'current-page-selector'){
			return $.wpjam_list_table_pagination($('#current-page-selector').val());
		}else if(active_element_id == 'search-submit' || active_element_id == 'wpjam-search-input'){
			return $.wpjam_list_table_search_action();
		}else if(active_element_id == 'filter_action'){
			return $.wpjam_list_table_filter_action($(this).serializeArray());
		}else if(active_element_id == 'doaction'){
			return $.wpjam_list_table_bulk_action(active_element_id);
		}else if(active_element_id == 'doaction2'){
			return $.wpjam_list_table_bulk_action(active_element_id);
		}
	});

	$('body').on('submit', "#posts-filter", function(e){

		var active_element_id	= $(document.activeElement).attr('id');

		if(active_element_id == 'doaction'){
			return $.wpjam_list_table_bulk_action(active_element_id);
		}else if(active_element_id == 'doaction2'){
			return $.wpjam_list_table_bulk_action(active_element_id);
		}
	});

	$('body').on('click', '.list-table-filter', function(){
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
		wpjam_page_setting.params.orderby	= $(this).data('orderby');
		wpjam_page_setting.params.order		= $(this).data('order');
		wpjam_page_setting.params.paged		= 1;

		return $.wpjam_list_table_query_items(true);
	});

	$('body').on('click', '.wpjam-button', function(e){
		e.preventDefault();

		if($(this).data('confirm')){
			if(confirm('确定要'+$(this).data('title')+'吗?') == false){
				return false;
			}
		}

		$.wpjam_page_action({
			page_action_type:	$(this).data('direct') ? 'direct' : 'form',
			data:				$(this).data('data'),
			tb_width:			$(this).data('tb_width'),
			tb_height:			$(this).data('tb_height'),
			page_action:		$(this).data('action'),
			action_title:		$(this).data('title'),
			_ajax_nonce:		$(this).data('nonce')
		});
	});

	$('body').on('submit', '#wpjam_form', function(e){
		e.preventDefault();

		$.wpjam_page_action({
			page_action_type:	'submit',
			data: 				$(this).serialize(),
			page_action:		$(this).data('action'),
			action_title:		$(this).data('title'),
			_ajax_nonce:		$(this).data('nonce')
		});
	});

	$('body').on('submit', '#wpjam_option', function(e){
		e.preventDefault();	

		$.wpjam_option_action({
			data:	$(this).serialize()
		});
	});

	// Tab 切换
	if($('div.div-tab').length){
		var current_tab = '';

		if(isset(wpjam_page_setting.current_option) && !isset(wpjam_page_setting.current_tab)){
			current_tab = wpjam_page_setting.params.option_tab;			
		}

		$.wpjam_tab_nav(current_tab);

		$('.nav-tab-wrapper a.nav-tab').on('click',function(){
			var current_tab	= $(this).data('tab_id');
			$.wpjam_tab_nav(current_tab);			
		});
	}
});

window.frame_imgs = new Array();		
function show_wx_img(src, iframe_width, iframe_height, url) {
	iframe_width	= iframe_width || 0;
	iframe_height	= iframe_height || 0;
	url				= url || 0;

	if(iframe_width){
		var img_html	= '<img id="img" src=\'' + src + '?' + Math.random() + '\' />';
	}else{
		var img_html	= '<img id="img" style="max-width:100%;" src=\'' + src + '?' + Math.random() + '\' />';
	}
	
	if(url){
		img_html	= '<a href="'+url+'" target="_blank">'+img_html+'</a>';
	}

	var frame_id		= 'frameimg' + Math.random();

	if(iframe_width){
		window.frame_imgs[frame_id] = '<body style="margin:0;padding:0;">'+img_html+'<script>window.onload = function() {wx_iframe=parent.document.getElementById(\'' + frame_id + '\'); wx_img = document.getElementById(\'img\'); iframe_width=wx_iframe.width; iframe_height=wx_iframe.height; img_width = wx_img.width; img_height = wx_img.height; if((img_width/img_height)>(iframe_width/iframe_height)){ wx_img.style.height=\'100%\'; img_width=Math.ceil(iframe_height/img_height*img_width); wx_img.style.marginLeft=(iframe_width - img_width) / 2+\'px\'; }else{ wx_img.style.width=\'100%\'; img_height=Math.ceil(iframe_width/img_width*img_height); wx_img.style.marginTop=(iframe_height - img_height) / 2+\'px\'; } }<' + '/script></body>';

		return '<iframe id="' + frame_id + '" src="javascript:parent.frame_imgs[\''+frame_id+'\'];" width="'+iframe_width+'" height="'+iframe_height+'" frameBorder="0" scrolling="no"></iframe>';
	}else{
		window.frame_imgs[frame_id] = '<body style="margin:0;padding:0;">'+img_html+'<script>window.onload = function() { parent.document.getElementById(\'' + frame_id + '\').height = document.getElementById(\'img\').height+\'px\'; }<' + '/script></body>';

		return '<iframe id="' + frame_id + '" src="javascript:parent.frame_imgs[\''+frame_id+'\'];" width="100%" frameBorder="0" scrolling="no"></iframe>';
	}
}



