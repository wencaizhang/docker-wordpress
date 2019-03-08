=== WPJAM Basic ===
Contributors: denishua
Donate link: http://wpjam.com/
Tags: WPJAM,性能优化
Requires at least: 4.0
Tested up to: 5.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

<strong>最新 3.0 版本，要求 Linux 服务器，和 PHP 7.2 版本，以及服务器支持 Memcached。</strong> WPJAM Basic 是我爱水煮鱼博客多年来使用 WordPress 来整理的优化插件，WPJAM Basic 除了能够优化你的 WordPress，也是 WordPress 果酱团队进行 WordPress 二次开发的基础。

== Description ==

WPJAM Basic 是<a href="http://blog.wpjam.com/">我爱水煮鱼博客</a>多年来使用 WordPress 来整理的优化插件，WPJAM Basic 除了能够优化你的 WordPress ，也是 WordPress 果酱团队进行 WordPress 二次开发的基础。

WPJAM Basic 主要功能，就是去掉 WordPress 当中一些不常用的功能，比如日志修订等，还有就是提供一些经常使用的函数，比如获取日志中第一张图，获取日志摘要等。

如果你的主机安装了 Memcacached 等这类内存缓存组件和对应的 WordPress 插件，这个插件也针对提供一些针对一些常用的插件和函数提供了对象缓存的优化版本。

除此之外，WPJAM Basic 还支持多达十二个扩展，你可以根据自己的需求选择开启：

* 文章目录
* 简单 SEO
* SMTP 发信
* Rewrite 优化
* 文章类型转换器
* 文章浏览统计
* 统计代码
* 用户角色
* 相关文章
* 百度站长
* 301跳转
* 移动主题

详细介绍和安装说明： <a href="http://blog.wpjam.com/project/wpjam-basic/">http://blog.wpjam.com/project/wpjam-basic/</a>。

== Installation ==

1. 上传 `wpjam-basic`目录 到 `/wp-content/plugins/` 目录
2. 激活插件，开始设置使用。

== Changelog ==

= 3.5 =
* 5.1 版本兼容处理
* 添加「301跳转」扩展
* 添加「移动主题」扩展
* 添加「百度站长」扩展，修正预览提交
* 讨论组移动到 WPJAM 菜单下
* 修正简单SEO的标题功能
* 修正相关文章中包含置顶文章的bug
* 将高级缩略图集成到缩略图设置
* 优化「去掉分类目录 URL 中的 category」功能

= 3.4 =
* 支持 UCLOUD 对象存储
* 支持屏蔽Gutenberg
* 修正部分站点不能更新 CDN 设置保存的问题。
* 修复文章内链接替换成 CDN 链接的bug。
* 修复图片中文名bug
* 添加高级缩略图扩展
* 添加相关文章扩展
* 更新核心接口

= 3.3 =
* 重构整个插件文件夹，更加合理
* 支持缓存 WordPress 菜单，真正实现首页0SQL。
* 把中文版后台 Settings 菜单名称改成设置。中文版好久不更新，只能自己手动来了
* 更新 WPJAM 后台 Javascript 库
* class-wpjam-thumbnail.php

= 3.2 =
* 重磅功能：七牛云存储升级为CDN功能，支持七牛云存储，阿里云OSS和腾讯与COS
* 提供选项让用户去掉URL中category
* 提供选项让用户上传图片加上时间戳
* 提供选项让用户可以简化后台用户界面
* 修复CDN组件的未设置文件扩展名会导致博客打不开的bug
* 增强WPJAM SEO扩展，支持sitemap拆分
* 增强讨论组功能，支持搜索和性能优化

= 3.1 =
* 修正 WPJAM Basic 3.0 以后大部分 bug
* 想到好方法，重新支持回 PHP7.2以下版本，但是PHP7.2以下版本不再新增功能和修正
* 修正主题自定义功能失效的bug
* 修正七牛冲突的问题
* 添加 object-cache.php 到 template 目录

= 3.0 =
* 基于 PHP 7.2 进行代码重构，效率更高，更加快速。
* 全AJAX操作后台

= 2.6 =
* 分拆功能组件
* WPJAM Basic 作为基础插件库使用

= 2.5 =
* 版本大更新
* 修复七牛图片在微信客户端显示问题

= 2.4 =
* 上架 WordPress 官方插件站
* 更新 wpjam-setting-api.php
* 新增屏蔽 WordPress REST API 功能
* 新增屏蔽文章 Embed 功能
* 由于腾讯已经取消“通过发送邮件的方式发表 Qzone 日志”功能，取消同步到QQ空间功能

= 2.3 = 
* 新增数据库优化
* 内置列表功能

= 2.2 = 
* 新增短代码
* 新增 SMTP 功能
* 新增插入统计代码功能

= 2.1 = 
* 新增最简洁效率最高的 SEO 功能

= 2.0 =
* 初始版本直接来个 2.0 感觉牛逼点