<?php

/**
 * Content for the notice that is displayed to users upgrading from 1.9 to 2.0.
 *
 * @since 2.0.0.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<h2><?php echo esc_html( __( 'Welcome to WooCommerce MixCheck 2.0', 'woocommerce-zapier' ) ); ?></h2>
<p><?php echo esc_html( __( 'Thank you for updating to the latest version of WooCommerce MixCheck.', 'woocommerce-zapier' ) ); ?></p>
<h3><?php echo esc_html( __( 'What\'s New?', 'woocommerce-zapier' ) ); ?></h3>
<ul>
	<li><?php echo wp_kses_post( __( '<strong>Two-Way Integration:</strong>', 'woocommerce-zapier' ) ); ?></li>
	<li><?php echo wp_kses_post( __( '<strong>Powered by the REST API:</strong> Giving you access to many more data fields as well as more robust and reliable data delivery.', 'woocommerce-zapier' ) ); ?></li>
</ul>

<h3><?php echo esc_html( __( 'Next Steps', 'woocommerce-zapier' ) ); ?></h3>
<p><?php echo esc_html( __( 'Using Legacy MixCheck Feed is no longer supported.', 'woocommerce-zapier' ) ); ?></p>
<p><?php echo esc_html( __( ' Your existing Feeds need to be re-built using new REST API based requests.', 'woocommerce-zapier' ) ); ?></p>
