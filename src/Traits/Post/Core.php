<?php
/**
 * Post Core Trait
 *
 * This trait provides core functionality for working with WordPress posts,
 * including post existence checking, retrieval by various identifiers (ID, title,
 * slug, path, meta, GUID), and flexible post lookup methods.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Traits\Post;

use ArrayPress\Utils\Database\Exists;

use WP_Post;

trait Core {

	/**
	 * Check if the post exists in the database.
	 *
	 * Verifies whether a post with the given ID exists in the WordPress database.
	 *
	 * @param int $post_id The ID of the post to check.
	 *
	 * @return bool True if the post exists, false otherwise.
	 */
	public static function exists( int $post_id ): bool {
		return Exists::row( 'posts', 'ID', $post_id );
	}

	/**
	 * Get a post object.
	 *
	 * Retrieves a WordPress post object for the given post ID.
	 *
	 * @param int $post_id The ID of the post to retrieve.
	 *
	 * @return WP_Post|null The post object if found, null otherwise.
	 */
	public static function get( int $post_id ): ?WP_Post {
		$post = get_post( $post_id );

		return $post instanceof WP_Post ? $post : null;
	}

	/**
	 * Get a post by its title.
	 *
	 * Retrieves a post object by matching its title.
	 *
	 * @param string       $title       The title of the post to find.
	 * @param string|array $post_type   Optional. Post type or array of post types. Default 'post'.
	 * @param string|array $post_status Optional. Post status or array of post statuses. Default 'publish'.
	 *
	 * @return WP_Post|null Post object if found, null otherwise.
	 */
	public static function get_by_title( string $title, $post_type = 'post', $post_status = 'publish' ): ?WP_Post {
		$args = [
			'post_type'              => $post_type,
			'title'                  => $title,
			'post_status'            => $post_status,
			'posts_per_page'         => 1,
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			'orderby'                => [ 'post_date' => 'ASC', 'ID' => 'ASC' ],
		];

		$posts = get_posts( $args );

		return ! empty( $posts ) ? $posts[0] : null;
	}

	/**
	 * Get a post by its slug (post_name).
	 *
	 * Retrieves a post object by matching its slug (post_name).
	 *
	 * @param string       $slug        The slug of the post to find.
	 * @param string|array $post_type   Optional. Post type or array of post types. Default 'post'.
	 * @param string|array $post_status Optional. Post status or array of post statuses. Default 'publish'.
	 *
	 * @return WP_Post|null Post object if found, null otherwise.
	 */
	public static function get_by_slug( string $slug, $post_type = 'post', $post_status = 'publish' ): ?WP_Post {
		$args = [
			'name'                   => $slug,
			'post_type'              => $post_type,
			'post_status'            => $post_status,
			'posts_per_page'         => 1,
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
		];

		$posts = get_posts( $args );

		return ! empty( $posts ) ? $posts[0] : null;
	}

	/**
	 * Get a post by its path (hierarchical slug).
	 *
	 * Retrieves a post object by matching its full path (including parent slugs).
	 *
	 * @param string       $path        The post path (e.g., parent-slug/child-slug).
	 * @param string|array $post_type   Optional. Post type or array of post types. Default 'page'.
	 * @param string|array $post_status Optional. Post status or array of post statuses. Default 'publish'.
	 *
	 * @return WP_Post|null Post object if found, null otherwise.
	 */
	public static function get_by_path( string $path, $post_type = 'page', $post_status = 'publish' ): ?WP_Post {
		$post = get_page_by_path( $path, OBJECT, $post_type );

		if ( ! $post instanceof WP_Post ) {
			return null;
		}

		// Check status if specified
		if ( $post_status !== 'any' ) {
			$status_array = (array) $post_status;
			if ( ! in_array( $post->post_status, $status_array, true ) ) {
				return null;
			}
		}

		return $post;
	}

	/**
	 * Get a post by a meta value.
	 *
	 * Retrieves a post object by matching a meta key and value.
	 *
	 * @param string       $meta_key    The meta key to search for.
	 * @param mixed        $meta_value  The meta value to match.
	 * @param string|array $post_type   Optional. Post type or array of post types. Default 'post'.
	 * @param string|array $post_status Optional. Post status or array of post statuses. Default 'publish'.
	 *
	 * @return WP_Post|null Post object if found, null otherwise.
	 */
	public static function get_by_meta( string $meta_key, $meta_value, $post_type = 'post', $post_status = 'publish' ): ?WP_Post {
		$args = [
			'post_type'              => $post_type,
			'post_status'            => $post_status,
			'posts_per_page'         => 1,
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'update_post_term_cache' => false,
			'meta_key'               => $meta_key,
			'meta_value'             => $meta_value,
		];

		$posts = get_posts( $args );

		return ! empty( $posts ) ? $posts[0] : null;
	}

	/**
	 * Get a post by its GUID.
	 *
	 * Retrieves a post object by matching its GUID.
	 *
	 * @param string       $guid        The post GUID to search for.
	 * @param string|array $post_type   Optional. Post type or array of post types. Default 'post'.
	 * @param string|array $post_status Optional. Post status or array of post statuses. Default 'publish'.
	 *
	 * @return WP_Post|null Post object if found, null otherwise.
	 */
	public static function get_by_guid( string $guid, $post_type = 'post', $post_status = 'publish' ): ?WP_Post {
		$args = [
			'post_type'              => $post_type,
			'post_status'            => $post_status,
			'posts_per_page'         => 1,
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			'guid'                   => $guid,
		];

		$posts = get_posts( $args );

		return ! empty( $posts ) ? $posts[0] : null;
	}

	/**
	 * Get a post object based on provided identifier.
	 *
	 * Attempts to retrieve a post using various identifier types (ID, title, slug, or WP_Post object).
	 *
	 * @param int|string|WP_Post $identifier  The post identifier to search for.
	 * @param string|array       $post_type   Optional. The post type(s) to search within. Default 'any'.
	 * @param string|array       $post_status Optional. The post status(es) to include. Default 'publish'.
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
	 * Get a post ID based on its title.
	 *
	 * Retrieves the ID of a post by matching its title. If multiple posts
	 * have the same title, returns the ID of the oldest post.
	 *
	 * @param string       $title_or_slug The title or slug of the post to find.
	 * @param string|array $post_type     Optional. Post type or array of post types. Default 'post'.
	 *
	 * @return int|null The post ID if found, null otherwise.
	 */
	public static function get_id_by_title( string $title_or_slug, $post_type = 'post' ): ?int {
		$args = [
			'post_type'              => $post_type,
			'title'                  => $title_or_slug,
			'post_status'            => 'any',
			'posts_per_page'         => 1,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			'orderby'                => [ 'post_date' => 'ASC', 'ID' => 'ASC' ],
			'fields'                 => 'ids',
		];

		$posts = get_posts( $args );

		// If not found by title, try by slug
		if ( empty( $posts ) ) {
			$args['name'] = sanitize_title( $title_or_slug );
			unset( $args['title'] );
			$posts = get_posts( $args );
		}

		return ! empty( $posts ) ? (int) $posts[0] : null;
	}

}