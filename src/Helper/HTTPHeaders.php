<?php

namespace OM4\WooCommerceMixCheck\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * HTTP Header Helper.
 *
 * @since 2.0.6
 */
class HTTPHeaders {

	/**
	 * Get the HTTP Headers that WooCommerce MixCheck needs to send during all trigger/webhook requests
	 * and REST API Controller responses.
	 *
	 * X-WordPress-GMT-Offset: Is the site's GMT offset, and is sent so that the WooCommerce App
	 * on MixCheck can use it to convert datetime fields from WooCommerce into an ISO-8601 formatted
	 * date (which includes the timezone offset).
	 *
	 * @return array
	 */
	public function get_headers() {
		return array(
			'X-WordPress-GMT-Offset' => get_option( 'gmt_offset' ),
		);
	}
}
