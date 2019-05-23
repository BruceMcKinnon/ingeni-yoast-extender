=== Ingeni Slick Carousel ===

Contributors: Bruce McKinnon
Tags: yoast, seo
Requires at least: 4.8
Tested up to: 5.1.1
Stable tag: 2019.01

Used in conjunction with Yoast SEO. Automatically inserts meta descriptions and target words if none are specified.



== Description ==

* - Used in conjunction with Yoast SEO. 

* - Automatically inserts meta descriptions and target words if none are specified.

* - Provides a shortcode that adds Google Analytics tracking support for file downloads.




== Installation ==

1. Upload the 'ingeni-yoast-extender’ folder to the '/wp-content/plugins/' directory.

2. Activate the plugin through the 'Plugins' menu in WordPress.



== Frequently Asked Questions ==


++ How to I track file downloads via Google Analytics? ++

1 = Replace the <a> tag with the [ga-track-event] shortcode

For example, replace <a href=“https://mydomain.com/files/todownload.pdf” target=“_blank” class=“button”>Click to Download</a>

with:

[ga-track-event file_url="files/todownload.pdf" class="button" text=“Click to Download"]

This produces the following code:

<a class="button" href="https://mydomain.com/files/todownload.pdf" target="_blank" rel="noopener" onclick="ga('send', 'event', 'Download', 'pdf', ’todownload’, 0);">Click to Download</a>


Available parameters are:

file_url - full path to the file. The current domain is suffixed to the file_url if not specified.

new_tab - open the link in a new tab. Default = 1.

category - GA event category. Default = “event"

action - GA description of the event. Defaults to "Download",

opt_label - GA optional descriptor of the event. Defaults to the file extension.

opt_value - GA optional value of the event. Defaults to the file name.

opt_noninteraction - By default, the event hit sent by _trackEvent() will impact a visitor's bounce rate. By setting this parameter to true, this event hit will not be used in bounce rate calculations. Default = 1

text - Text that the user sees on the link. Defaults to “Download Now"

class - Optional class to add to the <a> tag. Defaults to nothing.





== Changelog ==

v2019.01 - Initial version.
