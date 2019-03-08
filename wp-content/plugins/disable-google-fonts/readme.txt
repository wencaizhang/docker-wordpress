=== Disable Google Fonts ===
Contributors: dimadin
Donate link: https://milandinic.com/donate/
Tags: Open Sans, Google Fonts, Google Web Fonts
Requires at least: 3.5
Tested up to: 5.1
Requires PHP: 5.2.4
Stable tag: 2.0

Disable enqueuing of fonts from Google used by WordPress core, default themes, Gutenberg, and many more.

== Description ==

[Plugin homepage](https://milandinic.com/wordpress/plugins/disable-google-fonts/) | [Plugin author](https://milandinic.com/) | [Donate](https://milandinic.com/donate/)

This plugin stops loading of fonts from Google Fonts used by WordPress core, Gutenberg plugin, bundled themes (Twenty Twelve, Twenty Thirteen, Twenty Fourteen, Twenty Fifteen, Twenty Sixteen, Twenty Seventeen), and most other themes. If theme or plugin (whose name is not listed here) uses fonts from Google Fonts, those fonts still might be loaded if that theme or plugin loads fonts from Google in a way that is incompatible with this plugin.

Reasons for not using Google Fonts might be privacy and security, local development or production, blocking of Google's servers, characters not supported by font, performance.

Disable Google Fonts is a very lightweight, it has no settings, just activate it and it works immediately.

And it's on [GitHub](https://github.com/dimadin/disable-google-fonts).

== Installation ==

1. Upload `disable-google-fonts` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Changelog ==

= 2.0 =
* Released on 23rd December 2018
* Change logic when disabling font via string translation filter.
* Change plugin description to better explain in which situations it works.
* Add support for WordPress 5.0, latest version of Gutenberg plugin, and most of the themes in WordPress.org Themes Repository.

= 1.4 =
* Released on 28th September 2018
* Remove fonts used in Gutenberg.

= 1.3 =
* Released on 12th December 2016
* Remove fonts used in Twenty Seventeen.

= 1.2 =
* Released on 9th December 2015
* Remove fonts used in Twenty Sixteen.

= 1.1 =
* Released on 29th December 2014
* Remove fonts used in Twenty Fifteen.
