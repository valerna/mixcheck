<?php

declare(strict_types=1);

namespace OM4\WooCommerceMixCheck;

use OM4\WooCommerceMixCheck\API\API;
use OM4\WooCommerceMixCheck\API\Controller\PingController;
use OM4\WooCommerceMixCheck\API\Controller\WebhookController;
use OM4\WooCommerceMixCheck\API\Controller\WebhookTopicsController;
use OM4\WooCommerceMixCheck\AdminUI;
use OM4\WooCommerceMixCheck\Auth\AuthKeyRotator;
use OM4\WooCommerceMixCheck\Auth\KeyDataStore;
use OM4\WooCommerceMixCheck\Auth\SessionAuthenticate;
use OM4\WooCommerceMixCheck\Helper\FeatureChecker;
use OM4\WooCommerceMixCheck\Helper\HTTPHeaders;
use OM4\WooCommerceMixCheck\Helper\WordPressDB;
use OM4\WooCommerceMixCheck\Installer;
use OM4\WooCommerceMixCheck\LegacyMigration\ExistingUserUpgrade;
use OM4\WooCommerceMixCheck\LegacyMigration\LegacyFeedsDeletedNotice;
use OM4\WooCommerceMixCheck\LegacyMigration\UpgradedToVersion2Notice;
use OM4\WooCommerceMixCheck\Logger;
use OM4\WooCommerceMixCheck\NewUser\NewUser;
use OM4\WooCommerceMixCheck\NewUser\NewUserWelcomeNotice;
use OM4\WooCommerceMixCheck\Plugin;
use OM4\WooCommerceMixCheck\Plugin\Bookings\BookingResource;
use OM4\WooCommerceMixCheck\Plugin\Bookings\BookingsTaskCreator;
use OM4\WooCommerceMixCheck\Plugin\Bookings\Plugin as BookingsPlugin;
use OM4\WooCommerceMixCheck\Plugin\Bookings\V1Controller as BookingsV1Controller;
use OM4\WooCommerceMixCheck\Plugin\Memberships\Plan\Controller as MembershipPlanController;
use OM4\WooCommerceMixCheck\Plugin\Memberships\Plan\MembershipPlanResource;
use OM4\WooCommerceMixCheck\Plugin\Memberships\Plan\MembershipPlanTaskCreator;
use OM4\WooCommerceMixCheck\Plugin\Memberships\Plugin as MembershipsPlugin;
use OM4\WooCommerceMixCheck\Plugin\Memberships\User\Controller as UserMembershipController;
use OM4\WooCommerceMixCheck\Plugin\Memberships\User\UserMembershipResource;
use OM4\WooCommerceMixCheck\Plugin\Memberships\User\UserMembershipsTaskCreator;
use OM4\WooCommerceMixCheck\Plugin\Subscriptions\Note\Controller as SubscriptionNoteController;
use OM4\WooCommerceMixCheck\Plugin\Subscriptions\Note\SubscriptionNoteResource;
use OM4\WooCommerceMixCheck\Plugin\Subscriptions\Note\SubscriptionNoteTaskCreator;
use OM4\WooCommerceMixCheck\Plugin\Subscriptions\Plugin as SubscriptionsPlugin;
use OM4\WooCommerceMixCheck\Plugin\Subscriptions\SubscriptionResource;
use OM4\WooCommerceMixCheck\Plugin\Subscriptions\SubscriptionsTaskCreator;
use OM4\WooCommerceMixCheck\Plugin\Subscriptions\V1Controller as SubscriptionsV1Controller;
use OM4\WooCommerceMixCheck\Plugin\Subscriptions\WCSV1Controller;
use OM4\WooCommerceMixCheck\Privacy;
use OM4\WooCommerceMixCheck\SystemStatus\UI as SystemStatusUI;
use OM4\WooCommerceMixCheck\TaskHistory\Installer as TaskHistoryInstaller;
use OM4\WooCommerceMixCheck\TaskHistory\ListTable;
use OM4\WooCommerceMixCheck\TaskHistory\Listener\TriggerListener;
use OM4\WooCommerceMixCheck\TaskHistory\Task\TaskDataStore;
use OM4\WooCommerceMixCheck\TaskHistory\UI as TaskHistoryUI;
use OM4\WooCommerceMixCheck\Vendor\League\Container\Container;
use OM4\WooCommerceMixCheck\Webhook\DataStore as WebhookDataStore;
use OM4\WooCommerceMixCheck\Webhook\DeliveryFilter as WebhookDeliveryFilter;
use OM4\WooCommerceMixCheck\Webhook\Installer as WebhookInstaller;
use OM4\WooCommerceMixCheck\Webhook\Resources;
use OM4\WooCommerceMixCheck\Webhook\TopicsRetriever as WebhookTopicsRetriever;
use OM4\WooCommerceMixCheck\WooCommerceResource\Coupon\Controller as CouponController;
use OM4\WooCommerceMixCheck\WooCommerceResource\Coupon\CouponResource;
use OM4\WooCommerceMixCheck\WooCommerceResource\Coupon\CouponTaskCreator;
use OM4\WooCommerceMixCheck\WooCommerceResource\Customer\Controller as CustomerController;
use OM4\WooCommerceMixCheck\WooCommerceResource\Customer\CustomerResource;
use OM4\WooCommerceMixCheck\WooCommerceResource\Customer\CustomerTaskCreator;
use OM4\WooCommerceMixCheck\WooCommerceResource\Manager as ResourceManager;
use OM4\WooCommerceMixCheck\WooCommerceResource\Order\Controller as OrderController;
use OM4\WooCommerceMixCheck\WooCommerceResource\Order\Note\Controller as OrderNoteController;
use OM4\WooCommerceMixCheck\WooCommerceResource\Order\Note\OrderNoteResource;
use OM4\WooCommerceMixCheck\WooCommerceResource\Order\Note\OrderNoteTaskCreator;
use OM4\WooCommerceMixCheck\WooCommerceResource\Order\OrderResource;
use OM4\WooCommerceMixCheck\WooCommerceResource\Order\OrderTaskCreator;
use OM4\WooCommerceMixCheck\WooCommerceResource\Product\Controller as ProductController;
use OM4\WooCommerceMixCheck\WooCommerceResource\Product\Price\Controller as ProductPriceController;
use OM4\WooCommerceMixCheck\WooCommerceResource\Product\ProductResource;
use OM4\WooCommerceMixCheck\WooCommerceResource\Product\ProductTaskCreator;
use OM4\WooCommerceMixCheck\WooCommerceResource\Product\Stock\Controller as ProductStockController;
use OM4\WooCommerceMixCheck\WooCommerceResource\Product\WCVariationController;
use WC_Webhook_Data_Store;

