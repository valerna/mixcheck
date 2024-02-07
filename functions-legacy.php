<?php

use OM4\Zapier\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * This function should be used to access the OM4\MixCheck\Plugin singleton class.
 * It's simpler to use this function instead of a global variable.
 *
 * @deprecated 2.0.0 Legacy MixCheck Feeds should be replaced with REST API based Webhooks.
 *
 * @return OM4\Zapier\Plugin
 */
function WC_MixCheck() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	wc_deprecated_function( 'WC_MixCheck', '2.0.0' );
	return Plugin::instance();
}
