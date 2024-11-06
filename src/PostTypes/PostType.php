<?php
/**
 * Post Type Utilities for WordPress
 *
 * This file contains two utility classes:
 * - PostType: Static utility functions for working with a single post type
 * - PostTypes: Static utility functions for working with multiple post types
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\PostTypes;

use WP_Post_Type;

/**
 * Class PostType
 *
 * Static utility functions for working with a single post type.
 */
class PostType {

	/**
	 * Check if a post type exists.
	 *
	 * @param string $post_type Post type name.
	 *
	 * @return bool Whether the post type exists.
	 */
	public static function exists( string $post_type ): bool {
		return post_type_exists( $post_type );
	}

	/**
	 * Get post type object.
	 *
	 * @param string $post_type Post type name.
	 *
	 * @return WP_Post_Type|null Post type object or null if it doesn't exist.
	 */
	public static function get( string $post_type ): ?WP_Post_Type {
		if ( ! self::exists( $post_type ) ) {
			return null;
		}

		return get_post_type_object( $post_type );
	}

	/**
	 * Check if a post type supports a specific feature.
	 *
	 * @param string $post_type Post type name.
	 * @param string $feature   Feature to check.
	 *
	 * @return bool Whether the post type supports the feature.
	 */
	public static function supports_feature( string $post_type, string $feature ): bool {
		return post_type_supports( $post_type, $feature );
	}

	/**
	 * Get available taxonomies for a post type.
	 *
	 * @param string $post_type Post type name.
	 * @param array  $args      Optional. Arguments to filter taxonomies.
	 *
	 * @return array Array of taxonomy objects.
	 */
	public static function get_taxonomies( string $post_type, array $args = [] ): array {
		return get_object_taxonomies( $post_type, $args );
	}

	/**
	 * Get the labels for a post type.
	 *
	 * @param string $post_type Post type name.
	 *
	 * @return object|null Post type labels object or null if post type doesn't exist.
	 */
	public static function get_labels( string $post_type ): ?object {
		$post_type_obj = get_post_type_object( $post_type );

		return $post_type_obj ? $post_type_obj->labels : null;
	}

	/**
	 * Check if a post type is hierarchical.
	 *
	 * @param string $post_type Post type name.
	 *
	 * @return bool Whether the post type is hierarchical.
	 */
	public static function is_hierarchical( string $post_type ): bool {
		$post_type_obj = self::get( $post_type );

		return $post_type_obj ? $post_type_obj->hierarchical : false;
	}

	/**
	 * Get the capabilities for a post type.
	 *
	 * @param string $post_type Post type name.
	 *
	 * @return object|null Post type capabilities object or null if post type doesn't exist.
	 */
	public static function get_capabilities( string $post_type ): ?object {
		$post_type_obj = self::get( $post_type );

		return $post_type_obj ? $post_type_obj->cap : null;
	}

	/**
	 * Get the archive URL for a post type.
	 *
	 * @param string $post_type Post type name.
	 *
	 * @return string|false Archive URL or false if post type doesn't exist or doesn't have an archive.
	 */
	public static function get_archive_url( string $post_type ) {
		$post_type_obj = self::get( $post_type );

		return $post_type_obj && $post_type_obj->has_archive ? get_post_type_archive_link( $post_type ) : false;
	}

	/**
	 * Check if a post type is public.
	 *
	 * @param string $post_type Post type name.
	 *
	 * @return bool Whether the post type is public.
	 */
	public static function is_public( string $post_type ): bool {
		$post_type_obj = self::get( $post_type );

		return $post_type_obj ? $post_type_obj->public : false;
	}

	/**
	 * Get rewrite rules for a post type.
	 *
	 * @param string $post_type Post type name.
	 *
	 * @return array|false Rewrite rules array or false if post type doesn't exist.
	 */
	public static function get_rewrite_rules( string $post_type ) {
		$post_type_obj = self::get( $post_type );

		return $post_type_obj ? $post_type_obj->rewrite : false;
	}

	/**
	 * Get the menu position for a post type.
	 *
	 * @param string $post_type Post type name.
	 *
	 * @return int|null Menu position or null if not set.
	 */
	public static function get_menu_position( string $post_type ): ?int {
		$post_type_obj = self::get( $post_type );

		return $post_type_obj ? $post_type_obj->menu_position : null;
	}

	/**
	 * Count posts in a post type.
	 *
	 * @param string $post_type Post type name.
	 * @param string $status    Optional. Post status to count. Default 'publish'.
	 *
	 * @return int Number of posts.
	 */
	public static function count_posts( string $post_type, string $status = 'publish' ): int {
		$counts = wp_count_posts( $post_type );

		return (int) ( $counts->{$status} ?? 0 );
	}

