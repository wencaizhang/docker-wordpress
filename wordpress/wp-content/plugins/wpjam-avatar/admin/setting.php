<?php
add_filter('wpjam_avatar_setting', function(){
	$fields = [
		'defaults'	=> ['title'=>'默认头像',		'type'=>'mu-img',	'item_type'=>'url'],
		'priority'	=> ['title'=>'默认优先级',	'type'=>'radio',	'options'=>['default'=>'直接从默认头像中随机选择，不管用户的Gravatar设置<br />','gravatar'=>'如果用户在Gravatar设置了头像，优先使用Gravatar中设置的头像<br />']]
	];
		
	return compact('fields');
});