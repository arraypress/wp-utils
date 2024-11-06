<?php
/**
 * Post Types Utility Class for WordPress
 *
 * This class provides utility functions for working with WordPress post types, including
 * methods for retrieving supported post types, checking post type features, and getting
 * formatted options for post types.
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
 * Class Post Types
 *
 * Utility functions for working with multiple post types.
 */
class PostTypes {

	/**
	 * Check if multiple post types exist.
	 *
	 * @param array $post_types Array of post type names to check.
	 *
	 * @return array Array of existing post type names.
	 */
	public static function exists( array $post_types ): array {
		if ( empty( $post_types ) ) {
			return [];
		}

		return array_filter( $post_types, function ( $post_type ) {
			return post_type_exists( $post_type );
		} );
	}

	/**
	 * Get multiple post type objects at once.
	 *
	 * @param array $post_types          Array of post type names to retrieve.
	 * @param bool  $include_nonexistent Whether to include non-existent post types in result. Default false.
	 *
	 * @return array Array of post type objects with post type names as keys.
	 */
	public static function get( array $post_types, bool $include_nonexistent = false ): array {
		if ( empty( $post_types ) ) {
			return [];
		}

		$results = [];
		foreach ( $post_types as $post_type ) {
			$post_type_obj = get_post_type_object( $post_type );

			if ( $post_type_obj instanceof WP_Post_Type ) {
				$results[ $post_type ] = $post_type_obj;
			} elseif ( $include_nonexistent ) {
				$results[ $post_type ] = null;
			}
		}

		return $results;
	}

	/**
	 * Get multiple post type labels at once.
	 *
	 * @param array  $post_types Array of post type names.
	 * @param string $label      Optional. Specific label to retrieve (e.g., 'name', 'singular_name').
	 *                           If empty, returns all labels.
	 *
	 * @return array Array of post type labels with post type names as keys.
	 */
	public static function get_labels( array $post_types, string $label = '' ): array {
		if ( empty( $post_types ) ) {
			return [];
		}

		$results = [];
		foreach ( $post_types as $post_type ) {
			$post_type_obj = get_post_type_object( $post_type );
			if ( ! $post_type_obj ) {
				continue;
			}

			if ( $label ) {
				$results[ $post_type ] = $post_type_obj->labels->$label ?? null;
			} else {
				$results[ $post_type ] = $post_type_obj->labels;
			}
		}

		return $results;
	}

	/**
	 * Check which post types support specific features.
	 *
	 * @param array  $post_types Array of post type names to check.
	 * @param string $feature    Feature to check for.
	 *
	 * @return array Array of post type names that support the feature.
	 */
	public static function supports_feature( array $post_types, string $feature ): array {
		if ( empty( $post_types ) || empty( $feature ) ) {
			return [];
		}

		return array_filter( $post_types, function ( $post_type ) use ( $feature ) {
			return post_type_supports( $post_type, $feature );
		} );
	}

	/**
	 * Get capabilities for multiple post types.
	 *
	 * @param array $post_types Array of post type names.
	 *
	 * @return array Array of capabilities objects with post type names as keys.
	 */
	public static function get_capabilities( array $post_types ): array {
		if ( empty( $post_types ) ) {
			return [];
		}

		$results = [];
		foreach ( $post_types as $post_type ) {
			$post_type_obj = get_post_type_object( $post_type );
			if ( $post_type_obj ) {
				$results[ $post_type ] = $post_type_obj->cap;
			}
		}

		return $results;
	}

	/**
	 * Check which post types are hierarchical.
	 *
	 * @param array $post_types Array of post type names to check.
	 *
	 * @return array Array of hierarchical post type names.
	 */
	public static function get_hierarchical( array $post_types ): array {
		if ( empty( $post_types ) ) {
			return [];
		}

		return array_filter( $post_types, function ( $post_type ) {
			$post_type_obj = get_post_type_object( $post_type );

			return $post_type_obj ? $post_type_obj->hierarchical : false;
		} );
	}

	/**
	 * Get post type options in a standardized format.
	 *
	 * @param array  $args             Optional. Arguments to filter post types. Default empty array.
	 * @param bool   $exclude_defaults Optional. Whether to exclude default post types. Default false.
	 * @param string $label_field      Optional. Which field to use as label. Accepts 'singular_name' or 'name'.
	 *                                 Default 'singular_name'.
	 *
	 * @return array An array of post type options in label/value format.
	 */
	public static function get_options( array $args = [], bool $exclude_defaults = false, string $label_field = 'singular_name' ): array {
		// Set default arguments if none provided
		$defaults = [
			'public'  => true,
			'show_ui' => true,
		];
		$args     = wp_parse_args( $args, $defaults );

		// Get post types as objects to access their properties
		$post_types = get_post_types( $args, 'objects' );

		if ( empty( $post_types ) || ! is_array( $post_types ) ) {
			return [];
		}

		// Remove default post types if specified
		if ( $exclude_defaults ) {
			unset( $post_types['post'], $post_types['page'], $post_types['attachment'] );
		}

		$options = [];
		foreach ( $post_types as $post_type => $post_type_obj ) {
			if ( ! isset( $post_type, $post_type_obj->labels ) ) {
				continue;
			}

			// Get the appropriate label based on the label_field parameter
			if ( $label_field === 'name' ) {
				$label = $post_type_obj->labels->name ?? $post_type_obj->labels->singular_name ?? $post_type;
			} else {
				$label = $post_type_obj->labels->singular_name ?? $post_type_obj->labels->name ?? $post_type;
			}

			$options[] = [
				'value' => esc_attr( $post_type ),
				'label' => esc_html( $label ),
			];
		}

		// Sort options alphabetically by label
		usort( $options, static function ( $a, $b ) {
			return strcasecmp( $a['label'], $b['label'] );
		} );

		return $options;
	}

	/**
	 * Get the post types that support Gutenberg.
	 *
	 * @return array An array of post type names that support Gutenberg.
	 */
	public static function get_editor_supported(): array {
		$supported_post_types = [];
		foreach ( get_post_types_by_support( 'editor' ) as $post_type ) {
			if ( use_block_editor_for_post_type( $post_type ) ) {
				$supported_post_types[] = $post_type;
			}
		}

		return $supported_post_types;
	}

	/**
	 * Get all registered post types with specific criteria.
	 *
	 * @param array  $args             Optional. Arguments to filter post types.
	 * @param string $output           Optional. The type of output to return. Accepts 'names' or 'objects'.
	 * @param bool   $exclude_defaults Optional. Whether to exclude default post types.
	 *
	 * @return array Array of post type names or objects.
	 */
	public static function get_registered( array $args = [], string $output = 'names', bool $exclude_defaults = false ): array {
		$defaults = [ 'public' => true, 'show_ui' => true ];
		$args     = wp_parse_args( $args, $defaults );

		$post_types = get_post_types( $args, $output );

		if ( $exclude_defaults ) {
			unset( $post_types['post'], $post_types['page'], $post_types['attachment'] );
		}

		return $post_types;
	}

	/**
	 * Get post types that support a specific feature.
	 *
	 * @param string $feature The feature to check for.
	 *
	 * @return array Array of post type names that support the feature.
	 */
	public static function get_types_by_feature( string $feature ): array {
		return get_post_types_by_support( $feature );
	}

}