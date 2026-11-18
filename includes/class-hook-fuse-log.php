<?php
/**
 * Log of caught errors, kept small: identical errors are merged and only the newest entries are kept.
 *
 * @package HookFuse
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Caught errors are collected in memory and written once, at the end of the request.
 */
final class Hook_Fuse_Log {

	const OPTION      = 'hook_fuse_log';
	const MAX_ENTRIES = 50;
	const MAX_MESSAGE = 300;

	/**
	 * Entries caught during this request, keyed by signature.
	 *
	 * @var array<string,array>
	 */
	private $pending = array();

	/**
	 * Remember a caught error.
	 *
	 * @param string    $owner     Protected plugin folder.
	 * @param string    $hook_name Hook name.
	 * @param string    $callback  Callback label.
	 * @param Throwable $error     What was thrown.
	 */
	public function record( $owner, $hook_name, $callback, Throwable $error ) {
		$message = substr( wp_strip_all_tags( $error->getMessage() ), 0, self::MAX_MESSAGE );
		$where   = self::short_path( $error->getFile() ) . ':' . $error->getLine();
		$key     = md5( $owner . '|' . $hook_name . '|' . $callback . '|' . get_class( $error ) . '|' . $where );

		if ( empty( $this->pending ) ) {
			add_action( 'shutdown', array( $this, 'flush' ) );
		}
		if ( isset( $this->pending[ $key ] ) ) {
			++$this->pending[ $key ]['count'];
			return;
		}
		$this->pending[ $key ] = array(
			'plugin'   => $owner,
			'hook'     => $hook_name,
			'callback' => $callback,
			'type'     => get_class( $error ),
			'message'  => $message,
			'where'    => $where,
			'count'    => 1,
			'first'    => time(),
			'last'     => time(),
		);

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- only with WP_DEBUG on, so developers still see the error.
			error_log( sprintf( 'Hook Fuse caught %s in %s on hook "%s": %s (%s)', get_class( $error ), $callback, $hook_name, $message, $where ) );
		}
	}

	/**
	 * Merge this request's entries into the stored log.
	 */
	public function flush() {
		if ( empty( $this->pending ) ) {
			return;
		}
		$entries = $this->all();
		foreach ( $this->pending as $key => $entry ) {
			if ( isset( $entries[ $key ] ) ) {
				$entries[ $key ]['count'] += $entry['count'];
				$entries[ $key ]['last']   = $entry['last'];
				$entries[ $key ]['message'] = $entry['message'];
			} else {
				$entries[ $key ] = $entry;
			}
		}
		uasort(
			$entries,
			static function ( $a, $b ) {
				return $b['last'] - $a['last'];
			}
		);
		update_option( self::OPTION, array_slice( $entries, 0, self::MAX_ENTRIES, true ), false );
		$this->pending = array();
	}

	/**
	 * Stored entries, newest first.
	 *
	 * @return array<string,array>
	 */
	public function all() {
		$entries = get_option( self::OPTION, array() );
		return is_array( $entries ) ? $entries : array();
	}

	/**
	 * Entries seen after a timestamp.
	 *
	 * @param int $since Unix time.
	 * @return array<string,array>
	 */
	public function since( $since ) {
		return array_filter(
			$this->all(),
			static function ( $entry ) use ( $since ) {
				return (int) $entry['last'] > (int) $since;
			}
		);
	}

	/**
	 * Delete every entry.
	 */
	public function clear() {
		delete_option( self::OPTION );
	}

	/**
	 * Path relative to wp-content, so the log does not reveal the server's directory layout.
	 *
	 * @param string $file Absolute path.
	 * @return string
	 */
	private static function short_path( $file ) {
		$file    = wp_normalize_path( (string) $file );
		$content = wp_normalize_path( WP_CONTENT_DIR );
		return 0 === strpos( $file, $content ) ? ltrim( substr( $file, strlen( $content ) ), '/' ) : basename( $file );
	}
}
