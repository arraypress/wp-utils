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
 * @version       1.0.0
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Query;

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

		/** @var string The order direction */
		private string $order = 'DESC';

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
		public function field( string $field, string $order = 'DESC' ): self {
			$this->orderby[ $field ] = strtoupper( $order );

			return $this;
		}

		/**
		 * Order by post date.
		 *
		 * @param string $order The order direction ('ASC' or 'DESC').
		 *
		 * @return self
		 */
		public function date( string $order = 'DESC' ): self {
			return $this->field( 'date', $order );
		}

		/**
		 * Order by post modified date.
		 *
		 * @param string $order The order direction ('ASC' or 'DESC').
		 *
		 * @return self
		 */
		public function modified( string $order = 'DESC' ): self {
			return $this->field( 'modified', $order );
		}

		/**
		 * Order by post title.
		 *
		 * @param string $order The order direction ('ASC' or 'DESC').
		 *
		 * @return self
		 */
		public function title( string $order = 'ASC' ): self {
			return $this->field( 'title', $order );
		}

		/**
		 * Order by post name (slug).
		 *
		 * @param string $order The order direction ('ASC' or 'DESC').
		 *
		 * @return self
		 */
		public function name( string $order = 'ASC' ): self {
			return $this->field( 'name', $order );
		}

		/**
		 * Order by post type.
		 *
		 * @param string $order The order direction ('ASC' or 'DESC').
		 *
		 * @return self
		 */
		public function type( string $order = 'ASC' ): self {
			return $this->field( 'type', $order );
		}

		/**
		 * Order by post author.
		 *
		 * @param string $order The order direction ('ASC' or 'DESC').
		 *
		 * @return self
		 */
		public function author( string $order = 'ASC' ): self {
			return $this->field( 'author', $order );
		}

		/**
		 * Order by post ID.
		 *
		 * @param string $order The order direction ('ASC' or 'DESC').
		 *
		 * @return self
		 */
		public function id( string $order = 'DESC' ): self {
			return $this->field( 'ID', $order );
		}

		/**
		 * Order by parent ID.
		 *
		 * @param string $order The order direction ('ASC' or 'DESC').
		 *
		 * @return self
		 */
		public function parent( string $order = 'ASC' ): self {
			return $this->field( 'parent', $order );
		}

		/**
		 * Order by comment count.
		 *
		 * @param string $order The order direction ('ASC' or 'DESC').
		 *
		 * @return self
		 */
		public function commentCount( string $order = 'DESC' ): self {
			return $this->field( 'comment_count', $order );
		}

		/**
		 * Order by menu order.
		 *
		 * @param string $order The order direction ('ASC' or 'DESC').
		 *
		 * @return self
		 */
		public function menu( string $order = 'ASC' ): self {
			return $this->field( 'menu_order', $order );
		}

		/**
		 * Order by a meta value.
		 *
		 * @param string $meta_key The meta key to order by.
		 * @param string $order    The order direction ('ASC' or 'DESC').
		 * @param string $type     The meta value type (default: 'CHAR').
		 *
		 * @return self
		 */
		public function meta( string $meta_key, string $order = 'DESC', string $type = 'CHAR' ): self {
			$this->orderby['meta_value'] = strtoupper( $order );
			$this->orderby['meta_key']   = $meta_key;
			$this->orderby['meta_type']  = $type;

			return $this;
		}

		/**
		 * Order by a meta value numerically.
		 *
		 * @param string $meta_key The meta key to order by.
		 * @param string $order    The order direction ('ASC' or 'DESC').
		 *
		 * @return self
		 */
		public function metaNum( string $meta_key, string $order = 'DESC' ): self {
			return $this->meta( $meta_key, $order, 'NUMERIC' );
		}

		/**
		 * Order posts randomly.
		 *
		 * @return self
		 */
		public function rand(): self {
			$this->orderby = 'rand';

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
			$this->order = strtoupper( $order );

			return $this;
		}

		/**
		 * Get the constructed orderby query array.
		 *
		 * @return array
		 */
		public function get(): array {
			$query = [ 'orderby' => $this->orderby, 'order' => $this->order ];

			// Handle special case for random ordering
			if ( $this->orderby === 'rand' ) {
				$query['orderby'] = 'rand';
				unset( $query['order'] );
			}

			return $query;
		}

		/**
		 * Static method to create a simple orderby query.
		 *
		 * @param string $field The field to order by.
		 * @param string $order The order direction ('ASC' or 'DESC').
		 *
		 * @return array
		 */
		public static function simple( string $field, string $order = 'DESC' ): array {
			return ( new self() )->field( $field, $order )->get();
		}

		/**
		 * Static method to create a simple meta orderby query.
		 *
		 * @param string $meta_key The meta key to order by.
		 * @param string $order    The order direction ('ASC' or 'DESC').
		 * @param string $type     The meta value type (default: 'CHAR').
		 *
		 * @return array
		 */
		public static function simpleMeta( string $meta_key, string $order = 'DESC', string $type = 'CHAR' ): array {
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