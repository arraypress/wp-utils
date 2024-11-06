<?php
/**
 * Taxonomies Utility Class for WordPress
 *
 * This class provides a comprehensive set of utility functions for working with WordPress taxonomies.
 * It offers methods for retrieving, filtering, and analyzing taxonomies across the WordPress system.
 * The class is designed to simplify common taxonomy-related operations and extend WordPress's built-in taxonomy
 * functionality.
 *
 * Key features include:
 * - Retrieval of registered taxonomies with various filtering options
 * - Analysis of taxonomy relationships with post types
 * - Utility functions for working with multiple taxonomies
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Taxonomies;

/**
 * Class Taxonomies
 *
 * Utility functions for working with multiple taxonomies.
 */
class Taxonomies {

	/**
	 * Check if multiple taxonomies exist.
	 *
	 * @param array $taxonomies An array of taxonomy names to check.
	 *
	 * @return bool True if all specified taxonomies exist, false otherwise.
	 */
	public static function exists( array $taxonomies ): bool {
		foreach ( $taxonomies as $taxonomy ) {
			if ( ! taxonomy_exists( $taxonomy ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Get all registered taxonomies.
	 *
	 * @param array  $args   Optional. Arguments to filter taxonomies.
	 * @param string $output Optional. The type of output to return. Accepts 'names' or 'objects'.
	 *
	 * @return array An array of taxonomy names or WP_Taxonomy objects.
	 */
	public static function get( array $args = [], string $output = 'names' ): array {
		return get_taxonomies( $args, $output );
	}

	/**
	 * Get registered taxonomies and return them in label/value format.
	 *
	 * @param array $args Optional. Arguments to filter taxonomies.
	 *
	 * @return array An array of taxonomies in label/value format.
	 */
	public static function get_options( array $args = [] ): array {
		$taxonomies = self::get( $args, 'objects' );

		if ( empty( $taxonomies ) ) {
			return [];
		}

		$options = [];

		foreach ( $taxonomies as $taxonomy => $details ) {
			$options[] = [
				'value' => esc_attr( $taxonomy ),
				'label' => esc_html( $details->label ),
			];
		}

		return $options;
	}

	/**
	 * Get all public taxonomies.
	 *
	 * @param string $output Optional. The type of output to return. Accepts 'names' or 'objects'.
	 *
	 * @return array An array of public taxonomies.
	 */
	public static function get_public( string $output = 'names' ): array {
		return self::get( [ 'public' => true ], $output );
	}

	/**
	 * Get taxonomies associated with a specific post type.
	 *
	 * @param string $post_type The post type name.
	 * @param string $output    Optional. The type of output to return. Accepts 'names' or 'objects'.
	 *
	 * @return array An array of taxonomy names or objects associated with the post type.
	 */
	public static function get_for_post_type( string $post_type, string $output = 'names' ): array {
		return get_object_taxonomies( $post_type, $output );
	}

	/**
	 * Get all hierarchical taxonomies.
	 *
	 * @param string $output Optional. The type of output to return. Accepts 'names' or 'objects'.
	 *
	 * @return array An array of hierarchical taxonomies.
	 */
	public static function get_hierarchical( string $output = 'names' ): array {
		return self::get( [ 'hierarchical' => true ], $output );
	}

	/**
	 * Get all non-hierarchical taxonomies.
	 *
	 * @param string $output Optional. The type of output to return. Accepts 'names' or 'objects'.
	 *
	 * @return array An array of non-hierarchical taxonomies.
	 */
	public static function get_non_hierarchical( string $output = 'names' ): array {
		return self::get( [ 'hierarchical' => false ], $output );
	}

	/**
	 * Get the number of terms in each taxonomy.
	 *
	 * @param array $taxonomies Optional. An array of taxonomy names. If empty, all taxonomies will be counted.
	 *
	 * @return array An associative array of taxonomy names and their term counts.
	 */
	public static function get_term_counts( array $taxonomies = [] ): array {
		$counts     = [];
		$taxonomies = empty( $taxonomies ) ? self::get() : $taxonomies;

		foreach ( $taxonomies as $taxonomy ) {
			$term_count          = wp_count_terms( [ 'taxonomy' => $taxonomy ] );
			$counts[ $taxonomy ] = is_wp_error( $term_count ) ? 0 : (int) $term_count;
		}

		return $counts;
	}

	/**
	 * Get common post types among multiple taxonomies.
	 *
	 * @param array $taxonomies An array of taxonomy names.
	 *
	 * @return array An array of post type names common to all specified taxonomies.
	 */
	public static function get_common_post_types( array $taxonomies ): array {
		if ( empty( $taxonomies ) ) {
			return [];
		}

		$common_post_types = Taxonomy::get_post_types( $taxonomies[0] );

		foreach ( $taxonomies as $taxonomy ) {
			$post_types        = Taxonomy::get_post_types( $taxonomy );
			$common_post_types = array_intersect( $common_post_types, $post_types );
		}

		return $common_post_types;
	}

	/**
	 * Compare two taxonomies and return their differences.
	 *
	 * @param string $taxonomy1 The name of the first taxonomy.
	 * @param string $taxonomy2 The name of the second taxonomy.
	 *
	 * @return array An array describing the differences between the two taxonomies.
	 */
	public static function compare( string $taxonomy1, string $taxonomy2 ): array {
		$tax1 = Taxonomy::get( $taxonomy1 );
		$tax2 = Taxonomy::get( $taxonomy2 );

		if ( ! $tax1 || ! $tax2 ) {
			return [ 'error' => 'One or both taxonomies do not exist.' ];
		}

		$differences = [];

		$properties = [
			'label',
			'description',
			'public',
			'hierarchical',
			'show_ui',
			'show_in_menu',
			'show_in_nav_menus',
			'show_tagcloud',
			'show_in_quick_edit',
			'show_admin_column',
			'meta_box_cb',
			'capabilities',
			'rewrite',
			'query_var'
		];

		foreach ( $properties as $prop ) {
			if ( $tax1->$prop !== $tax2->$prop ) {
				$differences[ $prop ] = [
					$taxonomy1 => $tax1->$prop,
					$taxonomy2 => $tax2->$prop
				];
			}
		}

		// Compare object types (associated post types)
		if ( $tax1->object_type !== $tax2->object_type ) {
			$differences['object_type'] = [
				$taxonomy1 => $tax1->object_type,
				$taxonomy2 => $tax2->object_type
			];
		}

		return $differences;
	}

}