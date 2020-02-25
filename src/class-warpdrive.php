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
        // Include database size module
        new DatabaseSizePlugin();
    }
}
