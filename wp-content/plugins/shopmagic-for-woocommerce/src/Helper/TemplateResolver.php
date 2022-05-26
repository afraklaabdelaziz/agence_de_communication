<?php
declare(strict_types=1);

namespace WPDesk\ShopMagic\Helper;

use DomainException;
use ShopMagicVendor\WPDesk\Forms\Resolver\DefaultFormFieldResolver;
use ShopMagicVendor\WPDesk\View\Resolver\ChainResolver;
use ShopMagicVendor\WPDesk\View\Resolver\DirResolver;
use ShopMagicVendor\WPDesk\View\Resolver\Resolver;
use ShopMagicVendor\WPDesk\View\Resolver\WPThemeResolver;

/**
 * Static factory method wrapper for easier use of templates across app and extensions.
 */
final class TemplateResolver extends ChainResolver {
	const THEME_DIR = 'shopmagic';

	/** @var ?string */
	private static $root_path;

	/** @return void */
	public static function set_root_path( string $root_path ) {
		self::$root_path = $root_path;
	}

	public static function for_admin( string $path = '' ): Resolver {
		if ( empty( self::$root_path ) ) {
			throw new DomainException( 'Template root path not set!' );
		}

		$admin_root_path = self::$root_path . DIRECTORY_SEPARATOR . 'src/Admin/';

		return new self(
			new DirResolver( $admin_root_path . 'templates' . $path ),
			new DirResolver( $admin_root_path . 'Automation/AutomationFormFields/field-templates' ),
			new DirResolver( $admin_root_path . 'SelectAjaxField/templates' ),
			new DirResolver( $admin_root_path . 'Form/templates' ),
			new DefaultFormFieldResolver()
		);
	}

	public static function for_admin_metabox(): Resolver {
		if ( empty( self::$root_path ) ) {
			throw new DomainException( 'Template root path not set!' );
		}

		return new self(
			new DirResolver( self::$root_path . DIRECTORY_SEPARATOR . 'src/Admin/Automation/Metabox/templates' ),
			new DirResolver( self::$root_path . DIRECTORY_SEPARATOR . 'src/Admin/templates/metabox' )
		);
	}

	public static function for_placeholder( string $subdir = '' ): Resolver {
		return self::for_public( 'placeholder' . DIRECTORY_SEPARATOR . $subdir );
	}

	public static function for_public( string $relative_path = '' ): Resolver {
		if ( empty( self::$root_path ) ) {
			throw new DomainException( 'Template root path not set!' );
		}

		return new self(
			new WPThemeResolver( self::THEME_DIR . DIRECTORY_SEPARATOR . $relative_path ),
			new WPThemeResolver( self::THEME_DIR ), // backward compatibility.
			new DirResolver( self::$root_path . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $relative_path )
		);
	}

}
