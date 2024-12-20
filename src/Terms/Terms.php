<?php
/**
 * Terms Utility Class for WordPress
 *
 * This class provides a comprehensive set of utility functions for working with multiple WordPress terms.
 * It offers methods for searching, retrieving, analyzing, and modifying terms across taxonomies.
 * The class is designed to simplify common term-related operations and extend WordPress's built-in term functionality.
 *
 * Key features include:
 * - Term searching and retrieval based on various identifiers
 * - Related terms analysis
 * - Unused terms detection
 * - Term merging and bulk operations
 * - Term field extraction and manipulation
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Terms;

use ArrayPress\Utils\Traits\Terms\{
	Analysis,
	Conditional,
	Core,
	ObjectRelations,
	Query,
	Sanitize,
	Utility
};

/**
 * Class Terms
 *
 * Utility functions for working with multiple terms.
 */
class Terms {
	use Analysis;
	use Conditional;
	use Core;
	use ObjectRelations;
	use Query;
	use Sanitize;
	use Utility;
}