	/**
	 * Get the singular label for a post type.
	 *
	 * @param string $post_type Post type name.
	 *
	 * @return string|null Singular label or null if post type doesn't exist.
	 */
	public static function get_singular_label( string $post_type ): ?string {
		$post_type_obj = self::get( $post_type );

		return $post_type_obj ? $post_type_obj->labels->singular_name : null;
	}

	/**
	 * Get the plural label for a post type.
	 *
	 * @param string $post_type Post type name.
	 *
	 * @return string|null Plural label or null if post type doesn't exist.
	 */
	public static function get_plural_label( string $post_type ): ?string {
		$post_type_obj = self::get( $post_type );

		return $post_type_obj ? $post_type_obj->labels->name : null;
	}

	/**
	 * Get the menu name for a post type.
	 *
	 * @param string $post_type Post type name.
	 *
	 * @return string|null Menu name or null if post type doesn't exist.
	 */
	public static function get_menu_name( string $post_type ): ?string {
		$post_type_obj = self::get( $post_type );

		return $post_type_obj ? $post_type_obj->labels->menu_name : null;
	}

	/**
	 * Get the "Add New" label for a post type.
	 *
	 * @param string $post_type Post type name.
	 *
	 * @return string|null Add New label or null if post type doesn't exist.
	 */
	public static function get_add_new_label( string $post_type ): ?string {
		$post_type_obj = self::get( $post_type );

		return $post_type_obj ? $post_type_obj->labels->add_new : null;
	}

	/**
	 * Get specific label for a post type.
	 *
	 * @param string $post_type Post type name.
	 * @param string $label     Label key to retrieve (e.g., 'add_new', 'edit_item', 'view_item', etc.).
	 *
	 * @return string|null Requested label or null if post type or label doesn't exist.
	 */
	public static function get_label( string $post_type, string $label ): ?string {
		$post_type_obj = self::get( $post_type );

		return $post_type_obj && isset( $post_type_obj->labels->{$label} )
			? $post_type_obj->labels->{$label}
			: null;
	}

	/**
	 * Get the description for a post type.
	 *
	 * @param string $post_type Post type name.
	 *
	 * @return string|null Description or null if post type doesn't exist.
	 */
	public static function get_description( string $post_type ): ?string {
		$post_type_obj = self::get( $post_type );

		return $post_type_obj ? $post_type_obj->description : null;
	}

	/**
	 * Get the slug/name used in URLs for a post type archive.
	 *
	 * @param string $post_type Post type name.
	 *
	 * @return string|null Archive slug or null if post type doesn't exist or has no archive.
	 */
	public static function get_archive_slug( string $post_type ): ?string {
		$post_type_obj = self::get( $post_type );
		if ( ! $post_type_obj || ! $post_type_obj->has_archive ) {
			return null;
		}

		return is_string( $post_type_obj->has_archive )
			? $post_type_obj->has_archive
			: $post_type_obj->rewrite['slug'] ?? $post_type;
	}

	/**
	 * Get all available labels for a post type.
	 *
	 * @param string $post_type        Post type name.
	 * @param bool   $include_defaults Whether to include default labels. Default false.
	 *
	 * @return array|null Array of all labels or null if post type doesn't exist.
	 */
	public static function get_all_labels( string $post_type, bool $include_defaults = false ): ?array {
		$post_type_obj = self::get( $post_type );
		if ( ! $post_type_obj ) {
			return null;
		}

		$labels = (array) $post_type_obj->labels;

		if ( ! $include_defaults ) {
			// Remove labels that are using default values
			$defaults = get_post_type_labels( new \stdClass() );
			foreach ( $labels as $key => $label ) {
				if ( isset( $defaults->{$key} ) && $defaults->{$key} === $label ) {
					unset( $labels[ $key ] );
				}
			}
		}

		return $labels;
	}

	/**
	 * Check if post type supports a specific label.
	 *
	 * @param string $post_type Post type name.
	 * @param string $label     Label key to check.
	 *
	 * @return bool Whether the label exists for the post type.
	 */
	public static function has_label( string $post_type, string $label ): bool {
		$post_type_obj = self::get( $post_type );

		return $post_type_obj && isset( $post_type_obj->labels->{$label} );
	}

	/**
	 * Get the REST API base slug for a post type.
	 *
	 * @param string $post_type Post type name.
	 *
	 * @return string|null REST API base slug or null if post type doesn't exist or has no REST support.
	 */
	public static function get_rest_base( string $post_type ): ?string {
		$post_type_obj = self::get( $post_type );
		if ( ! $post_type_obj || ! $post_type_obj->show_in_rest ) {
			return null;
		}

		return $post_type_obj->rest_base ?? $post_type;
	}

}