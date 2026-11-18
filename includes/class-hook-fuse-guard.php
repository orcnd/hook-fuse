<?php
/**
 * The guard: wraps the hook callbacks of protected plugins so an uncaught error stays inside the callback.
 *
 * @package HookFuse
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wraps callbacks lazily. WordPress runs the special "all" hook right before every action and filter, so
 * the guard looks at a hook's callbacks just before they run and wraps the ones whose code lives in a
 * protected plugin's folder. A wrapped callback that throws is logged; a filter then passes its input on
 * unchanged and an action simply ends, so the page keeps rendering.
 *
 * Not covered: errors while a plugin's files are loaded, errors outside hooks, and real fatal errors
 * such as running out of memory. PHP cannot catch those.
 */
final class Hook_Fuse_Guard {

	/**
	 * Protected plugin folder => absolute directory with a trailing slash.
	 *
	 * @var array<string,string>
	 */
	private $dirs;

	/**
	 * Where caught errors go.
	 *
	 * @var Hook_Fuse_Log
	 */
	private $log;

	/**
	 * Hook name => callback ids already looked at, so each callback is inspected once per request.
	 *
	 * @var array<string,array<string,bool>>
	 */
	private $seen = array();

	/**
	 * Constructor.
	 *
	 * @param array<string,string> $dirs Protected plugin folder => directory.
	 * @param Hook_Fuse_Log        $log  Error log.
	 */
	public function __construct( array $dirs, Hook_Fuse_Log $log ) {
		$this->dirs = $dirs;
		$this->log  = $log;
	}

	/**
	 * Start watching hooks. Does nothing when no plugin is protected.
	 */
	public function register() {
		if ( empty( $this->dirs ) ) {
			return;
		}
		add_filter( 'all', array( $this, 'before_hook' ) );
	}

	/**
	 * Runs before every hook. Wraps the protected callbacks of the hook that is about to run.
	 *
	 * @param string $hook_name Hook about to run.
	 */
	public function before_hook( $hook_name ) {
		global $wp_filter;
		if ( ! is_string( $hook_name ) || ! isset( $wp_filter[ $hook_name ] ) || ! ( $wp_filter[ $hook_name ] instanceof WP_Hook ) ) {
			return;
		}
		$hook = $wp_filter[ $hook_name ];
		foreach ( $hook->callbacks as $priority => $callbacks ) {
			foreach ( $callbacks as $id => $callback ) {
				if ( isset( $this->seen[ $hook_name ][ $id ] ) ) {
					continue;
				}
				$this->seen[ $hook_name ][ $id ] = true;
				$owner                           = $this->owner_of( $callback['function'] );
				if ( null === $owner ) {
					continue;
				}
				$hook->callbacks[ $priority ][ $id ]['function'] = $this->wrap( $callback['function'], $hook_name, $owner );
			}
		}
	}

	/**
	 * A callback that runs the original and catches anything it throws.
	 *
	 * @param callable $original  Original callback.
	 * @param string   $hook_name Hook it is attached to.
	 * @param string   $owner     Protected plugin folder it belongs to.
	 * @return Closure
	 */
	private function wrap( $original, $hook_name, $owner ) {
		$log   = $this->log;
		$label = self::label( $original );
		return static function ( ...$args ) use ( $original, $hook_name, $owner, $label, $log ) {
			try {
				return call_user_func_array( $original, $args );
			} catch ( Throwable $error ) {
				$log->record( $owner, $hook_name, $label, $error );
				return isset( $args[0] ) ? $args[0] : null;
			}
		};
	}

	/**
	 * The protected plugin folder a callback's code lives in, or null.
	 *
	 * @param mixed $callback Hook callback.
	 * @return string|null
	 */
	private function owner_of( $callback ) {
		$file = self::file_of( $callback );
		if ( '' === $file ) {
			return null;
		}
		$file = wp_normalize_path( $file );
		foreach ( $this->dirs as $folder => $dir ) {
			if ( 0 === strpos( $file, $dir ) ) {
				return $folder;
			}
		}
		return null;
	}

	/**
	 * File that defines a callback, or '' when it cannot be told (built-in functions, bad callbacks).
	 *
	 * @param mixed $callback Hook callback.
	 * @return string
	 */
	private static function file_of( $callback ) {
		try {
			if ( is_string( $callback ) && false !== strpos( $callback, '::' ) ) {
				$callback = explode( '::', $callback, 2 );
			}
			if ( is_array( $callback ) && 2 === count( $callback ) ) {
				$reflection = new ReflectionMethod( $callback[0], (string) $callback[1] );
			} elseif ( $callback instanceof Closure || is_string( $callback ) ) {
				$reflection = new ReflectionFunction( $callback );
			} elseif ( is_object( $callback ) && method_exists( $callback, '__invoke' ) ) {
				$reflection = new ReflectionMethod( $callback, '__invoke' );
			} else {
				return '';
			}
			return (string) $reflection->getFileName();
		} catch ( ReflectionException $e ) {
			return '';
		}
	}

	/**
	 * Readable name of a callback for the log, such as "Some\\Class::method".
	 *
	 * @param mixed $callback Hook callback.
	 * @return string
	 */
	private static function label( $callback ) {
		if ( is_string( $callback ) ) {
			return $callback;
		}
		if ( is_array( $callback ) && 2 === count( $callback ) ) {
			$class = is_object( $callback[0] ) ? get_class( $callback[0] ) : (string) $callback[0];
			return $class . '::' . (string) $callback[1];
		}
		if ( $callback instanceof Closure ) {
			return 'closure';
		}
		return is_object( $callback ) ? get_class( $callback ) : 'callback';
	}
}
