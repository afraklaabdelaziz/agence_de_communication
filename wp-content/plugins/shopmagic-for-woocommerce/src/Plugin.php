<?php

namespace WPDesk\ShopMagic;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use ShopMagicVendor\WPDesk\Forms\Field\CheckboxField;
use ShopMagicVendor\WPDesk\Logger\WPDeskLoggerFactory;
use ShopMagicVendor\WPDesk\Notice;
use ShopMagicVendor\WPDesk\Notice\Notice as AdminNotice;
use ShopMagicVendor\WPDesk\PluginBuilder\Plugin\AbstractPlugin;
use ShopMagicVendor\WPDesk\PluginBuilder\Plugin\HookableCollection;
use ShopMagicVendor\WPDesk\PluginBuilder\Plugin\HookableParent;
use ShopMagicVendor\WPDesk\ShowDecision\PostTypeStrategy;
use ShopMagicVendor\WPDesk\View\Renderer\SimplePhpRenderer;
use ShopMagicVendor\WPDesk_Plugin_Info;
use WPDesk\ShopMagic\Action\ActionFactory2;
use WPDesk\ShopMagic\ActionExecution\ExecutionCreator\ExecutionCreatorContainer;
use WPDesk\ShopMagic\ActionExecution\ExecutionCreator\QueueExecutionCreator;
use WPDesk\ShopMagic\ActionExecution\ExecutionStrategyFactory;
use WPDesk\ShopMagic\ActionExecution\QueueActionRunner;
use WPDesk\ShopMagic\Admin\Admin;
use WPDesk\ShopMagic\Admin\Automation\ManualActionsTriggerQueue;
use WPDesk\ShopMagic\Admin\RateNotice\RateNotices;
use WPDesk\ShopMagic\Admin\RateNotice\TwoWeeksNotice;
use WPDesk\ShopMagic\Admin\Settings\GeneralSettings;
use WPDesk\ShopMagic\Admin\Settings\Settings;
use WPDesk\ShopMagic\Automation\AutomationFactory;
use WPDesk\ShopMagic\Automation\AutomationPostType;
use WPDesk\ShopMagic\AutomationOutcome\OutcomeFactory;
use WPDesk\ShopMagic\AutomationOutcome\OutcomeReposistory;
use WPDesk\ShopMagic\AutomationOutcome\OutcomeTable;
use WPDesk\ShopMagic\Beacon\Beacon;
use WPDesk\ShopMagic\CommunicationList\CommunicationListPostType;
use WPDesk\ShopMagic\CommunicationList\CommunicationListRepository;
use WPDesk\ShopMagic\Customer\CustomerDAO;
use WPDesk\ShopMagic\Customer\CustomerFactory;
use WPDesk\ShopMagic\Database\DatabaseSchema;
use WPDesk\ShopMagic\DataSharing\TestDataProviderFactory;
use WPDesk\ShopMagic\Event\Builtin\OrderPending;
use WPDesk\ShopMagic\Event\EventFactory2;
use WPDesk\ShopMagic\Event\EventMutex;
use WPDesk\ShopMagic\Filter\ComparisionType\SelectManyToManyType;
use WPDesk\ShopMagic\Filter\FilterFactory2;
use WPDesk\ShopMagic\Frontend;
use WPDesk\ShopMagic\Guest\GuestBackgroundConverter;
use WPDesk\ShopMagic\Guest\GuestDAO;
use WPDesk\ShopMagic\Guest\GuestFactory;
use WPDesk\ShopMagic\Guest\GuestOrderIntegration;
use WPDesk\ShopMagic\Guest\GuestProductIntegration;
use WPDesk\ShopMagic\Helper\TemplateResolver;
use WPDesk\ShopMagic\Helper\WordPressPluggableHelper;
use WPDesk\ShopMagic\HookEmitter\CronHeartbeat;
use WPDesk\ShopMagic\HookEmitter\OutcomeCleaner;
use WPDesk\ShopMagic\Integration\ExternalPluginsAccess;
use WPDesk\ShopMagic\MarketingLists\ConfirmedSubscriptionSaver;
use WPDesk\ShopMagic\MarketingLists\DAO\ListFactory;
use WPDesk\ShopMagic\MarketingLists\DAO\ListTable;
use WPDesk\ShopMagic\MarketingLists\Shortcode\FrontendListSubscription;
use WPDesk\ShopMagic\Placeholder\PlaceholderFactory2;
use WPDesk\ShopMagic\Tracker;

