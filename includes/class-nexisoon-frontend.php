<?php
/**
 * Frontend rendering.
 *
 * @package NexiSoon
 */

defined( 'ABSPATH' ) || exit;

/**
 * NexiSoon frontend controller.
 */
class NexiSoon_Frontend {
	/**
	 * Settings model.
	 *
	 * @var NexiSoon_Settings
	 */
	private $settings;

	/**
	 * Assets controller.
	 *
	 * @var NexiSoon_Assets
	 */
	private $assets;

	/**
	 * Constructor.
	 *
	 * @param NexiSoon_Settings $settings Settings model.
	 * @param NexiSoon_Assets   $assets   Assets controller.
	 */
	public function __construct( $settings, $assets ) {
		$this->settings = $settings;
		$this->assets   = $assets;
	}

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	public function hooks() {
		add_action( 'template_redirect', array( $this, 'maybe_render_page' ), 0 );
	}

	/**
	 * Render the NexiSoon page when enabled and the request is not bypassed.
	 *
	 * @return void
	 */
	public function maybe_render_page() {
		$settings = $this->settings->get();

		if ( ! $this->should_render( $settings ) ) {
			return;
		}

		$this->send_headers( $settings );
		$this->render_page( $settings );
		exit;
	}

	/**
	 * Decide whether to replace the current frontend request.
	 *
	 * @param array $settings Current settings.
	 * @return bool
	 */
	private function should_render( $settings ) {
		if ( empty( $settings['enabled'] ) ) {
			return false;
		}

		if ( is_admin() || wp_doing_ajax() || wp_doing_cron() || nexisoon_is_rest_request() ) {
			return false;
		}

		if ( $this->is_login_request() ) {
			return false;
		}

		if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Determine if the current request is for a WordPress login/register page.
	 *
	 * @return bool
	 */
	private function is_login_request() {
		global $pagenow;

		if ( in_array( $pagenow, array( 'wp-login.php', 'wp-register.php' ), true ) ) {
			return true;
		}

		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

		return false !== strpos( $request_uri, 'wp-login.php' );
	}

	/**
	 * Send the correct status and cache headers.
	 *
	 * @param array $settings Current settings.
	 * @return void
	 */
	private function send_headers( $settings ) {
		if ( headers_sent() ) {
			return;
		}

		if ( 'maintenance' === $settings['mode'] ) {
			status_header( 503 );
			header( 'Retry-After: 3600' );
		} else {
			status_header( 200 );
		}

		nocache_headers();
		header( 'Content-Type: text/html; charset=' . get_bloginfo( 'charset' ) );
	}

	/**
	 * Register and enqueue frontend assets for the standalone NexiSoon page.
	 *
	 * @param bool $has_countdown Whether the countdown script is needed.
	 * @return void
	 */
	private function enqueue_frontend_assets( $has_countdown ) {
		wp_enqueue_style(
			'nexisoon-frontend',
			$this->assets->get_frontend_style_url(),
			array(),
			NEXISOON_VERSION
		);

		if ( $has_countdown ) {
			wp_enqueue_script(
				'nexisoon-countdown',
				$this->assets->get_countdown_script_url(),
				array(),
				NEXISOON_VERSION,
				true
			);
		}
	}

	/**
	 * Render the complete NexiSoon HTML document.
	 *
	 * @param array $settings Current settings.
	 * @return void
	 */
	private function render_page( $settings ) {
		$template_path   = nexisoon_get_template_path( $settings['template'] );
		$logo_url        = nexisoon_get_attachment_url( $settings['logo_id'] );
		$favicon_url     = nexisoon_get_attachment_url( $settings['favicon_id'] );
		$seo_title       = $settings['seo_title'] ? $settings['seo_title'] : $settings['heading'];
		$seo_description = $settings['seo_description'] ? $settings['seo_description'] : $settings['subheading'];
		$is_maintenance = 'maintenance' === $settings['mode'];
		$has_countdown  = ! empty( $settings['countdown_enabled'] ) && ! empty( $settings['countdown_datetime'] );

		$this->enqueue_frontend_assets( $has_countdown );

		?>
		<!doctype html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta charset="<?php bloginfo( 'charset' ); ?>">
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<title><?php echo esc_html( $seo_title ); ?></title>
			<?php if ( $seo_description ) : ?>
				<meta name="description" content="<?php echo esc_attr( $seo_description ); ?>">
			<?php endif; ?>
			<?php if ( $is_maintenance ) : ?>
				<meta name="robots" content="noindex, nofollow">
			<?php endif; ?>
			<?php if ( $favicon_url ) : ?>
				<link rel="icon" href="<?php echo esc_url( $favicon_url ); ?>">
			<?php endif; ?>
			<?php wp_print_styles( array( 'nexisoon-frontend' ) ); ?>
			<style>
				:root {
					--nexisoon-background: <?php echo esc_html( $settings['background_color'] ); ?>;
					--nexisoon-text: <?php echo esc_html( $settings['text_color'] ); ?>;
					--nexisoon-button: <?php echo esc_html( $settings['button_color'] ); ?>;
				}
			</style>
			<?php do_action( 'nexisoon_head' ); ?>
		</head>
		<body class="nexisoon-body nexisoon-template-<?php echo esc_attr( $settings['template'] ); ?>">
			<?php include $template_path; ?>
			<?php wp_print_footer_scripts(); ?>
			<?php do_action( 'nexisoon_footer' ); ?>
		</body>
		</html>
		<?php
	}
}
