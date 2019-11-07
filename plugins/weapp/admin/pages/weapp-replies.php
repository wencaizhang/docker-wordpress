<?php
include(WEAPP_PLUGIN_DIR.'includes/class-weapp-reply-setting.php');
include(WEAPP_PLUGIN_DIR.'admin/includes/class-weapp-reply-setting.php');

include(WEAPP_PLUGIN_DIR.'includes/class-weapp-message.php');
include(WEAPP_PLUGIN_DIR.'admin/includes/class-weapp-message.php');

add_filter('wpjam_weapp_replies_list_table', function (){	
	return array(
		'title'				=> '自定义回复',
		'singular'			=> 'weapp-reply',
		'plural'			=> 'weapp-replies',
		'primary_column'	=> 'msg_type',
		'primary_key'		=> 'id',
		'search'			=> true,
		'ajax'				=> true,
		'model'				=> 'WEAPP_AdminReplySetting',
		'capability'		=> 'manage_weapp_'.weapp_get_appid(),
	);
});

add_action('admin_head', function(){
	?>

	<style type="text/css">
	th.column-match{
		width:70px;
	}

	th.column-reply_type, th.column-keyword{
		width: 120px;
	}

	th.column-msg_type{
		width:180px;
	}

	div.reply_item {
		padding: 12px;
		width: 320px;
		border: 1px solid #CCC;
		float: left;
		box-shadow: 1px 1px 3px #CCC;
		border-radius: 4px;
	}

	div.reply_item a {
		float: left;
		width: 320px;
	}

	div.reply_item div.small {
		width: 40px;
		height: 40px;
		float: right;
		background-size: cover;
	}

	div.reply_item div.big {
		width: 320px;
		height: 160px;
	}

	div.reply_item h3 {
		font-size: 14px;
		margin: 0 0 10px 0;
		overflow: hidden;
		text-overflow: ellipsis;
		display: -webkit-box;
		-webkit-box-orient: vertical;
		-webkit-line-clamp: 2;
	}

	div.reply_item p {
		margin: 0;
		float: left;
		width: 270px;
		overflow: hidden;
		text-overflow: ellipsis;
		display: -webkit-box;
		-webkit-box-orient: vertical;
		-webkit-line-clamp: 3;
	}
	</style>

	<script type="text/javascript">
	jQuery(function ($) {
		$('body').on('list_table_action_success', function(event, response){
			// console.log(response);

			$("input[name=msg_type]").change(function(){

				$('#tr_keyword').hide();
				$('#tr_match').hide();
				$("#tr_sessionfrom").hide();

				var selected = $('input[name=msg_type]:checked').val();

				if (selected == 'text') {
					$('#tr_keyword').show();
					$('#tr_match').show();
				} else if (selected == 'event') {
					$("#tr_sessionfrom").show();
				}
			});

			$("input[name=msg_type]").change();

			$("input[name=reply_type]").change(function () {
				var selected = $("input[name=reply_type]:checked").val();

				$('#tr_text').hide();
				$("#tr_image").hide();
				$("#tr_link").hide();
				$("#tr_miniprogrampage").hide();

				$('#tr_'+selected).show();
			});

			$("input[name=reply_type]").change();
		});
	});
	</script>
	<?php
});