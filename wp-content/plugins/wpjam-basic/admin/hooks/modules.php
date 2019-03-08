<?php
include WPJAM_BASIC_PLUGIN_DIR.'admin/includes/class-wpjam-module.php';

function wpjam_modules_form($wpjam_module){
	if( $_SERVER['REQUEST_METHOD'] == 'POST' ){
		check_admin_referer('wpjam_modules');

		if(!current_user_can('manage_shop')){
			ob_clean();
			wp_die('无权限');
		}

		$modules 	= [];
		$disable	= [];

		$module_orders	= $_POST['module_orders'];

		foreach ($module_orders as $module_key) {
			$module	= $_POST[$module_key]??[];

			$module['enable']	= $module['enable']??false;
			if($module['enable']){
				$modules[$module_key]	= $module;
			}else{
				$disable[$module_key]	= $module;
			}
		}

		$modules	= array_merge($modules, $disable);

		$wpjam_module->update_modules($modules);

		wpjam_admin_add_error('保存成功');
	}
	
	wpjam_display_errors();

	echo '<form method="post" action="#" enctype="multipart/form-data" id="form">';
	
	$settings	= $wpjam_module->get_settings();
	$fields		= $wpjam_module->get_fields();

	$modules	= $wpjam_module->get_modules();
	$cubes		= $wpjam_module->get_cubes();

	$enable		= $disable = [];

	foreach ($modules as $key => $module) {
		if(empty($module['type']) || $module['type'] != 'cube'){
			if(isset($settings[$key])){
				$module	= wp_parse_args($module, $settings[$key]);
				unset($settings[$key]);
			}else{
				continue;
			}
		}else{
			if(isset($cubes[$key])){
				$cube_module	= $settings['cubes']+['enable'=>false,	'type'=>'cube', 'title'=>$cubes[$key]['title'], 'cube_title'=>$cubes[$key]['title']];
				$module			= wp_parse_args($module, $cube_module);
				unset($cubes[$key]);
			}else{
				continue;
			}
		}

		if(!empty($module['enable'])){
			$enable[$key]	= $module;
		}else{
			$disable[$key]	= $module;
		}
	}

	if($cubes){
		foreach ($cubes as $cube_key=>$cube) {
			$disable[$cube_key]	= $settings['cubes']+['enable'=>false,	'type'=>'cube',	'title'=>$cube['title'],	'cube_title'=>$cube['title']];
		}
	}

	unset($settings['cubes']);

	if($settings){
		foreach ($settings as $key => $setting) {
			$disable[$key]	= $setting;
		}
	}

	if(isset($enable['search_box'])){
		$enable	= array_merge(['search_box'=>$enable['search_box']], $enable);
	}

	$modules	= compact('enable', 'disable');
	
	foreach ($modules as $module_card => $sub_modules){

		$class	= 'card';
		if($module_card == 'enable'){
			$class	.= ' nav-menus-php';
		}

		?>

		<div class="<?php echo $class; ?>" id="<?php echo $module_card; ?>_module">

			<?php if($module_card == 'enable'){ ?>

			<h3>已用模块</h3>
			
			<p>拖放各个模块到您喜欢的顺序，点击右侧的箭头可进行更详细的设置。</p>

			<ul class="menu sortable" id="menu-to-edit"> 
			
			<?php }else{ ?>

			<h3>可用模块</h3>

			<p>将下面模块拖动到左侧「已用模块」并保存。</p>

			<ul class="menu draggable" id="menu-to-edit"> 
			
			<?php } ?>

			<?php if($sub_modules){ ?>

			<?php foreach($sub_modules as $module_key=>$module){ ?>

				<?php 

				if(empty($module)){ 
					continue; 
				}

				$module_type	= $module['type'] = $module['type'] ?? $module_key;

				if($module_type == 'featured'){
					continue;
				}

				?>

				<li id="menu-item-<?php echo $module_key;?>" data-module="<?php echo $module_key; ?>" class="menu-item menu-item-edit-inactive<?php if($module_key != 'search_box'){echo ' enable';}?>">
					<div class="menu-item-bar">
						<div class="menu-item-handle" data-module="<?php echo $module_key; ?>">
							<span class="item-title">
								<span class="menu-item-title">
									<?php echo $module['module_title']; ?>

									<?php if($module_type == 'cube' && $module['cube_title']){ echo '('.$module['cube_title'].')'; } ?>
									<?php if(!empty($module['title'])){ ?>
										<?php if($module['title'] != $module['module_title']){ ?>
										：<span style="font-weight: normal; font-size: smaller;"><?php echo $module['title'];?></span>
										<?php } ?>
									<?php }else{ ?>
										<?php if(in_array('title', $module['fields'])){?>
											：<span style="font-weight: normal; font-size: smaller;">不显示标题</span>
										<?php } ?>
									<?php } ?>
								</span>
							</span>
							<span class="item-controls">
								<!-- <span class="item-type" id="item-type-<?php echo $module_key; ?>"><?php echo ($module['enable']?'启用':'停用'); ?></span> -->
								<span class="item-edit"></span>
							</span>
						</div>
					</div>

					<div class="menu-item-settings wp-clearfix">
						<?php echo wpjam_get_field_html(['name'=>'module_orders[]', 'type'=>'hidden', 'value'=>$module_key]); ?>
						<?php foreach ($module['fields'] as $field_key){

						$field	= $fields[$field_key];


						$field['name']			= $module_key.'['.$field_key.']';
						$field['key']			= $module_key.'__'.$field_key;
						$field['data-module']	= $module_key;

						$field['value']	= $module[$field_key] ?? '';

						$field_html = wpjam_get_field_html($field);

						if($field['type'] != 'hidden'){ ?>
						
						<p class="field-<?php echo $module_key;?>-<?php echo $field_key; ?> description description-wide">
							<label for="edit-menu-item-<?php echo $module_key;?>-<?php echo $field_key; ?>">

						<?php echo $field['title'].'：'; ?>

						<?php } ?>

						<?php echo $field_html; ?>

						<?php if($field['type'] != 'hidden'){ ?>

						</label>
						</p>

						<?php }	 ?>

						<?php } ?>
						
						<div class="menu-item-actions description-wide submitbox">
							<div class="alignright">
								<?php //submit_button(); ?>
							</div>

							<a class="item-delete submitdelete deletion" data-module="<?php echo $module_key; ?>" id="delete-<?php echo $module_key; ?>" href="javascript:;">移除</a> <span class="meta-sep hide-if-no-js"> | </span> 
							<a class="item-cancel submitcancel hide-if-no-js" data-module="<?php echo $module_key; ?>" id="cancel-<?php echo $module_key; ?>" href="javascript:;">取消</a>
						</div>

					</div>

					<ul class="menu-item-transport"></ul>
				</li>

			<?php } ?>	

			<?php } ?>

			</ul>

			<?php 

			if($module_card == 'enable'){
				wp_nonce_field('wpjam_modules');
				submit_button();	
			}else{
				?><p>&nbsp;</p>
				<?php
			}
			?>

		</div>

		<?php } ?>
				
	</form>

	<style type="text/css">
	#enable_module{max-width: 414px; margin-right: 2em; float: left;}
	#disable_module{max-width: 200px; float: left;}
	#disable_module .menu-item-bar .menu-item-handle{width: inherit;}
	#disable_module .menu-item-handle .item-title{margin-right: inherit; }
	#disable_module .item-controls{display: none;}
	</style>
	<script type="text/javascript">
	jQuery(function($){
		function wpjam_toggle_module(module){
			if($('#menu-item-'+module).hasClass('menu-item-edit-inactive')){
				$('#menu-item-'+module).removeClass('menu-item-edit-inactive').addClass('menu-item-edit-active');
			}else{
				$('#menu-item-'+module).removeClass('menu-item-edit-active').addClass('menu-item-edit-inactive');
			}
		}

		$('.menu-item-handle').on('click', function(){
			var module = $(this).data('module');
			wpjam_toggle_module(module);
		});

		$('.sortable').sortable({items: '> li.enable'});

		$('.draggable li').draggable({
			connectToSortable: '.sortable',
			// helper: "clone",
			revert: 'invalid',
			stop: function( event, ui ) {
				if($(this).parent().hasClass('sortable')){
					var module = $(this).data('module');
					$('#'+module+'__enable').val(1);
					// $('#item-type-'+module).html('启用');
				}
			}
		});

		$('.item-delete').on('click', function(){
			var module = $(this).data('module');
			$('#'+module+'__enable').val(0);

			$('#menu-item-'+module).removeClass('menu-item-edit-active').addClass('menu-item-edit-inactive');
			$('#disable_module >ul').append($('#menu-item-'+module));
			$('#enable_module #menu-item-'+module).remove();

		});

		$('.item-cancel').on('click', function(){
			var module = $(this).data('module');
			wpjam_toggle_module(module);
		});
	});
	</script>

	<?php
}