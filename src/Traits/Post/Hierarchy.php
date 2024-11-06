<?php
/**
 * Post Hierarchy Trait
 *
 * Provides functionality for working with hierarchical post relationships,
 * including parent-child relationships, ancestry, and sibling posts.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Traits\Post;

use WP_Post;

trait Hierarchy {

	/**
	 * Get post parent details.
	 *
	 * Retrieves the parent post object for a given post.
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
	 * Get the parent page ID.
	 *
	 * Retrieves the ID of the parent page for a given page.
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
	 * Check if a post is a child of another post.
	 *
	 * Determines whether a post is a direct child of another post.
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
	 * Recursively get page children.
	 *
	 * Retrieves all child pages for a given parent page, including
	 * children of those children (recursive).
	 *
	 * @param int   $post_id Page ID.
	 * @param array $args    Additional arguments for get_posts.
	 *
	 * @return array Array of child page IDs.
	 */
	public static function get_children( int $post_id, array $args = [] ): array {
		$defaults = [
			'post_parent' => $post_id,
			'post_type'   => 'page',
			'numberposts' => - 1,
			'post_status' => 'any',
			'fields'      => 'ids',
		];

		$args     = wp_parse_args( $args, $defaults );
		$page_ids = get_posts( $args );

		foreach ( $page_ids as $child_page_id ) {
			$page_ids = array_merge( $page_ids, self::get_children( $child_page_id, $args ) );
		}

		return $page_ids;
	}

	/**
	 * Get immediate child pages.
	 *
	 * Retrieves only the direct child pages of a given parent page.
	 *
	 * @param int   $post_id The ID of the parent page.
	 * @param array $args    Optional. Additional arguments for get_posts.
	 *
	 * @return WP_Post[] Array of child page objects.
	 */
	public static function get_immediate_children( int $post_id, array $args = [] ): array {
		$default_args = [
			'post_parent'    => $post_id,
			'post_type'      => 'page',
			'posts_per_page' => - 1,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
			'post_status'    => 'publish'
		];

		$args = wp_parse_args( $args, $default_args );

		return get_posts( $args );
	}

	/**
	 * Get sibling pages.
	 *
	 * Retrieves all sibling pages (pages that share the same parent).
	 *
	 * @param int   $post_id      The ID of the reference page.
	 * @param bool  $include_self Whether to include the reference page in results.
	 * @param array $args         Optional. Additional arguments for get_posts.
	 *
	 * @return WP_Post[] Array of sibling page objects.
	 */
	public static function get_siblings( int $post_id, bool $include_self = false, array $args = [] ): array {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return [];
		}

		$default_args = [
			'post_parent'    => $post->post_parent,
			'post_type'      => get_post_type( $post ),
			'posts_per_page' => - 1,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
			'post_status'    => 'publish'
		];

		if ( ! $include_self ) {
			$default_args['exclude'] = [ $post_id ];
		}

		$args = wp_parse_args( $args, $default_args );

		return get_posts( $args );
	}

	/**
	 * Get ancestor IDs.
	 *
	 * Retrieves an array of ancestor page IDs for a given page.
	 *
	 * @param int $post_id The ID of the page.
	 *
	 * @return array Array of ancestor page IDs.
	 */
	public static function get_ancestors( int $post_id ): array {
		return get_post_ancestors( $post_id );
	}

	/**
	 * Check if page is ancestor.
	 *
	 * Determines if a page is an ancestor of another page.
	 *
	 * @param int $post_id       The ID of the potential ancestor page.
	 * @param int $descendant_id The ID of the potential descendant page.
	 *
	 * @return bool True if the page is an ancestor, false otherwise.
	 */
	public static function is_ancestor( int $post_id, int $descendant_id ): bool {
		return in_array( $post_id, get_post_ancestors( $descendant_id ), true );
	}

	/**
	 * Get the highest-level ancestor.
	 *
	 * Retrieves the top-most ancestor of a page (the root of its hierarchy).
	 *
	 * @param int $post_id The ID of the page.
	 *
	 * @return WP_Post|null The top ancestor post object or null if not found.
	 */
	public static function get_top_ancestor( int $post_id ): ?WP_Post {
		$ancestors = get_post_ancestors( $post_id );

		if ( empty( $ancestors ) ) {
			return null;
		}

		// The last ancestor in the array is the highest-level ancestor
		$top_ancestor_id = end( $ancestors );
		$ancestor        = get_post( $top_ancestor_id );

		return $ancestor instanceof WP_Post ? $ancestor : null;
	}

	/**
	 * Get hierarchical depth.
	 *
	 * Calculates how deep in the hierarchy a page is.
	 *
	 * @param int $post_id The ID of the page.
	 *
	 * @return int The depth (0 for top-level pages).
	 */
	public static function get_depth( int $post_id ): int {
		return count( get_post_ancestors( $post_id ) );
	}

	/**
	 * Check if the page is a parent page (has no parent).
	 *
	 * Determines if a page is a top-level parent page.
	 *
	 * @param int|WP_Post $page Page ID or WP_Post object.
	 *
	 * @return bool True if it's a parent page, false otherwise.
	 */
	public static function is_parent_page( $page ): bool {
		return self::get_parent_id( $page ) === 0;
	}

	/**
	 * Check if a page has child pages.
	 *
	 * Determines if a page has any child pages.
	 *
	 * @param int|WP_Post $page Page ID or WP_Post object.
	 *
	 * @return bool True if the page has children, false otherwise.
	 */
	public static function has_children( $page ): bool {
		$children = get_pages( [
			'child_of' => get_post( $page )->ID,
			'fields'   => 'ids',
		] );

		return ! empty( $children );
	}

}