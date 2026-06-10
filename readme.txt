=== NexiSoon – Coming Soon, Under Construction & Maintenance Mode ===
Contributors: nexibyllc
Tags: coming soon, maintenance mode, under construction, launch page
Requires at least: 6.4
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Create a clean coming soon, under construction, or maintenance mode page for your WordPress website.

== Description ==

NexiSoon helps site owners temporarily hide the public frontend while keeping the real website available to logged-in administrators.

The free version includes:

* Coming Soon, Under Construction, and Maintenance Mode options.
* Three bundled templates: Classic Centered, Minimal Launch, and Bold Countdown.
* Logo and favicon uploads using the WordPress media library.
* Heading, subheading, optional button, footer text, and color controls.
* Optional countdown timer.
* SEO title and meta description fields.
* Proper maintenance mode 503 status with Retry-After header.
* Administrator bypass so users with `manage_options` can view the real site.

NexiSoon does not load remote templates, make external API calls, track users, or include license/payment code.

== Installation ==

1. Upload the `nexisoon` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the Plugins screen in WordPress.
3. Go to NexiSoon in the WordPress admin menu.
4. Configure your page and enable NexiSoon.

== Frequently Asked Questions ==

= Will administrators still see the real website? =

Yes. Logged-in users with the `manage_options` capability bypass the NexiSoon page.

= Does NexiSoon block the login page or admin area? =

No. The WordPress login page, admin area, AJAX requests, REST API requests, and cron requests are not blocked.

= Does maintenance mode send a 503 status? =

Yes. Maintenance Mode sends HTTP 503 and a Retry-After header. Coming Soon and Under Construction modes send HTTP 200.

= Are templates loaded remotely? =

No. All free templates are bundled inside the plugin.

== Screenshots ==

1. NexiSoon settings page.
2. Classic Centered template.
3. Minimal Launch template.
4. Bold Countdown template.

== Changelog ==

= 1.0.1 =
* Refined admin template selection with preview cards.

= 1.0.0 =
* Initial free release.

== Upgrade Notice ==

= 1.0.1 =
Improved admin template selection UI.

= 1.0.0 =
Initial free release.
