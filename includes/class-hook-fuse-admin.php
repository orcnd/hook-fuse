<?php
/**
 * Admin screen under Tools: choose the plugins to protect, read the log, switch the weekly email.
 *
 * @package HookFuse
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The Tools > Hook Fuse page and a small notice on the Dashboard and Plugins screens when new errors
 * were caught. The notice goes away once the page is opened or the notice is dismissed.
 */
final class Hook_Fuse_Admin {

	const PAGE      = 'hook-fuse';
	const SEEN_META = 'hook_fuse_seen';

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
	 * Register the page, the form handlers and the notice.
	 */
	public function register() {
		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_post_hook_fuse_save', array( $this, 'handle_save' ) );
		add_action( 'admin_post_hook_fuse_clear', array( $this, 'handle_clear' ) );
		add_action( 'admin_post_hook_fuse_dismiss', array( $this, 'handle_dismiss' ) );
		add_action( 'admin_notices', array( $this, 'notice' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'styles' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( HOOK_FUSE_FILE ), array( $this, 'action_links' ) );
	}

	/**
	 * Add the Tools page.
	 */
	public function menu() {
		add_management_page(
			__( 'Hook Fuse', 'hook-fuse' ),
			__( 'Hook Fuse', 'hook-fuse' ),
			'manage_options',
			self::PAGE,
			array( $this, 'render' )
		);
	}

	/**
	 * Make the log fit narrow screens: long hook, callback and file names break, and on phones each cell
	 * gets its own line with the column name. Printed on the Hook Fuse page only.
	 *
	 * @param string $hook_suffix Current admin page.
	 */
	public function styles( $hook_suffix ) {
		if ( 'tools_page_' . self::PAGE !== $hook_suffix ) {
			return;
		}
		wp_add_inline_style(
			'common',
			'.hook-fuse-log code { overflow-wrap: anywhere; }
			@media (max-width: 600px) {
				.hook-fuse-log thead { display: none; }
				.hook-fuse-log td { display: block; overflow-wrap: anywhere; }
				.hook-fuse-log td::before { content: attr(data-colname); display: block; font-weight: 600; }
			}'
		);
	}

	/**
	 * "Settings" link on the Plugins screen.
	 *
	 * @param string[] $links Existing links.
	 * @return string[]
	 */
	public function action_links( $links ) {
		$links[] = '<a href="' . esc_url( admin_url( 'tools.php?page=' . self::PAGE ) ) . '">' . esc_html__( 'Settings', 'hook-fuse' ) . '</a>';
		return $links;
	}

	/**
	 * Notice on the Dashboard and Plugins screens only, and only when there is something new.
	 */
	public function notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$screen = get_current_screen();
		if ( ! $screen || ! in_array( $screen->id, array( 'dashboard', 'plugins' ), true ) ) {
			return;
		}
		$new = count( $this->log->since( (int) get_user_meta( get_current_user_id(), self::SEEN_META, true ) ) );
		if ( 0 === $new ) {
			return;
		}
		$view    = admin_url( 'tools.php?page=' . self::PAGE );
		$dismiss = wp_nonce_url( admin_url( 'admin-post.php?action=hook_fuse_dismiss' ), 'hook_fuse_dismiss' );
		printf(
			'<div class="notice notice-warning"><p>%s <a href="%s">%s</a> | <a href="%s">%s</a></p></div>',
			esc_html(
				sprintf(
					/* translators: %d: number of distinct errors. */
					_n(
						'Hook Fuse caught %d new error in a protected plugin. Your pages kept working.',
						'Hook Fuse caught %d new errors in protected plugins. Your pages kept working.',
						$new,
						'hook-fuse'
					),
					$new
				)
			),
			esc_url( $view ),
			esc_html__( 'See what happened', 'hook-fuse' ),
			esc_url( $dismiss ),
			esc_html__( 'Dismiss', 'hook-fuse' )
		);
	}

	/**
	 * The Tools page.
	 */
	public function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		update_user_meta( get_current_user_id(), self::SEEN_META, time() );

