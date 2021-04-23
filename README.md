# Warpdrive - Savvii hosting plugin
This plugin adds several speed and security related features to your WordPress website on the Savvii hosting platform.
We welcome contributions, see the section on contributing for more information.

## Support
For plugin support, use our [knowledge base](https://support.savvii.nl/en/support/solutions/folders/11000008408).
For development support, please look over the [CONTRIBUTING.md](https://github.com/Savvii/warpdrive/blob/master/CONTRIBUTING.md) before raising an [issue here on github](https://github.com/Savvii/warpdrive/issues).

## Features
### Cache flusher
The cache flusher flushes the enabled/available caches (Varnish, Memcached, OpCache and/or Sucuri) when certain changes are saved in WordPress.
Depending on the users caching setting this module will flush the varnish cache in different ways.
You can choose between flushing the cache on "(custom) post/page edit or publish" and "all post/page edit or publish, comment changes, attachment changes".
If your site is having heavy traffic we recommend you to choose the "(custom) post/page edit or publish" option to minimize cache flushes.
This way individual objects will age away in the cache to minimize the strain on the webserver.
If you want the cache to flush immediately every time you update a post/comment/attachment in WordPress you can choose "all post/page edit or publish, comment changes, attachment changes".
Only (custom) post/page, comment changes and attachment changes will flush the cache on save or publish.

#### Flush on demand
The full page cache can be flushed from another plugin by using:

    do_action( 'warpdrive_cache_flush' ); // This will flush the entire cache
    do_action( 'warpdrive_domain_flush' ); // This will only flush the cache of the current domain

### Read logs
From within WordPress on the Warpdrive dashboard there are links to see the last 10/100 entries in the access or error log entries.

### Security
All login attempts, succesful and failed, are logged to syslog using LOG_AUTH. The same format as sshd is used for messages:

    Apr 30 17:39:11 vvv warpdrive[5759]: Authentication success for author from 192.168.50.1
    Apr 30 17:39:24 vvv warpdrive[5759]: Authentication success for warp from 192.168.50.1
    Apr 30 17:39:52 vvv warpdrive[5759]: Authentication failure for admin from 192.168.50.1
    Apr 30 17:40:08 vvv warpdrive[5758]: Authentication failure for admin from 192.168.50.1
    Apr 30 17:40:25 vvv warpdrive[5758]: Blocked author enumeration from 192.168.50.1
    Apr 30 17:40:38 vvv warpdrive[5759]: Blocked author enumeration from 192.168.50.1

A different part this module is to prevent leaking sensitive account information.
Author enumeration is blocked and logged. Login attempts with wrong credentials won't give information about valid usernames, a generic message is shown on unknown username and on correct username but wrong password.
We do not log any passwords.

## Contributing
For contributing information refer to the [CONTRIBUTING.md](https://github.com/Savvii/warpdrive/blob/master/CONTRIBUTING.md).
