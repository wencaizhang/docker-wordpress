=== Open external links in a new window ===
Contributors: kezze
Donate link: https://www.paypal.com/xclick/business=paypal%40kezze.dk&item_name=Donation&no_note=1&tax=0&currency_code=EUR
Tags: links, external links, target blank, target new, window.open, new window, blank window, new tab, blank tab, tabs, SEO, xhtml strict, javascript
Requires at least: 2.0
Tested up to: 5.0
Stable tag: trunk

Opens all (or specific) external links in a new window. XHTML Strict compliant and search engine optimized (SEO).

== Description ==
Opens external links (starting with `http://` or `https://`) in a separate browser tab (or window). You can also specify certain URLs that should either be forced to open in a new window or ignored.
The plugin produces XHTML Strict compliant code and is also search engine optimized (SEO).
This is done using JavaScript's `window.open()`-function.

Most other plugins perform a hack by altering the `target` parameter (i.e. `<a href="http://somewhere.example" target="_blank">`). That method is not XHTML Strict compliant.
This plugin handles the links client-side, which lets search engines follow the links properly. Also, if a browser does not support JavaScript, the plugin is simply inactive, and does not result in any errors.

**Credits**
Inspired by the [Zap_NewWindow](http://www.zappelfillip.de/2005-12-05/zap_newwindow/ "Another Wordpress plugin")-plugin by [Tom K&ouml;hler](http://www.zappelfillip.de/ "His website is mostly in German").
The banner is a [photo](http://www.flickr.com/photos/monja/1367946568/in/photostream/) by [Monja Da Riva](http://www.monja.it/).

**Translations**
Danish by [Kristian Risager Larsen](https://kristianrisagerlarsen.dk).
Dutch by [Paul Staring](http://www.collectief-it.nl/)
Lithuanian by [Vincent G](http://Host1Free.com).
Other translations will be appreciated!

**Known bugs**
The plugin conflicts with other plugins that change the links' `onClick´-attribute.

== Installation ==
1. Copy the plugin to /wp-content/plugins/
1. Activate the plugin.
1. Eventually, change the settings in Settings->External links.

== Changelog ==

= 1.3.3 =
Verified compatibility with Wordpress 5.0

= 1.3.2 =
Updated: Danish translation

= 1.3.1 =
Verified compatibility with Wordpress 4.0
Added: Plugin logo for Wordpress 4.0
Added: Dutch translation.

= 1.3 =
Added: Possibility to force and ignore user-defined strings in URLs. This feature has been requested.
Added: Lithuanian and Danish translation.

= 1.2 =
Added: Translation-ready.

= 1.1.1 =
Fixed: Deprecation warning (Thanks to [boo1865](http://wordpress.org/support/topic/plugin-open-external-links-in-a-new-window-doesnt-work?replies=2#post-2152292))

= 1.1.0 =
Changed: Better practice for opening links. The plugin now uses the onClick-attribute instead of writing JavaScript directly into the href-attribute. This enables users to right-click the link and open in a new window/tab, save the target etc.

= 1.0.1 =
Fixed: Removes target attribute from links instead of setting the attribute to null. (Thanks to [crashnet](http://wordpress.org/support/topic/plugin-open-external-links-in-a-new-window-target-attribute-left-empty?replies=2#post-1813522))

= 1.0 =
Fixed: Credits to Tom K&ouml;hler (Charset).
Fixed: Links.

= 0.9 =
Initial release.

== Upgrade Notice ==

= 1.3.1 =
Wordpress 4.0-compatibility, and Dutch translation.

= 1.3 =
In Settings->External links, you can now specify URL's that should be either forced to open in a new window, or ignored.

= 1.2 =
Added: Translation-ready.

= 1.1.1 =
Fixed: Deprecation warning.

= 1.1.0 =
Better practice for opening links. Please upgrade.

= 1.0.1 =
Minor bugfix.

= 1.0 =
Ready for production.

= 0.9 =
Initial release
