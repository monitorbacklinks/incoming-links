=== Incoming Links ===
Contributors: MonitorBacklinks, deconf
Tags: incoming, links, backlinks, monitor, incoming links, inbound links, referrers, inlinks, inward links, SEO
Requires at least: 3.1.0
Tested up to: 4.1
Stable tag: 0.9.10b
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Keep track of your existing backlinks and automatically detect any new incoming links.

== Description ==

This is a beta version, at this stage, please consider [submiting a bug](https://github.com/monitorbacklinks/incoming-links/issues) instead of rating it.

Every website owner knows how valuable a quality backlink is. All incoming links to a website will play a crucial role in obtaining a higher page rank on Search Engines. For a successful SEO strategy you have to get as many backlinks as possible and also monitor the quality of your new links. Incoming Links is a WordPress plugin provided for free by [Monitor Backlinks](https://monitorbacklinks.com/), which will report all your incoming links and will automatically check the quality of those links (SEO Data like anchor text and the type of link: follow/nofollow).

= How does it work? =

When a new referrer is detected, the link is added to a waiting list, to be automatically verified. At the selected time interval the links from pending for verification list are checked for validity, text anchors and follow/nofollow tags. When a valid backlink is found, the link and its SEO details are added to a Valid Backlinks list. You can verify this list on your website backend or you can have it by mail as a daily/weekly report.    

= Incoming Links Widgets =

To easily keep track of your new incoming links, two backend widgets will display your Recent Backlinks and Backlinks Statistic in your Administration Dashboard.

Use the frontend widget (Appearance -> Widgets -> Recent Backlinks) to display in your website the latest domains linking back to you.

= Incoming Links Dashboard =

A dedicated dashboard will allow you to manage and view all your valid backlinks and all referrers pending for verification. You can view details about each valid backlink (detection date, anchor text, follow/nofollow tags, source domain) and you can remove/highlight each backlink.

= Automatic Emails =

The plugin will generate reports and send automatic emails with your latest backlinks, daily or weekly at a specified time. After receiving these reports by email, you can share them with your friends, team or business partners.

= Blocking Options =

With these options, you have full control over the verification process. You can choose to ignore backlinks from a specific referrer by blocking its Domain Name, Referrer Link or IP Address.

= Other Settings and Options =

A set of advanced options will allow a full customization of the plugin by: ignoring referrers for certain roles, limiting the number of crawled links per domain, ignoring referrers matching a certain regexp pattern, automatically blocking a domain after a specified number of retries, setting the number of referrers to be checked per cron and switching between wordpress cron jobs and SO managed cron jobs. 

= Related Links =

A short tutorial is available here: [Incoming Links Tutorial](http://monitorbacklinks.com/blog/incoming-links/)

== Installation ==

1. Upload the full directory into your wp-content/plugins directory
2. Activate the plugin at the plugin administration page
3. Configure the plugin by going to Incoming Links -> Settings in your administration menu
4. You can check your reports and latest backlinks in your administration dashboard or by accessing the Incoming Links main board   

A full tutorial is available here: [Incoming Links Tutorial](http://monitorbacklinks.com/blog/incoming-links/)

== Frequently Asked Questions == 

= Do I need to make any additional settings for the plugin to work? =

The plugin should work properly out of the box, but you can customize it to fit your needs or to improve its performance.

= More Questions? =

Have additional questions? FAQ and features description are available on [Monitor Backlinks Blog](http://monitorbacklinks.com/blog/incoming-links/) 

== Screenshots ==

1. Incoming Links
2. Incoming Links Settings Page
3. Frontend Widget
4. Recent Backlinks Widget and Backlinks Statistic Widget

== License ==

This plugin it's released under the GPLv2, you can use it free of charge on your personal or commercial website.

== Changelog ==

= 01.02.2015 - v0.9.10b =

- XSS vulnerability fix

= 22.12.2014 - v0.9.9b =

- pagination fix for Main Dashboard, Block Ip, Block Referrer, Block Domains and Referrers pending for verification

= 18.04.2014 - v0.9.8b =

- new placement for alerts and errors
- css fixes 

= 18.04.2014 - v0.9.7b =

- alert when DISABLE_WP_CRON is set to TRUE 

= 08.04.2014 - v0.9.6b =

- bugfix: removed array_shift from a global var
- mail template update
- additional checks on wp_remote_get response

= 16.03.2014 - v0.9.5b =

- canonical referrers exclusion
- mail subject update, mail body update
- removed short tags in frontend widget
- linking to actual url in backend widget
- fixed strict standard warning for send_emails()
- removing assets from github
- unique domains in frontend widget

= 08.03.2014 - v0.9.4b =

- checking referrers for 200 response code before parsing

= 29.02.2014 - v0.9.3b =

- replaced curl with wp_remote_get
- prevent phpQuery memory leaks
- no further parsing on pages greater than 2 Mb
- referrers from pages greater than 2 Mb are added to block list
- removed php short tags to increase code portability

= 26.02.2014 - v0.9.2b =

- disabled cURL follow redirects
- fixed URL in Incoming Links dashboard

= 22.02.2014 - v0.9.1b =

- cURL user agent standard format

= 11.02.2014 - v0.9b =

- first release
