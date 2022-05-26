<?php

namespace WPDesk\ShopMagic\Admin;

use ShopMagicVendor\WPDesk\View\Renderer\SimplePhpRenderer;
use WPDesk\ShopMagic\Action\ActionFactory2;
use WPDesk\ShopMagic\Action\Builtin\SendMail\AbstractSendMailAction;
use WPDesk\ShopMagic\Admin\Automation\AutomationListActions\ActionDuplicate;
use WPDesk\ShopMagic\Admin\Automation\AutomationListActions\ActionExport;
use WPDesk\ShopMagic\Admin\Automation\AutomationListActions\BulkActionExport;
use WPDesk\ShopMagic\Admin\Automation\Metabox;
use WPDesk\ShopMagic\Admin\Automation\Metabox\ActionMetabox;
use WPDesk\ShopMagic\Admin\Automation\Metabox\EventMetabox;
use WPDesk\ShopMagic\Admin\Automation\Metabox\FilterMetabox;
use WPDesk\ShopMagic\Admin\Automation\Metabox\ManualActionsMetabox;
use WPDesk\ShopMagic\Admin\Automation\Metabox\PlaceholdersMetabox;
use WPDesk\ShopMagic\Admin\Automation\PlaceholderDialog;
use WPDesk\ShopMagic\Admin\Automation\RecipesTab;
use WPDesk\ShopMagic\Admin\CommunicationList\CommunicationListSettingsMetabox;
use WPDesk\ShopMagic\Admin\CommunicationList\FormShortcodeMetabox;
use WPDesk\ShopMagic\Admin\Form\Fields\PostAjaxSelect;
use WPDesk\ShopMagic\Admin\MarketingLists\SubscribersTransport\Process;
use WPDesk\ShopMagic\Admin\Outcome\TableList;
use WPDesk\ShopMagic\Admin\SelectAjaxField\AutomationSelectAjax;
use WPDesk\ShopMagic\Admin\SelectAjaxField\CustomerSelectAjax;
use WPDesk\ShopMagic\Admin\Settings\Settings;
use WPDesk\ShopMagic\Admin\TableList\AdminPage;
use WPDesk\ShopMagic\Admin\Welcome\Welcome;
use WPDesk\ShopMagic\Automation\AutomationDuplicator;
use WPDesk\ShopMagic\Automation\AutomationElementLists;
use WPDesk\ShopMagic\Automation\AutomationFactory;
use WPDesk\ShopMagic\Automation\AutomationPostType;
use WPDesk\ShopMagic\AutomationOutcome\OutcomeTable;
use WPDesk\ShopMagic\CommunicationList\CommunicationListPostType;
use WPDesk\ShopMagic\Event\EventFactory2;
use WPDesk\ShopMagic\Filter\FilterFactory2;
use WPDesk\ShopMagic\FormIntegration;
use WPDesk\ShopMagic\Helper\TemplateResolver;
use WPDesk\ShopMagic\Integration\AdFreePlugins;
use WPDesk\ShopMagic\MarketingLists\DAO\ListFactory;
use WPDesk\ShopMagic\MarketingLists\DAO\ListTable;
use WPDesk\ShopMagic\Placeholder\PlaceholderFactory2;
use WPDesk\ShopMagic\Recipe\RecipeAjaxImport;
use WPDesk\ShopMagic\Recipe\RecipeProvider;

/**
 * Admin ShopMagic Front Manager.
 *
 * @package WPDesk\ShopMagic\Admin
 */
final class Admin {

	/** @var string */
	private $plugin_url;

	/** @var EventFactory2 */
	private $event_factory;

	/** @var FilterFactory2 */
	private $filter_factory;

	/** @var ActionFactory2 */
	private $action_factory;

	/** @var PlaceholderFactory2 */
	private $placeholder_factory;

	/** @var AutomationFactory */
	private $automation_factory;

	/** @var FormIntegration */
	private $form_integration;

	/** @var bool */
	private $is_pro_active;

	/** @var OutcomeTable */
	private $outcome_table;

	public function __construct(
			string $plugin_url,
			EventFactory2 $event_factory,
			FilterFactory2 $filter_factory,
			ActionFactory2 $action_factory,
			PlaceholderFactory2 $placeholder_factory,
			AutomationFactory $automation_factory,
			FormIntegration $form_integration,
			OutcomeTable $outcome_table,
			bool $is_pro_active
	) {
		$this->plugin_url          = $plugin_url;
		$this->event_factory       = $event_factory;
		$this->filter_factory      = $filter_factory;
		$this->action_factory      = $action_factory;
		$this->placeholder_factory = $placeholder_factory;
		$this->automation_factory  = $automation_factory;
		$this->form_integration    = $form_integration;
		$this->outcome_table       = $outcome_table;
		$this->is_pro_active       = $is_pro_active;
	}