/**
 * Main plugin class. The most important flow decisions are made here.
 *
 * @package WPDesk\ShopMagic
 */
class Plugin extends AbstractPlugin implements LoggerAwareInterface, HookableCollection {
	use LoggerAwareTrait;
	use HookableParent;

	/**
	 * Most info about plugin internals.
	 *
	 * @var WPDesk_Plugin_Info
	 */
	protected $plugin_info;

	/** @var Event\EventFactory2 */
	private $event_factory;

	/** @var Filter\FilterFactory2 */
	private $filter_factory;

	/** @var Action\ActionFactory2 */
	private $action_factory;

	/** @var Placeholder\PlaceholderFactory2 */
	private $placeholder_factory;

	/** @var FormIntegration */
	private $form_integration;

	/** @var AutomationFactory */
	private $automation_factory;

	/** @var ExecutionStrategyFactory */
	private $execution_factory;

	/** @var DatabaseSchema */
	private $db_schema;

	public function __construct( WPDesk_Plugin_Info $plugin_info ) {
		class_alias( self::class, 'ShopMagic' );
		class_alias( Placeholder\PlaceholderFactoryCore::class, '\WPDesk\ShopMagic\Placeholder\PlaceholderFactory' ); // backward compat for 2.0 version when Factory could have been serialized.
		class_alias( Action\ActionFactoryCore::class, '\WPDesk\ShopMagic\Action\ActionFactory' ); // backward compat for 2.0 version when Factory could have been serialized.
		class_alias( Event\EventFactoryCore::class, '\WPDesk\ShopMagic\Event\EventFactory' ); // backward compat for 2.0 version when Factory could have been serialized.
		class_alias( Filter\FilterFactoryCore::class, '\WPDesk\ShopMagic\Filter\FilterFactory' ); // backward compat for 2.0 version when Factory could have been serialized.
		TemplateResolver::set_root_path( $plugin_info->get_plugin_dir() );

		$this->logger = new NullLogger();

		/** @noinspection PhpParamsInspection */
		parent::__construct( $plugin_info ); // @phpstan-ignore-line

		$this->forms_integration();
		class_alias( SelectManyToManyType::class, '\WPDesk\ShopMagic\Filter\ComparisionType\SelectToManyType' ); // fallback after rename.

		$event_mutex               = new EventMutex();
		$this->placeholder_factory =
			/**
			 * You can use this filter to override the main placeholder factory. Placeholder factory is
			 * a class that is responsible for creating every placeholder in the ShopMagic.
			 * Through this filter you can completely change how and when a placeholder will be created.
			 *
			 * @param $factory Placeholder\PlaceholderFactoryCore Default ShopMagic placeholder factory.
			 *
			 * @return PlaceholderFactory2
			 * @internal This filter requires knowledge about a Factory pattern and an Placeholder interface. If you simply want to add/change available placeholders please use shopmagic/core/placeholders filter.
			 * @since 2.0
			 * @deprecated 2.37 Factory cannot be overwritten through filter.
			 */
			apply_filters( 'shopmagic/core/factory/placeholder', new Placeholder\PlaceholderFactoryCore() );
		$this->event_factory =
			/**
			 * You can use this filter to override the main Event factory. Event factory is
			 * a class that is responsible for creating every Event in the ShopMagic.
			 * Through this filter you can completely change how and when a event is created.
			 *
			 * @param $factory Event\EventFactoryCore Default ShopMagic events factory.
			 *
			 * @return EventFactory2
			 * @internal This filter requires knowledge about a Factory pattern and an Event interface. If you simply want to add/change available events please use shopmagic/core/events.
			 * @since 2.0
			 * @deprecated 2.37 Factory cannot be overwritten through filter.
			 */
			apply_filters( 'shopmagic/core/factory/event', new Event\EventFactoryCore( $event_mutex, $this->is_pro_active() ) );

		$this->filter_factory =
			/**
			 * You can use this filter to override the main Filter factory. Filter factory is
			 * a class that is responsible for creating every Filter in the ShopMagic.
			 * Through this filter you can completely change how and when a Filter is created.
			 *
			 * @param $factory Filter\FilterFactoryCore Default ShopMagic filter factory.
			 *
			 * @return FilterFactory2
			 * @internal This filter requires knowledge about a Factory pattern, a ChainOfResponsibility pattern and a Filter interface. If you simply want to add/change available Filters please use shopmagic/core/filters.
			 * @since 2.0
			 * @deprecated 2.37 Factory cannot be overwritten through filter.
			 */
			apply_filters( 'shopmagic/core/factory/filter', new Filter\FilterFactoryCore( $this->is_pro_active() ) );
		$this->action_factory =
			/**
			 * You can use this filter to override the main Action factory. Action factory is
			 * a class that is responsible for creating every Action in the ShopMagic.
			 * Through this filter you can completely change how and when a Action is created.
			 *
			 * @param $factory Action\ActionFactoryCore Default ShopMagic action factory.
			 *
			 * @return ActionFactory2
			 * @internal This filter requires knowledge about a Factory pattern and a Action interface. If you simply want to add/change available Action please use shopmagic/core/actions.
			 * @since 2.0
			 * @deprecated 2.37 Factory cannot be overwritten through filter.
			 */
			apply_filters( 'shopmagic/core/factory/action', new Action\ActionFactoryCore() );
		$this->form_integration = new FormIntegration();

		$this->execution_factory = new ExecutionCreatorContainer();

		TestDataProviderFactory::set_event_factory( $this->event_factory );

		$this->automation_factory = new AutomationFactory(
			$this->event_factory,
			$this->action_factory,
			$this->filter_factory,
			$this->execution_factory
		);

		define( 'SHOPMAGIC_BASE_FILE', 'deprecated' );
		define( 'SHOPMAGIC_PLUGIN_URL', $plugin_info->get_plugin_url() . '/' ); // deprecated.

		global $wpdb;
		$this->db_schema = new DatabaseSchema( $plugin_info->get_plugin_file_name(), $wpdb );
		$this->db_schema->register_activation_hook();
	}

