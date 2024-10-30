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

namespace ArrayPress\Utils\Posts;

use WP_Post;
use WP_Query;
use WP_User;
use ArrayPress\Utils\Common\Extract;
use ArrayPress\Utils\Common\Str;
use ArrayPress\Utils\Database\Exists;
use ArrayPress\Utils\Shared\Meta;

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

		/** Post Details *************************************************************/

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

			return $post && get_userdata( $post->post_author );
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

		/**
		 * Get the scheduled date of a post, if applicable.
		 *
		 * @param int    $post_id The ID of the post.
		 * @param string $format  Optional. The format to return the date in. Default is 'Y-m-d H:i:s'.
		 *
		 * @return string|null The scheduled date in the specified format, or null if the post is not scheduled.
		 */
		public static function get_scheduled_date( int $post_id, string $format = 'Y-m-d H:i:s' ): ?string {
			$post = get_post( $post_id );

			if ( ! $post || $post->post_status !== 'future' ) {
				return null;
			}

			return mysql2date( $format, $post->post_date );
		}

		/**
		 * Get the current status of a post.
		 *
		 * @param int $post_id The ID of the post.
		 *
		 * @return string|false The current status of the post, or false if the post doesn't exist.
		 */
		public static function get_status( int $post_id ) {
			return get_post_status( $post_id );
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

			return Str::word_count( $post->post_content );
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
		 * Retrieve the edit URL for a post.
		 *
		 * @param int    $post_id    The post ID.
		 * @param string $capability The capability required to edit the post. Default 'edit_post'.
		 *
		 * @return string|null The edit URL if the current user has permission, null otherwise.
		 */
		public static function get_edit_link( int $post_id, string $capability = 'edit_post' ): ?string {
			return current_user_can( $capability, $post_id ) ? get_edit_post_link( $post_id ) : null;
		}

		/**
		 * Retrieve the categories for a post.
		 *
		 * @param int    $post_id  The post ID.
		 * @param string $taxonomy Optional. The taxonomy to lookup. Default 'category'.
		 *
		 * @return array An array of category objects if available, an empty array otherwise.
		 */
		public static function get_categories( int $post_id, string $taxonomy = 'category' ): array {
			return get_the_terms( $post_id, $taxonomy ) ?: [];
		}

		/**
		 * Retrieve the tags for a post.
		 *
		 * @param int    $post_id  The post ID.
		 * @param string $taxonomy Optional. The taxonomy to lookup. Default 'post_tag'.
		 *
		 * @return array An array of tag objects if available, an empty array otherwise.
		 */
		public static function get_tags( int $post_id, string $taxonomy = 'post_tag' ): array {
			return get_the_terms( $post_id, $taxonomy ) ?: [];
		}

		/** Post Comments ************************************************************/

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

		/** Post Comments ************************************************************/

		/**
		 * Extract content from post based on type.
		 *
		 * @param int    $post_id The post ID.
		 * @param string $type    The type of content to extract.
		 * @param array  $args    Additional arguments for specific extraction types.
		 *
		 * @return array An array of extracted content.
		 */
		public static function extract( int $post_id, string $type, array $args = [] ): array {
			$post = get_post( $post_id );

			if ( ! $post ) {
				return [];
			}

			$content = $post->post_content;

			switch ( $type ) {
				case 'urls':
					return Extract::urls( $content );
				case 'image_urls':
					return Extract::image_urls( $content );
				case 'audio_urls':
					return Extract::audio_urls( $content );
				case 'video_urls':
					return Extract::video_urls( $content );
				case 'social_urls':
					return Extract::social_urls( $content );
				case 'archive_urls':
					return Extract::archive_urls( $content );
				case 'emails':
					return Extract::emails( $content );
				case 'mentions':
					return Extract::mentions( $content );
				case 'hashtags':
					return Extract::hashtags( $content );
				case 'amounts':
					$include_negative = $args['include_negative'] ?? false;

					return Extract::amounts( $content, $include_negative );
				case 'ip_addresses':
					return Extract::ip_addresses( $content );
				case 'phone_numbers':
					return Extract::phone_numbers( $content );
				case 'shortcodes':
					return Extract::shortcodes( $content );
				case 'dates':
					$format = $args['format'] ?? 'Y-m-d';

					return Extract::dates( $content, $format );
				case 'user_ids':
					$check_all_nums = $args['check_all_nums'] ?? false;

					return Extract::user_ids( $content, $check_all_nums );
				case 'post_ids':
					$check_all_nums = $args['check_all_nums'] ?? false;

					return Extract::post_ids( $content, $check_all_nums );
				case 'usernames':
					$validate = $args['validate'] ?? true;

					return Extract::usernames( $content, $validate );
				case 'post_slugs':
					$validate = $args['validate'] ?? true;

					return Extract::post_slugs( $content, $validate );
				default:
					return [];
			}
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
		 * Get the featured image (thumbnail) HTML for a post.
		 *
		 * Retrieves the post thumbnail with specified dimensions and attributes.
		 * If no thumbnail exists, returns an empty string.
		 *
		 * @param int   $post_id The post ID. Default 0 (current post).
		 * @param array $args    {
		 *                       Optional. Arguments to customize the thumbnail display.
		 *
		 * @type int    $width   Thumbnail width in pixels. Default 64.
		 * @type int    $height  Thumbnail height in pixels. Default 64.
		 * @type string $class   CSS class names. Default empty.
		 * @type string $alt     Image alt text. Default empty.
		 * @type string $size    Image size. Accepts any registered image size name. Default calculated from width/height.
		 *                       }
		 *
		 * @return string HTML img element or empty string if no thumbnail exists.
		 */
		public static function get_thumbnail( int $post_id = 0, array $args = [] ): string {
			$default_args = [
				'width'  => 64,
				'height' => 64,
				'class'  => '',
				'alt'    => '',
				'size'   => [ $args['width'] ?? 64, $args['height'] ?? 64 ]
			];

			$args = wp_parse_args( $args, $default_args );

			return get_the_post_thumbnail( $post_id, $args['size'], $args );
		}

		/**
		 * Reschedule a post.
		 *
		 * @param int    $post_id  The post ID.
		 * @param string $new_date The new date in MySQL format (YYYY-MM-DD HH:MM:SS).
		 *
		 * @return bool True if the post was rescheduled successfully, false otherwise.
		 */
		public static function reschedule( int $post_id, string $new_date ): bool {
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
		public static function get_revisions( int $post_id ): array {
			return wp_get_post_revisions( $post_id );
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
		 * Get post menu order.
		 *
		 * @param int $post_id The post ID.
		 *
		 * @return int Current menu order of the post.
		 */
		public static function get_menu_order( int $post_id ): int {
			$post = get_post( $post_id );
			if ( ! $post ) {
				return 0;
			}

			return absint( $post->menu_order );
		}

		/**
		 * Update post menu order.
		 *
		 * @param int $post_id   The post ID.
		 * @param int $new_order The new menu order to set.
		 *
		 * @return int Updated menu order of the post.
		 */
		public static function update_menu_order( int $post_id, int $new_order ): int {
			$post = get_post( $post_id );
			if ( ! $post ) {
				return 0;
			}

			wp_update_post( [
				'ID'         => $post_id,
				'menu_order' => $new_order
			] );

			return $new_order;
		}

		/**
		 * Get all custom fields for a post, excluding WordPress default fields.
		 *
		 * @param int $post_id The post ID.
		 *
		 * @return array An array of custom fields.
		 */
		public static function get_all_custom_fields( int $post_id ): array {
			$all_fields     = get_post_custom( $post_id );
			$default_fields = [ '_edit_lock', '_edit_last', '_wp_page_template', '_thumbnail_id' ];

			return array_diff_key( $all_fields, array_flip( $default_fields ) );
		}

		/**
		 * Get the next post in sequence based on a numeric meta value.
		 *
		 * @param int    $post_id  The current post ID.
		 * @param string $meta_key The meta key to order by.
		 *
		 * @return int|null The ID of the next post or null if not found.
		 */
		public static function get_next_by_meta( int $post_id, string $meta_key ): ?int {
			$current_value = Meta::get_cast( 'post', $post_id, $meta_key, 'int' );
			if ( $current_value === null ) {
				return null;
			}

			$args = [
				'post_type'      => get_post_type( $post_id ),
				'posts_per_page' => 1,
				'meta_key'       => $meta_key,
				'meta_value'     => $current_value,
				'meta_compare'   => '>',
				'orderby'        => 'meta_value_num',
				'order'          => 'ASC',
			];

			$next_post = get_posts( $args );

			return ! empty( $next_post ) ? $next_post[0]->ID : null;
		}

		/** Conditional **************************************************************/

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
		 * Check if a post is a revision.
		 *
		 * @param int $post_id The ID of the post.
		 *
		 * @return bool True if the post is a revision, false otherwise.
		 */
		public static function is_revision( int $post_id ): bool {
			return wp_is_post_revision( $post_id ) !== false;
		}

		/**
		 * Check if a post is in a specific format.
		 *
		 * @param int    $post_id The ID of the post.
		 * @param string $format  The format to check for (e.g., 'aside', 'gallery', 'link', 'image', 'quote', 'status', 'video', 'audio', 'chat').
		 *
		 * @return bool True if the post is in the specified format, false otherwise.
		 */
		public static function is_format( int $post_id, string $format ): bool {
			return has_post_format( $format, $post_id );
		}

		/**
		 * Check if a post is a child of another post.
		 *
		 * @param int $post_id   The ID of the post to check.
		 * @param int $parent_id The ID of the potential parent post.
		 *
		 * @return bool True if the post is a child of the specified parent, false otherwise.
		 */
		public static function is_child_of( int $post_id, int $parent_id ): bool {
			$post = get_post( $post_id );

			return $post && $post->post_parent === $parent_id;
		}

		/**
		 * Check if a post is excluded based on its meta or its terms' meta.
		 *
		 * @param int    $post_id            The ID of the post to check.
		 * @param string $exclusion_key      The meta key that indicates exclusion.
		 * @param string $taxonomy           Optional. The taxonomy to check for term-based exclusion. Default is 'category'.
		 * @param string $term_exclusion_key Optional. The term meta key that indicates exclusion. Default is 'exclude'.
		 *
		 * @return bool True if the post is excluded, false otherwise.
		 */
		public static function is_excluded( int $post_id, string $exclusion_key, string $taxonomy = 'category', string $term_exclusion_key = 'exclude' ): bool {
			if ( self::is_excluded_by_meta( $post_id, $exclusion_key ) ) {
				return true;
			}

			// Check if any of the post's terms are excluded
			return Taxonomy::is_excluded_by_terms( $post_id, $taxonomy, $term_exclusion_key );
		}

		/**
		 * Check if a post is excluded based on its own meta.
		 *
		 * @param int    $post_id       The ID of the post to check.
		 * @param string $exclusion_key The meta key that indicates exclusion.
		 *
		 * @return bool True if the post is excluded, false otherwise.
		 */
		private static function is_excluded_by_meta( int $post_id, string $exclusion_key ): bool {
			return (bool) get_post_meta( $post_id, $exclusion_key, true );
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

			return $post && ! empty( $post->post_password );
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
		 * Check if a post has comments.
		 *
		 * @param int $post_id The ID of the post.
		 *
		 * @return bool True if the post has comments, false otherwise.
		 */
		public static function has_comments( int $post_id ): bool {
			return get_comments_number( $post_id ) > 0;
		}

		/**
		 * Check if a post has been modified since publication.
		 *
		 * @param int $post_id The ID of the post.
		 *
		 * @return bool True if the post has been modified, false otherwise.
		 */
		public static function has_been_modified( int $post_id ): bool {
			$post = get_post( $post_id );

			return $post && $post->post_modified > $post->post_date;
		}

		/**
		 * Check if a post has a custom field (meta key).
		 *
		 * @param int    $post_id  The ID of the post.
		 * @param string $meta_key The meta key to check for.
		 *
		 * @return bool True if the post has the custom field, false otherwise.
		 */
		public static function has_custom_field( int $post_id, string $meta_key ): bool {
			return metadata_exists( 'post', $post_id, $meta_key );
		}

		/**
		 * Check if a post has a specific shortcode.
		 *
		 * @param int    $post_id   The ID of the post.
		 * @param string $shortcode The shortcode to check for.
		 *
		 * @return bool True if the post contains the shortcode, false otherwise.
		 */
		public static function has_shortcode( int $post_id, string $shortcode ): bool {
			$post = get_post( $post_id );

			return $post && has_shortcode( $post->post_content, $shortcode );
		}

		/**
		 * Check if a post contains a specific block.
		 *
		 * @param int    $post_id The post ID.
		 * @param string $block   The block name to check for.
		 *
		 * @return bool True if the post contains the block, false otherwise.
		 */
		public static function has_block( int $post_id, string $block ): bool {
			$post = get_post( $post_id );

			return $post && has_block( $block, $post->post_content );
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

		/** Custom Post Type *********************************************************/

		/**
		 * Check if a post is of a specific post type.
		 *
		 * @param string   $post_type The post type to check against.
		 * @param int|null $post_id   Optional. The post ID to check. Default is the current post ID.
		 *
		 * @return bool True if the post is of the specified post type, false otherwise.
		 */
		public static function is_post_type( string $post_type, int $post_id = null ): bool {
			$post_id = $post_id ?: get_the_ID();

			if ( empty( $post_id ) ) {
				return false;
			}

			return $post_type === get_post_type( $post_id );
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

		/** Custom Post Type *********************************************************/

		/**
		 * Retrieve the URI of a specific post.
		 *
		 * @param int   $post_id    Post ID.
		 * @param array $query_args Optional. Query arguments to append to the URI. Default empty array.
		 * @param bool  $force_ssl  Optional. Force the URI to be HTTPS. Default false.
		 *
		 * @return string The URI of the specified post, or an empty string if not found.
		 */
		public static function get_uri( int $post_id, array $query_args = [], bool $force_ssl = false ): string {
			$post_uri = get_permalink( $post_id );

			// If post not found, return empty string
			if ( ! $post_uri ) {
				return '';
			}

			// Force HTTPS if required
			if ( $force_ssl ) {
				$post_uri = set_url_scheme( $post_uri, 'https' );
			}

			// Add query arguments if any
			if ( ! empty( $query_args ) ) {
				$post_uri = add_query_arg( $query_args, $post_uri );
			}

			return $post_uri;
		}

		/**
		 * Get the parent page ID.
		 *
		 * @param int|WP_Post $page Page ID or WP_Post object.
		 *
		 * @return int Parent page ID, or 0 if there's no parent.
		 */
		public static function get_parent_id( $page ): int {
			$post = get_post( $page );

			return $post ? $post->post_parent : 0;
		}

		/**
		 * Check if the page is a parent page (has no parent).
		 *
		 * @param int|WP_Post $page Page ID or WP_Post object.
		 *
		 * @return bool True if it's a parent page, false otherwise.
		 */
		public static function is_parent_page( $page ): bool {
			return self::get_parent_id( $page ) === 0;
		}

		/**
		 * Get child pages of a specific page.
		 *
		 * @param int|WP_Post $page Page ID or WP_Post object.
		 *
		 * @return array An array of child page IDs.
		 */
		public static function get_child_pages( $page ): array {
			$children = get_pages( [
				'child_of' => $page,
			] );

			return array_map( function ( $child ) {
				return $child->ID;
			}, $children );
		}

		/**
		 * Check if a page has child pages.
		 *
		 * @param int|WP_Post $page Page ID or WP_Post object.
		 *
		 * @return bool True if the page has children, false otherwise.
		 */
		public static function has_children( $page ): bool {
			$children = self::get_child_pages( $page );

			return ! empty( $children );
		}

		/**
		 * Determines if we're currently on a specific page
		 *
		 * @param int|string $page_id   The ID or slug of the page to check
		 * @param string     $shortcode Optional. Shortcode to check for
		 * @param string     $block     Optional. Block name to check for
		 *
		 * @return bool True if on the specified page, false otherwise
		 */
		public static function is_specific_page( $page_id, string $shortcode = '', string $block = '' ): bool {
			global $wp_query;

			$is_object_set    = isset( $wp_query->queried_object );
			$is_object_id_set = isset( $wp_query->queried_object_id );
			$is_specific_page = is_page( $page_id );

			if ( ! $is_object_set ) {
				unset( $wp_query->queried_object );
			} elseif ( is_singular() ) {
				$content = $wp_query->queried_object->post_content;
			}

			if ( ! $is_object_id_set ) {
				unset( $wp_query->queried_object_id );
			}

			// If we know this isn't the primary page, check other methods.
			if ( ! $is_specific_page && isset( $content ) ) {
				if ( ( ! empty( $shortcode ) && has_shortcode( $content, $shortcode ) ) ||
				     ( ! empty( $block ) && function_exists( 'has_block' ) && has_block( $block, $content ) ) ) {
					$is_specific_page = true;
				}
			}

			// Filter & return
			return apply_filters( 'is_specific_page', $is_specific_page, $page_id, $shortcode, $block );
		}

		/**
		 * Get the URL of a specific page
		 *
		 * @param int|string $page_id  The ID or slug of the page
		 * @param array      $args     Extra query args to add to the URI
		 * @param bool       $no_cache Optional. Whether to add cache busting. Default false.
		 *
		 * @return string|null Full URL to the page, if present | null if it doesn't exist
		 */
		public static function get_page_uri( $page_id, array $args = [], bool $no_cache = false ): ?string {
			$uri = null;

			if ( self::is_specific_page( $page_id ) ) {
				global $post;
				$uri = $post instanceof WP_Post ? get_permalink( $post->ID ) : null;
			}

			if ( empty( $uri ) ) {
				$uri = get_permalink( $page_id );
			}

			if ( ! empty( $args ) ) {
				// Check for backward compatibility
				if ( is_string( $args ) ) {
					$args = str_replace( '?', '', $args );
				}
				$args = wp_parse_args( $args );
				$uri  = add_query_arg( $args, $uri );
			}

			$scheme   = defined( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN ? 'https' : 'admin';
			$ajax_url = admin_url( 'admin-ajax.php', $scheme );

			if ( ( ! preg_match( '/^https/', $uri ) && preg_match( '/^https/', $ajax_url ) ) || self::is_ssl_enforced() ) {
				$uri = preg_replace( '/^http:/', 'https:', $uri );
			}

			if ( $no_cache ) {
				$uri = self::add_cache_busting( $uri );
			}

			return apply_filters( 'get_page_uri', $uri, $page_id, $args );
		}

		/**
		 * Gets a page URI with a specific query parameter.
		 *
		 * @param int|string $page_id     The ID or slug of the page
		 * @param string     $param_name  The name of the query parameter
		 * @param mixed      $param_value The value of the query parameter
		 *
		 * @return string
		 */
		public static function get_page_uri_with_param( $page_id, string $param_name, $param_value ): string {
			$query_args = [
				$param_name => $param_value,
			];

			return self::get_page_uri( $page_id, $query_args );
		}

		/**
		 * Check if SSL is being enforced.
		 *
		 * @return bool
		 */
		private static function is_ssl_enforced(): bool {
			return apply_filters( 'is_ssl_enforced', false );
		}

		/**
		 * Add cache busting to a URL.
		 *
		 * @param string $url The URL to add cache busting to.
		 *
		 * @return string
		 */
		private static function add_cache_busting( string $url ): string {
			return add_query_arg( 'nocache', uniqid(), $url );
		}

		/**
		 * Get the age of a post in days since it was published.
		 *
		 * @param int  $post_id              The ID of the post.
		 * @param bool $include_draft_time   Optional. Whether to include time spent as draft. Default false.
		 *
		 * @return int|null Number of days since the post was published, or null if post doesn't exist.
		 */
		public static function get_age( int $post_id, bool $include_draft_time = false ): ?int {
			$post = get_post( $post_id );

			if ( ! $post ) {
				return null;
			}

			// Get the current time in the site's timezone
			$current_time = current_time( 'timestamp', true );

			$post_date = $include_draft_time ?
				strtotime( $post->post_date_gmt ) :
				strtotime( $post->post_modified_gmt );

			// Calculate difference in days
			$age_in_days = floor( ( $current_time - $post_date ) / DAY_IN_SECONDS );

			return (int) max( 0, $age_in_days );
		}

	}

endif;