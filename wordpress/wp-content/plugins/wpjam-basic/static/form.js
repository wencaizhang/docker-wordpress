jQuery(function($){
	$.fn.extend({
		wpjam_add_attachment: function(attachment){
			var render	= wp.template('wpjam-img');
			
			$(this).prev('input').val($(this).data('item_type') == 'url' ? attachment.url : attachment.id);
			$(this).html(render({
				img_url		: attachment.url,
				img_style	: $(this).data('img_style'),
				thumb_args	: $(this).data('thumb_args')
			})).removeClass('button add_media');
		},

		wpjam_remove_attachemnt: function(){
			$(this).prev('input').val('');
			$(this).find('img').fadeOut(300);
			$(this).find('del-img').remove();

			$(this).addClass('button add_media').html('<span class="wp-media-buttons-icon"></span> 添加图片</button>');

			return false;
		},

		wpjam_query_data_list: function(search_term){
			var args = {
				action:		'wpjam-query',
				data_type:	$(this).data('data_type')
			};

			search_term	= search_term || '';

			if(args.data_type == 'post_type'){
				args.post_type	= $(this).data('post_type');
				if(search_term){
					args.s		= search_term;
				}
			}else if(args.data_type == 'taxonomy'){
				args.taxonomy	= $(this).data('taxonomy');
				if(search_term){
					args.search	= search_term;
				}
			}

			var datalist	= $(this).attr('list');
			
			$.post(
				ajaxurl, 
				args,
				function(data, status){
					$('datalist#'+datalist).empty();

					if(args.data_type == 'post_type'){
						$.each(data.posts, function(index, post){
							$('datalist#'+datalist).append('<option value="'+post.id+'" label="'+post.title+'"></option>');
						});
					}else if(args.data_type == 'taxonomy'){
						$.each(data.terms, function(index, term){
							$('datalist#'+datalist).append('<option value="'+term.id+'" label="'+term.name+'"></option>');
						});
					}
				}
			)
		}
	});

	var del_item = '<a href="javascript:;" class="button del-item">删除</a> <span class="dashicons dashicons-menu"></span>';

	var custom_uploader;
	if (custom_uploader) {
		custom_uploader.open();
		return;
	}

	$('body').on('click', '.wpjam-file', function(e) {	
		e.preventDefault();	// 阻止事件默认行为。

		var prev_input	= $(this).prev('input');
		var item_type	= $(this).data('item_type');
		var title		= (item_type == 'image')?'选择图片':'选择文件';

		custom_uploader = wp.media({
			title:		title,
			library:	{ type: item_type },
			button:		{ text: title },
			multiple:	false 
		}).on('select', function() {
			var attachment = custom_uploader.state().get('selection').first().toJSON();
			prev_input.val(attachment.url);
			$('.media-modal-close').trigger('click');
		}).open();

		return false;
	});

	//上传单个图片
	$('body').on('click', '.wpjam-img', function(e) {	
		e.preventDefault();	// 阻止事件默认行为。

		var img_wrap	= $(this);

		if(wp.media.view.settings.post.id){
			custom_uploader = wp.media({
				title:		'选择图片',
				library:	{ type: 'image' },
				button:		{ text: '选择图片' },
				frame:		'post',
				multiple:	false 
			// }).on('select', function() {
			}).on('open',function(){
				$('.media-frame').addClass('hide-menu');
			}).on('insert', function() {
				img_wrap.wpjam_add_attachment(custom_uploader.state().get('selection').first().toJSON());
				
				$('.media-modal-close').trigger('click');
			}).open();
		}else{
			custom_uploader = wp.media({
				title:		'选择图片',
				library:	{ type: 'image' },
				button:		{ text: '选择图片' },
				multiple:	false 
			}).on('select', function() {
				img_wrap.wpjam_add_attachment(custom_uploader.state().get('selection').first().toJSON());
				
				$('.media-modal-close').trigger('click');
			}).open();
		}

		return false;
	});

	//上传多个图片或者文件
	$('body').on('click', '.wpjam-mu-file', function(e) {
		e.preventDefault();	// 阻止事件默认行为。

		var render		= wp.template('wpjam-mu-file');
		var prev_input	= $(this).prev('input');
		var item_type	= $(this).data('item_type');
		var title		= (item_type == 'image')?'选择图片':'选择文件';
		
		custom_uploader = wp.media({
			title:		title,
			library:	{ type: item_type },
			button:		{ text: title },
			multiple:	true
		}).on('select', function() {
			custom_uploader.state().get('selection').map( function( attachment ) {
				attachment	= attachment.toJSON();
				
				prev_input.parent().before(render({
					img_url	: attachment.url,
					input_name	: prev_input.attr('name'),
					input_id	: prev_input.attr('id'),
				}));
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
		var input_name	= $(this).data('input_name');
		var item_type	= $(this).data('item_type');
		var mu_img_wrap	= $(this);

		if(wp.media.view.settings.post.id){
			custom_uploader = wp.media({
				title:		'选择图片',
				library:	{ type: 'image' },
				button:		{ text: '选择图片' },
				frame:		'post',
				multiple:	true
			// }).on('select', function() {
			}).on('open',function(){
				$('.media-frame').addClass('hide-menu');
			}).on('insert', function() {
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
		}else{
			custom_uploader = wp.media({
				title:		'选择图片',
				library:	{ type: 'image' },
				button:		{ text: '选择图片' },
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
		}

		return false;
	});

	//  删除选项
	$('body').on('click', '.del-img', function(){
		return $(this).parent().wpjam_remove_attachemnt();
	});

	// 添加多个选项
	$('body').on('click', 'a.wpjam-mu-text', function(){
		var i		= $(this).data('i');
		var item	= $(this).parent().clone();

		i	= i+1;

		item.insertAfter($(this).parent());
		item.find('input').attr('id', $(this).data('key')+'_'+i).val('').show();
		item.find('span.wpjam-query-title').hide();
		item.find('a.wpjam-mu-text').data('i', i);

		$(this).parent().append(del_item);
		$(this).remove();

		return false;
	});

	$('body').on('click', 'a.wpjam-mu-fields', function(){
		var i		= $(this).data('i');
		var render	= wp.template($(this).data('tmpl-id'));

		i	= i+1;

		$(this).parent().after(render({i:i}));
		$(this).parent().append(del_item);
		$(this).parent().parent().trigger('mu_fields_added', i);
		$(this).remove();

		return false;
	});

	//  删除选项
	$('body').on('click', '.del-item', function(){
		var next_input	= $(this).parent().next('input');
		if(next_input.length > 0){
			next_input.val('');
		}

		$(this).parent().fadeOut(300, function(){
			$(this).remove();
		});

		return false;
	});

	
	$('body').on('focus', 'input.wpjam-query-id', function(){
		if($('datalist#'+$(this).attr('list')+' option').length == 0){
			$(this).wpjam_query_data_list();
		}
	});

	$('body').on('mouseover', 'input.wpjam-query-id', function(){
		if($('datalist#'+$(this).attr('list')+' option').length == 0){
			$(this).wpjam_query_data_list();
		}
	});

	$('body').on('keydown', 'input.wpjam-query-id', function(e){
		if(e.keyCode === 13){
    		var search_term = $(this).val();

			if($.isNumeric(search_term) == false){
				$(this).wpjam_query_data_list(search_term);
			}

			e.preventDefault();
			return false;
    	}
	});

	$('body').on('change', 'input.wpjam-query-id', function(e){
		var query_input	= $(this);
		var query_id	= $(this).val();
		var next_span	= $(this).next('span');

		if($.isNumeric(query_id)){
			$('datalist#'+$(this).attr('list')+' option').each(function(index, option){
				if(option.value == query_id){
					next_span.fadeIn(300).html('<span class="dashicons dashicons-dismiss"></span>'+option.label).css('display','inline-block');
					query_input.hide();
					return false;
				}
			});
		}

		return false;
	});

	//  重新设置 post_id
	$('body').on('click', 'span.wpjam-query-title span.dashicons', function(){
		$(this).parent().prev('input').fadeIn(300).val('');
		$(this).parent().hide();
		return false;
	});

	// 拖动排序
	$('.mu-fields').sortable({
		handle: '.dashicons-menu',
		cursor: 'move'
	});

	$('.mu-images').sortable({
		handle: '.dashicons-menu',
		cursor: 'move'
	});

	$('.mu-files').sortable({
		handle: '.dashicons-menu',
		cursor: 'move'
	});

	$('.mu-texts').sortable({
		handle: '.dashicons-menu',
		cursor: 'move'
	});

	$('.mu-imgs').sortable({
		cursor: 'move'
	});

	// $('.sortable').disableSelection();

	$('input.color').wpColorPicker();
	// $('.type-date').datepicker();

	$('body').on('click', '.is-dismissible .notice-dismiss', function(){
		$(this).prev('span').trigger('click');
	});
});

if (self != top) {
	document.getElementsByTagName('html')[0].className += ' TB_iframe';
}

function isset(obj){
	if(typeof(obj) != 'undefined' && obj !== null) {
		return true;
	}else{
		return false;
	}
}