<?php

namespace OM4\MixCheck;

use OM4\WooCommerceMixCheck\Logger as NewLogger;
use OM4\WooCommerceMixCheck\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Legacy Logger, extending 2.0 logger so that Legacy code can use it.
 *
 * @deprecated 2.0.0 Replaced by OM4\WooCommerceMixCheck\Logger
 */
class Logger extends NewLogger {

	/**
	 * Logger constructor.
	 */
	public function __construct() {
		parent::__construct( new Settings() );
		$this->context = array( 'source' => 'woocommerce-zapier-legacy' );
	}
}
