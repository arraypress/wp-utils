<?php
/**
 * Orderby Query Builder for WordPress
 *
 * This class provides a fluent interface for constructing orderby clauses in WordPress queries.
 * It supports both simple static queries and more complex chainable queries, allowing
 * developers to easily define sorting criteria based on various fields including post
 * properties, meta values, and random ordering.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.1
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Database\Query;

/**
 * Check if the class `Orderby` is defined, and if not, define it.
 */
if ( ! class_exists( 'Orderby' ) ) :

	/**
	 * Orderby Query Helper Class
	 *
	 * This class provides a set of methods to easily construct orderby clauses for WordPress queries.
	 * It supports both simple static queries and more complex chainable queries.
	 */
	class Orderby {

		/** @var array The constructed orderby query */
		private array $orderby = [];

		/** @var string The default order direction */
		private string $defaultOrder = 'DESC';

		/**
		 * Create a new OrderbyQuery instance.
		 *
		 * @return self
		 */
		public static function new(): self {
			return new self();
		}

		/**
		 * Add a field to order by.
		 *
		 * @param string $field The field to order by.
		 * @param string $order The order direction ('ASC' or 'DESC').
		 *
		 * @return self
		 */
		public function field( string $field, ?string $order = null ): self {
			$this->orderby[ $field ] = $order ?? $this->defaultOrder;

			return $this;
		}

		/**
		 * Order by post date.
		 *
		 * @param string|null $order The order direction ('ASC' or 'DESC').
		 *
		 * @return self
		 */
		public function date( ?string $order = null ): self {
			return $this->field( 'date', $order );
		}

		/**
		 * Order by post modified date.
		 *
		 * @param string|null $order The order direction ('ASC' or 'DESC').
		 *
		 * @return self
		 */
		public function modified( ?string $order = null ): self {
			return $this->field( 'modified', $order );
		}

		/**
		 * Order by post title.
		 *
		 * @param string|null $order The order direction ('ASC' or 'DESC').
		 *
		 * @return self
		 */
		public function title( ?string $order = null ): self {
			return $this->field( 'title', $order ?? 'ASC' );
		}

		/**
		 * Order by post name (slug).
		 *
		 * @param string|null $order The order direction ('ASC' or 'DESC').
		 *
		 * @return self
		 */
		public function name( ?string $order = null ): self {
			return $this->field( 'name', $order ?? 'ASC' );
		}

		/**
		 * Order by post type.
		 *
		 * @param string|null $order The order direction ('ASC' or 'DESC').
		 *
		 * @return self
		 */
		public function type( ?string $order = null ): self {
			return $this->field( 'type', $order ?? 'ASC' );
		}

		/**
		 * Order by post author.
		 *
		 * @param string|null $order The order direction ('ASC' or 'DESC').
		 *
		 * @return self
		 */
		public function author( ?string $order = null ): self {
			return $this->field( 'author', $order ?? 'ASC' );
		}

		/**
		 * Order by post ID.
		 *
		 * @param string|null $order The order direction ('ASC' or 'DESC').
		 *
		 * @return self
		 */
		public function id( ?string $order = null ): self {
			return $this->field( 'ID', $order );
		}

		/**
		 * Order by parent ID.
		 *
		 * @param string|null $order The order direction ('ASC' or 'DESC').
		 *
		 * @return self
		 */
		public function parent( ?string $order = null ): self {
			return $this->field( 'parent', $order ?? 'ASC' );
		}

		/**
		 * Order by comment count.
		 *
		 * @param string|null $order The order direction ('ASC' or 'DESC').
		 *
		 * @return self
		 */
		public function commentCount( ?string $order = null ): self {
			return $this->field( 'comment_count', $order );
		}

		/**
		 * Order by menu order.
		 *
		 * @param string|null $order The order direction ('ASC' or 'DESC').
		 *
		 * @return self
		 */
		public function menu( ?string $order = null ): self {
			return $this->field( 'menu_order', $order ?? 'ASC' );
		}

		/**
		 * Order by a meta value.
		 *
		 * @param string      $meta_key The meta key to order by.
		 * @param string|null $order    The order direction ('ASC' or 'DESC').
		 * @param string      $type     The meta value type (default: 'CHAR').
		 *
		 * @return self
		 */
		public function meta( string $meta_key, ?string $order = null, string $type = 'CHAR' ): self {
			$this->orderby['meta_value'] = $order ?? $this->defaultOrder;
			$this->orderby['meta_key']   = $meta_key;
			$this->orderby['meta_type']  = $type;

			return $this;
		}

		/**
		 * Order by a meta value numerically.
		 *
		 * @param string      $meta_key The meta key to order by.
		 * @param string|null $order    The order direction ('ASC' or 'DESC').
		 *
		 * @return self
		 */
		public function metaNum( string $meta_key, ?string $order = null ): self {
			return $this->meta( $meta_key, $order, 'NUMERIC' );
		}

		/**
		 * Order posts randomly.
		 *
		 * @return self
		 */
		public function rand(): self {
			$this->orderby = [ 'rand' => '' ];

			return $this;
		}

		/**
		 * Set the global order direction.
		 *
		 * @param string $order The order direction ('ASC' or 'DESC').
		 *
		 * @return self
		 */
		public function order( string $order ): self {
			$this->defaultOrder = strtoupper( $order );

			return $this;
		}

		/**
		 * Get the constructed orderby query array.
		 *
		 * @return array
		 */
		public function get(): array {
			$query = [ 'orderby' => $this->orderby ];

			// Add the default order if it's not a random order and no specific order was set
			if ( ! isset( $this->orderby['rand'] ) && count( $this->orderby ) === 1 ) {
				$query['order'] = $this->defaultOrder;
			}

			return $query;
		}

		/**
		 * Static method to create a simple orderby query.
		 *
		 * @param string      $field The field to order by.
		 * @param string|null $order The order direction ('ASC' or 'DESC').
		 *
		 * @return array
		 */
		public static function simple( string $field, ?string $order = null ): array {
			return ( new self() )->field( $field, $order )->get();
		}

		/**
		 * Static method to create a simple meta orderby query.
		 *
		 * @param string      $meta_key The meta key to order by.
		 * @param string|null $order    The order direction ('ASC' or 'DESC').
		 * @param string      $type     The meta value type (default: 'CHAR').
		 *
		 * @return array
		 */
		public static function simpleMeta( string $meta_key, ?string $order = null, string $type = 'CHAR' ): array {
			return ( new self() )->meta( $meta_key, $order, $type )->get();
		}

		/**
		 * Static method to create a random order query.
		 *
		 * @return array
		 */
		public static function simpleRandom(): array {
			return ( new self() )->rand()->get();
		}
	}
endif;