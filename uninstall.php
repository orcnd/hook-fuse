<?php
/**
 * Removes everything Hook Fuse stored.
 *
 * @package HookFuse
 */

if ( ! defined( 'ABSPATH' ) || ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'hook_fuse_settings' );
delete_option( 'hook_fuse_log' );
delete_option( 'hook_fuse_digest_sent' );
delete_metadata( 'user', 0, 'hook_fuse_seen', '', true );
wp_clear_scheduled_hook( 'hook_fuse_weekly_digest' );