	/**
	 * Integrate with WordPress and with other plugins using action/filter system.
	 *
	 * @return void
	 */
	public function hooks() {
		parent::hooks();

		$pro_is_active = $this->is_pro_active();
		require_once __DIR__ . '/Admin/Welcome/Popups.php';

		if ( $this->db_schema->is_old_database() ) {
			$this->db_schema->install();
		}

		add_action(
			'plugins_loaded',
			function () {
				$this->setLogger( $this->is_debug_mode() ? ( new WPDeskLoggerFactory() )->createWPDeskLogger() : new NullLogger() );
				LoggerFactory::set_logger( $this->logger );

				$queue = \WC_Queue::instance();

				$this->execution_factory->add_execution_creator( new QueueExecutionCreator( $queue, $this->placeholder_factory ) );
				( new QueueActionRunner( $this->placeholder_factory, $this->automation_factory ) )->initialize();

				OrderPending::initialize_pending_on_created_check( $queue );
				( new GuestBackgroundConverter( $queue, $this->logger ) )
					->initialize()
					->start_guest_extraction_if_needed();

				( new ManualActionsTriggerQueue( $this->automation_factory ) )->hooks();

				( new CronHeartbeat() )->hooks();
			}
		);

		add_action(
			'woocommerce_init',
			function () {
				global $wpdb;
				( new Notice\AjaxHandler( $this->get_plugin_url() . '/vendor_prefixed/wpdesk/wp-notice/assets' ) )->hooks();
				( new AutomationPostType( $this->event_factory ) )->hooks();
				( new CommunicationListPostType() )->hooks();

				( new Tracker\DeactivationTracker( $this->plugin_info->get_plugin_file_name() ) )->hooks();
				( new Tracker\TrackerNotices() )->hooks();
				( new Tracker\UsageDataTracker( $this->plugin_info->get_plugin_file_name() ) )->hooks();

				$customer_factory = new CustomerFactory();

				$list_table   = new ListTable( new ListFactory() );
				$lists_repository = new CommunicationListRepository( $wpdb );

				( new MarketingLists\Controller\CustomerSignUp(
					$list_table,
					$lists_repository
				) )->hooks();
				$customer_dao     = new CustomerDAO( null, $customer_factory );
				( new FrontendListSubscription( $list_table, $customer_dao, $lists_repository ) )->hooks();
				( new ConfirmedSubscriptionSaver($list_table, $customer_factory))->hooks();
				( new MarketingLists\Controller\PreferencesUpdate( $list_table ) )->hooks();

				( new MarketingLists\View\ListsOnCheckout( $list_table ) )->hooks();
				$view = new MarketingLists\View\AccountPreferences( $list_table, new SimplePhpRenderer( TemplateResolver::for_public( 'marketing-lists' ) ) );
				$view->hooks();
				( new MarketingLists\Shortcode\FrontendForm( $list_table ) )->hooks();

				$preferences_route = new MarketingLists\PreferencesRoute();
				$preferences_route->set_view( $view );
				$preferences_route->hooks();

				( new GuestOrderIntegration() )->hooks();
				( new GuestProductIntegration() )->hooks();

				$guest_dao     = new GuestDAO( $wpdb );
				$guest_factory = new GuestFactory( $guest_dao );

				$customer_interceptor = new Frontend\Interceptor\CurrentCustomer( $customer_factory, $guest_dao, $guest_factory );
				$customer_interceptor->setLogger( LoggerFactory::get_logger() );
				if ( GeneralSettings::get_option( 'enable_session_tracking', true ) ) {
					$customer_interceptor->hooks();
					( new Frontend\Interceptor\PreSubmitData( $customer_interceptor, $this->get_plugin_assets_url() ) )->hooks();
				}

				$outcome_table = new OutcomeTable( new OutcomeFactory( $customer_factory ) );
				if ( GeneralSettings::get_option( GeneralSettings::OUTCOMES_PURGE ) === CheckboxField::VALUE_TRUE ) {
					( new OutcomeCleaner( $outcome_table, LoggerFactory::get_logger() ) )->hooks();
				}

				$flexible_shipping_integration = new Integration\FlexibleShipping\Integrator();
				$flexible_shipping_integration->integrate_placeholders( $this->placeholder_factory );

				$flexible_checkout_fields_integration = new Integration\FlexibleCheckoutFields\Integrator();
				$flexible_checkout_fields_integration->integrate_placeholders( $this->placeholder_factory, LoggerFactory::get_logger() );

				// should be called BEFORE initialize_active_woocommerce_automations so the dependant plugins could attach themselves.

				/**
				 * If you want to write an integration with ShopMagic you should use this action.
				 * This action is executed when the ShopMagic core is ready to be used.
				 *
				 * @param string $version Version
				 *
				 * @deprecated Use shopmagic/core/initialized/v2
				 * @since 2.7.0
				 */
				do_action( 'shopmagic/core/initialized', $this->plugin_info->get_version(), $this );

				/**
				 * If you want to write an integration with ShopMagic you should use this action.
				 * This action is executed when the ShopMagic core is ready to be used and provides a ExternalPluginsAccess object to facilitate integration.
				 *
				 * Please make sure that your integration is checking $plugin_access->get_version() to ensure that your integration is compatible with current ShopMagic version.
				 * Remember that we use semantic versioning so can be sure that every time we make a breaking change we also increase a major version of the plugin.
				 *
				 * @param ExternalPluginsAccess $plugin_access Object with various tools that can be used for integration.
				 *
				 * @since 2.17.0
				 */
				do_action(
					'shopmagic/core/initialized/v2',
					new ExternalPluginsAccess(
						$this->plugin_info->get_version(),
						$guest_factory,
						$customer_factory,
						$customer_interceptor,
						LoggerFactory::get_logger(),
						new OutcomeReposistory( $wpdb ),
						$this->automation_factory,
						$this->execution_factory,
						$this->placeholder_factory,
						$outcome_table
					)
				);

				$this->action_factory->initialize_actions_additional_hooks( LoggerFactory::get_logger() );

				// required woocommerce_init for WC methods to fully works.
				$automations = $this->automation_factory->initialize_active_automations();

				if ( is_admin() ) {
					( new RateNotices(
						[ new TwoWeeksNotice( $this->plugin_url . '/assets', $automations, new PostTypeStrategy( AutomationPostType::TYPE ) ) ]
					) )->hooks();

					if ( PHP_VERSION_ID < 70200 ) {
						new Notice\PermanentDismissibleNotice(
							__( '<strong>ShopMagic will soon require PHP 7.2 or higher</strong>. Please update PHP to receive upcoming updates and ensure your website security.', 'shopmagic-for-woocommerce' ),
							'shopmagic-php-version-notice',
							AdminNotice::NOTICE_TYPE_WARNING
						);
					}
				}
			}
		);

		add_action(
			'shopmagic/core/settings/ready',
			function () {
				if ( is_admin() && GeneralSettings::get_option( 'disable_beacon', 'no' ) !== 'yes' ) {
					( new Beacon(
						'6057086f-4b25-4e12-8735-fbc556d2dc01',
						new PostTypeStrategy( AutomationPostType::TYPE ),
						$this->get_plugin_assets_url()
					) )->hooks();
				}
			}
		);

		if ( is_admin() ) {
			( new Admin(
				$this->plugin_info->get_plugin_url(),
				$this->event_factory,
				$this->filter_factory,
				$this->action_factory,
				$this->placeholder_factory,
				$this->automation_factory,
				$this->form_integration,
				new OutcomeTable( new OutcomeFactory( new CustomerFactory() ) ),
				$this->is_pro_active()
			) )->hooks();
		}

		$this->hooks_on_hookable_objects();
	}