	private function we_need_styles(): bool {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		// Check on frontend.
		if ( ( isset( $_GET['post_type'] ) && $_GET['post_type'] === 'shopmagic_automation' ) ) {
			return true;
		}
		// Check on admin pages.
		if ( is_admin() && isset( $_GET['post'] ) ) {
			$post = get_post( absint( wp_unslash( $_GET['post'] ) ) );
			if ( ! is_null( $post ) && $post->post_type === 'shopmagic_automation' ) {
				return true;
			}
		}
		return false;
		// phpcs:enable
	}

	/**
	 * @return void
	 */
	private function clear_other_styles() {
		// Compatibility with special-occasion-reminder plugin.
		if ( class_exists( \SOR_Public::class ) ) {
			add_action(
				'admin_enqueue_scripts',
				function () {
					$styles = wp_styles();
					$styles->remove( 'bootstrap4' );
				},
				100
			);
		}
	}

	/**
	 * @return void
	 */
	public function hooks() {
		$need_styles = $this->we_need_styles();
		if ( $need_styles && ! class_exists( \WC_Admin_Assets::class, false ) ) {
			class_exists( \WC_Admin_Assets::class );
		}
		if ( $need_styles ) {
			$this->clear_other_styles();
		}

		add_action(
			'admin_init',
			function () {
				$renderer = new SimplePhpRenderer( TemplateResolver::for_admin_metabox() );
				foreach ( $this->get_metaboxes() as $metabox ) {
					$metabox->initialize( $renderer );
				}
				( new CommunicationListSettingsMetabox() )->hooks();

				( new PostAjaxSelect() )
					->set_name( 'list_id' )
					->hooks();

				AutomationSelectAjax::hooks();
				CustomerSelectAjax::hooks();
			}
		);

		( new Queue\CancelQueueAction() )->hooks();
		( new Queue\ListMenu() )->hooks();

		add_action(
			'admin_menu',
			function () {
				foreach ( $this->get_admin_pages() as $admin_page ) {
					$admin_page->register();
				}
			}
		);

		( new Process( new ListTable( new ListFactory() ) ) )->hooks();

		( new Outcome\SingleOutcome() )->hooks();

		( new Guest\ListMenu() )->hooks();
		( new Guest\SingleGuest() )->hooks();

		( new Automation\ManualActionsConfirmPage( $this->automation_factory ) )->hooks();
		( new ActionDuplicate( new AutomationDuplicator() ) )->hooks();
		( new ActionExport() )->hooks();
		( new BulkActionExport() )->hooks();

		( new Settings() )->hooks();
		( new Welcome( $this->is_pro_active ) )->hooks();
		( new PlaceholderDialog( $this->placeholder_factory ) )->hooks();
		( new AdFreePlugins() )->hooks();

		add_action( 'shopmagic/core/initialized/v2', [ $this, 'prepare_recipe_dependencies' ], 9999 );

		add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts' ] );
		add_action( 'admin_print_footer_scripts', [ $this, 'admin_tinymce' ] );

		add_filter( 'mce_external_plugins', [ $this, 'setup_tinymce_plugin' ] );
		add_filter( 'mce_buttons', [ $this, 'add_tinymce_toolbar_button' ] );
		add_filter( 'script_loader_tag', [ $this, 'load_js_as_module' ], 10, 2 );
	}

	/** @return AdminPage[] */
	private function get_admin_pages(): array {
		$renderer = new SimplePhpRenderer( TemplateResolver::for_admin() );

		return [
			new Outcome\ListPage( new TableList( $this->outcome_table ) ),
			new MarketingLists\ListPage( new MarketingLists\TableList( new ListTable( new ListFactory() ) ) ),
			new MarketingLists\SubscribersTransport\Page( $renderer ),
		];
	}

	/**
	 * Prepare dependencies after all addon plugins are hooked to get the actual list of elements.
	 *
	 * @return void
	 */
	public function prepare_recipe_dependencies() {
		$dependencies = new AutomationElementLists(
			array_keys( $this->event_factory->get_event_list() ),
			array_keys( $this->filter_factory->get_filter_list() ),
			array_keys( $this->action_factory->get_action_list() ),
			$this->placeholder_factory->get_possible_placeholder_slugs() // @phpstan-ignore-line
		);
		$provider     = new RecipeProvider( __DIR__ . '/recipes', $dependencies );
		( new RecipesTab( $provider ) )->hooks();
		( new RecipeAjaxImport( $dependencies ) )->hooks();
	}

