<?php


namespace WPDesk\ShopMagic\Recipe;

use WPDesk\ShopMagic\Automation\AutomationElementLists;

final class RecipeProvider {

	/** @var string */
	private $dir;

	/** @var AutomationElementLists */
	private $dependencies;

	public function __construct( string $dir, AutomationElementLists $dependencies ) {
		$this->dir          = $dir;
		$this->dependencies = $dependencies;
	}

	public function get_recipe( string $id ): Recipe {
		$full_filename = $this->dir . '/' . $id;

		return new Recipe(
			json_decode( (string) file_get_contents( $full_filename ), true ),
			$id,
			$this->dependencies
		);
	}

	private function get_locale(): string {
		$locale = get_locale();
		if ( ! file_exists( $this->dir . '/' . $locale ) ) {
			$default_locale = 'en_US';
			$locale         = $default_locale;
		}

		return $locale;
	}

	/** @return \Generator<Recipe> */
	public function get_recipes(): \Generator {
		$locale       = $this->get_locale();
		$lang_path    = $this->dir . '/' . $locale;
		$recipe_files = (array) scandir( $lang_path );
		sort( $recipe_files, SORT_NATURAL );
		foreach ( $recipe_files as $filename ) {
			$full_filename = $lang_path . '/' . $filename;
			if ( is_file( $full_filename ) ) {
				yield $this->get_recipe( $locale . '/' . $filename );
			}
		}
	}
}