defined( 'ABSPATH' ) || exit;

/**
 * Dependency Injection Container Service for WooCommerce MixCheck.
 *
 * @since 2.0.0
 */
class ContainerService {

	/**
	 * Dependency Injection Container instance.
	 *
	 * @see https://container.thephpleague.com/2.x/ for documentation.
	 *
	 * @var Container
	 */
	protected $container;

	/**
	 * Constructor, initialises our container.
	 */
	public function __construct() {

		$this->container = new Container();

		$this->register();
	}

	/**
	 * Register classes into the container.
	 * For registering classes, we use callback functions here so that the
	 * classes are only instantiated when first requested from the Container.
	 * So we can register it alphabetically.
	 *
	 * @return void
	 */
	protected function register() {
		$this->container->add(
			AdminUI::class,
			function () {
				return new AdminUI(
					$this->get( TaskHistoryUI::class ),
					$this->get( Settings::class ),
					$this->get( SystemStatusUI::class )
				);
			}
		);

		$this->container->add(
			API::class,
			function () {
				return new API(
					$this->get( FeatureChecker::class ),
					$this->get( ResourceManager::class ),
					$this->get( HTTPHeaders::class ),
					$this
				);
			}
		);

		$this->container->add(
			FeatureChecker::class,
			function () {
				return new FeatureChecker();
			}
		);

		$this->container->add(
			HTTPHeaders::class,
			function () {
				return new HTTPHeaders();
			}
		);

		$this->container->add(
			Installer::class,
			function () {
				return new Installer(
					$this->get( Logger::class )
				);
			}
		);

		$this->container->add(
			ExistingUserUpgrade::class,
			function () {
				return new ExistingUserUpgrade(
					$this->get( Logger::class ),
					$this->get( Settings::class ),
					$this->get( WordPressDB::class ),
					$this->get( UpgradedToVersion2Notice::class ),
					$this->get( LegacyFeedsDeletedNotice::class )
				);
			}
		);

		$this->container->add(
			UpgradedToVersion2Notice::class,
			function () {
				return new UpgradedToVersion2Notice(
					$this->get( Logger::class ),
					$this->get( Settings::class ),
					$this->get( AdminUI::class )
				);
			}
		);

		$this->container->add(
			LegacyFeedsDeletedNotice::class,
			function () {
				return new LegacyFeedsDeletedNotice(
					$this->get( Logger::class ),
					$this->get( Settings::class ),
					$this->get( AdminUI::class )
				);
			}
		);

		$this->container->add(
			NewUser::class,
			function () {
				return new NewUser(
					$this->get( Settings::class ),
					$this->get( TaskDataStore::class ),
					$this->get( KeyDataStore::class ),
					$this->get( NewUserWelcomeNotice::class )
				);
			}
		);

		$this->container->add(
			NewUserWelcomeNotice::class,
			function () {
				return new NewUserWelcomeNotice(
					$this->get( Logger::class ),
					$this->get( Settings::class ),
					$this->get( AdminUI::class )
				);
			}
		);

		$this->container->addShared(
			ResourceManager::class,
			function () {
				return new ResourceManager( $this );
			}
		);

		// Resource: Customer.
		$this->container->add(
			CustomerResource::class,
			function () {
				return new CustomerResource();
			}
		);

		// Resource: Customer.
		$this->container->add(
			CustomerTaskCreator::class,
			function () {
				return new CustomerTaskCreator(
					$this->get( Logger::class ),
					$this->get( TaskDataStore::class )
				);
			}
		);

		$this->container->add(
			CustomerController::class,
			function () {
				return new CustomerController(
					$this->get( Logger::class ),
					$this->get( CustomerTaskCreator::class )
				);
			}
		);

		// Resource: Coupon.
		$this->container->add(
			CouponResource::class,
			function () {
				return new CouponResource(
					$this->get( FeatureChecker::class )
				);
			}
		);

		$this->container->add(
			CouponTaskCreator::class,
			function () {
				return new CouponTaskCreator(
					$this->get( Logger::class ),
					$this->get( TaskDataStore::class )
				);
			}
		);

		$this->container->add(
			CouponController::class,
			function () {
				return new CouponController(
					$this->get( Logger::class ),
					$this->get( CouponTaskCreator::class )
				);
			}
		);

		// Resource: Order.
		$this->container->add(
			OrderResource::class,
			function () {
				return new OrderResource(
					$this->get( FeatureChecker::class )
				);
			}
		);

		$this->container->add(
			OrderTaskCreator::class,
			function () {
				return new OrderTaskCreator(
					$this->get( Logger::class ),
					$this->get( TaskDataStore::class )
				);
			}
		);

		$this->container->add(
			OrderController::class,
			function () {
				return new OrderController(
					$this->get( Logger::class ),
					$this->get( OrderTaskCreator::class )
				);
			}
		);

		// Resource: Order Note.
		$this->container->add(
			OrderNoteResource::class,
			function () {
				return new OrderNoteResource(
					$this->get( FeatureChecker::class ),
					$this->get( OrderNoteController::class )
				);
			}
		);

		$this->container->add(
			OrderNoteTaskCreator::class,
			function () {
				return new OrderNoteTaskCreator(
					$this->get( Logger::class ),
					$this->get( TaskDataStore::class )
				);
			}
		);

		$this->container->add(
			OrderNoteController::class,
			function () {
				return new OrderNoteController(
					$this->get( Logger::class ),
					$this->get( OrderNoteTaskCreator::class ),
					$this->get( FeatureChecker::class ),
					$this->get( WordPressDB::class )
				);
			}
		);

		// Resource: Product.
		$this->container->add(
			ProductResource::class,
			function () {
				return new ProductResource();
			}
		);

		$this->container->add(
			ProductTaskCreator::class,
			function () {
				return new ProductTaskCreator(
					$this->get( Logger::class ),
					$this->get( TaskDataStore::class )
				);
			}
		);

		$this->container->add(
			ProductController::class,
			function () {
				return new ProductController(
					$this->get( Logger::class ),
					$this->get( ProductTaskCreator::class ),
					$this->get( WCVariationController::class )
				);
			}
		);

		$this->container->add(
			ProductPriceController::class,
			function () {
				return new ProductPriceController(
					$this->get( Logger::class ),
					$this->get( ProductTaskCreator::class )
				);
			}
		);

		$this->container->add(
			ProductStockController::class,
			function () {
				return new ProductStockController(
					$this->get( Logger::class ),
					$this->get( ProductTaskCreator::class )
				);
			}
		);

		$this->container->add(
			WCVariationController::class,
			function () {
				return new WCVariationController(
					$this->get( Logger::class ),
					$this->get( ProductTaskCreator::class )
				);
			}
		);

		// Bookings.
		$this->container->add(
			BookingsPlugin::class,
			function () {
				return new BookingsPlugin(
					$this->get( FeatureChecker::class ),
					$this->get( Logger::class )
				);
			}
		);

		$this->container->add(
			BookingResource::class,
			function () {
				return new BookingResource(
					$this->get( BookingsV1Controller::class ),
					$this->get( FeatureChecker::class )
				);
			}
		);

		$this->container->add(
			BookingsTaskCreator::class,
			function () {
				return new BookingsTaskCreator(
					$this->get( Logger::class ),
					$this->get( TaskDataStore::class )
				);
			}
		);

		$this->container->add( BookingsV1Controller::class );

		// Memberships.
		$this->container->add(
			MembershipsPlugin::class,
			function () {
				return new MembershipsPlugin(
					$this->get( FeatureChecker::class ),
					$this->get( Logger::class ),
					$this
				);
			}
		);

		$this->container->add(
			MembershipPlanResource::class,
			function () {
				return new MembershipPlanResource(
					$this->get( FeatureChecker::class )
				);
			}
		);

		$this->container->add(
			MembershipPlanController::class,
			function () {
				return new MembershipPlanController(
					$this->get( Logger::class ),
					$this->get( MembershipPlanTaskCreator::class )
				);
			}
		);

		$this->container->add(
			MembershipPlanTaskCreator::class,
			function () {
				return new MembershipPlanTaskCreator(
					$this->get( Logger::class ),
					$this->get( TaskDataStore::class )
				);
			}
		);

		$this->container->add(
			UserMembershipResource::class,
			function () {
				return new UserMembershipResource(
					$this->get( FeatureChecker::class )
				);
			}
		);

		$this->container->add(
			UserMembershipController::class,
			function () {
				return new UserMembershipController(
					$this->get( Logger::class ),
					$this->get( UserMembershipsTaskCreator::class )
				);
			}
		);

		$this->container->add(
			UserMembershipsTaskCreator::class,
			function () {
				return new UserMembershipsTaskCreator(
					$this->get( Logger::class ),
					$this->get( TaskDataStore::class )
				);
			}
		);

		// Subscriptions.
		$this->container->add(
			SubscriptionsPlugin::class,
			function () {
				return new SubscriptionsPlugin(
					$this->get( FeatureChecker::class ),
					$this->get( Logger::class ),
					$this
				);
			}
		);

		$this->container->add(
			SubscriptionResource::class,
			function () {
				return new SubscriptionResource(
					$this->get( FeatureChecker::class ),
					$this->get( SubscriptionsV1Controller::class )
				);
			}
		);

		$this->container->add(
			SubscriptionsTaskCreator::class,
			function () {
				return new SubscriptionsTaskCreator(
					$this->get( Logger::class ),
					$this->get( TaskDataStore::class )
				);
			}
		);

		$this->container->add(
			SubscriptionsV1Controller::class,
			function () {
				return new SubscriptionsV1Controller(
					$this->get( Logger::class ),
					$this->get( SubscriptionsTaskCreator::class ),
					$this->get( WCSV1Controller::class )
				);
			}
		);

		$this->container->add(
			WCSV1Controller::class,
			function () {
				return new WCSV1Controller(
					$this->get( Logger::class ),
					$this->get( SubscriptionsTaskCreator::class ),
					$this->get( FeatureChecker::class )
				);
			}
		);

		// Resource: Subscription Note.
		$this->container->add(
			SubscriptionNoteResource::class,
			function () {
				return new SubscriptionNoteResource(
					$this->get( FeatureChecker::class ),
					$this->get( SubscriptionNoteController::class )
				);
			}
		);

		$this->container->add(
			SubscriptionNoteTaskCreator::class,
			function () {
				return new SubscriptionNoteTaskCreator(
					$this->get( Logger::class ),
					$this->get( TaskDataStore::class )
				);
			}
		);

		$this->container->add(
			SubscriptionNoteController::class,
			function () {
				return new SubscriptionNoteController(
					$this->get( Logger::class ),
					$this->get( SubscriptionNoteTaskCreator::class ),
					$this->get( FeatureChecker::class ),
					$this->get( WordPressDB::class )
				);
			}
		);

		$this->container->add(
			KeyDataStore::class,
			function () {
				return new KeyDataStore(
					$this->get( WordPressDB::class )
				);
			}
		);

		$this->container->add(
			AuthKeyRotator::class,
			function () {
				return new AuthKeyRotator(
					$this->get( KeyDataStore::class ),
					$this->get( Logger::class )
				);
			}
		);

		$this->container->add(
			ListTable::class,
			function () {
				return new ListTable(
					$this->get( TaskDataStore::class ),
					$this->get( Resources::class ),
					$this->get( FeatureChecker::class ),
					$this->get( ResourceManager::class )
				);
			}
		);

		$this->container->add(
			Logger::class,
			function () {
				return new Logger(
					$this->get( Settings::class )
				);
			}
		);

		$this->container->add(
			PingController::class,
			function () {
				return new PingController(
					$this->get( Logger::class )
				);
			}
		);

		$this->container->add(
			Plugin::class,
			function () {
				return new Plugin(
					$this
				);
			}
		);

		$this->container->add(
			Privacy::class,
			function () {
				return new Privacy();
			}
		);

		$this->container->add(
			SystemStatusUI::class,
			function () {
				return new SystemStatusUI(
					$this->get( Settings::class ),
					$this->get( TaskDataStore::class ),
					$this->get( KeyDataStore::class ),
					$this->get( WebhookDataStore::class ),
					$this->get( Installer::class )
				);
			}
		);

		$this->container->add(
			SessionAuthenticate::class,
			function () {
				return new SessionAuthenticate(
					$this->get( KeyDataStore::class ),
					$this->get( Logger::class )
				);
			}
		);

		$this->container->add(
			TaskDataStore::class,
			function () {
				return new TaskDataStore(
					$this->get( WordPressDB::class )
				);
			}
		);

		$this->container->add(
			TaskHistoryInstaller::class,
			function () {
				return new TaskHistoryInstaller(
					$this->get( Logger::class ),
					$this->get( WordPressDB::class ),
					$this->get( TaskDataStore::class )
				);
			}
		);

		$this->container->add(
			TaskHistoryUI::class,
			function () {
				return new TaskHistoryUI(
					$this,
					$this->get( ResourceManager::class )
				);
			}
		);

		$this->container->add(
			TriggerListener::class,
			function () {
				return new TriggerListener(
					$this->get( Logger::class ),
					$this->get( TaskDataStore::class ),
					$this->get( Resources::class ),
					$this
				);
			}
		);

		$this->container->add(
			Settings::class,
			function () {
				return new Settings();
			}
		);

		$this->container->add(
			WebhookDataStore::class,
			function () {
				return new WebhookDataStore( new WC_Webhook_Data_Store() );
			}
		);

		$this->container->add(
			WebhookDeliveryFilter::class,
			function () {
				return new WebhookDeliveryFilter(
					$this->get( HTTPHeaders::class )
				);
			}
		);

		$this->container->add(
			WebhookInstaller::class,
			function () {
				return new WebhookInstaller(
					$this->get( Logger::class ),
					$this->get( WebhookDataStore::class )
				);
			}
		);

		$this->container->add(
			WebhookController::class,
			function () {
				return new WebhookController( $this->get( ResourceManager::class ) );
			}
		);

		$this->container->add(
			WebhookTopicsController::class,
			function () {
				return new WebhookTopicsController(
					$this->get( Resources::class ),
					$this->get( ResourceManager::class )
				);
			}
		);

		$this->container->add(
			Resources::class,
			function () {
				return new Resources(
					$this->get( WebhookTopicsRetriever::class ),
					$this->get( ResourceManager::class )
				);
			}
		);

		$this->container->add(
			WebhookTopicsRetriever::class,
			function () {
				return new WebhookTopicsRetriever();
			}
		);

		$this->container->add(
			WordPressDB::class,
			function () {
				return new WordPressDB();
			}
		);
	}

	/**
	 * Retrieve an item/service from the container.
	 *
	 * @template T of object
	 * @param class-string<T> $alias Class name/alias.
	 * @return T
	 */
	public function get( $alias ) {
		return $this->container->get( $alias );
	}
}
