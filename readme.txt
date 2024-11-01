=== Short URL FE ===
Contributors: jmferri
Tags: ShortURL, Short URL, ShortURL, short link, ShortLink, post
Requires at least: 5.0
Tested up to: 5.9
Requires PHP: 7.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Show a Short URL for all of your blog posts and optionally for pages

== Description ==

This plugin is based on **[Prasanna SP Tiny URL](https://wordpress.org/plugins/tiny-url/)** plugin and uses some of its code.

This plugin shows a short URL for each of your blog posts after post content (in FrontEnd). Short URLs are great for sharing your posts on micro-blogging sites like twitter, identi.ca etc., This plugin sends current post or page URL to [v.gd](https://v.gd) or [TinyURL.com](https://tinyurl.com) and gets a short URL for the same. Then it shows that Short URL after post content. You can select which users can see short URL textbox: "All users", "Regitered users" or "Registered users that can edit the post/page". User can just click on the box to select URL, or click on a Copy button to Copy the Short URL to clipboard. You can also show Short URLs for pages by selecting *Show Short URL FE on pages* option in plugin settings page.

**Note:** Please read V.gd [terms of use](https://v.gd/terms.php) before activating the plugin. You must abide by them after activating the plugin.
**Note:** Please read TinyURL's [terms of use](https://tinyurl.com/#terms) before activating the plugin. You must abide by them after activating the plugin. TinyURL is a trademark of TinyURL, LLC

== Installation ==

1. Upload the `short-url-fe` folder to the `/wp-content/plugins/` directory
2. Activate the Short URL FE plugin through the 'Plugins' menu in WordPress
3. Plugin will automatically add short URLs for blog posts

== Screenshots ==

1. Plugin showing short URL for a blog post
2. Short URL with Copy button
3. Short URL FE options page

== Frequently Asked Questions ==

= Does it show short URLs for pages as well? =

Yes, it does. But, you need to enable this feature on Short URL FE plugin options page.

= Copy button is not showing up next to short URL textbox. Why? =

Make sure you've selected <strong>Show Copy URL button</strong> in Short URL FE options.

== Other Notes ==

= How to style the output? =

You can add a class for textbox and a class for button on Short URL FE plugin options page.

The output of this plugin is wrapped in a `<p>` tag with class `short-url-fe`. Use this class and, optionally, your classes to style them.
Example,

`/* this change background color for area where plugin is shown */
.short-url-fe {
    background-color: #ccc;
    padding: 4px;
    border-radius: 4px;
}
/* Assuming you added a class 'mytextbox' for textbox, this adjusts textbox style */
.short-url-fe .mytextbox {
    width: min(20rem, 70%);
    box-shadow: 2px 2px 4px 1px #ccc inset;
    padding: .5rem 4px;
}
/* and assuming you added a class 'mycopybutton' for button, this adjusts button style */
.short-url-fe .mycopybutton {
    background-color: lightblue;
    color: darkblue;
}`

== Changelog ==

= 1.0.0 =

* Initial public release.
