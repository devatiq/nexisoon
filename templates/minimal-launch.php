<?php
/**
 * Minimal Launch template.
 *
 * @package NexiSoon
 */

defined( 'ABSPATH' ) || exit;

$nexisoon_modes      = NexiSoon_Settings::get_modes();
$nexisoon_mode_label = isset( $nexisoon_modes[ $settings['mode'] ] ) ? $nexisoon_modes[ $settings['mode'] ] : __( 'Coming Soon', 'nexisoon' );
?>
<main class="nexisoon-page" role="main">
	<section class="nexisoon-container" aria-labelledby="nexisoon-heading">
		<?php if ( $logo_url ) : ?>
			<div class="nexisoon-logo">
				<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
			</div>
		<?php endif; ?>

		<p class="nexisoon-kicker"><?php echo esc_html( $nexisoon_mode_label ); ?></p>
		<h1 id="nexisoon-heading" class="nexisoon-heading"><?php echo esc_html( $settings['heading'] ); ?></h1>

		<?php if ( $settings['subheading'] ) : ?>
			<p class="nexisoon-subheading"><?php echo esc_html( $settings['subheading'] ); ?></p>
		<?php endif; ?>

		<?php if ( $has_countdown ) : ?>
			<div class="nexisoon-countdown" data-nexisoon-countdown="<?php echo esc_attr( $settings['countdown_datetime'] ); ?>">
				<div class="nexisoon-countdown-item">
					<span class="nexisoon-countdown-value" data-unit="days">00</span>
					<span class="nexisoon-countdown-label"><?php esc_html_e( 'Days', 'nexisoon' ); ?></span>
				</div>
				<div class="nexisoon-countdown-item">
					<span class="nexisoon-countdown-value" data-unit="hours">00</span>
					<span class="nexisoon-countdown-label"><?php esc_html_e( 'Hours', 'nexisoon' ); ?></span>
				</div>
				<div class="nexisoon-countdown-item">
					<span class="nexisoon-countdown-value" data-unit="minutes">00</span>
					<span class="nexisoon-countdown-label"><?php esc_html_e( 'Minutes', 'nexisoon' ); ?></span>
				</div>
				<div class="nexisoon-countdown-item">
					<span class="nexisoon-countdown-value" data-unit="seconds">00</span>
					<span class="nexisoon-countdown-label"><?php esc_html_e( 'Seconds', 'nexisoon' ); ?></span>
				</div>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $settings['button_enabled'] ) && ! empty( $settings['button_url'] ) ) : ?>
			<a class="nexisoon-button" href="<?php echo esc_url( $settings['button_url'] ); ?>"><?php echo esc_html( $settings['button_text'] ); ?></a>
		<?php endif; ?>

		<?php if ( $settings['footer_text'] ) : ?>
			<footer class="nexisoon-footer"><?php echo wp_kses_post( nl2br( esc_html( $settings['footer_text'] ) ) ); ?></footer>
		<?php endif; ?>
	</section>
</main>
