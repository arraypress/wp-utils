<?php
/**
 * Base Component Class
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Elements\Components;

use ArrayPress\Utils\Elements\Field;

/**
 * Base Class
 *
 * Abstract base class providing essential methods for managing component assets and establishing
 * foundational functionality. This class serves as the parent for form components, ensuring
 * consistent asset loading and standardized behavior across the component hierarchy while
 * adhering to WordPress coding standards.
 */
abstract class Base {

	/**
	 * Ensure required assets are loaded
	 */
	protected static function ensure_assets(): void {
		Field::ensure_assets();
	}

	/**
	 * Ensure only styles are loaded
	 */
	protected static function ensure_styles(): void {
		Field::ensure_styles();
	}

	/**
	 * Ensure only scripts are loaded
	 */
	protected static function ensure_scripts(): void {
		Field::ensure_scripts();
	}

}