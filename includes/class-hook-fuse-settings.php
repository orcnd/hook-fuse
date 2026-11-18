<?php
/**
 * Stored settings: which plugins are protected and whether the weekly email is on.
 *
 * @package HookFuse
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings access. Everything lives in one non-autoloaded option.
 */
final class Hook_Fuse_Settings {

	const OPTION = 'hook_fuse_settings';

	/**
	 * Plugin folders protected by default when they are installed at first activation.
	 * The Meta pixel plugin is here because its WPForms integration throws a TypeError on forms with an
	 * email confirmation field, which ends the request with a fatal error.
	 */
	const PRESETS = array( 'official-facebook-pixel' );

	/**
	 * Current settings merged over the defaults.
	 *
	 * @return array{protected: string[], digest: bool}
	 */
	public static function get() {
		$saved = get_option( self::OPTION, array() );
		$saved = is_array( $saved ) ? $saved : array();
		return array(
			'protected' => isset( $saved['protected'] ) && is_array( $saved['protected'] ) ? array_values( array_map( 'strval', $saved['protected'] ) ) : array(),
			'digest'    => ! isset( $saved['digest'] ) || (bool) $saved['digest'],
		);
	}

	/**
	 * Save settings after cleaning them.
	 *
	 * @param string[] $folders Plugin folder names to protect.
	 * @param bool     $digest  Whether the weekly email is on.
	 */
	public static function save( array $folders, $digest ) {
		$folders = array_values( array_unique( array_filter( array_map( 'sanitize_key', $folders ) ) ) );
		$folders = array_values( array_diff( $folders, array( self::own_folder() ) ) );
		update_option(
			self::OPTION,
			array(
				'protected' => $folders,
				'digest'    => (bool) $digest,
			),
			false
		);
	}

	/**
	 * Absolute directories of the protected plugins, each ending in a slash.
	 *
	 * @return string[]
	 */
	public static function protected_dirs() {
		$dirs = array();
		foreach ( self::get()['protected'] as $folder ) {
			$dir = wp_normalize_path( trailingslashit( WP_PLUGIN_DIR ) . $folder . '/' );
			if ( is_dir( $dir ) ) {
				$dirs[ $folder ] = $dir;
			}
		}
		return $dirs;
	}

	/**
	 * Folder name of this plugin. It never protects itself.
	 *
	 * @return string
	 */
	public static function own_folder() {
		return basename( dirname( HOOK_FUSE_FILE ) );
	}

	/**
	 * On first activation, protect the preset plugins that are installed.
	 */
	public static function on_activate() {
		if ( false !== get_option( self::OPTION, false ) ) {
			return;
		}
		$folders = array();
		foreach ( self::PRESETS as $folder ) {
			if ( is_dir( trailingslashit( WP_PLUGIN_DIR ) . $folder ) ) {
				$folders[] = $folder;
			}
		}
		self::save( $folders, true );
	}
}
