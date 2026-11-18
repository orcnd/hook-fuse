<?php
/**
 * Plugin Name:       Hook Fuse
 * Plugin URI:        https://github.com/orcnd/hook-fuse
 * Description:       Stops a crash inside another plugin's hook from taking the whole page down. The error is caught, logged and reported once a week instead.
 * Version:           0.1.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            orcnd
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       hook-fuse
 *
 * @package HookFuse
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'HOOK_FUSE_VERSION', '0.1.0' );
define( 'HOOK_FUSE_FILE', __FILE__ );
define( 'HOOK_FUSE_DIR', plugin_dir_path( __FILE__ ) );

require_once HOOK_FUSE_DIR . 'includes/class-hook-fuse-settings.php';
require_once HOOK_FUSE_DIR . 'includes/class-hook-fuse-log.php';
require_once HOOK_FUSE_DIR . 'includes/class-hook-fuse-guard.php';
require_once HOOK_FUSE_DIR . 'includes/class-hook-fuse-digest.php';
require_once HOOK_FUSE_DIR . 'includes/class-hook-fuse-admin.php';

/**
 * Wires the parts together. The guard is registered as early as possible so it sees every hook the
 * protected plugins add, including the ones they add while WordPress is still loading plugins.
 */
function hook_fuse_boot() {
	$log   = new Hook_Fuse_Log();
	$guard = new Hook_Fuse_Guard( Hook_Fuse_Settings::protected_dirs(), $log );
	$guard->register();

	( new Hook_Fuse_Digest( $log ) )->register();

	if ( is_admin() ) {
		( new Hook_Fuse_Admin( $log ) )->register();
	}
}
hook_fuse_boot();

register_activation_hook( __FILE__, array( 'Hook_Fuse_Settings', 'on_activate' ) );
register_deactivation_hook( __FILE__, array( 'Hook_Fuse_Digest', 'unschedule' ) );
