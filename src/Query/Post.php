<?php
/**
 * Post Query Builder for WordPress
 *
 * This class provides a fluent interface for constructing post queries in WordPress.
 * It supports both simple static queries and more complex chainable queries, allowing
 * developers to easily filter and retrieve posts based on various criteria such as
 * post type, status, author, taxonomy, meta data, and more.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Query;

/**
 * Check if the class `Post` is defined, and if not, define it.
 */
if ( ! class_exists( 'Post' ) ) :

	/**
	 * Post Query Helper Class
	 *
	 * This class provides a set of methods to easily construct post queries for WordPress.
	 * It supports both simple static queries and more complex chainable queries.
	 */
	class Post {

		/** @var array The constructed post query arguments */
		private array $args = [];

		/**
		 * Create a new PostQuery instance.
		 *
		 * @return self
		 */
		public static function new(): self {
			return new self();
		}

		/**
		 * Set the post type(s) for the query.
		 *
		 * @param string|array $post_types Single post type or array of post types.
		 *
		 * @return self
		 */
		public function type( $post_types ): self {
			$this->args['post_type'] = $post_types;

			return $this;
		}

		/**
		 * Set the post status(es) for the query.
		 *
		 * @param string|array $post_statuses Single post status or array of post statuses.
		 *
		 * @return self
		 */
		public function status( $post_statuses ): self {
			$this->args['post_status'] = $post_statuses;

			return $this;
		}

		/**
		 * Set the number of posts to retrieve.
		 *
		 * @param int $posts_per_page Number of posts to retrieve (-1 for all posts).
		 *
		 * @return self
		 */
		public function limit( int $posts_per_page ): self {
			$this->args['posts_per_page'] = $posts_per_page;

			return $this;
		}

		/**
		 * Set the page number for pagination.
		 *
		 * @param int $paged Page number.
		 *
		 * @return self
		 */
		public function page( int $paged ): self {
			$this->args['paged'] = $paged;

			return $this;
		}

		/**
		 * Set the offset for the query.
		 *
		 * @param int $offset Number of posts to offset.
		 *
		 * @return self
		 */
		public function offset( int $offset ): self {
			$this->args['offset'] = $offset;

			return $this;
		}

		/**
		 * Include specific post IDs in the query.
		 *
		 * @param int|array $post_ids Single post ID or array of post IDs.
		 *
		 * @return self
		 */
		public function include( $post_ids ): self {
			$this->args['post__in'] = is_array( $post_ids ) ? $post_ids : [ $post_ids ];

			return $this;
		}

		/**
		 * Exclude specific post IDs from the query.
		 *
		 * @param int|array $post_ids Single post ID or array of post IDs to exclude.
		 *
		 * @return self
		 */
		public function exclude( $post_ids ): self {
			$this->args['post__not_in'] = is_array( $post_ids ) ? $post_ids : [ $post_ids ];

			return $this;
		}

		/**
		 * Set the parent post ID(s) for the query.
		 *
		 * @param int|array $parent_ids Single parent ID or array of parent IDs.
		 *
		 * @return self
		 */
		public function parent( $parent_ids ): self {
			$this->args['post_parent__in'] = is_array( $parent_ids ) ? $parent_ids : [ $parent_ids ];

			return $this;
		}

		/**
		 * Exclude posts with specific parent ID(s) from the query.
		 *
		 * @param int|array $parent_ids Single parent ID or array of parent IDs to exclude.
		 *
		 * @return self
		 */
		public function notParent( $parent_ids ): self {
			$this->args['post_parent__not_in'] = is_array( $parent_ids ) ? $parent_ids : [ $parent_ids ];

			return $this;
		}

		/**
		 * Set the author(s) for the query.
		 *
		 * @param int|array $author_ids Single author ID or array of author IDs.
		 *
		 * @return self
		 */
		public function author( $author_ids ): self {
			$this->args['author__in'] = is_array( $author_ids ) ? $author_ids : [ $author_ids ];

			return $this;
		}

		/**
		 * Exclude posts by specific author(s) from the query.
		 *
		 * @param int|array $author_ids Single author ID or array of author IDs to exclude.
		 *
		 * @return self
		 */
		public function notAuthor( $author_ids ): self {
			$this->args['author__not_in'] = is_array( $author_ids ) ? $author_ids : [ $author_ids ];

			return $this;
		}

		/**
		 * Set the search term for the query.
		 *
		 * @param string $search_term The search term.
		 *
		 * @return self
		 */
		public function search( string $search_term ): self {
			$this->args['s'] = $search_term;

			return $this;
		}

		/**
		 * Set the orderby parameter for the query.
		 *
		 * @param string|array $orderby The field(s) to order by.
		 *
		 * @return self
		 */
		public function orderBy( $orderby ): self {
			$this->args['orderby'] = $orderby;

			return $this;
		}

		/**
		 * Set the order parameter for the query.
		 *
		 * @param string $order The order ('ASC' or 'DESC').
		 *
		 * @return self
		 */
		public function order( string $order ): self {
			$this->args['order'] = strtoupper( $order );

			return $this;
		}

		/**
		 * Add a meta query to the post query.
		 *
		 * @param string $key     The meta key.
		 * @param mixed  $value   The meta value.
		 * @param string $compare The comparison operator (default: '=').
		 * @param string $type    The type of comparison (default: 'CHAR').
		 *
		 * @return self
		 */
		public function meta( string $key, $value, string $compare = '=', string $type = 'CHAR' ): self {
			if ( ! isset( $this->args['meta_query'] ) ) {
				$this->args['meta_query'] = [];
			}
			$this->args['meta_query'][] = [
				'key'     => $key,
				'value'   => $value,
				'compare' => $compare,
				'type'    => $type,
			];

			return $this;
		}

		/**
		 * Set the meta query relation.
		 *
		 * @param string $relation The relation between meta queries ('AND' or 'OR').
		 *
		 * @return self
		 */
		public function metaRelation( string $relation ): self {
			if ( ! isset( $this->args['meta_query'] ) ) {
				$this->args['meta_query'] = [];
			}
			$this->args['meta_query']['relation'] = strtoupper( $relation );

			return $this;
		}

		/**
		 * Add a tax query to the post query.
		 *
		 * @param string       $taxonomy The taxonomy name.
		 * @param string|array $terms    The term slug(s) or ID(s).
		 * @param string       $field    The term field to use (default: 'term_id').
		 * @param string       $operator The operator to test (default: 'IN').
		 *
		 * @return self
		 */
		public function tax( string $taxonomy, $terms, string $field = 'term_id', string $operator = 'IN' ): self {
			if ( ! isset( $this->args['tax_query'] ) ) {
				$this->args['tax_query'] = [];
			}
			$this->args['tax_query'][] = [
				'taxonomy' => $taxonomy,
				'field'    => $field,
				'terms'    => $terms,
				'operator' => $operator,
			];

			return $this;
		}

		/**
		 * Set the tax query relation.
		 *
		 * @param string $relation The relation between tax queries ('AND' or 'OR').
		 *
		 * @return self
		 */
		public function taxRelation( string $relation ): self {
			if ( ! isset( $this->args['tax_query'] ) ) {
				$this->args['tax_query'] = [];
			}
			$this->args['tax_query']['relation'] = strtoupper( $relation );

			return $this;
		}

		/**
		 * Add a date query to the post query.
		 *
		 * @param array $date_query The date query array.
		 *
		 * @return self
		 */
		public function date( array $date_query ): self {
			$this->args['date_query'] = $date_query;

			return $this;
		}

		/**
		 * Get the constructed post query arguments.
		 *
		 * @return array
		 */
		public function get(): array {
			return $this->args;
		}

		/**
		 * Static method to create a simple post type query.
		 *
		 * @param string|array $post_types Single post type or array of post types.
		 *
		 * @return array
		 */
		public static function simpleType( $post_types ): array {
			return ( new self() )->type( $post_types )->get();
		}

		/**
		 * Static method to create a simple post status query.
		 *
		 * @param string|array $post_statuses Single post status or array of post statuses.
		 *
		 * @return array
		 */
		public static function simpleStatus( $post_statuses ): array {
			return ( new self() )->status( $post_statuses )->get();
		}

		/**
		 * Static method to create a simple post ID query.
		 *
		 * @param int|array $post_ids Single post ID or array of post IDs.
		 *
		 * @return array
		 */
		public static function simpleId( $post_ids ): array {
			return ( new self() )->include( $post_ids )->get();
		}

	}
endif;