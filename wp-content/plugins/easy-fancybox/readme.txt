=== Easy FancyBox ===
Contributors: RavanH
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=ravanhagen%40gmail%2ecom&item_name=Easy%20FancyBox
Tags: fancybox, lightbox, gallery, image, photo, video, flash, overlay, youtube, vimeo, dailymotion, pdf, svg, iframe, swf, jquery, webp
Requires at least: 3.3
Tested up to: 5.1
Stable tag: 1.8.13

Easily enable the FancyBox jQuery extension on just about all media links. Multi-Site compatible. Supports iFrame and Flash movies.

== Description ==

Easy FancyBox plugin for WordPress websites gives you a flexible and aesthetic light box solution for just about all media links on your website. Easy FancyBox uses an updated version of the traditional FancyBox jQuery extension and is WP 3+ Multi-Site compatible. After activation you can find a new section **FancyBox** on your **Settings > Media** admin page where you can manage the media light box options.

After activation, all links to **JPG, GIF and PNG images** are automatically opened in the [FancyBox](http://fancybox.net/) Mac/Gnome-style lightbox that floats over the web page.

**GDPR / EU Privacy**

This plugin does not collect any data and does not set any browser cookies. However, the PRO version offers an option to disable the automatic popup after the first visit, which needs a browser cookie. This cookie stores the visitors first website visit timestamp and path on the client side. It is not shared nor is any data stored server side or elsewhere.

**FEATURES**

Supported media and content types:

- All common image formats _including_ webp
- Hosted video on **Youtube**, **Vimeo** _and_ **Dailmotion**
- PDF files (embed with object tag, in iframe or in external Google Docs Viewer)
- SWF (Flash) files
- SVG media images (thanks to Simon Maillard)
- Inline HTML content (see [instructions in the FAQs](https://wordpress.org/plugins/easy-fancybox#how%20can%20i%20display%20inline%20content%20in%20a%20fancybox%20overlay%20%3F))
- External web pages (see [instructions in the FAQs](https://wordpress.org/plugins/easy-fancybox#can%20i%20display%20web%20pages%20or%20html%20files%20in%20a%20fancybox%20overlay%3F))

Also supports:

- WordPress Galleries (option "Link to" must be set to "Media File")
- NextGEN galleries (see [instructions in the FAQs](https://wordpress.org/plugins/easy-fancybox#can%20nextgen%20gallery%20work%20with%20easy%20fancybox%20%3F))
- Image maps
- WordPress menu items (see [instructions in the FAQs](https://wordpress.org/plugins/easy-fancybox#can%20i%20make%20a%20menu%20item%20open%20in%20a%20fancybox%20overlay%20%3F))
- Jetpack Infinite Scroll

Additional features:

- Modal window option (see [instructions in the FAQs](https://wordpress.org/plugins/easy-fancybox#can%20i%20have%20a%20modal%20window%20%3F))
- Automatic detection of media file links
- Automatic detection of galleries
- Popup on page load optional (see [instructions in the FAQs](https://wordpress.org/plugins/easy-fancybox#can%20i%20make%20an%20image%20or%20hidden%20content%20to%20pop%20up%20in%20fancybox%20on%20page%20load%3F))
- Fade or Elastic popup effects
- Styling options for light box overlay (color and opacity) and window (border size and color)

For **advanced options** and **priority support**, there is a **[Pro extension](https://premium.status301.net/downloads/easy-fancybox-pro/)** available. See Pro features below.

See [FAQ's](https://wordpress.org/plugins/easy-fancybox/#faq) for instructions to manage YouTube, Dailymotion and Vimeo movies (and similar services) and tips to make inline content display in a FancyBox overlay.

Get support on the [Easy FancyBox web page](https://status301.net/wordpress-plugins/easy-fancybox/) or [WordPress forum](https://wordpress.org/support/plugin/easy-fancybox).

Visit [FancyBox](http://fancybox.net/) for more information and examples.

**PRO FEATURES**

- Priority support on dedicated forum
- Slideshow effect for galleries (autorotation)
- Spotlight effect for the light box overlay
- FacetWP, Gravity Forms and TablePress compatibility
- More styling options: rounded corners, inline content background and text colors
- More automatic popup options: triggered by URL hash, first link by media type, hide popup after first visit
- Pass dedicated light box setting per media link via link class (see [Metadata instructions in the FAQs](https://wordpress.org/plugins/easy-fancybox#how%20do%20i%20show%20content%20with%20different%20sizes%3F))
- More elastic (easing) popup effects on open and close
- Show/hide image title on mouse hover
- Fine-tune media link and gallery autodetection to match your theme source markup to allow galleries per post for example

For these additional features, you need to install the **[Pro extension](https://premium.status301.net/downloads/easy-fancybox-pro/)** alongside this free plugin.


= Contribute =

If you're happy with this plugin as it is, please consider writing a quick [rating](https://wordpress.org/support/plugin/easy-fancybox/reviews/#new-post) or helping other users out on the [support forum](https://wordpress.org/support/plugin/easy-fancybox).

If you wish to help build this plugin, you're very welcome to [translate Easy FancyBox into your language](https://translate.wordpress.org/projects/wp-plugins/easy-fancybox/) or contribute bug reports, feature suggestions and/or code on [Github](https://github.com/RavanH/easy-fancybox/).

**KNOWN ISSUES**

= General =

- **Outbound links or Downloads tracking** in some of the stats plugins can interfere with FancyBox. Disable such option or exclude links manually with a class (see instructions for SlimStat below)
- Most plugins and themes that already include a light box script. Continue reading to see if you are using one of the know ones or follow the troubleshooting steps to find out what is conflicting on your site.
- Any theme that is missing the obligatory `<?php wp_footer(); ?>` call in the footer.php template.
- When showing an iframe as inline content in FancyBox -- not advised, use fancybox-iframe instead! -- the iframe will become blank after opening and closing it. The solution is to link directly to the iframe source and use `class="fancybox-iframe"` instead.
- Embedded flash content that has no wmode or wmode 'window', is displayed above the overlay and other javascript rendered content like dropdown menus. WordPress does NOT check for missing wmode in oEmbed generated embed code. Since version 1.3.4.5, the missing wmode is added by this plugin for WP (auto-)embeds but not for other user-embedded content. Please make sure you set the wmode parameter to 'opaque' (best performance) or 'transparent' (only when you need transparency) for your embedded content.

= Plugin conflicts =

- **jQuery Updater** moves jQuery to version 2+ which is incompatible.
- **All in One SEO Pack** and **Analytics for WordPress** with outbound link tracking enabled. Disable that feature.
- **WP Slimstat** with Track Outbound Clicks enabled, will break the light box effect on some browsers. Adding `fancybox` (or any of the other classes like `fancybox-youtube,fancybox-iframe,fancybox-inline` depending on which media should be displayed in FancyBox) to the Do Not Track field is reported to solve the issue. Slimstat also might interfere with the YouTube url conversion. When clicking a Youtube link, the movie opens in an overlay as it is supposed to but immediately after that, the complete page gets redirected to the original YouTube page. Adding a `class="noslimstat"` to the link is reported to work around the issue.
- **Google Analytics for WordPress** converts links like `href="#anyID"` to `href="http://yoursite.url/page/#anyID"`, disabling inline content shown in FancyBox.
- Both the **uBillBoard** and **Camera slideshow** have their own easing script hard-coded which conflicts with the one in Easy FancyBox. The only way around the conflict is to set both the Easing In and Easing Out options on your Settings > Media page to **Swing**.
- **Wordpress Firewall 2** blocks access to image files needed for proper display of the FancyBox overlay in older IE and other non-css3 browsers.
- **WordPress Amazon Associate**: A script provided by Amazon and the FancyBox script are incompatible. Disabling _Product Preview_ in the **WP - Amazon > Settings** page should work around the issue.
- **WP Supersized** uses the Animate Enhanced jQuery extension which causes a conflict with the Easing extension used by FancyBox resulting in a 0px sized lightbox frame and/or some kind of positioning issue with auto-centering.

= Theme conflicts =

- Older versions of **Elegant Themes** have FancyBox integrated in a hard-coded way, making them incompatible with Easy FancyBox. In the latest versions of these themes, there is an option to disable the included FancyBox. Use this option to make your theme compatible with Easy FancyBox :)
- The **Mystique** theme has two options called "Lightbox" and "Optimize website for faster loading" that will break Easy FancyBox. Disable both in Mystique's options > Advanced.
- **Imbalance** and other themes that uses the Photo Galleria jQuery extension: turn of the JSGallery option.
- Themes like **Envisioned**, **Chameleon** and many others have FancyBox baked in. There is no solution other than stripping the theme of all FancyBox related code or better: disable the plugin and use the theme provided version...
- Themes based on the **Thesis** framework might see issues in IE 8, for which [a hack has been proposed](https://voidzonemedia.com/solutions/thesis-ie8-remove-ie7-emulation/)


== Installation ==

= Wordpress =

Quick installation: [Install now](https://coveredwebservices.com/wp-plugin-install/?plugin=easy-fancybox) !

 &hellip; OR &hellip;

Search for "easy fancybox" and install with that slick **Plugins > Add New** back-end page.

 &hellip; OR &hellip;

Follow these steps:

 1. Download archive.

 2. Upload the zip file via the Plugins > Add New > Upload page &hellip; OR &hellip; unpack and upload with your favorite FTP client to the /plugins/ folder.

 3. Activate the plugin on the Plug-ins page.

Done! By default, any images that are linked to directly (not to a WordPress page) from within your posts and pages, should now be opening in a FancyBox overlay :)

Not happy with the default settings? Check out the new options under **Settings > Media**.

= Wordpress MU / WordPress 3+ in Multi Site mode =

Same as above but do a **Network Activate** to activate FancyBox image overlays on each site on your network. No database tables are created or manipulated and no activation hook needs to be run for it to function with default settings.


== Frequently Asked Questions ==

= BASIC =

= What's FancyBox? =

Basically, it is a fancy way of presenting images, movies, portable documents and inline content on your website. For example, if you have scaled-down images in your posts which are linked to the original large version, instead of opening them on a blank page, FancyBox opens those in a smooth overlay. Visit [FancyBox](http://fancybox.net/) for more information and examples.


= Which version of FancyBox does this plugin use? =

This plugin uses an **updated version** of the original [FancyBox 1.3.4](http://fancybox.net), better adapted to the mobile era.


= I installed the plugin. What now? =

First, make sure that image  thumbnails in your posts and pages are linked to their full size counterpart directly. Open any post with thumbnail images in it for editing and select the first thumbnail. Click the **Edit Image** button that appears and choose **Link To: Media File**. From now on, clicking that thumbnail should open the full size version in FancyBox.

The same thing goes for WordPress Galleries. Choose **Link To: Media File** when inserting a gallery tag.


= Where is the settings page? =

There is no new settings page but there are many options you can change. You will find a new **FancyBox** section on **Settings > Media**. To see the default, check out the example under [Screenshots](https://wordpress.org/plugins/easy-fancybox/screenshots/) ...


= Help! It does not work... =

Please follow the trouble shooting steps near the end of the plugin description above to determine the cause. If that fails, ask for support on the [Easy FancyBox WordPress forum](https://wordpress.org/support/plugin/easy-fancybox) or go to the [development site](https://status301.net/wordpress-plugins/easy-fancybox/)


= ADVANCED =

= Will a WordPress gallery be displayed in a FancyBox overlay? =

Yes, but _only_ if you used the option **Link To: Media File** when inserting the gallery! The gallery quicktag/shortcode should look something like `[ gallery link="file" ]`.


= The lightbox does not look good on mobile devices. What can I do about that? =

The original FancyBox 1.3.4 script was not developed with mobile devices in mind and although the version used in this plugin has some adaptations for mobile devices, it might still be less optimal for very small screens. The only way around this issue is currently to disable FancyBox for small screen sizes on **Settings > Media** in the section **Miscellaneous > Browser & device compatibility**.


= Can I make a slideshow from my gallery? =

In the [Pro extension](https://premium.status301.net/downloads/easy-fancybox-pro/), there is an Advanced option called "Gallery Auto-rotation" for that.


= Can I exclude images or other links from auto-attribution? =

Yes. All links with class **nolightbox** that would normally get auto-enabled, will be excluded from opening in a FancyBox overlay.

`<a href="url/to/fullimg.jpg" class="nolightbox"><img src="url/to/thumbnail.jpg" /></a>`


= Can NextGEN Gallery work with Easy FancyBox ? =

NetxGEN has its own built in FancyBox version along with a choice of other light box scripts but if you prefer to use Easy FancyBox (because of better customizability and other media support) then you need to take some steps to make the two plugins compatible.

1. Go to your Settings > Media admin page and switch OFF the FancyBox "Auto-gallery" option in the Images section;
1. Go to Gallery > Other Options and set the Lightbox Effects option to "Custom" and click on **Show Advanced Settings**;
1. fill the Code field with
`class="fancybox" rel="%GALLERY_NAME%"`
1. Leave the other fields empty and save your settings.


= Can I use ONE thumbnail to open a complete gallery ? =

It can be done manually (using the internal WordPress gallery feature, or not) _or_ in combination with NextGen Gallery.

**Manual**

**A.** Open your post for editing in HTML mode and insert the first image thumbnail in your post content (linking to the images file, not page) to serve as the gallery thumbnail.

**B.** Place the following code to start a hidden div containing all the other images that should only be visible in FancyBox:
`
<div class="fancybox-hidden">
`

**C.** Right after that starting on a new line, insert all other images you want to show in your gallery. You can even use the WordPress internal gallery feature with the shortcode `[ gallery link="file" ]`. NOTE: if the gallery thumbnail is attached to the post, it will be show a second time when flipping through the gallery in FancyBox. If you do not want that, use an image that is not attached to the post as gallery thumbnail.

**D.** Close the hidden div with the following code on a new line:
`
</div>
`

**With NextGEN Gallery**

You can choose between two shortcodes to show a gallery that (1) limits images per gallery using the shortcode `[nggallery id=x]` or (2) per tag name (accross galleries; you need to set tag name manually => more work but more control) using the shortcode `[nggtags gallery=YourTagName,AnotherTagName]`.

General steps:

**A.** Place the shortcode of your choice in your page/post content.

**B.** Configure NextGen on **Gallery > Gallery Settings** to Display galleries as "NextGEN Basic Thumbnails" and then under the NextGEN Basic Thumbnails to at least have the following options set like this:

1. Number of images per page: 1
1. Use imagebrowser effect: No
1. Add hidden images: Yes

**C.** Optional: add the following new CSS rule to your theme stylesheet (or install [Custom CSS](https://wordpress.org/plugins/safecss/) or [Jetpack](https://wordpress.org/plugins/jetpack/) and add it on the new Appearance > Edit CSS admin page) to hide the page browsing links below the gallery thumbnail.
`
.ngg-navigation {
display:none;
}
`

= Can I play YouTube, Dailymotion and Vimeo movies in a FancyBox overlay? =

Yes. Simply create a link using the Share URL (the full Page URL, the Short URL with or without options like HD etc.) to the YouTube/Vimeo/Dailymotion page in your post content. If you have Auto-detect enabled, the plugin will take care of the rest for you! :)

If you have disabled Auto-detection, give the link a class attribute like `class="fancybox-youtube"` for Youtube, `class="fancybox-vimeo"` for Vimeo and `class="fancybox-dailymotion"` for Dailymotion, to manually enable FancyBox for it.

Both YouTube and Vimeo movies can be made to play immediately after opening by adding the paramer `autoplay=1` to the URL. For example, a short-url YouTube link that should play in HD mode, have the full screen button and auto-start on open, would look like:
`
<a href="https://youtu.be/N_tONWXYviM?hd=1&fs=1&autoplay=1">text or thumbnail</a>
`


= I want that 'Show in full-screen' button on my YouTube movies =

Append `&fs=1` to your YouTube share URL.


= Can I show a Youtube playlist in FancyBox? =

Yes, just go to Youtube page of any movie that's in the playlist and use the Share button to get the share URL just like with single movies, but this time place a checkmark at the 'Share with playlist' option.


= Can I link a NextGEN thumbnail to a Youtube movie in FancyBox? =

User Mark Szoldan shared a neat trick how to do this:

1. Follow the instructions to make Easy FancyBox work smoothly with NextGEN above and make sure it all works correctly for normal thumbnails linked to their full-size version.
1. Then give the image that you want to link to a Youtube movie the URL to the Youtube page as title.
1. Finally paste the code below into a Custom HTML widget that will live in your sidebar or footer bar, or you can hard-code it into your theme but make sure it come before the `wp_footer()` call...

`
<script type="text/javascript">
jQuery('.fancybox [title*="www.youtube.com"]').each(function() {
  var title = jQuery(this).attr('title');
  var desc = jQuery(this).parent().attr('title');
  jQuery(this).attr('title', desc);
  jQuery(this).parent().attr('href', title);
});
</script>
`

This script snippet will scan the image titles and if it finds a Youtube URL there, it will replace the links href attribute value accordingly.


= Can I create a gallery of Youtube thumbnails which open in FancyBox? =

You could do this manually by uploading individual thumbnails that you can retrieve by using the unique movie ID in these URLs for three different sizes:
`
https://img.youtube.com/vi/UNIQUE-MOVIE-ID/default.jpg
https://img.youtube.com/vi/UNIQUE-MOVIE-ID/mqdefault.jpg
https://img.youtube.com/vi/UNIQUE-MOVIE-ID/hqdefault.jpg
`
Other locations might be
`
https://img.youtube.com/vi/UNIQUE-MOVIE-ID/0.jpg (same as hqdefault.jpg)
https://img.youtube.com/vi/UNIQUE-MOVIE-ID/1.jpg
https://img.youtube.com/vi/UNIQUE-MOVIE-ID/2.jpg
https://img.youtube.com/vi/UNIQUE-MOVIE-ID/3.jpg
https://img.youtube.com/vi_webp/UNIQUE-MOVIE-ID/0.webp (same as hqdefault.webp)
etc...

`

But an easier method is this one, shared by Shashank Shekhar (thanks!) :

To create Youtube thumbnail galleries, install https://wordpress.org/plugins/youtube-simplegallery/ and set the 'Effect' option to fancybox. Then disable Youtube autodetection on Settings > Media.


= Can I display web pages or HTML files in a FancyBox overlay? =

Yes. First, enable the iFrame option on Settings > Media. Then, in your post or page content create a link to any web page or .htm(l) file in your content. Then switch to the Text tab in the Classic Editor or to Edit as HTML (under More options in the block menu) in Gutenberg, find the link `<a ... >` tag and give it a `class="fancybox-iframe"` attribute.

Note: Not all external web pages are allowed to be embedded in an iframe and may be blocked by a server response header or script. The result will be either an empty/blank light box or the target page "breaking out" of the light box and loading in the main browser tab.


= Can I show PDF files in a FancyBox overlay? =

Yes. Just place a link _with the URL ending in .pdf_ to your Portable Document file in the page content.

If you don't have *Auto-detect* checked under **PDF** on Settings > Media admin page, you will need to add `class="fancybox-pdf"` (to force pdf content recognition) to the link to enable FancyBox for it.


= Can I play SWF files in a FancyBox overlay? =

Yes. Just place a link _with the URL ending in .swf_ to your Flash file in the page content.

If you don't have *Auto-detect* checked under **SWF** on Settings > Media admin page, you will need to add either `class="fancybox"` or `class="fancybox-swf"` (to force swf content recognition) to the link to enable FancyBox for it.


= How do I show content with different sizes? =

FancyBox tries to detect the size of the content automatically but if it can not find a size, it will default to the settings for that particular content type as set on the Settings > Media page.

The **[Pro extension](https://premium.status301.net/downloads/easy-fancybox-pro/)** provides an extra option to allow you to manually override this by defining the width and height wrapped in curly braces in the class attribute of the link itself. Make sure the option "Include the Metadata jQuery extension script..." under FancyBox | Miscellaneous | Advanced on Settings > Media is enabled.

For example, a Flash movie with different size:

`
<a class="fancybox-swf {width:1024,height:675}" href="link-to-your-swf"></a>
`

= The flash movie in the overlay shows BELOW some other flash content that is on the same page! =

Make sure the OTHER flash content as a **wmode** set, preferably to 'opaque' or else 'transparent' but never 'window' or missing. For example, if your embedded object looks something like:
`
<object type="application/x-shockwave-flash" width="200" height="300" data="...url...">
<param name="allowfullscreen" value="true" />
<param name="allowscriptaccess" value="always" />
<param name="movie" value="...url..." />
</object>
`
just add `<param name="wmode" value="opaque" />` among the other parameters. Or if you are using an embed like:
`
<object width="640" height="385">
<param name="movie" value="...url..."></param>
<param name="allowFullScreen" value="true"></param>
<param name="allowscriptaccess" value="always"></param>
<embed src="...url..." type="application/x-shockwave-flash" width="640" height="385" allowscriptaccess="always" allowfullscreen="true" wmode="window"></embed>
</object>
`
just change that `wmode="window"` to `wmode="opaque"` or add the attribute if it is missing.


= How can I display INLINE content in a FancyBox overlay ? =

First go to your **Settings > Media** admin page and activate the **Inline** option under the FancyBox settings. After saving, the amin page will show a new section called Inline where you can tweak its parameters.

Next, open your page/post for editing in the HTML tab and wrap the inline content in
`
<div style="display:none" class="fancybox-hidden"><div id="fancyboxID-1" class="hentry" style="width:460px;max-width:100%;">
...inline content here...
</div></div>
`

Then place a FancyBox link tag with class attribute "fancybox-inline" anywhere else in the post/page content that will point to the inline content like
`
<a href="#fancyboxID-1" class="fancybox-inline">Read my inline content</a>
`

NOTE: The wrapping divs ID *must* be unique and it must correspond with the links HREF with a # in front of it. When using the above example for more FancyBox inline content (hidden div + opening link) combinations on one page, give the second one the ID  fancyboxID-2 and so on...

NOTE 2: If you find that the inline content shown in FancyBox is styled very different than the rests of the page content, then you might want to change the div tag attribute `class="hentry"` to something else that matches your theme. Find out what class name is used for the main content on your site and re-use that.


= Can I display a contact form in FancyBox? =

Yes. There are several methods imaginable but the easiest would be to use the Inline method. First go to your Settings > Media admin page and enable the Inline Content option. Next, go back to edit your post or page in the Text editor tab. The inline content can be a shortcode like in this example using Contact Forms 7 and Easy FancyBox:

`
<a href="#contact_form_pop" class="fancybox-inline">Contact Us</a>

<div style="display:none" class="fancybox-hidden">
    <div id="contact_form_pop" class="hentry" style="width:460px;max-width:100%;">
        [contact-form-7 id="87" title="Contact form 1"]
    </div>
</div>
`
Where you replace the shortcode (between the [ and ] characters) with the one given by the plugin. It can also work with shortcode by other plugins like Jetpack's Contact Form module. Change the class attribute to reflect the class used for the div that wraps your post content to have any form CSS style rules that are limited to post content, be applied to the inline content inside FancyBox.


= Can I make an image or hidden content to pop up in FancyBox on page load? =

Yes. A link that has the ID **fancybox-auto** (Note: there can be only ONE link like that on a page!) will be triggered automatically on page load.

Use the instructions above for inline content but this time give the link also `id="fancybox-auto"` (leave the class too) and remove the anchor text to hide it. Now the hidden div content will pop up automatically when a visitor opens the page.

Same can be done with an image, flash movie, PDF or iframe link! But please remember there can be only **one** item using the ID fancybox-auto per page...


= Can I have a modal window ? =

Yes, just create a hidden inline content light box (can be auto-popup) as described above and give the link an extra class "modal". This will remove all options to close the light box, like the close button, an overlay click or escape key.

This means there is NO option to close the light box, unless you create a link like this inside the hidden inline content div:

`
<a href="" class="fancybox-close">Accept</a>
`


= Can I make a menu item open in a FancyBox overlay ? =

Yes. But it depends on you theme what you need to do to make it work. If you are on WordPress 3+ and your theme supports the new internal Custom Menu feature or if you are using a custom menu in a sidebar widget, it's easy:

1. Go to Settings > Media and enable FancyBox iFrame support.
2. Go to Appearance > Menus and open the little tab "Screen Options" in the top-right corner.
3. Enable the option "CSS Classes" under Advanced menu properties.
4. Now give the menu item you want to open in a FancyBox iframe the class `fancybox-iframe`.

If you are on an older version of WordPress or if you cannot use WP's Menus, you will need to do some heavy theme hacking to get it to work. Basically, what you need to achieve is that the menu item you want opened in a lightbox overlay, should get a `class="fancybox-iframe"` attribute.


= How can I make AJAX loaded content be seen by FancyBox ? =

Easy FancyBox initially scans the page source for media links on the "Document Loaded" event. This means right after the page source has become available to and read by the browser. When content is added or modified through AJAX (meaning after initial page load) by your theme or another plugin, then FancyBox will not be aware of any media links in that new content.

To make Easy FancyBox rescan the updated page source after content has been modified though AJAX, there is an event listener available. This event is also triggered by the Jetpack Infinite Scroll module. To use this event, you'll need to modify the theme or other plugin script that handles the AJAX calls.

You can trigger the event like this:
`
jQuery(document.body).trigger('post-load');
`

Note: It completely depends on the AJAX script where this code snippet should be placed. Optimally, right _after_ the DOM modification where content is added or modified. In most cases at the end of the AJAX Success handler.


= Is Easy FancyBox multi-site compatible? =

Yes. Designed to work with **Network Activate** and does not require manual activation on each site in your network.


= TROUBLE SHOOTING =

If, after activation, your images do not open in a FancyBox overlay, there are several possible reasons. Some are easily solved, others are more difficult. Follow these basic checks to make sure everything is in place:

= Basic checks =

1. Make sure that thumbnail images are linked *directly* to their larger counterpart, not to a dynamic WordPress page that includes the larger image. This means when you insert an image in your posts or pages, you need to select **Media File** at the Link option instead of Page URL. You'll have to manually edit your old posts if you have always inserted images with `Page URL` before, FancyBox cannot do this for you.
1. Make sure you have all the needed media and their *Auto-detect* options activated on your **Settings > Media** admin page. If you are using images in other formats that JPG, GIF or PNG, you need to add the extensions to the Auto-detect field for Images. Please note: The image file names must actaully _end_ with that extension! This means that if you have an image file that (for example) has _no_ extension (does not end with .jpg or any other) even if is in JPEG compressed format, the FancyBox will not be able to detect is as an image. You will need to manually give those links the class `fancybox image` to trigger FancyBox.

= General trouble shooting steps =

1. Switch off all other plugins and switch your sites appearance to the default Twenty Seventeen theme. FancyBox should work now. If so, continue with the next step. If not, re-install the plugin and verify the basic steps above.
1. Switch back to your original theme and check if FancyBox is still working. If so, continue with the next step. If not, See the Theme Incompatibility checks below.
1. One by one, switch each plugin that you had running before back ON. Keep checking to see at which point FancyBox starts failing and you will hve found the conflicting plugin.

= Theme Incompatibility checks =

1. See known theme conflicts above first, then continue with these following steps.
1. Make sure your theme is capable of placing the needed javascript and css in the page header and footer. Open any page on your site and view the source code by right-clicking on an empty section and selecting 'View source...' (or similar). There you will need to check of there are any references to javascript files like `jquery.fancybox.min.js?ver=x.x.x` near the closing `</body>` tag. There should also be a `easy-fancybox.min.css?ver=x.x.x` in the head section... If it's not there, your theme is really out of date. Consider switching to a new theme fast!
1. Make sure that your theme does not load the main jQuery library file more than once. Look for references to javascript files like `jquery.js?ver=x.x.x` or `jquery.min.js` in the page source code. If you find more than one, try to find out in which theme template file that second reference is hard-coded and remove that line. Usually in header.php or footer.php
1. Check if your theme loads another or the same lightbox script. Look for references to Thickbox, Prettyphoto, Lightbox2, Colorbox or FancyBox script files or code. These are very likely to cause the incompatibility and you will either have to remove these by hacking your theme or switch to another theme.

If you still do not get to see your images in FancyBox, ask on the [Easy FancyBox WordPress forum](https://wordpress.org/tags/easy-fancybox) or go to the [development site](https://status301.net/wordpress-plugins/easy-fancybox/)

= Plugin Incompatibility checks =

1. If you followed the general trouble shooting steps above, you should now be aware of which plugin is conflicting whith Easy FancyBox. See known plugin conflicts above first. If the plugin and its solution are not mentioned there, continue with the following steps.
1. Make sure that the plugins do not make the main jQuery library file load more than once. Look for references to javascript files like `jquery.js?ver=x.x.x` or `jquery.min.js` in the page source code. If you find more than one, try to find out where that comes from.
1. Check if your theme loads another or the same lightbox script or any other of the needed jQuery extensions like jquery.easing or jquery.mousewheel. Look for references to Thickbox, Prettyphoto, Lightbox2, Colorbox or FancyBox script files or code. These are very likely to cause the incompatibility and you will have to either find a setting in the other plugin to switch OFF the use of the conflicting script (possible in NextGEN for example, see under Advanced below) or choose between the two conflicting plugins.


== Screenshots ==

1. Example image with **Overlay** caption. This is the default way Easy FancyBox displays images. Other options are **Inside** and the old **Outside**.

2. Example of a YouTube movie in overlay.


== Upgrade Notice ==

= 1.8.13 =
Prepare Visual Composer Masonry Grid Gallery compatibility option


== Changelog ==

= 1.8.13 =
* Fix version constant issue
* Prepare Visual Composer Masonry Grid Gallery compatibility option

= 1.8.11 =
* Fix Vimeo player direct links breaking

= 1.8.10 =
* Force default autoselector for galleries

= 1.8.9 =
* Prevent gallery next/prev links to show dud target
* FIX: allow youtube url parameters before v=

= 1.8.7 =
* Autoplay Youtube/Vimeo/Dailymotion
* FIX: Exclude Vimeo user pages from autodetect
* FIX: Exclude facebook/twitter share link
* FIX: PDF embed tag

= 1.8.6 =
* Gutenberg file block download button compatibility
* Gutenberg gallery block compatibility
* FIX: Missing argument in easyFancyBox::add_video_wmode_opaque()
* Remove version URL parameters
* FIX: jQuery 3+ e.indexOf is not a function

= 1.8.5 =
* FIX: prevent Inline content title

= 1.8.4 =
* Improved center on scroll behavior with title outside
* FIX: Pro options compatibility

= 1.8.3 =
* FIX: AutoScale option restored
* FIX: outline issue in Firefox
* Prevent SiteGround Optimizer warning about break outside loop

= 1.8.2 =
* FIX: main method not returning true in some cases
* Force all hosted video to https
* FIX: video iframe needs allow="autoplay" on modern browsers
* FIX: default enqueue priority not 10
* FIX: possible infinite loop in prev/next and image preloader
* Move main method (back) to init, position 9
* Introducing easy_fancybox_enqueue_scripts action hook

= 1.8 =
* NEW: Google Docs Viewer for PDF
* NEW: Youtube privacy-enhanced embed
* NEW: Compatibility options: late script inclusion, jquery exclusion, no wp_add_inline_script
* FancyBox: Improved mobile viewport height detection
* FancyBox: now skips subsequent double links in gallery
* FancyBox: new PDF content type
* FancyBox: improved error messages
* Dedicated IE8 stylesheet
* jQuery Easing update to 1.4.1

= 1.7 =
* NEW: Aspect ratio for video frames on smaller screens
* NEW: Global minimum screen size
* NEW: Loading icon for video/iframe content
* NEW: Resize light box on device orientation or browser window size change
* NEW: Modal window class and close button class
* FIX: pre PHP 5.4 compatibility
* FIX: iPhone iframe scrolling
* FIX: Autoptimize compatibility
* Switch to wp_add_inline_script() script printing, thanks @szepeviktor

= 1.6.3 =
* FIX: inline js minification incompatibility, thanks @alexiswilke

= 1.6.2 =
* FIX: line breaks hidden on options media admin page since WP 4.9, thanks @garrett-eclipse

= 1.6.1 =
* Nolightbox class in menu also for other media types than images
* FIX: CSS color code
* Spelling fixes, thanks @garrett-eclipse
* FIX: Pinterest button compatibility
* FIX: Double load plugin text domain

= 1.6 =
* Add webp to default Autodetect image types
* Exclude more rel attribute values from galleries
* BUGFIX: gallery preload
* Update jquery.easing.js and jquery.mousewheel.js

= 1.5.8.2 =
* BUGFIX: use dirname(__FILE__) instead of relying on __DIR__ to be available
* Explicit transparency for gallery navigation links

= 1.5.8 =
* FIX: variable variable php 7 compat
* FIX: obj undefined in minified js
* FIX: nofancybox in menu ignored, thanks Trishah
* Color value sanitize
* NEW: auto popup delay
* NEW: pro extension version compatibility check routine
* NEW: margin option
* NEW: iFrame alow full screen option
* Dropped mu-plugins support
* Added support for universal nolightbox class
* Set focus on iframe after load
* FIX: No center on scroll on touch devices
* FIX: Allow fullscreen videos

= 1.5.7 =
* FIX: Pro extension link update
* NEW: WebP support and class='image' to force image media type
* IE 6-8 css rules optional
* iframe embed for Youtube, Vimeo and Dailymotion
* Croation translation
* HTML5 players allowfullscreen default

= 1.5.6 =
* iPad positioning patch
* Don't unregister scripts that are not ours even for conflict prevention
* box-sizing: border-box issue in Firefox fixed
* Allow mousewheel scrolling page in the background again

= 1.5.5 =
* Prevent mousewheel scrolling page in the background
* New stylesheet IE alphaimageloader path fix approach
* Czech translation added
* Updated Indonesian translation

= 1.5.2 =
* BUGFIX: easy_fancybox_handler() in combo with trigger('click') causes Uncaught Exception script error

= 1.5.1 =
* FIX: jQuery 1.9+ compatibility
* Dropping support for gForms again -- "Cannot convert 'c' to object" error in combination with some older gForms version :(
* NEW: support for Infinite Scroll by Jetpack

= 1.5.0 =
* FIX: CSS3 box-sizing issue (Twenty Thirteen) misplacing close button
* NEW: Added SVG support. Thanks to Simon Maillard.
* Pre WP 3.6: jQuery 1.9+ compatibility
* JQuery Mousewheel extension update to 3.1.3
* NEW: Elegant Themes compatibility
* Some small Touch device compatibility improvement hacks to the 1.3.4 script
* Major plugin overhaul: Class implementation
* NEW: Disable hide on overlay click
* NEW: Allow automatic resizing to large image size set on Settings > Media during media upload via the hidden WordPress function media_upload_max_image_resize() TODO test more!
* NEW Options: iFrame scrolling, autoScale, key navigation/close, cyclic galleries
* Metadata custom parameters and Mousewheel gallery scrolling scripts optional
* Basic RTL languages/text direction support (gallery navigation inversion, title position)
* BUGFIX: https in stylesheet on Windows IIS
* Improved W3TC compatibility: query string issue
* Gravity Forms in ajax mode compatibility
* Use jQuery's bind('ready') for better ajax content compatibility
* Dynamic stylesheet response headers to allow browser caching
* Minified version of jquery.metadata.js
* Auto-detect on image map areas
* nolightbox class for menu items
* SECURITY: Settings sanitization
* BUGFIX: load_textdomain firing after the main settings array is loaded, leaving text strings in it untranslated.
* BUGFIX: missing signs in Youtube url regular expression
* BUGFIX: unquoted rel attribute selectors in jquery.fancybox-1.3.4.js
* BUGFIX: broken url path in IE stylesheet when missing $_SERVER['SERVER_NAME']
* BUGFIX: easing extension not needed on linear easing

= 1.3.4.9 =
* NEW: Lithuanian translation
* NEW: Hindi translation
* NEW: Indonesian translation
* NEW: Romanian translation
* NEW: Polish translation
* NEW: Spanish translation
* NEW: jQuery Metadata support
* NEW: Image map AREA support for all content types
* NEW: new iFrame/HTML5 embed code for YouTube, Vimeo and Dailymotion
* NEW: fix WordPress Dailymotion auto-embed code missing wmode
* Some changes to default settings
* Updated Dutch translation
* BUGFIX: Opening speed

= 1.3.4.8 =
* NEW: Advanced option: Gallery auto-rotation
* NEW: Spotlight effect
* Improved auto-enable and auto-gallery settings
* BIGFIX: CSS IE6 hack
* BIGFIX: PDF object in IE7

= 1.3.4.6 =
* PDF embed compatibility improvement
* NEW: Show/hide title on mouse hover action
* NEW: Auto-gallery modes (Disabled, page/post images only, all)
* NEW: Dailymotion support
* Links with id **fancybox-auto** will be triggered on page load
* Anything with class **fancybox-hidden"** will be hidden
* Support for menu items in iframe
* Added class **nofancybox** for exclusion when auto-enabling

= 1.3.4.5 =
* FancyBox script version 1.3.4 (2010/11/11 - http://fancybox.net/changelog/)
* NEW: Support for PDF
* NEW: Easing options
* YouTube, Vimeo and iFrame options adjustable
* lots and lots of more options!
* BIGFIX: work-around for missing wmode in WordPress (auto-)embedded movies (credits: Crantea Mihaita)

= 1.3.3.4.2 =
* BIGFIX: iframe width
* BIGFIX: image overlay size in Google Chrome browser issue (FancyBox 1.3.3)
* BIGFIX: fancybox-swf

= 1.3.3.4 =
* FancyBox script version 1.3.3 (2010/11/4 - http://fancybox.net/changelog/)
* Vimeo support
* YouTube Short URL support (disabled by default)
* Auto-recognition and seperate class `fancybox-youtube` for YouTube
* Auto-recognition and seperate class `fancybox-vimeo` for Vimeo

= 1.3.2 =
* FancyBox script version 1.3.2 (2010/10/10 - http://fancybox.net/changelog/)

= 1.3.1.3 =
* translation .pot file available
* Dutch translation
* NEW: YouTube and Flash movie support
* NEW: Iframe support
* added option Auto-enable for...

= 1.3.1.2 =
* added option titlePosition : over / inside / outside
* added option transitionIn : elastic / fade / none
* added option transitionOut : elastic / fade / none

= 1.3.1.1 =
* small jQuery speed improvement by chaining object calls

= 1.3.1 =
* Using FancyBox version 1.3.1
