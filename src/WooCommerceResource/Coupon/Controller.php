<?php

namespace OM4\WooCommerceMixCheck\WooCommerceResource\Coupon;

use OM4\WooCommerceMixCheck\API\API;
use OM4\WooCommerceMixCheck\Logger;
use OM4\WooCommerceMixCheck\TaskHistory\Listener\APIListenerTrait;
use OM4\WooCommerceMixCheck\WooCommerceResource\Coupon\CouponTaskCreator;
use WC_REST_Coupons_Controller;

defined( 'ABSPATH' ) || exit;

/**
 * Exposes WooCommerce's REST API v3 Coupons endpoint via the WooCommerce MixCheck endpoint namespace.
 *
 * @since 2.0.0
 */
class Controller extends WC_REST_Coupons_Controller {

	use APIListenerTrait;

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = API::REST_NAMESPACE;

	/**
	 * Resource Type (used for Task History items).
	 *
	 * @var string
	 */
	protected $resource_type = 'coupon';

	/**
	 * Logger instance.
	 *
	 * @var Logger
	 */
	protected $logger;


	/**
	 * CouponTaskCreator instance.
	 *
	 * @var CouponTaskCreator
	 */
	protected $task_creator;

	/**
	 * Constructor.
	 *
	 * @param Logger            $logger       Logger instance.
	 * @param CouponTaskCreator $task_creator CouponTaskCreator instance.
	 */
	public function __construct( Logger $logger, CouponTaskCreator $task_creator ) {
		$this->logger       = $logger;
		$this->task_creator = $task_creator;
		$this->add_filter_to_check_for_request_validation_error();
	}
}
