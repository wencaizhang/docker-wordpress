jQuery(function($){

	var custom_uploader;
	if (custom_uploader) {
		custom_uploader.open();
		return;
	}

	//上传单个图片
	$('body').on("click", '.wpjam-file', function(e) {	
		e.preventDefault();	// 阻止事件默认行为。

		var prev_input	= $(this).prev("input");
		var item_type	= $(this).data('item_type');
		var title		= (item_type == 'image')?'选择图片':'选择文件';

		custom_uploader = wp.media({
			title:		title,
			library:	{ type: item_type },
			button:	{ text: title },
			multiple:	false 
		}).on('select', function() {
			var attachment = custom_uploader.state().get('selection').first().toJSON();
			prev_input.val(attachment.url);
			$('.media-modal-close').trigger('click');
		}).open();

		return false;
	});

	//上传单个图片
	$('body').on("click", '.wpjam-img', function(e) {	
		e.preventDefault();	// 阻止事件默认行为。

		var render		= wp.template('wpjam-img');
		var prev_input	= $(this).prev("input");
		var item_type	= $(this).data('item_type');
		var img_style	= $(this).data('img_style');
		var thumb_args	= $(this).data('thumb_args');
		var img_wrap	= $(this);

		custom_uploader = wp.media({
			title:		'选择图片',
			library:	{ type: 'image' },
			button:		{ text: '选择图片' },
			multiple:	false 
		}).on('select', function() {
			var attachment	= custom_uploader.state().get('selection').first().toJSON();
			var img_value	= (item_type == 'url')?attachment.url:attachment.id;

			prev_input.val(img_value);

			img_wrap.html(render({
				img_url		: attachment.url,
				img_style	: img_style,
				thumb_args	: thumb_args
			}));

			img_wrap.removeClass('default');
			
			$('.media-modal-close').trigger('click');
		}).open();

		return false;
	});

	//上传多个图片或者文件
	$('body').on('click', '.wpjam-mu-file', function(e) {
		e.preventDefault();	// 阻止事件默认行为。

		var render		= wp.template('wpjam-mu-file');
		var prev_input	= $(this).prev("input");
		var item_type	= $(this).data('item_type');
		var title		= (item_type == 'image')?'选择图片':'选择文件';
		
		custom_uploader = wp.media({
			title:		title,
			library:	{ type: item_type },
			button:	{ text: title },
			multiple:	true
		}).on('select', function() {
			custom_uploader.state().get('selection').map( function( attachment ) {
				attachment	= attachment.toJSON();
				data		= {
					img_url	: attachment.url,
					input_name	: prev_input.attr('name'),
					input_id	: prev_input.attr('id'),
				};
				prev_input.parent().before(render(data));
			});
			$('.media-modal-close').trigger('click');
		}).open();

		prev_input.focus();

		return false;
	});

	//上传多个图片
	$('body').on('click', '.wpjam-mu-img', function(e) {
		e.preventDefault();	// 阻止事件默认行为。
		
		var render		= wp.template('wpjam-mu-img');
		var input_name	= $(this).data("input_name");
		var item_type	= $(this).data('item_type');
		var mu_img_wrap	= $(this);
		
		custom_uploader = wp.media({
			title:		'选择图片',
			library:	{ type: 'image' },
			button:	{ text: '选择图片' },
			multiple:	true
		}).on('select', function() {
			custom_uploader.state().get('selection').map( function( attachment ) {
				attachment	= attachment.toJSON();
				mu_img_wrap.before(render({
					img_url	: attachment.url, 
					img_value	: (item_type == 'url')?attachment.url:attachment.id,
					input_name	: input_name
				}));
			});
			$('.media-modal-close').trigger('click');
		}).open();

		return false;
	});

	//  删除选项
	$('body').on('click', '.del-img', function(){
		var img_wrap = $(this).parent();
		img_wrap.prev("input").val('');

		$(this).prev("img").fadeOut(300, function(){
			$(this).remove();
			img_wrap.addClass('default');
		});

		$(this).remove();

		return false;
	});

	// 添加多个选项
	$('body').on('click', 'a.wpjam-mu-text', function(){
		var prev_input	= $(this).prev("input");
		var prev_value 	= prev_input.val();

		prev_input.val('');

		$(this).parent().after($(this).parent().clone());

		prev_input.val(prev_value).after(wp.template('wpjam-del-item'));

		$(this).remove();

		return false;
	});

	$('body').on('click', 'a.wpjam-mu-fields', function(){
		var i				= $(this).data('i');
		var render			= wp.template($(this).data('tmpl-id'));
		var render_del_item	= wp.template('wpjam-del-item');

		var prev_element	= $(this).prev();

		i	= i+1;

		$(this).data('i', i);
		$(this).parent().after(render({i:i}));
		$(this).parent().parent().trigger('mu_fields_added', i);
		$(this).remove();
		prev_element.after(render_del_item);

		return false;
	});

	//  删除选项
	$('body').on('click', '.del-item', function(){
		var next_input	= $(this).parent().next("input");
		if(next_input.length > 0){
			next_input.val('');
		}

		$(this).parent().fadeOut(300, function(){
			$(this).remove();
		});

		return false;
	});

	// 拖动排序
	
	$('.mu-fields').sortable({
		handle: '.dashicons-menu'
	});

	$('.mu-images').sortable({
		handle: '.dashicons-menu'
	});

	$('.mu-files').sortable({
		handle: '.dashicons-menu'
	});

	$('.mu-texts').sortable({
		handle: '.dashicons-menu'
	});

	$('.mu-imgs').sortable();

	// $( ".sortable" ).disableSelection();

	$("input.color").wpColorPicker();
	// $( ".type-date" ).datepicker();
	
	// Tab 切换
	if($('div.div-tab').length){
		var current_tab = '';

		if($('#current_tab').length){ // 如果是设置页面，获取当前的 current_tab 的值
			current_tab	= $('#current_tab').first().val();
		}
		
		if(current_tab == ''){ //设置第一个为当前 tab显示
			current_tab	= $('div.div-tab').first()[0].id.replace('tab_','');
		}

		var htitle		= $('#tab_title_'+current_tab).parent()[0].tagName;

		$('div.div-tab').hide();

		$('#tab_title_'+current_tab).addClass('nav-tab-active');
		$('#tab_'+current_tab).show();
		$('#current_tab').val(current_tab);

		$(htitle+' a.nav-tab').on('click',function(){

			var prev_tab	= current_tab;
			current_tab		= $(this)[0].id.replace('tab_title_','');

			$('#tab_title_'+prev_tab).removeClass('nav-tab-active');
			$(this).addClass('nav-tab-active');

			$('#tab_'+prev_tab).hide();
			$('#tab_'+current_tab).show();
			
			if($('#current_tab').length){
				$('#current_tab').val(current_tab);
			}
		});
	}

	$('body').on('click', '.wpjam-notice.is-dismissible .notice-dismiss', function(){
		var notice_key	= $(this).parent().data('key');
		$.ajax({
			type: "post",
			url: ajaxurl,
			data: { 
				action:			'delete_wpjam_notice', 
				key:			notice_key,
				// _ajax_nonce:	wpjam_setting.nonce
			}
		});
	});
});


var MediaCollectionFilter = wp.media.view.AttachmentFilters.extend({
	id: 'media-collection-filter',

	createFilters: function() {
		var filters = {};
		_.each( wp.media.view.settings.collections || {}, function( value, index ) {
			filters[ index ] = {
				text: value.name,
				props: {
					collection: value.slug,
				}
			};
		});
		filters.all = {
			text:  '所有分类',
			props: {
				collection: ''
			},
			priority: 10
		};
		this.filters = filters;
	}
});

var AttachmentsBrowser = wp.media.view.AttachmentsBrowser;
wp.media.view.AttachmentsBrowser = wp.media.view.AttachmentsBrowser.extend({
	createToolbar: function() {
		AttachmentsBrowser.prototype.createToolbar.call( this );
		this.toolbar.set( 'MediaCollectionFilter', new MediaCollectionFilter({
			controller: this.controller,
			model:	 this.collection.props,
			priority: -75
		}).render() );
	}
});

if (self != top) {
	document.getElementsByTagName('html')[0].className += ' TB_iframe';
}

function isset(obj){
	if(typeof(obj) != "undefined" && obj !== null) {
		return true;
	}else{
		return false;
	}
}