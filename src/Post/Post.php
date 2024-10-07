<?php
/**
 * Post Utilities for WordPress
 *
 * This class provides utility functions for working with WordPress posts, including
 * methods for checking post existence, retrieving post data, working with post types,
 * checking for shortcodes and blocks, handling post metadata, and managing post content.
 * It also offers functions to work with taxonomy terms, post thumbnails, and scheduling posts.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Post;

use WP_Post;
use WP_Query;
use ArrayPress\Utils\Database\Exists;

/**
 * Check if the class `Post` is defined, and if not, define it.
 */
if ( ! class_exists( 'Post' ) ) :

	/**
	 * Post Utilities
	 *
	 * Provides utility functions for managing WordPress posts, such as checking post existence,
	 * retrieving post data by various identifiers, working with post meta, taxonomy terms, and
	 * handling post content. It also supports managing post thumbnails, scheduling posts, and
	 * extracting content-related information like links and word count.
	 */
	class Post {

		/** Post Existence and Retrieval *********************************************/

		/**
		 * Check if the post exists in the database.
		 *
		 * @param int $post_id The ID of the post to check.
		 *
		 * @return bool True if the post exists, false otherwise.
		 */
		public static function exists( int $post_id ): bool {
			// Bail if a post ID was not passed.
			if ( empty( $post_id ) ) {
				return false;
			}

			return Exists::row( 'posts', 'ID', $post_id );
		}

		/**
		 * Retrieves the ID of a post based on its title and post type.
		 *
		 * @param string $post_title The title of the post to retrieve the ID for.
		 * @param string $post_type  Optional. The post type to search within. Default is 'post'.
		 *
		 * @return int|null The ID of the first matching post, or null if no matching post is found.
		 */
		public static function get_id_by_title( string $post_title, string $post_type = 'post' ): ?int {
			$args = [
				'post_type'              => $post_type,
				'title'                  => $post_title,
				'post_status'            => 'any',
				'posts_per_page'         => 1,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false,
				'orderby'                => [ 'post_date' => 'ASC', 'ID' => 'ASC' ],
				'fields'                 => 'ids',
			];

			$posts = get_posts( $args );

			return ! empty( $posts ) ? (int) $posts[0] : null;
		}

		/**
		 * Get a post object based on provided identifier (ID, title, slug, or WP_Post object).
		 *
		 * @param int|string|WP_Post $identifier  The post identifier to search for.
		 * @param string|array       $post_type   The post type(s) to search within. Default is 'any'.
		 * @param string|array       $post_status The post status(es) to include. Default is 'publish'.
		 *
		 * @return WP_Post|null The post object if found, null otherwise.
		 */
		public static function get_by_identifier( $identifier, $post_type = 'any', $post_status = 'publish' ): ?WP_Post {
			// If $identifier is already a WP_Post object, return it
			if ( $identifier instanceof WP_Post ) {
				return $identifier;
			}

			// If $identifier is numeric, try to get post by ID
			if ( is_numeric( $identifier ) ) {
				$post = get_post( (int) $identifier );

				return ( $post instanceof WP_Post ) ? $post : null;
			}

			// Prepare arguments for get_posts()
			$args = [
				'post_type'      => $post_type,
				'post_status'    => $post_status,
				'posts_per_page' => 1,
				'no_found_rows'  => true,
				'fields'         => 'ids',
			];

			// Try to find by slug first
			$args['name'] = sanitize_title( $identifier );
			$posts        = get_posts( $args );

			if ( empty( $posts ) ) {
				unset( $args['name'] );
				$args['title'] = $identifier;
				$posts         = get_posts( $args );
			}

			return ! empty( $posts ) ? get_post( $posts[0] ) : null;
		}

		/**
		 * Get post parent details.
		 *
		 * @param int $post_id The post ID.
		 *
		 * @return WP_Post|null Parent post object if exists, null otherwise.
		 */
		public static function get_post_parent( int $post_id ): ?WP_Post {
			$post = get_post( $post_id );
			if ( ! $post || ! $post->post_parent ) {
				return null;
			}

			return get_post( $post->post_parent );
		}

		/**
		 * Get post author details.
		 *
		 * @param int $post_id The post ID.
		 *
		 * @return WP_User|false The author's user object or false if not found.
		 */
		public static function get_author_details( int $post_id ) {
			$post = get_post( $post_id );
			if ( ! $post ) {
				return false;
			}

			return get_userdata( $post->post_author );
		}

		/**
		 * Retrieves the content of a specific page.
		 *
		 * @param int $post_id The post ID.
		 *
		 * @return string The content of the specified post/page.
		 */
		public static function get_content( int $post_id ): string {
			$post = get_post( $post_id );

			return $post ? apply_filters( 'the_content', $post->post_content ) : '';
		}

		/**
		 * Checks if a specific page is published.
		 *
		 * @param int $post_id The post ID.
		 *
		 * @return bool True if the page is published, false otherwise.
		 */
		public static function is_published( int $post_id ): bool {
			return get_post_status( $post_id ) === 'publish';
		}

		/**
		 * Get the page's last modified date.
		 *
		 * @param int    $post_id The post ID.
		 * @param string $format  Optional. PHP date format. Default 'Y-m-d H:i:s'.
		 *
		 * @return string The formatted date.
		 */
		public static function get_last_modified( int $post_id, string $format = 'Y-m-d H:i:s' ): string {
			$post = get_post( $post_id );

			return $post ? mysql2date( $format, $post->post_modified ) : '';
		}

		/**
		 * Get the page's published date.
		 *
		 * @param int    $post_id The post ID.
		 * @param string $format  Optional. PHP date format. Default 'Y-m-d H:i:s'.
		 *
		 * @return string The formatted date.
		 */
		public static function get_published_date( int $post_id, string $format = 'Y-m-d H:i:s' ): string {
			$post = get_post( $post_id );

			return $post ? mysql2date( $format, $post->post_date ) : '';
		}

		/** Post Content and Meta Data ***********************************************/

		/**
		 * Get a specific field from the post.
		 *
		 * @param int    $post_id The post ID.
		 * @param string $field   The field name.
		 *
		 * @return mixed The field value or null if not found.
		 */
		public static function get_field( int $post_id, string $field ) {
			$post = get_post( $post_id );

			if ( ! $post ) {
				return null;
			}

			// First, check if it's a property of the post object
			if ( isset( $post->$field ) ) {
				return $post->$field;
			}

			// Check if it's a custom meta field
			return get_post_meta( $post->ID, $field, true );
		}

		/**
		 * Get a post meta value with optional type casting.
		 *
		 * @param int                  $post_id  The post ID.
		 * @param string               $meta_key The meta key to retrieve.
		 * @param callable|string|null $callback Optional. Callback function to process the value. Default null.
		 * @param mixed                $default  Optional. Default value if meta doesn't exist. Default 0.
		 *
		 * @return mixed The meta value.
		 */
		public static function get_meta_value( int $post_id, string $meta_key, $callback = null, $default = 0 ) {
			$value = get_post_meta( $post_id, $meta_key, true );

			if ( $value === '' || $value === false ) {
				return $default;
			}

			if ( $callback !== null ) {
				if ( is_callable( $callback ) ) {
					return $callback( $value );
				} elseif ( is_string( $callback ) && function_exists( $callback ) ) {
					return $callback( $value );
				}
			}

			return $value;
		}

		/**
		 * Check if a post has a specific meta value set to true.
		 *
		 * @param int                  $post_id         The ID of the post.
		 * @param string               $meta_key        The meta key to check.
		 * @param bool                 $default         Optional. A default value to return if the meta value is not found. Default is false.
		 * @param callable|string|null $global_callback Optional. A callback function or function name to retrieve a global option. Default is null.
		 * @param bool                 $global_default  Optional. A default value to return if the global option is not found. Default is false.
		 *
		 * @return bool True if the meta value or global option is set to true, false otherwise.
		 */
		public static function is_meta_true( int $post_id, string $meta_key, bool $default = false, $global_callback = null, bool $global_default = false ): bool {
			$meta_value = get_post_meta( $post_id, $meta_key, true );

			if ( $meta_value !== '' ) {
				return (bool) $meta_value;
			}

			if ( $global_callback !== null ) {
				if ( is_callable( $global_callback ) ) {
					return (bool) $global_callback( $global_default );
				} elseif ( is_string( $global_callback ) && function_exists( $global_callback ) ) {
					return (bool) $global_callback( $global_default );
				}
			}

			return $default;
		}

		/** Shortcodes, Blocks, and Content Handling *********************************/

		/**
		 * Check if a post has a specific shortcode.
		 *
		 * @param WP_Post|int $post      The post object or ID.
		 * @param string      $shortcode The shortcode to check for.
		 *
		 * @return bool True if the post contains the shortcode, false otherwise.
		 */
		public static function has_shortcode( $post, string $shortcode ): bool {
			if ( is_numeric( $post ) ) {
				$post = get_post( (int) $post );
			}

			if ( empty( $post ) ) {
				return false;
			}

			return has_shortcode( $post->post_content, $shortcode );
		}

		/**
		 * Check if a post contains a specific block.
		 *
		 * @param WP_Post|int $post  The post object or ID.
		 * @param string      $block The block name to check for.
		 *
		 * @return bool True if the post contains the block, false otherwise.
		 */
		public static function has_block( $post, string $block ): bool {
			if ( is_numeric( $post ) ) {
				$post = get_post( (int) $post );
			}

			if ( empty( $post ) ) {
				return false;
			}

			return has_block( $block, $post->post_content );
		}

		/**
		 * Count words in post content.
		 *
		 * @param int $post_id The post ID.
		 *
		 * @return int The number of words in the post content.
		 */
		public static function count_words( int $post_id ): int {
			$post = get_post( $post_id );

			if ( ! $post ) {
				return 0;
			}

			$content = wp_strip_all_tags( $post->post_content );

			return str_word_count( $content );
		}

		/**
		 * Extract all links from post content.
		 *
		 * @param int $post_id The post ID.
		 *
		 * @return array An array of links found in the post content.
		 */
		public static function extract_links_from_content( int $post_id ): array {
			$post = get_post( $post_id );

			if ( ! $post ) {
				return [];
			}

			$links = [];
			preg_match_all( '/<a\s+.*?href=[\"\'](.+?)[\"\'].*?>/i', $post->post_content, $matches );

			if ( ! empty( $matches[1] ) ) {
				$links = array_unique( $matches[1] );
			}

			return $links;
		}

		/** Taxonomies and Terms *****************************************************/

		/**
		 * Retrieve the amounts for terms in a specific taxonomy for a post.
		 *
		 * @param int    $post_id  The ID of the post.
		 * @param string $taxonomy The taxonomy to retrieve terms from.
		 * @param string $meta_key The meta key to retrieve amounts from.
		 *
		 * @return array An array of amounts for the terms.
		 */
		public static function get_taxonomy_amounts( int $post_id, string $taxonomy, string $meta_key ): array {
			$amounts = [];

			$terms = get_the_terms( $post_id, $taxonomy );

			if ( empty( $terms ) || is_wp_error( $terms ) ) {
				return $amounts;
			}

			foreach ( $terms as $term ) {
				$amount = get_term_meta( $term->term_id, $meta_key, true );

				if ( ! empty( $amount ) ) {
					$amounts[] = floatval( $amount );
				}
			}

			return array_unique( $amounts );
		}

		/**
		 * Retrieve the highest or lowest amount for terms in a specific taxonomy for a post.
		 *
		 * @param int    $post_id     The ID of the post.
		 * @param string $taxonomy    The taxonomy to retrieve terms from.
		 * @param string $meta_key    The meta key to retrieve amounts from.
		 * @param bool   $use_highest Whether to use the highest amount. Default true.
		 *
		 * @return float|null The highest or lowest amount, or null if no amounts found.
		 */
		public static function get_taxonomy_amount( int $post_id, string $taxonomy, string $meta_key, bool $use_highest = true ): ?float {
			$amounts = self::get_taxonomy_amounts( $post_id, $taxonomy, $meta_key );

			if ( empty( $amounts ) ) {
				return null;
			}

			return $use_highest ? max( $amounts ) : min( $amounts );
		}

		/**
		 * Get the number of comments or comments for a specific post.
		 *
		 * @param int|WP_Post $post The post object or ID.
		 * @param array       $args Optional. An array of arguments for comment retrieval or counting.
		 *
		 * @return int|array The number of comments or an array of comment objects, depending on the 'count' argument.
		 */
		public static function get_comments( $post, array $args = [] ) {
			$post = get_post( $post );
			if ( ! $post ) {
				return $args['count'] ?? false ? 0 : [];
			}

			$default_args = [
				'post_id' => $post->ID,
				'status'  => 'approve',
				'type'    => 'comment'
			];

			$args = wp_parse_args( $args, $default_args );

			return get_comments( $args );
		}

		/**
		 * Get the number of comments for a specific post.
		 *
		 * @param int|WP_Post $post The post object or ID.
		 * @param array       $args Optional. An array of arguments for comment counting.
		 *
		 * @return int The number of comments for the post.
		 */
		public static function get_comment_count( $post, array $args = [] ): int {
			$args['count'] = true;

			return self::get_comments( $post, $args );
		}

		/**
		 * Recursively get page children.
		 *
		 * @param int   $post_id Page ID.
		 * @param array $args    Additional arguments for get_posts.
		 *
		 * @return int[] Array of child page IDs.
		 */
		public static function get_children( int $post_id, array $args = [] ): array {
			$defaults = [
				'post_parent' => $post_id,
				'post_type'   => 'page',
				'numberposts' => - 1,
				'post_status' => 'any',
				'fields'      => 'ids',
			];

			$args = wp_parse_args( $args, $defaults );

			$page_ids = get_posts( $args );

			// Recursively get children pages.
			foreach ( $page_ids as $child_page_id ) {
				$page_ids = array_merge( $page_ids, self::get_children( $child_page_id, $args ) );
			}

			return $page_ids;
		}

		/**
		 * Get post statuses and return them in label/value format.
		 *
		 * @param array $args Optional. Arguments to filter post statuses.
		 *
		 * @return array An array of post statuses in label/value format.
		 */
		public static function get_status_options( array $args = [] ): array {
			$defaults   = [];
			$args       = wp_parse_args( $args, $defaults );
			$post_stati = get_post_stati( $args, 'objects' );

			if ( empty( $post_stati ) || ! is_array( $post_stati ) ) {
				return [];
			}

			$options = [];

			foreach ( $post_stati as $status => $details ) {
				if ( ! isset( $status, $details ) ) {
					continue;
				}

				$options[] = [
					'value' => esc_attr( $status ),
					'label' => esc_html( $details->label ?? $status ),
				];
			}

			return $options;
		}

		/**
		 * Get registered custom post types and return them in label/value format.
		 *
		 * @param array $args Optional. Arguments to filter custom post types.
		 *
		 * @return array An array of custom post types in label/value format.
		 */
		public static function get_custom_post_type_options( array $args = [] ): array {
			$defaults   = [ '_builtin' => false ];
			$args       = wp_parse_args( $args, $defaults );
			$post_types = get_post_types( $args, 'objects' );

			if ( empty( $post_types ) || ! is_array( $post_types ) ) {
				return [];
			}

			$options = [];

			foreach ( $post_types as $post_type => $details ) {
				$options[] = [
					'value' => esc_attr( $post_type ),
					'label' => esc_html( $details->label ),
				];
			}

			return $options;
		}

		/**
		 * Check if a post is of a specific post type.
		 *
		 * @param string   $post_type The post type to check against.
		 * @param int|null $post_id   Optional. The post ID to check. Default is the current post ID.
		 *
		 * @return bool True if the post is of the specified post type, false otherwise.
		 */
		public static function is_type( string $post_type, int $post_id = null ): bool {
			$post_id = $post_id ?: get_the_ID();

			if ( empty( $post_id ) ) {
				return false;
			}

			return $post_type === get_post_type( $post_id );
		}

		/**
		 * Check if a post is excluded based on a specific meta key or term meta.
		 *
		 * @param int    $post_id        The ID of the post.
		 * @param string $meta_key       The meta key to check.
		 * @param string $taxonomy       Optional. The taxonomy to check terms in. Default is 'category'.
		 * @param string $term_meta_key  Optional. The term meta key to check. Default is 'exclude'.
		 * @param string $transient_key  Optional. The transient cache key. Default is ''.
		 * @param bool   $include_terms  Optional. Whether to include terms in the check. Default is true.
		 * @param int    $cache_duration Optional. The duration for which to cache the result, in seconds. Default is 43200 (12 hours).
		 *
		 * @return bool True if the post is excluded, false otherwise.
		 */
		public static function is_excluded( int $post_id, string $meta_key, string $taxonomy = 'category', string $term_meta_key = 'exclude', string $transient_key = '', bool $include_terms = true, int $cache_duration = 43200 ): bool {
			$cache_key = $transient_key ?: 'is_excluded_' . $post_id;

			$is_excluded = get_transient( $cache_key );

			if ( false === $is_excluded ) {
				$is_excluded = (bool) get_post_meta( $post_id, $meta_key, true );

				if ( ! $is_excluded && $include_terms ) {
					$terms = wp_get_post_terms( $post_id, $taxonomy, [
						'meta_key'   => $term_meta_key,
						'meta_value' => 'on',
						'fields'     => 'ids'
					] );

					$is_excluded = ! is_wp_error( $terms ) && ! empty( $terms );
				}

				set_transient( $cache_key, $is_excluded, $cache_duration );
			}

			return $is_excluded;
		}

		/**
		 * Retrieve a customizable message for a post.
		 *
		 * @param int                  $post_id         The ID of the post.
		 * @param string               $meta_key        The meta key for the post-specific message.
		 * @param callable|string|null $global_callback Optional. A callback function or function name to retrieve a global message. Default is null.
		 * @param string               $default_message Optional. A default message to use if no specific or global message is found. Default is an empty string.
		 *
		 * @return string The message.
		 */
		public static function get_custom_message( int $post_id, string $meta_key, $global_callback = null, string $default_message = '' ): string {
			$message = get_post_meta( $post_id, $meta_key, true );

			if ( ! empty( $message ) ) {
				return $message;
			}

			if ( $global_callback !== null ) {
				$global_message = null;
				if ( is_callable( $global_callback ) ) {
					$global_message = $global_callback();
				} elseif ( is_string( $global_callback ) && function_exists( $global_callback ) ) {
					$global_message = $global_callback();
				}

				if ( ! empty( $global_message ) ) {
					return $global_message;
				}
			}

			return $default_message;
		}

		/**
		 * Retrieve the term meta values for a specific taxonomy and post, and process them.
		 *
		 * @param int                  $post_id  The ID of the post.
		 * @param string               $taxonomy The taxonomy to retrieve terms from.
		 * @param string               $meta_key The meta key to retrieve from the terms.
		 * @param callable|string|null $callback Optional. A callback function or function name to process the meta value. Default is 'floatval'.
		 *
		 * @return array The processed term meta values.
		 */
		public static function get_term_meta_values( int $post_id, string $taxonomy, string $meta_key, $callback = 'floatval' ): array {
			$terms = get_the_terms( $post_id, $taxonomy );

			if ( ! $terms || is_wp_error( $terms ) ) {
				return [];
			}

			$values = [];

			foreach ( $terms as $term ) {
				$meta_value = get_term_meta( $term->term_id, $meta_key, true );

				if ( $meta_value !== '' ) {
					if ( is_callable( $callback ) ) {
						$values[] = $callback( $meta_value );
					} elseif ( is_string( $callback ) && function_exists( $callback ) ) {
						$values[] = $callback( $meta_value );
					} else {
						$values[] = $meta_value;
					}
				}
			}

			return array_unique( $values );
		}

		/**
		 * Retrieve a single processed term meta value for a specific taxonomy and post.
		 *
		 * @param int                  $post_id     The ID of the post.
		 * @param string               $taxonomy    The taxonomy to retrieve terms from.
		 * @param string               $meta_key    The meta key to retrieve from the terms.
		 * @param callable|string|null $callback    Optional. A callback function or function name to process the meta value. Default is 'floatval'.
		 * @param bool                 $use_highest Optional. Whether to use the highest or lowest value. Default is true (highest).
		 *
		 * @return float The single processed term meta value.
		 */
		public static function get_single_term_meta_value( int $post_id, string $taxonomy, string $meta_key, $callback = 'floatval', bool $use_highest = true ): float {
			$values = self::get_term_meta_values( $post_id, $taxonomy, $meta_key, $callback );

			if ( empty( $values ) ) {
				return 0.0;
			}

			return $use_highest ? max( $values ) : min( $values );
		}

		/**
		 * Check if a post has a thumbnail.
		 *
		 * @param int $post_id The ID of the post.
		 *
		 * @return bool True if the post has a thumbnail, false otherwise.
		 */
		public static function has_thumbnail( int $post_id ): bool {
			return current_theme_supports( 'post-thumbnails' ) && has_post_thumbnail( $post_id );
		}

		/**
		 * Retrieve the URL of the post thumbnail.
		 *
		 * @param int $post_id The ID of the post.
		 *
		 * @return string|false The URL of the post thumbnail or false if not set.
		 */
		public static function get_thumbnail_url( int $post_id ) {
			return get_the_post_thumbnail_url( $post_id );
		}

		/**
		 * Retrieves the terms of the specified taxonomy attached to the given post.
		 *
		 * This function is a wrapper to keep framework code DRY.
		 *
		 * @param int|WP_Post $post     Post ID or object.
		 * @param string      $taxonomy Taxonomy name.
		 * @param bool        $term_ids Whether to return term IDs instead of term objects. Default is true.
		 *
		 * @return int[]|WP_Term[]|false|WP_Error Array of term IDs or WP_Term objects on success,
		 *                                        false if there are no terms or the post does not exist,
		 *                                        WP_Error on failure.
		 */
		public static function get_terms( $post, string $taxonomy, bool $term_ids = true ) {
			if ( empty( $post ) || ! taxonomy_exists( $taxonomy ) ) {
				return false;
			}

			// Retrieve the post object if a post ID is provided
			if ( is_numeric( $post ) ) {
				$post = get_post( (int) $post );
			}

			if ( empty( $post ) || ! isset( $post->ID ) ) {
				return false;
			}

			$terms = get_the_terms( $post->ID, $taxonomy );

			if ( $terms && ! is_wp_error( $terms ) ) {
				return $term_ids ? wp_list_pluck( $terms, 'term_id' ) : $terms;
			}

			return false;
		}

		/**
		 * Get all custom fields for a post.
		 *
		 * @param int $post_id The post ID.
		 *
		 * @return array An array of custom fields.
		 */
		public static function get_all_custom_fields( int $post_id ): array {
			$custom_fields = get_post_custom( $post_id );

			// Remove WordPress default fields
			$default_fields = [ '_edit_lock', '_edit_last', '_wp_page_template', '_thumbnail_id' ];
			foreach ( $default_fields as $field ) {
				unset( $custom_fields[ $field ] );
			}

			return $custom_fields;
		}

		/**
		 * Reschedule a post.
		 *
		 * @param int    $post_id  The post ID.
		 * @param string $new_date The new date in MySQL format (YYYY-MM-DD HH:MM:SS).
		 *
		 * @return bool True if the post was rescheduled successfully, false otherwise.
		 */
		public static function reschedule_post( int $post_id, string $new_date ): bool {
			$post = [
				'ID'            => $post_id,
				'post_date'     => $new_date,
				'post_date_gmt' => get_gmt_from_date( $new_date ),
				'post_status'   => 'future',
			];

			$result = wp_update_post( $post );

			return $result !== 0 && ! is_wp_error( $result );
		}


		/**
		 * Get post revisions.
		 *
		 * @param int $post_id The post ID.
		 *
		 * @return array An array of post revision objects.
		 */
		public static function get_post_revisions( int $post_id ): array {
			return wp_get_post_revisions( $post_id );
		}

		/**
		 * Check if a post is password protected.
		 *
		 * @param int $post_id The post ID.
		 *
		 * @return bool True if the post is password protected, false otherwise.
		 */
		public static function is_password_protected( int $post_id ): bool {
			$post = get_post( $post_id );

			return ! empty( $post->post_password );
		}

		/**
		 * Get attached media.
		 *
		 * @param int    $post_id    The post ID.
		 * @param string $media_type Optional. The media type (e.g., 'image', 'video'). Default 'any'.
		 *
		 * @return array An array of attachment objects.
		 */
		public static function get_attached_media( int $post_id, string $media_type = 'any' ): array {
			return get_attached_media( $media_type, $post_id );
		}

		/**
		 * Get post format.
		 *
		 * @param int $post_id The post ID.
		 *
		 * @return string|false The post format if set, false otherwise.
		 */
		public static function get_format( int $post_id ) {
			return get_post_format( $post_id );
		}

		/**
		 * Get related posts based on shared terms in specified taxonomies.
		 *
		 * @param int   $post_id    The post ID.
		 * @param array $args       Optional. An array of arguments. Default is an empty array.
		 * @param array $taxonomies Optional. An array of taxonomy names to consider. Default is ['post_tag', 'category'].
		 *
		 * @return array An array of related post objects.
		 */
		public static function get_related_posts(
			int $post_id, array $args = [], array $taxonomies = [
			'post_tag',
			'category'
		]
		): array {
			$default_args = [
				'posts_per_page'      => 5,
				'post__not_in'        => [ $post_id ],
				'ignore_sticky_posts' => 1,
				'orderby'             => 'relevance',
				'post_type'           => get_post_type( $post_id ),
			];

			$args = wp_parse_args( $args, $default_args );

			// Get all terms for the current post across specified taxonomies
			$terms = [];
			foreach ( $taxonomies as $taxonomy ) {
				$post_terms = wp_get_object_terms( $post_id, $taxonomy, [ 'fields' => 'ids' ] );
				if ( ! is_wp_error( $post_terms ) && ! empty( $post_terms ) ) {
					$terms = array_merge( $terms, $post_terms );
				}
			}

			if ( empty( $terms ) ) {
				return [];
			}

			// Prepare tax query
			$tax_query = [];
			foreach ( $taxonomies as $taxonomy ) {
				$tax_query[] = [
					'taxonomy' => $taxonomy,
					'field'    => 'term_id',
					'terms'    => $terms,
				];
			}

			if ( count( $taxonomies ) > 1 ) {
				$tax_query['relation'] = 'OR';
			}

			$args['tax_query'] = $tax_query;

			// Perform the query
			$related_query = new \WP_Query( $args );

			return $related_query->posts;
		}

		/**
		 * Get post reading time estimate.
		 *
		 * @param int $post_id          The post ID.
		 * @param int $words_per_minute Optional. Average reading speed. Default 200.
		 *
		 * @return int Estimated reading time in minutes.
		 */
		public static function get_reading_time( int $post_id, int $words_per_minute = 200 ): int {
			$word_count = self::count_words( $post_id );

			return (int) ceil( $word_count / $words_per_minute );
		}

		/**
		 * Check if post is sticky.
		 *
		 * @param int $post_id The post ID.
		 *
		 * @return bool True if the post is sticky, false otherwise.
		 */
		public static function is_sticky( int $post_id ): bool {
			return is_sticky( $post_id );
		}

		/**
		 * Get custom post type archive link.
		 *
		 * @param int $post_id The post ID.
		 *
		 * @return string|false The archive URL if successful, false otherwise.
		 */
		public static function get_post_type_archive_link( int $post_id ) {
			$post_type = get_post_type( $post_id );

			return get_post_type_archive_link( $post_type );
		}

		/**
		 * Get post template.
		 *
		 * @param int $post_id The post ID.
		 *
		 * @return string The template file name.
		 */
		public static function get_post_template( int $post_id ): string {
			return get_page_template_slug( $post_id );
		}

		/**
		 * Get or update post menu order.
		 *
		 * @param int      $post_id   The post ID.
		 * @param int|null $new_order Optional. New menu order to set.
		 *
		 * @return int Current menu order of the post.
		 */
		public static function post_menu_order( int $post_id, ?int $new_order = null ): int {
			$post = get_post( $post_id );
			if ( ! $post ) {
				return 0;
			}

			if ( $new_order !== null ) {
				wp_update_post( [
					'ID'         => $post_id,
					'menu_order' => $new_order
				] );

				return $new_order;
			}

			return $post->menu_order;
		}


	}

endif;