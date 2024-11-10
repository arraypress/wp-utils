<?php
/**
 * Term Utility Class for WordPress
 *
 * This class provides a comprehensive set of utility functions for working with individual WordPress terms.
 * It offers methods for navigating term hierarchies, retrieving related terms, and analyzing term relationships.
 * The class is designed to simplify common term-related operations and extend WordPress's built-in term functionality.
 *
 * Key features include:
 * - Hierarchical term navigation (parents, children, siblings, cousins)
 * - Term relationship analysis (descendants, ancestors)
 * - Term path and depth calculation
 * - Utility methods for term validation and information retrieval
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Terms;

use ArrayPress\Utils\Traits\Term\Core;
use ArrayPress\Utils\Traits\Term\Fields;
use ArrayPress\Utils\Traits\Term\Hierarchy;
use ArrayPress\Utils\Traits\Term\Analysis;
use ArrayPress\Utils\Traits\Term\Conditional;
use ArrayPress\Utils\Traits\Term\Utility;
use ArrayPress\Utils\Traits\Shared\Meta;

/**
 * Class Term
 *
 * Utility functions for working with a specific Term.
 */
class Term {
	use Core;
	use Fields;
	use Hierarchy;
	use Analysis;
	use Conditional;
	use Utility;
	use Meta;

	/**
	 * Get the meta type for this class.
	 *
	 * @return string
	 */
	protected static function get_meta_type(): string {
		return 'term';
	}
}