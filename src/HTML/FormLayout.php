<?php
/**
 * Form Layout Generator
 *
 * A configuration-based form generator for WordPress that enables complex form layouts
 * to be created using array structures. By separating form layout from field generation,
 * this class allows for flexible form composition through sections, rows, and fields
 * while maintaining WordPress coding standards and security practices.
 *
 * Example usage:
 * ```php
 * $form = [
 *     'type' => 'section',
 *     'args' => [
 *         'title' => 'User Settings',
 *         'desc'  => 'Configure your preferences'
 *     ],
 *     'content' => [
 *         [
 *             'type' => 'row',
 *             'content' => [
 *                 'field' => 'text',
 *                 'args' => [
 *                     'label' => 'Username',
 *                     'name'  => 'user_name',
 *                     'class' => 'regular-text'
 *                 ]
 *             ]
 *         ]
 *     ]
 * ];
 *
 * echo FormLayout::render($form);
 * ```
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.2.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\HTML;

class FormLayout {

	/**
	 * Render a form structure from an array configuration
	 *
	 * @param array $structure The form structure configuration
	 *
	 * @return string The rendered HTML
	 */
	public static function render( array $structure ): string {
		return self::build_element( $structure );
	}

	/**
	 * Build an element from its configuration
	 *
	 * @param array $config Element configuration
	 *
	 * @return string Rendered HTML
	 */
	private static function build_element( array $config ): string {
		// Handle array of elements
		if ( isset( $config[0] ) ) {
			return implode( '', array_map( [ self::class, 'build_element' ], $config ) );
		}

		$type    = $config['type'] ?? '';
		$args    = $config['args'] ?? [];
		$content = '';

		// Handle content
		if ( isset( $config['content'] ) ) {
			if ( is_array( $config['content'] ) ) {
				if ( isset( $config['content']['field'] ) ) {
					// Direct field configuration
					$content = self::create_field(
						$config['content']['field'],
						$config['content']['args'] ?? []
					);
				} else {
					// Nested structure
					$content = self::build_element( $config['content'] );
				}
			} else {
				$content = $config['content'];
			}
		}

		// Handle different element types
		switch ( $type ) {
			case 'section':
				return Field::section( $content, $args );

			case 'row':
				return Field::row( $content, $args );

			case 'heading':
				$heading = '';
				if ( ! empty( $args['title'] ) ) {
					$heading .= Element::create( 'h2',
						[ 'class' => 'title' ],
						esc_html( $args['title'] )
					);
				}
				if ( ! empty( $args['desc'] ) ) {
					$heading .= Element::create( 'p',
						[ 'class' => 'description' ],
						esc_html( $args['desc'] )
					);
				}

				return $heading;

			default:
				return $content;
		}
	}

	/**
	 * Create a field using the Field class
	 *
	 * @param string $type Field type
	 * @param array  $args Field arguments
	 *
	 * @return string Rendered field
	 */
	private static function create_field( string $type, array $args ): string {
		if ( method_exists( Field::class, $type ) ) {
			return Field::$type( $args );
		}

		return '';
	}

}