		$settings  = Hook_Fuse_Settings::get();
		$protected = array_flip( $settings['protected'] );
		$entries   = $this->log->all();
		$plugins   = $this->other_plugins();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Hook Fuse', 'hook-fuse' ); ?></h1>
			<?php // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display only; set by our own redirect after a nonce-checked save. ?>
			<?php if ( isset( $_GET['updated'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Settings saved.', 'hook-fuse' ); ?></p></div>
			<?php endif; ?>
			<p><?php esc_html_e( 'When a protected plugin throws an error inside one of its hooks, Hook Fuse catches it, logs it here and lets the page finish loading. The plugin itself is not changed.', 'hook-fuse' ); ?></p>

			<h2><?php esc_html_e( 'Caught errors', 'hook-fuse' ); ?></h2>
			<?php if ( empty( $entries ) ) : ?>
				<p><?php esc_html_e( 'Nothing caught yet.', 'hook-fuse' ); ?></p>
			<?php else : ?>
				<table class="widefat striped hook-fuse-log">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Last seen', 'hook-fuse' ); ?></th>
							<th><?php esc_html_e( 'Times', 'hook-fuse' ); ?></th>
							<th><?php esc_html_e( 'Plugin', 'hook-fuse' ); ?></th>
							<th><?php esc_html_e( 'Hook and callback', 'hook-fuse' ); ?></th>
							<th><?php esc_html_e( 'Error', 'hook-fuse' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $entries as $entry ) : ?>
							<tr>
								<td data-colname="<?php esc_attr_e( 'Last seen', 'hook-fuse' ); ?>"><?php echo esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), (int) $entry['last'] ) ); ?></td>
								<td data-colname="<?php esc_attr_e( 'Times', 'hook-fuse' ); ?>"><?php echo esc_html( number_format_i18n( (int) $entry['count'] ) ); ?></td>
								<td data-colname="<?php esc_attr_e( 'Plugin', 'hook-fuse' ); ?>"><?php echo esc_html( $this->plugin_name( $entry['plugin'] ) ); ?></td>
								<td data-colname="<?php esc_attr_e( 'Hook and callback', 'hook-fuse' ); ?>"><code><?php echo esc_html( $entry['hook'] ); ?></code><br><code><?php echo esc_html( $entry['callback'] ); ?></code></td>
								<td data-colname="<?php esc_attr_e( 'Error', 'hook-fuse' ); ?>"><strong><?php echo esc_html( $entry['type'] ); ?></strong>: <?php echo esc_html( $entry['message'] ); ?><br><code><?php echo esc_html( $entry['where'] ); ?></code></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="hook_fuse_clear">
					<?php wp_nonce_field( 'hook_fuse_clear' ); ?>
					<?php submit_button( __( 'Clear the log', 'hook-fuse' ), 'secondary', 'hook-fuse-clear' ); ?>
				</form>
			<?php endif; ?>

			<h2><?php esc_html_e( 'Settings', 'hook-fuse' ); ?></h2>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="hook_fuse_save">
				<?php wp_nonce_field( 'hook_fuse_save' ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Protected plugins', 'hook-fuse' ); ?></th>
						<td>
							<fieldset>
								<?php foreach ( $plugins as $folder => $name ) : ?>
									<label>
										<input type="checkbox" name="hook_fuse_protected[]" value="<?php echo esc_attr( $folder ); ?>" <?php checked( isset( $protected[ $folder ] ) ); ?>>
										<?php echo esc_html( $name ); ?>
									</label><br>
								<?php endforeach; ?>
							</fieldset>
							<p class="description"><?php esc_html_e( 'Protect only plugins that cause trouble. Errors in protected plugins are hidden from visitors, so check this page after updates.', 'hook-fuse' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Weekly email', 'hook-fuse' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="hook_fuse_digest" value="1" <?php checked( $settings['digest'] ); ?>>
								<?php
								echo esc_html(
									sprintf(
										/* translators: %s: the site's admin email address. */
										__( 'Send a summary of new errors once a week to %s', 'hook-fuse' ),
										get_option( 'admin_email' )
									)
								);
								?>
							</label>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Save the settings form.
	 */
	public function handle_save() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'hook-fuse' ), 403 );
		}
		check_admin_referer( 'hook_fuse_save' );
		$folders = isset( $_POST['hook_fuse_protected'] ) ? array_map( 'sanitize_key', (array) wp_unslash( $_POST['hook_fuse_protected'] ) ) : array();
		$folders = array_values( array_intersect( $folders, array_keys( $this->other_plugins() ) ) );
		Hook_Fuse_Settings::save( $folders, ! empty( $_POST['hook_fuse_digest'] ) );
		wp_safe_redirect( admin_url( 'tools.php?page=' . self::PAGE . '&updated=1' ) );
		exit;
	}

	/**
	 * Clear the log.
	 */
	public function handle_clear() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'hook-fuse' ), 403 );
		}
		check_admin_referer( 'hook_fuse_clear' );
		$this->log->clear();
		wp_safe_redirect( admin_url( 'tools.php?page=' . self::PAGE ) );
		exit;
	}

	/**
	 * Hide the notice until something new is caught.
	 */
	public function handle_dismiss() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'hook-fuse' ), 403 );
		}
		check_admin_referer( 'hook_fuse_dismiss' );
		update_user_meta( get_current_user_id(), self::SEEN_META, time() );
		wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url() );
		exit;
	}

	/**
	 * Installed plugins other than Hook Fuse, as folder => name. Single-file plugins have no folder and
	 * are left out.
	 *
	 * @return array<string,string>
	 */
	private function other_plugins() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$list = array();
		foreach ( get_plugins() as $file => $data ) {
			$folder = dirname( $file );
			if ( '.' === $folder || Hook_Fuse_Settings::own_folder() === $folder ) {
				continue;
			}
			$list[ $folder ] = $data['Name'];
		}
		asort( $list );
		return $list;
	}

	/**
	 * Display name of a plugin folder.
	 *
	 * @param string $folder Plugin folder.
	 * @return string
	 */
	private function plugin_name( $folder ) {
		$plugins = $this->other_plugins();
		return isset( $plugins[ $folder ] ) ? $plugins[ $folder ] : $folder;
	}
}
