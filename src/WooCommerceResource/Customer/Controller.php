<?php

namespace OM4\WooCommerceMixCheck\WooCommerceResource\Customer;

use OM4\WooCommerceMixCheck\API\API;
use OM4\WooCommerceMixCheck\Logger;
use OM4\WooCommerceMixCheck\TaskHistory\Listener\APIListenerTrait;
use OM4\WooCommerceMixCheck\WooCommerceResource\Customer\CustomerTaskCreator;
use WC_REST_Customers_Controller;

defined( 'ABSPATH' ) || exit;

/**
 * Exposes WooCommerce's REST API v3 Customers endpoint via the WooCommerce MixCheck endpoint namespace.
 *
 * @since 2.0.0
 */
class Controller extends WC_REST_Customers_Controller {

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
	protected $resource_type = 'customer';

	/**
	 * Logger instance.
	 *
	 * @var Logger
	 */
	protected $logger;

	/**
	 * CustomerTaskCreator instance.
	 *
	 * @var CustomerTaskCreator
	 */
	protected $task_creator;

	/**
	 * Constructor.
	 *
	 * @param Logger              $logger     Logger instance.
	 * @param CustomerTaskCreator $task_creator CustomerTaskCreator instance.
	 */
	public function __construct( Logger $logger, CustomerTaskCreator $task_creator ) {
		$this->logger       = $logger;
		$this->task_creator = $task_creator;
		$this->add_filter_to_check_for_request_validation_error();
	}
}