	/**
	 * Returns true when debug mode is on.
	 *
	 * @return bool
	 */
	private function is_debug_mode(): bool {
		$helper_options = get_option( 'wpdesk_helper_options', [] );

		return isset( $helper_options['debug_log'] ) && '1' === $helper_options['debug_log'];
	}

	/**
	 * It's a good idea that FrontendForm fields have a local namespace to create more stable env for other plugins.
	 *
	 * @return void
	 */
	private function forms_integration() {
		class_alias( \ShopMagicVendor\WPDesk\Forms\Field\CheckboxField::class, \WPDesk\ShopMagic\FormField\Field\CheckboxField::class );
		class_alias( \ShopMagicVendor\WPDesk\Forms\Field\Header::class, \WPDesk\ShopMagic\FormField\Field\Header::class );
		class_alias( \ShopMagicVendor\WPDesk\Forms\Field\HiddenField::class, \WPDesk\ShopMagic\FormField\Field\HiddenField::class );
		class_alias( \ShopMagicVendor\WPDesk\Forms\Field\InputTextField::class, \WPDesk\ShopMagic\FormField\Field\InputTextField::class );
		class_alias( \ShopMagicVendor\WPDesk\Forms\Field\MultipleInputTextField::class, \WPDesk\ShopMagic\FormField\Field\MultipleInputTextField::class );
		class_alias( \ShopMagicVendor\WPDesk\Forms\Field\ImageInputField::class, \WPDesk\ShopMagic\FormField\Field\ImageInputField::class );
		class_alias( \ShopMagicVendor\WPDesk\Forms\Field\NoOnceField::class, \WPDesk\ShopMagic\FormField\Field\NoOnceField::class );
		class_alias( \ShopMagicVendor\WPDesk\Forms\Field\NoValueField::class, \WPDesk\ShopMagic\FormField\Field\NoValueField::class );
		class_alias( \ShopMagicVendor\WPDesk\Forms\Field\Paragraph::class, \WPDesk\ShopMagic\FormField\Field\Paragraph::class );
		class_alias( \ShopMagicVendor\WPDesk\Forms\Field\ProductSelect::class, \WPDesk\ShopMagic\FormField\Field\ProductSelect::class );
		class_alias( \ShopMagicVendor\WPDesk\Forms\Field\RadioField::class, \WPDesk\ShopMagic\FormField\Field\RadioField::class );
		class_alias( \ShopMagicVendor\WPDesk\Forms\Field\SelectField::class, \WPDesk\ShopMagic\FormField\Field\SelectField::class );
		class_alias( \ShopMagicVendor\WPDesk\Forms\Field\SubmitField::class, \WPDesk\ShopMagic\FormField\Field\SubmitField::class );
		class_alias( \ShopMagicVendor\WPDesk\Forms\Field\TextAreaField::class, \WPDesk\ShopMagic\FormField\Field\TextAreaField::class );
		class_alias( \ShopMagicVendor\WPDesk\Forms\Field\WooSelect::class, \WPDesk\ShopMagic\FormField\Field\WooSelect::class );
		class_alias( \ShopMagicVendor\WPDesk\Forms\Field\WyswigField::class, \WPDesk\ShopMagic\FormField\Field\WyswigField::class );

		class_alias( \ShopMagicVendor\WPDesk\Forms\Field\NoValueField::class, \WPDesk\ShopMagic\FormField\NoValueField::class );
		class_alias( \ShopMagicVendor\WPDesk\Forms\Field\BasicField::class, \WPDesk\ShopMagic\FormField\BasicField::class );
	}

