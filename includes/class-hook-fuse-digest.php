<?php
/**
 * Weekly email to the site's admin address listing the errors caught since the last email.
 *
 * @package HookFuse
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * One email a week at most, and only when something new was caught. It replaces the "Your Site is
 * Experiencing a Technical Issue" emails WordPress sends for each fatal error.
 */
final class Hook_Fuse_Digest {

	const EVENT     = 'hook_fuse_weekly_digest';
	const LAST_SENT = 'hook_fuse_digest_sent';

	/**
	 * Error log.
	 *
	 * @var Hook_Fuse_Log
	 */
	private $log;

	/**
	 * Constructor.
	 *
	 * @param Hook_Fuse_Log $log Error log.
	 */
	public function __construct( Hook_Fuse_Log $log ) {
		$this->log = $log;
	}

	/**
	 * Keep the weekly event scheduled while the email is on, and remove it when it is off.
	 */
	public function register() {
		add_action( self::EVENT, array( $this, 'send' ) );
		add_action( 'init', array( $this, 'sync_schedule' ) );
	}

	/**
	 * Schedule or unschedule the event to match the setting.
	 */
	public function sync_schedule() {
		$on        = Hook_Fuse_Settings::get()['digest'];
		$scheduled = wp_next_scheduled( self::EVENT );
		if ( $on && ! $scheduled ) {
			wp_schedule_event( time() + WEEK_IN_SECONDS, 'weekly', self::EVENT );
		} elseif ( ! $on && $scheduled ) {
			self::unschedule();
		}
	}

	/**
	 * Remove the weekly event (also on deactivation).
	 */
	public static function unschedule() {
		wp_clear_scheduled_hook( self::EVENT );
	}

	/**
	 * Send the email if anything new was caught since the last one.
	 */
	public function send() {
		if ( ! Hook_Fuse_Settings::get()['digest'] ) {
			return;
		}
		$since   = (int) get_option( self::LAST_SENT, 0 );
		$entries = $this->log->since( $since );
		if ( empty( $entries ) ) {
			return;
		}

		$lines = array();
		foreach ( $entries as $entry ) {
			$lines[] = sprintf(
				/* translators: 1: number of times, 2: plugin folder, 3: hook name, 4: error message, 5: file and line. */
				__( '%1$dx  %2$s  on hook "%3$s": %4$s (%5$s)', 'hook-fuse' ),
				(int) $entry['count'],
				$entry['plugin'],
				$entry['hook'],
				$entry['message'],
				$entry['where']
			);
		}

		$subject = sprintf(
			/* translators: %s: site name. */
			__( '[%s] Hook Fuse: errors caught this week', 'hook-fuse' ),
			wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES )
		);
		$body = __( 'Hook Fuse stopped these errors from breaking your pages. The plugins named below still have a bug; consider reporting it to their authors.', 'hook-fuse' )
			. "\n\n" . implode( "\n", $lines ) . "\n\n"
			. sprintf(
				/* translators: %s: URL of the Hook Fuse page in the admin. */
				__( 'Details: %s', 'hook-fuse' ),
				admin_url( 'tools.php?page=hook-fuse' )
			);

		if ( wp_mail( get_option( 'admin_email' ), $subject, $body ) ) {
			update_option( self::LAST_SENT, time(), false );
		}
	}
}
