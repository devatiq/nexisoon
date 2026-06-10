<?php
/**
 * Admin settings screen and AJAX saving.
 *
 * @package NexiSoon
 */

defined( 'ABSPATH' ) || exit;

/**
 * NexiSoon admin controller.
 */
class NexiSoon_Admin {
	/**
	 * Settings model.
	 *
	 * @var NexiSoon_Settings
	 */
	private $settings;

	/**
	 * Constructor.
	 *
	 * @param NexiSoon_Settings $settings Settings model.
	 */
	public function __construct( $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	public function hooks() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'wp_ajax_nexisoon_save_settings', array( $this, 'ajax_save_settings' ) );
		add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_active_notice' ), 100 );
		add_action( 'admin_head', array( $this, 'render_admin_bar_styles' ) );
		add_action( 'wp_head', array( $this, 'render_admin_bar_styles' ) );
		add_filter( 'plugin_action_links_' . NEXISOON_PLUGIN_BASENAME, array( $this, 'add_plugin_action_links' ) );
	}

	/**
	 * Add dedicated NexiSoon admin menu and submenu structure.
	 *
	 * @return void
	 */
	public function add_admin_menu() {
		add_menu_page(
			__( 'NexiSoon', 'nexisoon' ),
			__( 'NexiSoon', 'nexisoon' ),
			'manage_options',
			'nexisoon',
			array( $this, 'render_settings_page' ),
			'dashicons-visibility',
			81
		);

		add_submenu_page(
			'nexisoon',
			__( 'NexiSoon Dashboard', 'nexisoon' ),
			__( 'Dashboard', 'nexisoon' ),
			'manage_options',
			'nexisoon',
			array( $this, 'render_settings_page' )
		);

		add_submenu_page(
			'nexisoon',
			__( 'NexiSoon Settings', 'nexisoon' ),
			__( 'Settings', 'nexisoon' ),
			'manage_options',
			'nexisoon-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Add action links to the Plugins list.
	 *
	 * @param array $links Existing action links.
	 * @return array
	 */
	public function add_plugin_action_links( $links ) {
		$action_links = array(
			'settings' => sprintf(
				'<a href="%1$s">%2$s</a>',
				esc_url( admin_url( 'admin.php?page=nexisoon' ) ),
				esc_html__( 'Settings', 'nexisoon' )
			),
		);

		return array_merge( $action_links, $links );
	}

	/**
	 * Add an active status item to the WordPress admin bar.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar Admin bar instance.
	 * @return void
	 */
	public function add_admin_bar_active_notice( $wp_admin_bar ) {
		if ( ! $this->should_show_admin_bar_notice() ) {
			return;
		}

		$settings = $this->settings->get();
		$title    = $this->get_admin_bar_notice_title( $settings['mode'] );

		$wp_admin_bar->add_node(
			array(
				'id'    => 'nexisoon-active-notice',
				'title' => esc_html( $title ),
				'href'  => esc_url( admin_url( 'admin.php?page=nexisoon' ) ),
				'meta'  => array(
					'class' => 'nexisoon-admin-bar-' . sanitize_html_class( $settings['mode'] ),
				),
			)
		);
	}

	/**
	 * Render scoped admin bar styles for the NexiSoon active notice.
	 *
	 * @return void
	 */
	public function render_admin_bar_styles() {
		if ( ! $this->should_show_admin_bar_notice() ) {
			return;
		}

		$settings = $this->settings->get();
		$color    = 'maintenance' === $settings['mode'] ? '#d63638' : '#2271b1';
		$hover    = 'maintenance' === $settings['mode'] ? '#b32d2e' : '#135e96';
		?>
		<style id="nexisoon-admin-bar-styles">
			#wp-admin-bar-nexisoon-active-notice > .ab-item {
				background: <?php echo esc_html( $color ); ?> !important;
				color: #fff !important;
				font-weight: 700 !important;
			}
			#wp-admin-bar-nexisoon-active-notice:hover > .ab-item,
			#wp-admin-bar-nexisoon-active-notice > .ab-item:focus {
				background: <?php echo esc_html( $hover ); ?> !important;
				color: #fff !important;
			}
		</style>
		<?php
	}

	/**
	 * Determine if the active notice should appear in the admin bar.
	 *
	 * @return bool
	 */
	private function should_show_admin_bar_notice() {
		if ( ! is_admin_bar_showing() || ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		$settings = $this->settings->get();

		return ! empty( $settings['enabled'] );
	}

	/**
	 * Return mode-specific admin bar notice text.
	 *
	 * @param string $mode Selected mode.
	 * @return string
	 */
	private function get_admin_bar_notice_title( $mode ) {
		$titles = array(
			'coming-soon'        => __( 'NexiSoon: Coming Soon Active', 'nexisoon' ),
			'under-construction' => __( 'NexiSoon: Under Construction Active', 'nexisoon' ),
			'maintenance'        => __( 'NexiSoon: Maintenance Active', 'nexisoon' ),
		);

		return isset( $titles[ $mode ] ) ? $titles[ $mode ] : $titles['coming-soon'];
	}

	/**
	 * Return supported settings tabs.
	 *
	 * @return array
	 */
	private function get_tabs() {
		return array(
			'general'   => __( 'General', 'nexisoon' ),
			'template'  => __( 'Template', 'nexisoon' ),
			'branding'  => __( 'Branding', 'nexisoon' ),
			'content'   => __( 'Content', 'nexisoon' ),
			'countdown' => __( 'Countdown', 'nexisoon' ),
			'seo'       => __( 'SEO', 'nexisoon' ),
			'styling'   => __( 'Styling', 'nexisoon' ),
			'preview'   => __( 'Preview', 'nexisoon' ),
		);
	}

	/**
	 * Get the active tab from the query string.
	 *
	 * @return string
	 */
	private function get_current_tab() {
		$tabs = $this->get_tabs();
		$tab  = 'general';

		if ( isset( $_GET['tab'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$tab = sanitize_key( wp_unslash( $_GET['tab'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		return array_key_exists( $tab, $tabs ) ? $tab : 'general';
	}

	/**
	 * Render the settings page.
	 *
	 * @return void
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'nexisoon' ) );
		}

		$settings      = $this->settings->get();
		$modes         = NexiSoon_Settings::get_modes();
		$templates     = NexiSoon_Settings::get_templates();
		$logo_url      = nexisoon_get_attachment_url( $settings['logo_id'] );
		$favicon_url   = nexisoon_get_attachment_url( $settings['favicon_id'] );
		$current_tab   = $this->get_current_tab();
		$status_class  = ! empty( $settings['enabled'] ) ? 'is-active' : 'is-disabled';
		$status_label  = ! empty( $settings['enabled'] ) ? __( 'Active', 'nexisoon' ) : __( 'Disabled', 'nexisoon' );
		$preview_url   = home_url( '/' );
		$preview_attrs = array(
			'settings' => $settings,
			'logo_url' => $logo_url,
		);
		?>
		<div class="wrap nexisoon-wrap">
			<div class="nexisoon-page-header">
				<div class="nexisoon-brand-mark" aria-hidden="true">S</div>
				<div>
					<h1><?php esc_html_e( 'NexiSoon', 'nexisoon' ); ?></h1>
					<p><?php esc_html_e( 'Create a temporary frontend page while your real website stays available to administrators.', 'nexisoon' ); ?></p>
				</div>
				<span id="nexisoon-status-badge" class="nexisoon-status-badge <?php echo esc_attr( $status_class ); ?>"><?php echo esc_html( $status_label ); ?></span>
			</div>

			<div id="nexisoon-toast" class="nexisoon-toast" role="status" aria-live="polite"></div>

			<?php $this->render_tab_navigation( $current_tab ); ?>

			<form id="nexisoon-settings-form" class="nexisoon-settings-form" method="post" action="#">
				<input type="hidden" name="nexisoon_nonce" value="<?php echo esc_attr( wp_create_nonce( 'nexisoon_save_settings' ) ); ?>" />

				<div class="nexisoon-tab-shell">
					<?php $this->render_tab_panel( 'general', $current_tab, array( $this, 'render_general_tab' ), compact( 'settings', 'modes' ) ); ?>
					<?php $this->render_tab_panel( 'template', $current_tab, array( $this, 'render_template_tab' ), compact( 'settings', 'templates' ) ); ?>
					<?php $this->render_tab_panel( 'branding', $current_tab, array( $this, 'render_branding_tab' ), compact( 'settings', 'logo_url', 'favicon_url' ) ); ?>
					<?php $this->render_tab_panel( 'content', $current_tab, array( $this, 'render_content_tab' ), compact( 'settings' ) ); ?>
					<?php $this->render_tab_panel( 'countdown', $current_tab, array( $this, 'render_countdown_tab' ), compact( 'settings' ) ); ?>
					<?php $this->render_tab_panel( 'seo', $current_tab, array( $this, 'render_seo_tab' ), compact( 'settings' ) ); ?>
					<?php $this->render_tab_panel( 'styling', $current_tab, array( $this, 'render_styling_tab' ), compact( 'settings' ) ); ?>
					<?php $this->render_tab_panel( 'preview', $current_tab, array( $this, 'render_preview_tab' ), array_merge( $preview_attrs, array( 'preview_url' => $preview_url ) ) ); ?>
				</div>

				<div class="nexisoon-save-bar">
					<div class="nexisoon-save-actions">
						<span class="spinner" id="nexisoon-save-spinner"></span>
						<button type="submit" class="button button-primary button-large" id="nexisoon-save-button"><?php esc_html_e( 'Save Settings', 'nexisoon' ); ?></button>
					</div>
				</div>
			</form>
		</div>
		<?php
	}

	/**
	 * Render tab navigation.
	 *
	 * @param string $current_tab Active tab.
	 * @return void
	 */
	private function render_tab_navigation( $current_tab ) {
		$tabs = $this->get_tabs();
		?>
		<nav class="nav-tab-wrapper nexisoon-tabs" aria-label="<?php esc_attr_e( 'NexiSoon settings tabs', 'nexisoon' ); ?>">
			<?php foreach ( $tabs as $tab => $label ) : ?>
				<a class="nav-tab <?php echo $current_tab === $tab ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( array( 'page' => 'nexisoon', 'tab' => $tab ), admin_url( 'admin.php' ) ) ); ?>" data-nexisoon-tab-link="<?php echo esc_attr( $tab ); ?>">
					<?php echo esc_html( $label ); ?>
				</a>
			<?php endforeach; ?>
		</nav>
		<?php
	}

	/**
	 * Render a tab panel wrapper.
	 *
	 * @param string   $tab         Tab slug.
	 * @param string   $current_tab Active tab.
	 * @param callable $callback    Render callback.
	 * @param array    $args        Callback arguments.
	 * @return void
	 */
	private function render_tab_panel( $tab, $current_tab, $callback, $args ) {
		$is_active = $current_tab === $tab;
		?>
		<section class="nexisoon-tab-panel <?php echo $is_active ? 'is-active' : ''; ?>" id="nexisoon-tab-<?php echo esc_attr( $tab ); ?>" data-nexisoon-tab-panel="<?php echo esc_attr( $tab ); ?>" <?php echo $is_active ? '' : 'hidden'; ?>>
			<?php call_user_func( $callback, $args ); ?>
		</section>
		<?php
	}

	/**
	 * Render General tab.
	 *
	 * @param array $args Tab args.
	 * @return void
	 */
	private function render_general_tab( $args ) {
		$settings = $args['settings'];
		$modes    = $args['modes'];
		?>
		<div class="nexisoon-card nexisoon-card-hero">
			<div>
				<p class="nexisoon-eyebrow"><?php esc_html_e( 'General', 'nexisoon' ); ?></p>
				<h2><?php esc_html_e( 'Control your site visibility', 'nexisoon' ); ?></h2>
				<p><?php esc_html_e( 'Turn NexiSoon on when public visitors should see your temporary page instead of the live website.', 'nexisoon' ); ?></p>
			</div>
			<span class="nexisoon-status-badge <?php echo ! empty( $settings['enabled'] ) ? 'is-active' : 'is-disabled'; ?>" data-nexisoon-status-preview><?php echo ! empty( $settings['enabled'] ) ? esc_html__( 'Active', 'nexisoon' ) : esc_html__( 'Disabled', 'nexisoon' ); ?></span>
		</div>

		<div class="nexisoon-card">
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row"><?php esc_html_e( 'Enable NexiSoon', 'nexisoon' ); ?></th>
						<td>
							<label class="nexisoon-switch">
								<input type="checkbox" name="nexisoon_settings[enabled]" value="1" <?php checked( 1, $settings['enabled'] ); ?> data-nexisoon-enabled-toggle />
								<span class="nexisoon-switch-ui" aria-hidden="true"></span>
								<span><?php esc_html_e( 'Show the NexiSoon page to public visitors.', 'nexisoon' ); ?></span>
							</label>
							<p class="description"><?php esc_html_e( 'When disabled, NexiSoon does not affect the frontend.', 'nexisoon' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="nexisoon_mode"><?php esc_html_e( 'Mode type', 'nexisoon' ); ?></label></th>
						<td>
							<?php $this->render_select( 'mode', 'nexisoon_mode', $modes, $settings['mode'] ); ?>
							<p class="description"><?php esc_html_e( 'Coming Soon and Under Construction return HTTP 200. Maintenance Mode returns HTTP 503.', 'nexisoon' ); ?></p>
						</td>
					</tr>
				</tbody>
			</table>
		</div>

		<div class="nexisoon-notice-card">
			<span class="dashicons dashicons-shield" aria-hidden="true"></span>
			<div>
				<strong><?php esc_html_e( 'Administrator bypass is always enabled.', 'nexisoon' ); ?></strong>
				<p><?php esc_html_e( 'Logged-in administrators with the manage_options capability continue to see the real website while public visitors see NexiSoon.', 'nexisoon' ); ?></p>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Template tab.
	 *
	 * @param array $args Tab args.
	 * @return void
	 */
	private function render_template_tab( $args ) {
		$settings  = $args['settings'];
		$templates = $args['templates'];
		?>
		<div class="nexisoon-card nexisoon-template-picker-card">
			<p class="nexisoon-eyebrow"><?php esc_html_e( 'Template', 'nexisoon' ); ?></p>
			<h2><?php esc_html_e( 'Select a template from the previews', 'nexisoon' ); ?></h2>
			<p><?php esc_html_e( 'Choose one of the three bundled layouts. Click a preview card to make it the active NexiSoon template.', 'nexisoon' ); ?></p>

			<div class="nexisoon-template-grid" role="radiogroup" aria-label="<?php esc_attr_e( 'Template selector', 'nexisoon' ); ?>">
				<?php foreach ( $templates as $value => $label ) : ?>
					<?php $is_selected = $settings['template'] === $value; ?>
					<label class="nexisoon-template-card <?php echo $is_selected ? 'is-selected' : ''; ?>" for="nexisoon_template_<?php echo esc_attr( $value ); ?>" data-template-card="<?php echo esc_attr( $value ); ?>" role="radio" aria-checked="<?php echo $is_selected ? 'true' : 'false'; ?>" tabindex="0">
						<input class="nexisoon-template-input" type="radio" id="nexisoon_template_<?php echo esc_attr( $value ); ?>" name="nexisoon_settings[template]" value="<?php echo esc_attr( $value ); ?>" <?php checked( $is_selected ); ?> />
						<span class="nexisoon-template-selected-indicator" aria-hidden="true">
							<span class="dashicons dashicons-yes"></span>
						</span>
						<span class="nexisoon-template-preview nexisoon-template-preview-<?php echo esc_attr( $value ); ?>" aria-hidden="true">
							<span class="nexisoon-preview-browser-bar"></span>
							<span class="nexisoon-preview-logo-dot"></span>
							<span class="nexisoon-preview-heading-line"></span>
							<span class="nexisoon-preview-text-line"></span>
							<span class="nexisoon-preview-countdown-row"><i></i><i></i><i></i><i></i></span>
							<span class="nexisoon-preview-button-pill"></span>
						</span>
						<span class="nexisoon-template-card-body">
							<strong><?php echo esc_html( $label ); ?></strong>
							<small><?php echo esc_html( $this->get_template_description( $value ) ); ?></small>
							<span class="nexisoon-template-choose"><?php echo $is_selected ? esc_html__( 'Selected', 'nexisoon' ) : esc_html__( 'Select template', 'nexisoon' ); ?></span>
						</span>
					</label>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Branding tab.
	 *
	 * @param array $args Tab args.
	 * @return void
	 */
	private function render_branding_tab( $args ) {
		$settings    = $args['settings'];
		$logo_url    = $args['logo_url'];
		$favicon_url = $args['favicon_url'];
		?>
		<div class="nexisoon-card">
			<p class="nexisoon-eyebrow"><?php esc_html_e( 'Branding', 'nexisoon' ); ?></p>
			<h2><?php esc_html_e( 'Add your visual identity', 'nexisoon' ); ?></h2>
			<p><?php esc_html_e( 'Use media from your WordPress library. Uploaded files remain in the media library if NexiSoon is deleted.', 'nexisoon' ); ?></p>
			<table class="form-table" role="presentation">
				<tbody>
					<?php
					$this->render_media_field(
						__( 'Logo', 'nexisoon' ),
						'logo_id',
						'nexisoon_logo_id',
						$settings['logo_id'],
						$logo_url,
						__( 'Select Logo', 'nexisoon' ),
						__( 'Remove Logo', 'nexisoon' ),
						__( 'Displayed above the main heading.', 'nexisoon' )
					);
					$this->render_media_field(
						__( 'Favicon', 'nexisoon' ),
						'favicon_id',
						'nexisoon_favicon_id',
						$settings['favicon_id'],
						$favicon_url,
						__( 'Select Favicon', 'nexisoon' ),
						__( 'Remove Favicon', 'nexisoon' ),
						__( 'Used as the browser tab icon on the NexiSoon page.', 'nexisoon' )
					);
					?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Render Content tab.
	 *
	 * @param array $args Tab args.
	 * @return void
	 */
	private function render_content_tab( $args ) {
		$settings = $args['settings'];
		?>
		<div class="nexisoon-card">
			<p class="nexisoon-eyebrow"><?php esc_html_e( 'Content', 'nexisoon' ); ?></p>
			<h2><?php esc_html_e( 'Write your visitor message', 'nexisoon' ); ?></h2>
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row"><label for="nexisoon_heading"><?php esc_html_e( 'Heading text', 'nexisoon' ); ?></label></th>
						<td><input type="text" class="regular-text" id="nexisoon_heading" name="nexisoon_settings[heading]" value="<?php echo esc_attr( $settings['heading'] ); ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="nexisoon_subheading"><?php esc_html_e( 'Subheading text', 'nexisoon' ); ?></label></th>
						<td><textarea class="large-text" rows="4" id="nexisoon_subheading" name="nexisoon_settings[subheading]"><?php echo esc_textarea( $settings['subheading'] ); ?></textarea></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Button', 'nexisoon' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="nexisoon_settings[button_enabled]" value="1" <?php checked( 1, $settings['button_enabled'] ); ?> />
								<?php esc_html_e( 'Enable button', 'nexisoon' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="nexisoon_button_text"><?php esc_html_e( 'Button text', 'nexisoon' ); ?></label></th>
						<td><input type="text" class="regular-text" id="nexisoon_button_text" name="nexisoon_settings[button_text]" value="<?php echo esc_attr( $settings['button_text'] ); ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="nexisoon_button_url"><?php esc_html_e( 'Button URL', 'nexisoon' ); ?></label></th>
						<td><input type="url" class="regular-text" id="nexisoon_button_url" name="nexisoon_settings[button_url]" value="<?php echo esc_url( $settings['button_url'] ); ?>" placeholder="https://example.com" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="nexisoon_footer_text"><?php esc_html_e( 'Footer text', 'nexisoon' ); ?></label></th>
						<td><textarea class="large-text" rows="3" id="nexisoon_footer_text" name="nexisoon_settings[footer_text]"><?php echo esc_textarea( $settings['footer_text'] ); ?></textarea></td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Render Countdown tab.
	 *
	 * @param array $args Tab args.
	 * @return void
	 */
	private function render_countdown_tab( $args ) {
		$settings = $args['settings'];
		$style    = isset( $settings['countdown_display_style'] ) ? $settings['countdown_display_style'] : 'boxes';
		?>
		<div class="nexisoon-card">
			<p class="nexisoon-eyebrow"><?php esc_html_e( 'Countdown', 'nexisoon' ); ?></p>
			<h2><?php esc_html_e( 'Build launch anticipation', 'nexisoon' ); ?></h2>
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row"><?php esc_html_e( 'Countdown timer', 'nexisoon' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="nexisoon_settings[countdown_enabled]" value="1" <?php checked( 1, $settings['countdown_enabled'] ); ?> />
								<?php esc_html_e( 'Enable countdown timer', 'nexisoon' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="nexisoon_countdown_datetime"><?php esc_html_e( 'Countdown date/time', 'nexisoon' ); ?></label></th>
						<td><input type="datetime-local" id="nexisoon_countdown_datetime" name="nexisoon_settings[countdown_datetime]" value="<?php echo esc_attr( $settings['countdown_datetime'] ); ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Countdown display style', 'nexisoon' ); ?></th>
						<td>
							<label><input type="radio" name="nexisoon_settings[countdown_display_style]" value="boxes" <?php checked( $style, 'boxes' ); ?> /> <?php esc_html_e( 'Boxes', 'nexisoon' ); ?></label><br />
							<label><input type="radio" name="nexisoon_settings[countdown_display_style]" value="inline" <?php checked( $style, 'inline' ); ?> /> <?php esc_html_e( 'Inline', 'nexisoon' ); ?></label>
							<p class="description"><?php esc_html_e( 'This setting currently updates the admin preview and prepares the option for future template controls.', 'nexisoon' ); ?></p>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Render SEO tab.
	 *
	 * @param array $args Tab args.
	 * @return void
	 */
	private function render_seo_tab( $args ) {
		$settings = $args['settings'];
		?>
		<div class="nexisoon-card">
			<p class="nexisoon-eyebrow"><?php esc_html_e( 'SEO', 'nexisoon' ); ?></p>
			<h2><?php esc_html_e( 'Control temporary page metadata', 'nexisoon' ); ?></h2>
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row"><label for="nexisoon_seo_title"><?php esc_html_e( 'SEO meta title', 'nexisoon' ); ?></label></th>
						<td><input type="text" class="regular-text" id="nexisoon_seo_title" name="nexisoon_settings[seo_title]" value="<?php echo esc_attr( $settings['seo_title'] ); ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="nexisoon_seo_description"><?php esc_html_e( 'SEO meta description', 'nexisoon' ); ?></label></th>
						<td><textarea class="large-text" rows="4" id="nexisoon_seo_description" name="nexisoon_settings[seo_description]"><?php echo esc_textarea( $settings['seo_description'] ); ?></textarea></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="nexisoon-notice-card">
			<span class="dashicons dashicons-search" aria-hidden="true"></span>
			<div>
				<strong><?php esc_html_e( 'Robots behavior', 'nexisoon' ); ?></strong>
				<p><?php esc_html_e( 'Coming Soon and Under Construction can be indexed normally. Maintenance Mode automatically adds noindex, nofollow to protect temporary outage pages from indexing.', 'nexisoon' ); ?></p>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Styling tab.
	 *
	 * @param array $args Tab args.
	 * @return void
	 */
	private function render_styling_tab( $args ) {
		$settings = $args['settings'];
		?>
		<div class="nexisoon-card">
			<p class="nexisoon-eyebrow"><?php esc_html_e( 'Styling', 'nexisoon' ); ?></p>
			<h2><?php esc_html_e( 'Match your brand colors', 'nexisoon' ); ?></h2>
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row"><label for="nexisoon_background_color"><?php esc_html_e( 'Background color', 'nexisoon' ); ?></label></th>
						<td><input type="text" class="nexisoon-color-field" id="nexisoon_background_color" name="nexisoon_settings[background_color]" value="<?php echo esc_attr( $settings['background_color'] ); ?>" data-default-color="#111827" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="nexisoon_text_color"><?php esc_html_e( 'Text color', 'nexisoon' ); ?></label></th>
						<td><input type="text" class="nexisoon-color-field" id="nexisoon_text_color" name="nexisoon_settings[text_color]" value="<?php echo esc_attr( $settings['text_color'] ); ?>" data-default-color="#ffffff" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="nexisoon_button_color"><?php esc_html_e( 'Button color', 'nexisoon' ); ?></label></th>
						<td><input type="text" class="nexisoon-color-field" id="nexisoon_button_color" name="nexisoon_settings[button_color]" value="<?php echo esc_attr( $settings['button_color'] ); ?>" data-default-color="#2563eb" /></td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Render Preview tab.
	 *
	 * @param array $args Tab args.
	 * @return void
	 */
	private function render_preview_tab( $args ) {
		$settings    = $args['settings'];
		$logo_url    = $args['logo_url'];
		$preview_url = $args['preview_url'];
		?>
		<div class="nexisoon-card nexisoon-preview-tab-card">
			<div class="nexisoon-preview-header">
				<div>
					<p class="nexisoon-eyebrow"><?php esc_html_e( 'Preview', 'nexisoon' ); ?></p>
					<h2><?php esc_html_e( 'Live admin preview', 'nexisoon' ); ?></h2>
					<p><?php esc_html_e( 'This preview updates as you change fields across the tabs.', 'nexisoon' ); ?></p>
				</div>
				<a class="button" href="<?php echo esc_url( $preview_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Preview NexiSoon Page', 'nexisoon' ); ?></a>
			</div>
			<?php $this->render_live_preview( $settings, $logo_url, true ); ?>
		</div>
		<?php
	}

	/**
	 * Save settings over AJAX.
	 *
	 * @return void
	 */
	public function ajax_save_settings() {
		if ( ! check_ajax_referer( 'nexisoon_save_settings', 'nexisoon_nonce', false ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Security check failed. Please refresh the page and try again.', 'nexisoon' ),
				),
				403
			);
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'You do not have permission to save these settings.', 'nexisoon' ),
				),
				403
			);
		}

		$raw_settings    = array();
		$posted_settings = array();

		if ( isset( $_POST['nexisoon_settings'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized by NexiSoon_Settings::sanitize().
			$posted_settings = wp_unslash( $_POST['nexisoon_settings'] );
		}

		if ( is_array( $posted_settings ) ) {
			$raw_settings = $posted_settings;
		}

		$saved       = $this->settings->save( $raw_settings );
		$logo_url    = nexisoon_get_attachment_url( $saved['logo_id'] );
		$favicon_url = nexisoon_get_attachment_url( $saved['favicon_id'] );

		wp_send_json_success(
			array(
				'message'     => __( 'Settings saved.', 'nexisoon' ),
				'settings'    => $saved,
				'logo_url'    => $logo_url,
				'favicon_url' => $favicon_url,
			)
		);
	}

	/**
	 * Render a select field.
	 *
	 * @param string $name    Setting key.
	 * @param string $id      Field ID.
	 * @param array  $options Select options.
	 * @param string $current Current value.
	 * @return void
	 */
	private function render_select( $name, $id, $options, $current ) {
		?>
		<select id="<?php echo esc_attr( $id ); ?>" name="nexisoon_settings[<?php echo esc_attr( $name ); ?>]">
			<?php foreach ( $options as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $current, $value ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Render a media uploader field.
	 *
	 * @param string $label         Field label.
	 * @param string $name          Setting key.
	 * @param string $id            Field ID.
	 * @param int    $value         Attachment ID.
	 * @param string $url           Attachment URL.
	 * @param string $select_label  Select button text.
	 * @param string $remove_label  Remove button text.
	 * @param string $description   Field description.
	 * @return void
	 */
	private function render_media_field( $label, $name, $id, $value, $url, $select_label, $remove_label, $description = '' ) {
		$preview_id = $id . '_preview';
		?>
		<tr>
			<th scope="row"><label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label></th>
			<td>
				<div class="nexisoon-media-control">
					<input type="hidden" id="<?php echo esc_attr( $id ); ?>" name="nexisoon_settings[<?php echo esc_attr( $name ); ?>]" value="<?php echo esc_attr( absint( $value ) ); ?>" />
					<div class="nexisoon-media-preview" id="<?php echo esc_attr( $preview_id ); ?>">
						<?php if ( $url ) : ?>
							<img src="<?php echo esc_url( $url ); ?>" alt="<?php echo esc_attr( $label ); ?>" />
						<?php else : ?>
							<span><?php esc_html_e( 'Not selected', 'nexisoon' ); ?></span>
						<?php endif; ?>
					</div>
					<div>
						<div class="nexisoon-media-row">
							<button type="button" class="button nexisoon-media-upload" data-target="<?php echo esc_attr( $id ); ?>" data-preview="<?php echo esc_attr( $preview_id ); ?>" data-kind="<?php echo esc_attr( $name ); ?>">
								<?php echo esc_html( $select_label ); ?>
							</button>
							<button type="button" class="button nexisoon-media-remove" data-target="<?php echo esc_attr( $id ); ?>" data-preview="<?php echo esc_attr( $preview_id ); ?>">
								<?php echo esc_html( $remove_label ); ?>
							</button>
						</div>
						<?php if ( $description ) : ?>
							<p class="description"><?php echo esc_html( $description ); ?></p>
						<?php endif; ?>
					</div>
				</div>
			</td>
		</tr>
		<?php
	}

	/**
	 * Return a short template card description.
	 *
	 * @param string $template Template slug.
	 * @return string
	 */
	private function get_template_description( $template ) {
		$descriptions = array(
			'classic-centered' => __( 'Balanced centered layout with a polished card.', 'nexisoon' ),
			'minimal-launch'   => __( 'Clean editorial layout with left-aligned content.', 'nexisoon' ),
			'bold-countdown'   => __( 'High-impact layout built around the countdown.', 'nexisoon' ),
		);

		return isset( $descriptions[ $template ] ) ? $descriptions[ $template ] : '';
	}

	/**
	 * Render reusable admin live preview markup.
	 *
	 * @param array  $settings Current settings.
	 * @param string $logo_url Logo URL.
	 * @param bool   $large    Whether to render the large preview variant.
	 * @return void
	 */
	private function render_live_preview( $settings, $logo_url, $large = false ) {
		$classes = 'nexisoon-admin-preview';

		if ( $large ) {
			$classes .= ' is-large';
		}
		?>
		<div class="<?php echo esc_attr( $classes ); ?>" data-preview-template="<?php echo esc_attr( $settings['template'] ); ?>" style="background-color: <?php echo esc_attr( $settings['background_color'] ); ?>; color: <?php echo esc_attr( $settings['text_color'] ); ?>;">
			<div class="nexisoon-preview-logo">
				<?php if ( $logo_url ) : ?>
					<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php esc_attr_e( 'Selected logo', 'nexisoon' ); ?>" />
				<?php else : ?>
					<span><?php esc_html_e( 'Logo', 'nexisoon' ); ?></span>
				<?php endif; ?>
			</div>
			<p class="nexisoon-preview-kicker"><?php esc_html_e( 'NexiSoon Preview', 'nexisoon' ); ?></p>
			<h3 data-preview-heading><?php echo esc_html( $settings['heading'] ); ?></h3>
			<p data-preview-subheading><?php echo esc_html( $settings['subheading'] ); ?></p>
			<div class="nexisoon-preview-countdown <?php echo ( isset( $settings['countdown_display_style'] ) && 'inline' === $settings['countdown_display_style'] ) ? 'is-inline' : ''; ?>" data-preview-countdown><?php esc_html_e( '00 days 00 hours 00 minutes', 'nexisoon' ); ?></div>
			<a class="nexisoon-preview-button<?php echo $settings['button_enabled'] ? '' : ' is-hidden'; ?>" href="#" style="background-color: <?php echo esc_attr( $settings['button_color'] ); ?>;" data-preview-button>
				<?php echo esc_html( $settings['button_text'] ); ?>
			</a>
			<footer data-preview-footer><?php echo esc_html( $settings['footer_text'] ); ?></footer>
		</div>
		<?php
	}
}
