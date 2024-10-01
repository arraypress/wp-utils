<?php
/**
 * Posts Utilities for WordPress.
 *
 * @package       ArrayPress/Utils
 * @version       1.2.0
 */

declare( strict_types=1 );

namespace ArrayPress\Utils;

/**
 * Check if the class `Posts` is defined, and if not, define it.
 */
if ( ! class_exists( 'Posts' ) ) :

	class Posts {

		/** Search and Retrieval ********************************************************/

		/**
		 * Search posts by term and arguments, returning results in key/value or label/value format.
		 *
		 * @param string $search_term The term to search for.
		 * @param array  $args        The arguments to search with (e.g., post_type, numberposts, etc.).
		 *
		 * @return array An array of search results.
		 */
		public static function search( string $search_term, array $args = [] ): array {
			$options = [];

			if ( empty( $search_term ) ) {
				return $options;
			}

			$default_args = [
				'post_type'   => 'post',
				'numberposts' => - 1,
				'orderby'     => 'title',
				'order'       => 'ASC',
			];

			$args      = wp_parse_args( $args, $default_args );
			$args['s'] = $search_term;

			$posts = get_posts( $args );

			if ( empty( $posts ) ) {
				return $options;
			}

			foreach ( $posts as $post ) {
				$options[] = [
					'label' => esc_html( $post->post_title ),
					'value' => esc_attr( $post->ID ),
				];
			}

			return $options;
		}

		/**
		 * Get an array of post objects based on provided post IDs.
		 *
		 * @param int[] $post_ids An array of post IDs.
		 *
		 * @return WP_Post[] An array of post objects.
		 */
		public static function get_by_ids( array $post_ids ): array {
			$posts = [];

			$post_ids = Sanitize::object_ids( $post_ids );

			if ( empty( $post_ids ) ) {
				return $posts;
			}

			foreach ( $post_ids as $post_id ) {
				$post = get_post( $post_id );
				if ( $post ) {
					$posts[] = $post;
				}
			}

			return $posts;
		}

		/**
		 * Get an array of unique post IDs based on provided post titles or slugs.
		 *
		 * @param array  $post_titles An array of post titles or slugs to search for.
		 * @param string $post_type   Optional. The post type to search within. Default is 'post'.
		 *
		 * @return array An array of unique post IDs as integers.
		 */
		public static function get_ids_by_titles( array $post_titles, string $post_type = 'post' ): array {
			if ( empty( $post_titles ) ) {
				return [];
			}

			$unique_post_ids = [];

			foreach ( $post_titles as $title_or_slug ) {
				if ( ! empty( $title_or_slug ) ) {
					$post_id = Post::get_id_by_title( $title_or_slug, $post_type );

					if ( $post_id ) {
						$unique_post_ids[] = $post_id;
					}
				}
			}

			return array_unique( $unique_post_ids );
		}

		/**
		 * Get posts associated with specific terms.
		 *
		 * @param array  $term_ids An array of term IDs.
		 * @param string $taxonomy The taxonomy name.
		 * @param array  $args     Optional. Additional WP_Query arguments.
		 *
		 * @return array An array of post objects.
		 */
		public static function get_by_terms( array $term_ids, string $taxonomy, array $args = [] ): array {
			$defaults = [
				'post_type'   => 'any',
				'numberposts' => - 1,
				'tax_query'   => [
					[
						'taxonomy' => $taxonomy,
						'field'    => 'term_id',
						'terms'    => $term_ids,
					],
				],
			];
			$args     = wp_parse_args( $args, $defaults );

			return get_posts( $args );
		}

		/**
		 * Get posts by meta key and value.
		 *
		 * @param string $meta_key   The meta key to search for.
		 * @param mixed  $meta_value The meta value to match.
		 * @param array  $args       Additional query arguments.
		 *
		 * @return WP_Post[] An array of post objects.
		 */
		public static function get_by_meta( string $meta_key, $meta_value, array $args = [] ): array {
			$default_args = [
				'meta_key'    => $meta_key,
				'meta_value'  => $meta_value,
				'post_type'   => 'post',
				'numberposts' => - 1,
			];

			$args = wp_parse_args( $args, $default_args );

			return get_posts( $args );
		}

		/**
		 * Get posts by custom taxonomy term.
		 *
		 * @param string $taxonomy The custom taxonomy.
		 * @param int    $term_id  The term ID.
		 * @param array  $args     Additional query arguments.
		 *
		 * @return WP_Post[] An array of post objects.
		 */
		public static function get_by_taxonomy( string $taxonomy, int $term_id, array $args = [] ): array {
			$default_args = [
				'post_type'   => 'post',
				'numberposts' => - 1,
				'tax_query'   => [
					[
						'taxonomy' => $taxonomy,
						'field'    => 'term_id',
						'terms'    => $term_id,
					],
				],
			];

			$args = wp_parse_args( $args, $default_args );

			return get_posts( $args );
		}

		/**
		 * Get recent posts.
		 *
		 * @param int   $number The number of posts to retrieve.
		 * @param array $args   Additional query arguments.
		 *
		 * @return WP_Post[] An array of post objects.
		 */
		public static function get_recent( int $number = 5, array $args = [] ): array {
			$default_args = [
				'numberposts' => $number,
				'post_type'   => 'post',
				'orderby'     => 'date',
				'order'       => 'DESC',
			];

			$args = wp_parse_args( $args, $default_args );

			return get_posts( $args );
		}

		/**
		 * Get posts by author.
		 *
		 * @param int   $author_id The author ID to search for.
		 * @param array $args      Additional query arguments.
		 *
		 * @return WP_Post[] An array of post objects.
		 */
		public static function get_by_author( int $author_id, array $args = [] ): array {
			$default_args = [
				'author'      => $author_id,
				'post_type'   => 'post',
				'numberposts' => - 1,
			];

			$args = wp_parse_args( $args, $default_args );

			return get_posts( $args );
		}

		/**
		 * Get posts by category.
		 *
		 * @param int   $category_id The category ID to search for.
		 * @param array $args        Additional query arguments.
		 *
		 * @return WP_Post[] An array of post objects.
		 */
		public static function get_by_category( int $category_id, array $args = [] ): array {
			$default_args = [
				'category'    => $category_id,
				'post_type'   => 'post',
				'numberposts' => - 1,
			];

			$args = wp_parse_args( $args, $default_args );

			return get_posts( $args );
		}

		/**
		 * Get posts by date range.
		 *
		 * @param string $start_date The start date (YYYY-MM-DD).
		 * @param string $end_date   The end date (YYYY-MM-DD).
		 * @param array  $args       Additional query arguments.
		 *
		 * @return WP_Post[] An array of post objects.
		 */
		public static function get_by_date_range( string $start_date, string $end_date, array $args = [] ): array {
			$default_args = [
				'post_type'   => 'post',
				'date_query'  => [
					[
						'after'     => $start_date,
						'before'    => $end_date,
						'inclusive' => true,
					],
				],
				'numberposts' => - 1,
			];

			$args = wp_parse_args( $args, $default_args );

			return get_posts( $args );
		}

		/**
		 * Count posts by status.
		 *
		 * @param string $post_status The post status to count (e.g., 'publish', 'draft').
		 *
		 * @return int The number of posts with the specified status.
		 */
		public static function count_by_status( string $post_status ): int {
			$args = [
				'post_type'   => 'post',
				'post_status' => $post_status,
				'numberposts' => - 1,
			];

			return count( get_posts( $args ) );
		}

		/**
		 * Get post thumbnail URLs based on provided post IDs.
		 *
		 * @param int[]  $post_ids An array of post IDs.
		 * @param string $size     The size of the thumbnail (default: 'thumbnail').
		 *
		 * @return array An array of post thumbnail URLs.
		 */
		public static function get_thumbnails_by_ids( array $post_ids, string $size = 'thumbnail' ): array {
			$thumbnails = [];

			$post_ids = Sanitize::object_ids( $post_ids );

			if ( empty( $post_ids ) ) {
				return $thumbnails;
			}

			foreach ( $post_ids as $post_id ) {
				$thumbnail_url = get_the_post_thumbnail_url( $post_id, $size );
				if ( $thumbnail_url ) {
					$thumbnails[ $post_id ] = $thumbnail_url;
				}
			}

			return $thumbnails;
		}


		/**
		 * Get post permalinks based on provided post IDs.
		 *
		 * @param int[] $post_ids An array of post IDs.
		 *
		 * @return array An array of post permalinks.
		 */
		public static function get_permalinks_by_ids( array $post_ids ): array {
			$permalinks = [];

			$post_ids = Sanitize::object_ids( $post_ids );

			if ( empty( $post_ids ) ) {
				return $permalinks;
			}

			foreach ( $post_ids as $post_id ) {
				$permalink = get_permalink( $post_id );
				if ( $permalink ) {
					$permalinks[ $post_id ] = $permalink;
				}
			}

			return $permalinks;
		}

		/**
		 * Get post titles based on provided post IDs.
		 *
		 * @param int[] $post_ids An array of post IDs.
		 *
		 * @return array An array of post titles.
		 */
		public static function get_titles_by_ids( array $post_ids ): array {
			$titles = [];

			$post_ids = Sanitize::object_ids( $post_ids );

			if ( empty( $post_ids ) ) {
				return $titles;
			}

			foreach ( $post_ids as $post_id ) {
				$post = get_post( $post_id );
				if ( $post ) {
					$titles[ $post_id ] = $post->post_title;
				}
			}

			return $titles;
		}

		/**
		 * Update post meta for multiple posts.
		 *
		 * @param int[]  $post_ids   An array of post IDs.
		 * @param string $meta_key   The meta key to update.
		 * @param mixed  $meta_value The value to update the meta key with.
		 *
		 * @return bool True if the update was successful for all posts, false otherwise.
		 */
		public static function update_meta_for_posts( array $post_ids, string $meta_key, $meta_value ): bool {
			$post_ids = Sanitize::object_ids( $post_ids );

			if ( empty( $post_ids ) ) {
				return false;
			}

			$success = true;

			foreach ( $post_ids as $post_id ) {
				if ( ! update_post_meta( $post_id, $meta_key, $meta_value ) ) {
					$success = false;
				}
			}

			return $success;
		}

		/**
		 * Delete posts by IDs.
		 *
		 * @param int[] $post_ids An array of post IDs.
		 *
		 * @return bool True if all posts were deleted successfully, false otherwise.
		 */
		public static function delete_by_ids( array $post_ids ): bool {
			$post_ids = Sanitize::object_ids( $post_ids );

			if ( empty( $post_ids ) ) {
				return false;
			}

			$success = true;

			foreach ( $post_ids as $post_id ) {
				if ( ! wp_delete_post( $post_id, true ) ) {
					$success = false;
				}
			}

			return $success;
		}

		/**
		 * Get posts with no thumbnail.
		 *
		 * @param array $args Additional query arguments.
		 *
		 * @return WP_Post[] An array of post objects.
		 */
		public static function get_without_thumbnail( array $args = [] ): array {
			$default_args = [
				'post_type'   => 'post',
				'numberposts' => - 1,
				'meta_query'  => [
					[
						'key'     => '_thumbnail_id',
						'compare' => 'NOT EXISTS',
					],
				],
			];

			$args = wp_parse_args( $args, $default_args );

			return get_posts( $args );
		}

		/**
		 * Get sticky posts.
		 *
		 * @param array $args Additional query arguments.
		 *
		 * @return WP_Post[] An array of post objects.
		 */
		public static function get_sticky_posts( array $args = [] ): array {
			$default_args = [
				'post_type'   => 'post',
				'numberposts' => - 1,
				'post__in'    => get_option( 'sticky_posts' ),
			];

			$args = wp_parse_args( $args, $default_args );

			return get_posts( $args );
		}

		/**
		 * Get posts where a specific meta key exists.
		 *
		 * @param string $meta_key The meta key to check.
		 * @param array  $args     Additional query arguments.
		 *
		 * @return WP_Post[] An array of post objects.
		 */
		public static function get_where_meta_exists( string $meta_key, array $args = [] ): array {
			$default_args = [
				'post_type'   => 'post',
				'numberposts' => - 1,
				'meta_query'  => [
					[
						'key'     => $meta_key,
						'compare' => 'EXISTS',
					],
				],
			];

			$args = wp_parse_args( $args, $default_args );

			return get_posts( $args );
		}

		/**
		 * Get posts where a meta value is compared with a specific amount.
		 *
		 * @param string $meta_key The meta key to check.
		 * @param mixed  $amount   The amount to compare against.
		 * @param string $operator The comparison operator (e.g., '>', '<', '=', '!=').
		 * @param array  $args     Additional query arguments.
		 *
		 * @return WP_Post[] An array of post objects.
		 */
		public static function get_where_meta_compared( string $meta_key, $amount, string $operator, array $args = [] ): array {
			if ( ! Validate::is_valid_operator( $operator ) ) {
				return [];
			}

			$default_args = [
				'post_type'   => 'post',
				'numberposts' => - 1,
				'meta_query'  => [
					[
						'key'     => $meta_key,
						'value'   => $amount,
						'compare' => $operator,
						'type'    => 'NUMERIC',
					],
				],
			];

			$args = wp_parse_args( $args, $default_args );

			return get_posts( $args );
		}

		/**
		 * Get posts ordered by a meta key value.
		 *
		 * @param string $meta_key The meta key to order by.
		 * @param int    $number   The number of posts to retrieve.
		 * @param array  $args     Additional query arguments.
		 *
		 * @return WP_Post[] An array of post objects.
		 */
		public static function get_by_meta_ordered( string $meta_key, int $number = 10, array $args = [] ): array {
			$default_args = [
				'posts_per_page' => $number,
				'meta_query'     => [
					[
						'key'     => $meta_key,
						'value'   => '',
						'compare' => '!=',
					],
				],
				'fields'         => 'ids',
				'orderby'        => 'meta_value_num',
				'order'          => 'DESC',
			];

			$args = wp_parse_args( $args, $default_args );

			return get_posts( $args );
		}

		/**
		 * Get posts with a specific meta key and value.
		 *
		 * @param string $meta_key   The meta key to search for.
		 * @param mixed  $meta_value The meta value to match.
		 * @param array  $args       Additional query arguments.
		 *
		 * @return WP_Post[] An array of post objects.
		 */
		public static function get_by_meta_value( string $meta_key, $meta_value, array $args = [] ): array {
			$default_args = [
				'meta_key'       => $meta_key,
				'meta_value'     => $meta_value,
				'post_status'    => 'publish',
				'posts_per_page' => - 1,
				'fields'         => 'ids',
			];

			$args = wp_parse_args( $args, $default_args );

			return get_posts( $args );
		}

		/**
		 * Get posts with a specific meta key and a true value.
		 *
		 * @param string $meta_key The meta key to check.
		 * @param array  $args     Additional query arguments.
		 *
		 * @return WP_Post[] An array of post objects.
		 */
		public static function get_by_meta_true( string $meta_key, array $args = [] ): array {
			return self::get_by_meta_value( $meta_key, 1, $args );
		}

		/**
		 * Get post IDs ordered by a meta key value.
		 *
		 * @param string $meta_key The meta key to order by.
		 * @param int    $number   The number of posts to retrieve.
		 * @param array  $args     Additional query arguments.
		 *
		 * @return int[] An array of post IDs.
		 */
		public static function get_ids_by_meta_ordered( string $meta_key, int $number = 10, array $args = [] ): array {
			$args = [
				'posts_per_page' => $number,
				'meta_query'     => [
					[
						'key'     => $meta_key,
						'value'   => '',
						'compare' => '!=',
					],
				],
				'fields'         => 'ids',
				'orderby'        => 'meta_value_num',
				'order'          => 'DESC',
			];

			$args = wp_parse_args( $args, $args );

			return get_posts( $args );
		}

		/**
		 * Retrieves the terms of the specified taxonomy attached to the given posts.
		 *
		 * This function is a wrapper to keep framework code DRY and ensure consistency.
		 *
		 * @param int[]|WP_Post[] $post_ids       An array of post IDs or post objects.
		 * @param string          $taxonomy       Taxonomy name.
		 * @param bool            $return_objects Whether to return term objects instead of term IDs. Default is false.
		 *
		 * @return int[]|WP_Term[]|WP_Error An array of unique term IDs or term objects on success, an empty array if the
		 *                                   taxonomy does not exist or if there are no terms, or WP_Error on failure.
		 */
		public static function get_taxonomy_terms( array $post_ids, string $taxonomy, bool $return_objects = false ) {
			if ( ! taxonomy_exists( $taxonomy ) || empty( $post_ids ) ) {
				return [];
			}

			$terms_collection = [];

			foreach ( $post_ids as $post ) {
				if ( is_numeric( $post ) ) {
					$post = get_post( (int) $post );
				}

				if ( empty( $post ) || ! isset( $post->ID ) ) {
					continue;
				}

				$terms = get_the_terms( $post->ID, $taxonomy );

				if ( $terms && ! is_wp_error( $terms ) ) {
					foreach ( $terms as $term ) {
						$terms_collection[ $term->term_id ] = $term;
					}
				}
			}

			if ( $return_objects ) {
				return array_values( $terms_collection );
			}

			return array_map( 'intval', array_keys( $terms_collection ) );
		}


		/**
		 * Bulk update post meta for multiple posts.
		 *
		 * @param array  $post_ids   An array of post IDs.
		 * @param string $meta_key   The meta key to update.
		 * @param mixed  $meta_value The value to set for the meta key.
		 *
		 * @return bool True if the update was successful for all posts, false otherwise.
		 */
		public static function bulk_update_post_meta( array $post_ids, string $meta_key, $meta_value ): bool {
			$success = true;

			foreach ( $post_ids as $post_id ) {
				if ( ! update_post_meta( $post_id, $meta_key, $meta_value ) ) {
					$success = false;
				}
			}

			return $success;
		}

		/**
		 * Bulk change post status for multiple posts.
		 *
		 * @param array  $post_ids   An array of post IDs.
		 * @param string $new_status The new post status.
		 *
		 * @return bool True if the update was successful for all posts, false otherwise.
		 */
		public static function bulk_change_post_status( array $post_ids, string $new_status ): bool {
			$success = true;

			foreach ( $post_ids as $post_id ) {
				$post = array(
					'ID'          => $post_id,
					'post_status' => $new_status,
				);

				if ( ! wp_update_post( $post ) ) {
					$success = false;
				}
			}

			return $success;
		}

		/**
		 * Get upcoming scheduled posts.
		 *
		 * @param array $args Additional arguments for get_posts().
		 *
		 * @return array An array of upcoming scheduled posts.
		 */
		public static function get_upcoming_scheduled_posts( array $args = [] ): array {
			$default_args = [
				'post_status' => 'future',
				'orderby'     => 'date',
				'order'       => 'ASC',
				'numberposts' => - 1,
			];

			$args = wp_parse_args( $args, $default_args );

			return get_posts( $args );
		}

		/**
		 * Get post meta values based on provided post IDs and meta key.
		 *
		 * @param int[]  $post_ids An array of post IDs.
		 * @param string $meta_key The meta key to retrieve.
		 *
		 * @return array An array of post meta values.
		 */
		public static function get_posts_meta( array $post_ids, string $meta_key ): array {
			$meta_values = [];

			$post_ids = Sanitize::object_ids( $post_ids );

			if ( empty( $post_ids ) ) {
				return $meta_values;
			}

			foreach ( $post_ids as $post_id ) {
				$meta_value = get_post_meta( $post_id, $meta_key, true );
				if ( $meta_value !== null ) {
					$meta_values[ $post_id ] = $meta_value;
				}
			}

			return $meta_values;
		}

	}

endif;