<?php

namespace Savvii;

/**
 * Warpdrive class
 */
class Warpdrive {

    /**
     * Initialize various modules of Warpdrive
     */
    public static function load_modules() {
        // Load warpdrive.access_token
        $token = Options::access_token();

        new SavviiDashboard();
        new SecurityPlugin();

        // Include purge cache module
        new CacheFlusherPlugin();
        // Include read logs
        new ReadLogsPlugin();

        // Only load CDN when we want to
        $default_cdn_option = get_site_option( Options::CDN_ENABLE, false );
        if ( ! is_ssl() && get_option( Options::CDN_ENABLE, $default_cdn_option ) ) {
            new CdnLinkRewriterPlugin();
        }
    }
}