	/**
	 * Includes admin scripts in admin area
	 *
	 * @return void
	 */
	public function admin_scripts() {
		wp_register_style(
			'shopmagic-admin',
			SHOPMAGIC_PLUGIN_URL . 'assets/css/admin-style.css',
			[],
			SHOPMAGIC_VERSION
		);

		if ( $this->should_enqueue_scripts() ) {
			wp_enqueue_style( 'shopmagic-admin' );
			wp_enqueue_style( 'woocommerce_admin_styles' );
			wp_enqueue_style( 'wp-jquery-ui-dialog' );
			wp_enqueue_script( 'jquery-ui-progressbar' );

			wp_register_script(
				'shopmagic-admin-handler',
				SHOPMAGIC_PLUGIN_URL . 'assets/js/admin-handler.min.js',
				[
					'jquery',
					'jquery-blockui',
					'jquery-ui-datepicker',
					'jquery-ui-tabs',
					'wc-admin-meta-boxes',
					'wc-backbone-modal',
					'wp-util',
					'wp-i18n',
					'jquery-ui-dialog',
				],
				SHOPMAGIC_VERSION,
				false
			);

			wp_localize_script(
				'shopmagic-admin-handler',
				'ShopMagic',
				[
					'ajaxurl'           => admin_url( 'admin-ajax.php' ),
					'paramProcessNonce' => wp_create_nonce( 'shopmagic-ajax-process-nonce' ),
					'importNonce'       => wp_create_nonce( 'shopmagic-automation-import' ),
					'supportedMimes'    => AbstractSendMailAction::SUPPORTED_MIMES,
				]
			);

			wp_set_script_translations( 'shopmagic-admin-handler', 'shopmagic-for-woocommerce' );
			wp_enqueue_script( 'shopmagic-admin-handler' );

			wp_enqueue_media();
			wp_enqueue_editor();
		}
	}

	/** @return void */
	public function admin_tinymce() {
		if ( $this->should_enqueue_scripts() ) { ?>
			<div style="display: none"><?php wp_editor( '', 'shopmagic_editor' ); ?></div>
			<?php
		}
	}


	/**
	 * Includes additional TinyMCE plugin, which is not shipped with WP
	 *
	 * @param string[] $plugins array of plugins
	 *
	 * @return string[] array of plugins
	 */
	public function setup_tinymce_plugin( array $plugins ): array {
		$plugins['imgalign'] = $this->plugin_url . '/assets/js/tinymce/imgalign/plugin.js';

		return $plugins;
	}

	/**
	 * Adds a button to the TinyMCE / Visual Editor which the user can click
	 * to insert a link with a custom CSS class.
	 *
	 * @param string[] $buttons Array of registered TinyMCE Buttons
	 *
	 * @return string[] Modified array of registered TinyMCE Buttons
	 */
	public function add_tinymce_toolbar_button( array $buttons ): array {
		array_push( $buttons, '|', 'imgalign' );

		return $buttons;
	}

	/** @return Metabox[] */
	private function get_metaboxes(): array {
		return [
			new EventMetabox( $this->event_factory, $this->filter_factory, $this->placeholder_factory, $this->form_integration ),
			new FilterMetabox( $this->filter_factory, $this->form_integration ),
			new ActionMetabox( $this->action_factory, $this->form_integration ),
			new ManualActionsMetabox(),
			new PlaceholdersMetabox(),
			new FormShortcodeMetabox(),
		];
	}

	/** @internal */
	public function load_js_as_module( string $tag, string $handle ): string {
		if ( 'shopmagic-admin-handler' === $handle ) {
			return (string) preg_replace( '/<script /', '<script type="module" ', $tag );
		}

		return $tag;
	}

	private function should_enqueue_scripts(): bool {
		$current_screen = get_current_screen();
		if ( ! $current_screen instanceof \WP_Screen ) {
			return false;
		}

		if ( $current_screen->id === 'dashboard_page_manual-action-confirm' ) {
			return true;
		}

		if ( in_array(
			$current_screen->post_type,
			[
				AutomationPostType::TYPE,
				CommunicationListPostType::TYPE,
			],
			true
		) ) {
			return true;
		}

		return false;
	}
}
