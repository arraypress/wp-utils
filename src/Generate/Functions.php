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

namespace ArrayPress\Utils;

use ArrayPress\Utils\Generate\Table;
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

