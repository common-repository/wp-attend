=== WP Attend ===
PLUGIN URI: https://wp-attend.com
Contributors: bitsandarts
Donate link: https://wp-attend.com
Tags: attendance, attendances, registration, organisation, administration, email, calendar
Requires at least: 4.6
Tested up to: 5.2
Stable tag: 1.0
Requires PHP: 5.2.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

This plugin allows easy creation of events and registration of attendances via email.

== Description ==
WP attend is an easy to use plugin for the creation and managing of activities.
People can subsribe to receive attendance emails and can confirm or deny their attendance with one click.
You can easily create, manage and delete activities via a calendar, and choose to send attendance emails to your subscribers.
A clear overview of attendances is consultable per activity.

= Features: =
* Shortcode for a calendar to add, edit and delete activities
* Shortcode for a registration form to subsribe
* One click attendance confirmation emails for subscribers
* Overview of attendances per activity
* Manage your subscribers
* Add custom privacy policy for subscribers

><strong>Documentation</strong><br>
> Please visit the WP attend website for the [documentation](https://wp-attend.com/documentation)

== Installation ==

This section describes how to install the plugin and get it working.

e.g.

1. Upload the 'wp-attend' folder to the '/wp-content/plugins/' directory
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Use the WP Attend admin screen to configure the plugin
1. Add the calendar to a page with this shortcode: [wp_at_calendar]
1. Add the subscribtion form to a page with this shortcode: [wp_at_subscribe]


== Frequently Asked Questions ==

= When trying to create an activity I get the error message 'Sending email failed' =

You probably haven't configured your email. We recommend using a plugin like 'WP mail smpt' which is very easy to configure.

== Screenshots ==

1. Calendar
2. Subscribe
3. Day overview
4. Add activity
5. One click attendance email
6. Attendances overview
7. Manage subscriptions

== Changelog ==

= 1.0.3 =
* fixed more sanitization issues

= 1.0.2 =
* added security with sanitize, validation and escape

= 1.0 =
* Initial Release

== Upgrade Notice ==
= 1.0 =
Initial Release
