<?php
/**
 * Helper function to register roles and capabilities for WordPress
 *
 * @package       ArrayPress/WordPress-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\HTML;

use ArrayPress\Utils\HTML\WP\Table;
use Exception;

if ( ! function_exists( __NAMESPACE__ . '\render_custom_table' ) ) :
	/**
	 * Render a custom table with the provided configuration.
	 *
	 * @param array         $config         The table configuration array.
	 * @param callable|null $data_callback  Callback function to get table data.
	 * @param callable|null $error_callback Callback function for error handling.
	 *
	 * @return Table|null Returns the Table instance or null if an exception occurs.
	 */
	function render_custom_table(
		array $config,
		?callable $data_callback = null,
		?callable $error_callback = null
	): ?Table {
		try {
			// Initialize table generator
			$table = new Table();

			// Ensure required keys exist
			$defaults = [
				'key'           => 'custom-table-' . wp_generate_password( 6, false ),
				'title'         => '',
				'table_class'   => '',
				'columns'       => [],
				'empty_message' => __( 'No items found', 'arraypress' ),
			];

			$config = wp_parse_args( $config, $defaults );

			// If data callback was passed separately, add it to config
			if ( $data_callback !== null ) {
				$config['data_callback'] = $data_callback;
			}

			// Register the table
			$table->register( $config['key'], $config );

			// Render the table
			$table->render( $config['key'] );

			return $table;

		} catch ( Exception $e ) {
			if ( is_callable( $error_callback ) ) {
				call_user_func( $error_callback, $e );
			}

			return null;
		}
	}
endif;

if ( ! function_exists( __NAMESPACE__ . '\render_field' ) ) :
	/**
	 * Render a single form field using array configuration.
	 *
	 * @param array $config Field configuration array.
	 * @param array $args   Additional arguments for field wrapping.
	 *
	 * @return string HTML output of the field.
	 */
	function render_field( array $config, array $args = [] ): string {
		$defaults = [
			'type'     => 'text',     // Field type (text, textarea, select, etc.)
			'args'     => [],         // Field-specific arguments
			'wrapper'  => 'row',      // Wrapper type (row, group, none)
			'row_args' => [],         // Arguments for row wrapper if used
		];

		$config = wp_parse_args( $config, $defaults );

		// Check if method exists in Field class
		if ( ! method_exists( Field::class, $config['type'] ) ) {
			return '';
		}

		// Generate the field
		$field = Field::{$config['type']}( $config['args'] );

		// Handle wrapping
		if ( $config['wrapper'] === 'row' ) {
			return Field::row( $field, wp_parse_args( $args, $config['row_args'] ) );
		} elseif ( $config['wrapper'] === 'group' ) {
			return Field::group( $field, wp_parse_args( $args, $config['row_args'] ) );
		}

		return $field;
	}
endif;

if ( ! function_exists( __NAMESPACE__ . '\render_fields' ) ) :
	/**
	 * Render multiple form fields using array configuration.
	 *
	 * @param array  $fields       Array of field configurations.
	 * @param array  $args         Section arguments.
	 * @param string $wrapper_type Type of wrapper to use (section, div, none).
	 *
	 * @return string HTML output of all fields.
	 */
	function render_fields( array $fields, array $args = [], string $wrapper_type = 'section' ): string {
		$content = '';

		// Generate each field
		foreach ( $fields as $field ) {
			$content .= render_field( $field );
		}

		// Handle wrapping
		if ( $wrapper_type === 'section' ) {
			return Field::section( $content, $args );
		} elseif ( $wrapper_type === 'div' ) {
			return Element::div( $content, $args );
		}

		return $content;
	}
endif;

if ( ! function_exists( __NAMESPACE__ . '\render_form_layout' ) ) :
	/**
	 * Render a form layout using array configuration.
	 *
	 * Generates complex form layouts from a structured configuration array.
	 * Supports sections, rows, headings, and nested field structures.
	 *
	 * Example usage:
	 * ```php
	 * $layout = [
	 *     'type' => 'section',
	 *     'args' => [
	 *         'title' => 'User Settings',
	 *         'desc'  => 'Configure account preferences'
	 *     ],
	 *     'content' => [
	 *         [
	 *             'type' => 'heading',
	 *             'args' => [
	 *                 'title' => 'Personal Information'
	 *             ]
	 *         ],
	 *         [
	 *             'type' => 'row',
	 *             'content' => [
	 *                 'field' => 'text',
	 *                 'args' => [
	 *                     'label' => 'Full Name',
	 *                     'name'  => 'user_name'
	 *                 ]
	 *             ]
	 *         ]
	 *     ]
	 * ];
	 *
	 * echo render_form_layout($layout);
	 * ```
	 *
	 * @param array $layout The layout configuration array
	 * @param array $args   Optional global arguments that apply to all elements
	 *
	 * @return string The rendered HTML
	 */
	function render_form_layout( array $layout, array $args = [] ): string {
		return FormLayout::render( wp_parse_args( $layout, $args ) );
	}
endif;