<?php
/**
 * Taxonomy Query Builder for WordPress
 *
 * This class provides a fluent interface for constructing taxonomy queries in WordPress.
 * It supports both simple static queries and more complex chainable queries, allowing
 * developers to easily filter content based on taxonomy terms using various operators
 * such as IN, NOT IN, AND, EXISTS, and NOT EXISTS.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Query;

/**
 * Check if the class `Tax` is defined, and if not, define it.
 */
if ( ! class_exists( 'Tax' ) ) :

	/**
	 * Tax Query Helper Class
	 *
	 * This class provides a set of methods to easily construct taxonomy queries for WordPress.
	 * It supports both simple static queries and more complex chainable queries.
	 */
	class Tax {

		/** @var array The constructed taxonomy query */
		private array $query = [];

		/**
		 * Create a new TaxQuery instance.
		 *
		 * @return self
		 */
		public static function new(): self {
			return new self();
		}

		/**
		 * Add a taxonomy query condition.
		 *
		 * @param string $taxonomy         The taxonomy name.
		 * @param mixed  $terms            The term(s) to query for (ID, slug, or name).
		 * @param string $field            The field to match terms against (term_id, slug, or name). Default is 'term_id'.
		 * @param string $operator         The operator to test (IN, NOT IN, AND, EXISTS, NOT EXISTS). Default is 'IN'.
		 * @param bool   $include_children Whether to include child terms. Default is true.
		 *
		 * @return self
		 */
		public function add( string $taxonomy, $terms, string $field = 'term_id', string $operator = 'IN', bool $include_children = true ): self {
			$this->query[] = [
				'taxonomy'         => $taxonomy,
				'terms'            => $terms,
				'field'            => $field,
				'operator'         => strtoupper( $operator ),
				'include_children' => $include_children,
			];

			return $this;
		}

		/**
		 * Add an 'IN' condition for a taxonomy.
		 *
		 * @param string $taxonomy The taxonomy name.
		 * @param mixed  $terms    The term(s) to include.
		 * @param string $field    The field to match terms against (term_id, slug, or name). Default is 'term_id'.
		 *
		 * @return self
		 */
		public function in( string $taxonomy, $terms, string $field = 'term_id' ): self {
			return $this->add( $taxonomy, $terms, $field, 'IN' );
		}

		/**
		 * Add a 'NOT IN' condition for a taxonomy.
		 *
		 * @param string $taxonomy The taxonomy name.
		 * @param mixed  $terms    The term(s) to exclude.
		 * @param string $field    The field to match terms against (term_id, slug, or name). Default is 'term_id'.
		 *
		 * @return self
		 */
		public function notIn( string $taxonomy, $terms, string $field = 'term_id' ): self {
			return $this->add( $taxonomy, $terms, $field, 'NOT IN' );
		}

		/**
		 * Add an 'AND' condition for a taxonomy.
		 *
		 * @param string $taxonomy The taxonomy name.
		 * @param mixed  $terms    The term(s) that must all be present.
		 * @param string $field    The field to match terms against (term_id, slug, or name). Default is 'term_id'.
		 *
		 * @return self
		 */
		public function and( string $taxonomy, $terms, string $field = 'term_id' ): self {
			return $this->add( $taxonomy, $terms, $field, 'AND' );
		}

		/**
		 * Add an 'EXISTS' condition for a taxonomy.
		 *
		 * @param string $taxonomy The taxonomy name.
		 *
		 * @return self
		 */
		public function exists( string $taxonomy ): self {
			return $this->add( $taxonomy, [], 'term_id', 'EXISTS' );
		}

		/**
		 * Add a 'NOT EXISTS' condition for a taxonomy.
		 *
		 * @param string $taxonomy The taxonomy name.
		 *
		 * @return self
		 */
		public function notExists( string $taxonomy ): self {
			return $this->add( $taxonomy, [], 'term_id', 'NOT EXISTS' );
		}

		/**
		 * Set the relation between taxonomy queries.
		 *
		 * @param string $relation The relation between queries ('AND' or 'OR').
		 *
		 * @return self
		 */
		public function relation( string $relation ): self {
			$this->query['relation'] = strtoupper( $relation );

			return $this;
		}

		/**
		 * Get the constructed taxonomy query array.
		 *
		 * @return array
		 */
		public function get(): array {
			if ( count( $this->query ) > 1 && ! isset( $this->query['relation'] ) ) {
				$this->query['relation'] = 'AND';
			}

			return $this->query;
		}

		/**
		 * Static method to create a simple 'IN' taxonomy query.
		 *
		 * @param string $taxonomy The taxonomy name.
		 * @param mixed  $terms    The term(s) to include.
		 * @param string $field    The field to match terms against (term_id, slug, or name). Default is 'term_id'.
		 *
		 * @return array The taxonomy query array.
		 */
		public static function simpleIn( string $taxonomy, $terms, string $field = 'term_id' ): array {
			return ( new self() )->in( $taxonomy, $terms, $field )->get();
		}

		/**
		 * Static method to create a simple 'NOT IN' taxonomy query.
		 *
		 * @param string $taxonomy The taxonomy name.
		 * @param mixed  $terms    The term(s) to exclude.
		 * @param string $field    The field to match terms against (term_id, slug, or name). Default is 'term_id'.
		 *
		 * @return array The taxonomy query array.
		 */
		public static function simpleNotIn( string $taxonomy, $terms, string $field = 'term_id' ): array {
			return ( new self() )->notIn( $taxonomy, $terms, $field )->get();
		}

		/**
		 * Static method to create a simple 'AND' taxonomy query.
		 *
		 * @param string $taxonomy The taxonomy name.
		 * @param mixed  $terms    The term(s) that must all be present.
		 * @param string $field    The field to match terms against (term_id, slug, or name). Default is 'term_id'.
		 *
		 * @return array The taxonomy query array.
		 */
		public static function simpleAnd( string $taxonomy, $terms, string $field = 'term_id' ): array {
			return ( new self() )->and( $taxonomy, $terms, $field )->get();
		}

		/**
		 * Static method to create a simple 'EXISTS' taxonomy query.
		 *
		 * @param string $taxonomy The taxonomy name.
		 *
		 * @return array The taxonomy query array.
		 */
		public static function simpleExists( string $taxonomy ): array {
			return ( new self() )->exists( $taxonomy )->get();
		}

		/**
		 * Static method to create a simple 'NOT EXISTS' taxonomy query.
		 *
		 * @param string $taxonomy The taxonomy name.
		 *
		 * @return array The taxonomy query array.
		 */
		public static function simpleNotExists( string $taxonomy ): array {
			return ( new self() )->notExists( $taxonomy )->get();
		}

	}
endif;