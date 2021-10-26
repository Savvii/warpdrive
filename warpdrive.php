<?php
/**
 * Plugin name: Warpdrive
 * Plugin URI: https://github.com/Savvii/warpdrive
 * GitHub Plugin URI: Savvii/warpdrive
 * Description: Hosting plugin for Savvii
 * Version: 2.10.7
 * Author: Savvii <support@savvii.com>
 * Author URI: https://www.savvii.com
 * License: GPL-3.0-only
 *
 * Warpdrive, the hosting plugin for the Savvii hosting platform
 * Copyright (C) 2017 Savvii
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 */

spl_autoload_register( function( $class_name ) {
    if ( strpos( $class_name, 'Savvii\\' ) === 0 ) {
        $file = __DIR__ . str_replace( 'savvii\\', '/src/class-', strtolower( preg_replace( '/([a-zA-Z])(?=[A-Z])/', '$1-', $class_name ) ) ) . '.php';
        if ( file_exists( $file ) ) {
            require $file;
        }
    }
});
require __DIR__.'/src/compatibility.php';

add_action( 'plugins_loaded', [ 'Savvii\Warpdrive', 'load_modules' ] );
add_action( 'admin_init', [ 'Savvii\Updater', 'create_instance' ] );
add_action( 'cli_init', [ 'Savvii\Updater', 'create_instance' ] );
if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
    add_action( 'init', [ 'Savvii\Updater', 'create_instance' ] );
}