	/**
	 * Quick links on plugins page.
	 *
	 * @param string[] $links .
	 *
	 * @return string[]
	 * @internal
	 */
	public function links_filter( $links ): array {
		$plugin_links = [];

		$plugin_links[] = '<a href="' . Settings::get_url() . '">' . __(
			'Settings',
			'shopmagic-for-woocommerce'
		) . '</a>';
		$plugin_links[] = '<a href="https://shopmagic.app/docs/" target="_blank">' . __(
			'Docs',
			'shopmagic-for-woocommerce'
		) . '</a>';
		$plugin_links[] = '<a href="https://wordpress.org/support/plugin/shopmagic-for-woocommerce/" target="_blank">' . __(
			'Support',
			'shopmagic-for-woocommerce'
		) . '</a>';

		if ( ! $this->is_pro_active() ) {
			$plugin_links[] = '<a href="https://shopmagic.app/pricing/?utm_source=user-site&utm_medium=quick-link&utm_campaign=shopmagic-upgrade" target="_blank" style="color:#d64e07;font-weight:bold;">' . __(
				'Buy PRO',
				'shopmagic-for-woocommerce'
			) . '</a>';
		}

		return array_merge( $plugin_links, $links );
	}

	/**
	 * Check if any of ShopMagic PRO add-ons is active
	 */
	private function is_pro_active(): bool {
		$addons = [
			'shopmagic-advanced-filters/shopmagic-advanced-filters.php',
			'shopmagic-customer-coupons/shopmagic-customer-coupons.php',
			'shopmagic-delayed-actions/shopmagic-delayed-actions.php',
			'shopmagic-for-gravity-forms/shopmagic-for-gravity-forms.php',
			'shopmagic-manual-actions/shopmagic-manual-actions.php',
			'shopmagic-reviews/shopmagic-reviews.php',
			'shopmagic-slack/shopmagic-slack.php',
			'shopmagic-woocommerce-bookings/shopmagic-woocommerce-bookings.php',
			'shopmagic-woocommerce-memberships/shopmagic-woocommerce-memberships.php',
			'shopmagic-woocommerce-subscriptions/shopmagic-woocommerce-subscriptions.php',
		];

		foreach ( $addons as $addon ) {
			if ( WordPressPluggableHelper::is_plugin_active( $addon ) ) {
				return true;
			}
		}

		return false;
	}

}
