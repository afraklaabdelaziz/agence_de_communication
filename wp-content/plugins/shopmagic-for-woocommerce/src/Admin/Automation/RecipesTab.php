<?php


namespace WPDesk\ShopMagic\Admin\Automation;

use ShopMagicVendor\WPDesk\View\Renderer\SimplePhpRenderer;
use ShopMagicVendor\WPDesk\View\Resolver\DirResolver;
use WPDesk\ShopMagic\Recipe\RecipeProvider;

class RecipesTab {

	/** @var RecipeProvider */
	private $recipe_provider;

	public function __construct( RecipeProvider $provider ) {
		$this->recipe_provider = $provider;
	}

	public function hooks() {
		add_action( 'wp_ajax_shopmagic_recipes_tab', [ $this, 'render_tab' ] );
		add_action( 'wp_ajax_shopmagic_brew_recipe', [ $this, 'brew_recipe' ] );
		add_action( 'admin_notices', [ $this, 'shopmagic_admin_notice_recipes' ] );
	}

	/**
	 * @internal
	 */
	public function render_tab() {
		$renderer = new SimplePhpRenderer( new DirResolver( __DIR__ . DIRECTORY_SEPARATOR . 'templates' ) );
		echo $renderer->render( 'recipes_tab', [ 'recipes' => ( $this->recipe_provider )->get_recipes() ] );
		wp_die();
	}

	/**
	 * @internal
	 */
	public function brew_recipe() {
		if ( current_user_can( 'manage_options' ) ) {
			// escape ID for security reasons.
			$id_map = explode( '/', $_GET['recipe'] );
			$id_map = array_slice( array_map( 'sanitize_file_name', $id_map ), 0, 2 );
			$id     = implode( '/', $id_map );

			$recipe  = $this->recipe_provider->get_recipe( $id );
			$post_id = $recipe->import();
			wp_redirect( get_edit_post_link( $post_id, 'not-for-display-context' ) );
			exit();
		}
	}

	/**
	 * Display recipes admin notice
	 */
	public function shopmagic_admin_notice_recipes() {

		global $current_user;
		$user_id     = $current_user->ID;
		$notice_name = 'recipes';
		$screen      = get_current_screen();

		if ( $screen && $screen->post_type === 'shopmagic_automation' && $screen->action === 'add' && ! get_user_meta( $user_id, 'shopmagic_ignore_notice_' . $notice_name ) ) {

			echo '<div class="notice notice-info is-dismissible shopmagic-recipes-notice" data-notice-name="' . $notice_name . '"><h2>' . esc_html__( 'Recipes', 'shopmagic-for-woocommerce' ) . '</h2><p>' . esc_html__( 'Recipes are a great way to start with ShopMagic. Browse ready-to-use follow-up strategies, best email texts and save a lot of time!', 'shopmagic-for-woocommerce' ) . '</p><a class="button button-primary" href="' . admin_url( 'edit.php?post_type=shopmagic_automation#recipes_tab' ) . '" target="_blank">' . esc_html__( 'Browse recipes', 'shopmagic-for-woocommerce' ) . '</a><br><br></div>';
		}
	}
}
