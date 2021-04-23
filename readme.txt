=== Warpdrive ===
Contributors: fuegas
Tags: hosting
Requires at least: 4.0.1
Tested up to: 5.1.1
Requires PHP: 5.6.0
Stable tag: trunk
License: GPL-3.0-only
License URI: https://github.com/Savvii/warpdrive/blob/master/LICENSE

Hosting plugin for the platform of Savvii.

== Description ==

This plugin adds several speed and security related features to your WordPress website on the Savvii hosting platform. We welcome contributions, see [CONTRIBUTING](https://github.com/Savvii/warpdrive/blob/master/CONTRIBUTING.md) at our repository for more information.

A detailed [README](https://github.com/Savvii/warpdrive/blob/master/README.md) can be found at our repository.

A few notes about the sections above:

== Changelog ==

= 2.10.5
* OpCache and Sucuri support
* Flush on custom post types

= 2.10.4 =
* Rewrite for the update system

= 2.10.3 = (never released)
* Support for memcached
* small fixes

= 2.10.2 =
* Support GitHub Updater
* Add hooks for CLI and cron for the Updater

= 2.10.1 =
* Add readme.txt with a changelog

= 2.10.0 =
* Remove cookieless domain functionality

= 2.9.3 =
* Fix upgrader_post_install bug

= 2.9.2 =
* Fix autoloader, check if a file exists due to name collisions with other plugins

= 2.9.1 =
* Update PHPUnit dependency to 5.7.X

= 2.9.0 =
* Change Warpdrive menu location to header
* Refactor 'savvii' references to 'warpdrive'

= 2.8.5 =
* Change license to GPLv3-only
* Add travis config for automated tests

= 2.8.4 =
* Add type 'wordpress-plugin' to composer.json

= 2.8.3 =
* Support multisite checks for WordPress sites < 4.6

== Support ==

For plugin support, use our [knowledge base](https://support.savvii.nl/en/support/solutions/folders/11000008408).
For development support, please look over the [CONTRIBUTING.md](https://github.com/Savvii/warpdrive/blob/master/CONTRIBUTING.md) before raising an [issue here on github](https://github.com/Savvii/warpdrive/issues).
