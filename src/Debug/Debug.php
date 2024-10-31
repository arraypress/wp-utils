<?php
/**
 * Debug Utilities
 *
 * This class provides utility functions for debugging and logging,
 * with consistent styling and output formatting.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       2.1.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Debug;

/**
 * Check if the class `Debug` is defined, and if not, define it.
 */
if ( ! class_exists( 'Debug' ) ):
	/**
	 * Class Debug.
	 *
	 * @since 1.0.0
	 */
	class Debug {
		/**
		 * Track if styles have been enqueued
		 */
		private static bool $styles_enqueued = false;

		/**
		 * Get debug assets directory path
		 *
		 * @return string
		 */
		private static function get_assets_dir(): string {
			return dirname( __FILE__ ) . '/assets';
		}

		/**
		 * Get debug assets URL
		 *
		 * @return string
		 */
		private static function get_assets_url(): string {
			return plugins_url( 'assets', __FILE__ );
		}

		/**
		 * Check if debug mode is enabled.
		 *
		 * @return bool
		 */
		public static function is_enabled(): bool {
			$wp_debug     = defined( 'WP_DEBUG' ) && WP_DEBUG;
			$script_debug = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG;

			return $wp_debug || $script_debug;
		}

		/**
		 * Ensure debug styles are loaded
		 */
		private static function ensure_styles(): void {
			if ( self::$styles_enqueued || ! self::is_enabled() ) {
				return;
			}

			// Check if admin_enqueue_scripts has already fired
			if ( did_action( 'admin_enqueue_scripts' ) ) {
				self::enqueue_styles();
			} else {
				add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_styles' ] );
			}

			self::$styles_enqueued = true;
		}

		/**
		 * Enqueue debug styles
		 */
		public static function enqueue_styles(): void {
			if ( ! self::is_enabled() ) {
				return;
			}

			$assets_dir = self::get_assets_dir();
			$assets_url = self::get_assets_url();

			wp_enqueue_style(
				'arraypress-debug-styles',
				$assets_url . '/css/debug-styles.css',
				[],
				filemtime( $assets_dir . '/css/debug-styles.css' )
			);
		}

		/**
		 * Pretty print debug information with styling
		 *
		 * @param mixed  $data  Data to debug
		 * @param string $title Optional title for the debug output
		 * @param bool   $trace Whether to include stack trace
		 *
		 * @return void
		 */
		public static function pretty_print( $data, string $title = '', bool $trace = false ): void {
			if ( ! self::is_enabled() ) {
				return;
			}

			// Ensure styles are loaded
			self::ensure_styles();

			$output = '<div class="debug-container">';

			if ( $title ) {
				$output .= '<div class="debug-header">' . esc_html( $title ) . '</div>';
			}

			$output .= self::format_value( $data );

			if ( $trace ) {
				$output .= '<div class="debug-trace">';
				$output .= '<strong>Stack Trace:</strong><br>';
				foreach ( self::stacktrace() as $trace_entry ) {
					$output .= '<div class="debug-trace-entry">' . esc_html( $trace_entry ) . '</div>';
				}
				$output .= '</div>';
			}

			$output .= '</div>';

			echo $output;
		}

		/**
		 * Format a value for display
		 *
		 * @param mixed $value The value to format
		 * @param int   $depth Current recursion depth
		 *
		 * @return string
		 */
		private static function format_value( $value, int $depth = 0 ): string {
			$indent = str_repeat( '&nbsp;&nbsp;&nbsp;&nbsp;', $depth );

			if ( is_string( $value ) ) {
				return '<span class="debug-value string">"' . esc_html( $value ) . '"</span>';
			}

			if ( is_numeric( $value ) ) {
				return '<span class="debug-value number">' . $value . '</span>';
			}

			if ( is_bool( $value ) ) {
				return '<span class="debug-value boolean">' . ( $value ? 'true' : 'false' ) . '</span>';
			}

			if ( is_null( $value ) ) {
				return '<span class="debug-null">null</span>';
			}

			if ( is_array( $value ) ) {
				$output = '<div class="debug-array">';
				$output .= 'Array (' . count( $value ) . ') {<div class="debug-tree">';
				foreach ( $value as $key => $item ) {
					$output .= '<div class="debug-tree-item">';
					$output .= $indent . '[' . esc_html( $key ) . '] => ' . self::format_value( $item, $depth + 1 );
					$output .= '</div>';
				}
				$output .= '</div>}</div>';

				return $output;
			}

			if ( is_object( $value ) ) {
				$class  = get_class( $value );
				$output = '<div class="debug-object">';
				$output .= $class . ' {<div class="debug-tree">';
				foreach ( get_object_vars( $value ) as $key => $item ) {
					$output .= '<div class="debug-tree-item">';
					$output .= $indent . esc_html( $key ) . ' => ' . self::format_value( $item, $depth + 1 );
					$output .= '</div>';
				}
				$output .= '</div>}</div>';

				return $output;
			}

			return esc_html( print_r( $value, true ) );
		}

		/**
		 * Display an error message with styling
		 *
		 * @param string $message Error message
		 * @param bool   $trace   Include stack trace
		 *
		 * @return void
		 */
		public static function error( string $message, bool $trace = false ): void {
			if ( ! self::is_enabled() ) {
				return;
			}

			self::ensure_styles();

			$output = '<div class="debug-container">';
			$output .= '<div class="debug-error">';
			$output .= '<strong>Error:</strong> ' . esc_html( $message );
			$output .= '</div>';

			if ( $trace ) {
				$output .= '<div class="debug-trace">';
				foreach ( self::stacktrace() as $trace_entry ) {
					$output .= '<div class="debug-trace-entry">' . esc_html( $trace_entry ) . '</div>';
				}
				$output .= '</div>';
			}

			$output .= '</div>';

			echo $output;
		}

		/**
		 * Display a success message with styling
		 *
		 * @param string $message Success message
		 *
		 * @return void
		 */
		public static function success( string $message ): void {
			if ( ! self::is_enabled() ) {
				return;
			}

			self::ensure_styles();

			echo '<div class="debug-container">';
			echo '<div class="debug-success">';
			echo '<strong>Success:</strong> ' . esc_html( $message );
			echo '</div>';
			echo '</div>';
		}

		/**
		 * Display a warning message with styling
		 *
		 * @param string $message Warning message
		 * @param bool   $trace   Include stack trace
		 *
		 * @return void
		 */
		public static function warning( string $message, bool $trace = false ): void {
			if ( ! self::is_enabled() ) {
				return;
			}

			self::ensure_styles();

			$output = '<div class="debug-container">';
			$output .= '<div class="debug-warning">';
			$output .= '<strong>Warning:</strong> ' . esc_html( $message );
			$output .= '</div>';

			if ( $trace ) {
				$output .= '<div class="debug-trace">';
				foreach ( self::stacktrace() as $trace_entry ) {
					$output .= '<div class="debug-trace-entry">' . esc_html( $trace_entry ) . '</div>';
				}
				$output .= '</div>';
			}

			$output .= '</div>';

			echo $output;
		}

		/**
		 * Log data to the console with optional trace
		 *
		 * @param mixed $data  Data to log
		 * @param bool  $trace Whether to log the stacktrace
		 *
		 * @return void
		 */
		public static function console_log( $data, bool $trace = false ): void {
			if ( ! self::is_enabled() ) {
				return;
			}

			add_action( 'wp_footer', static fn() => self::render_log( $data, $trace ) );
			add_action( 'admin_footer', static fn() => self::render_log( $data, $trace ) );
		}

		/**
		 * Get the stack trace
		 *
		 * @return array
		 */
		public static function stacktrace(): array {
			$backtrace  = debug_backtrace();
			$stacktrace = [];

			foreach ( $backtrace as $index => $trace ) {
				if ( ! isset( $trace['file'] ) || ! isset( $trace['line'] ) ) {
					continue;
				}

				if ( 0 === $index ) {
					continue;
				}

				$stacktrace[] = $trace['file'] . ': ' . $trace['line'];
			}

			return $stacktrace;
		}

		/**
		 * Render console log
		 *
		 * @param mixed $data  Data to log
		 * @param bool  $trace Whether to log the stacktrace
		 *
		 * @return void
		 */
		private static function render_log( $data, bool $trace = true ): void {
			$stacktrace = self::stacktrace();

			echo '<script>';
			echo 'console.group("Debug Output");';
			echo 'console.log(' . json_encode( $data ) . ');';

			if ( $trace && $stacktrace ) {
				echo 'console.group("Stack Trace");';
				foreach ( $stacktrace as $trace ) {
					echo 'console.log(' . json_encode( $trace ) . ');';
				}
				echo 'console.groupEnd();';
			}

			echo 'console.groupEnd();';
			echo '</script>';
		}

		/**
		 * Output a table of data with styling
		 *
		 * @param array  $data  Array of data to display in table format
		 * @param string $title Optional table title
		 *
		 * @return void
		 */
		public static function table( array $data, string $title = '' ): void {
			if ( ! self::is_enabled() || empty( $data ) ) {
				return;
			}

			self::ensure_styles();

			$output = '<div class="debug-container">';

			if ( $title ) {
				$output .= '<div class="debug-header">' . esc_html( $title ) . '</div>';
			}

			$output .= '<table class="debug-table">';

			// Table headers
			$output .= '<tr>';
			foreach ( array_keys( reset( $data ) ) as $header ) {
				$output .= '<th>' . esc_html( $header ) . '</th>';
			}
			$output .= '</tr>';

			// Table data
			foreach ( $data as $row ) {
				$output .= '<tr>';
				foreach ( $row as $cell ) {
					$output .= '<td>' . self::format_value( $cell ) . '</td>';
				}
				$output .= '</tr>';
			}

			$output .= '</table></div>';

			echo $output;
		}

		/**
		 * Output variable type and content information
		 *
		 * @param mixed  $var   Variable to inspect
		 * @param string $title Optional title
		 *
		 * @return void
		 */
		public static function inspect( $var, string $title = '' ): void {
			if ( ! self::is_enabled() ) {
				return;
			}

			self::ensure_styles();

			$type = gettype( $var );
			if ( $type === 'object' ) {
				$type .= ' (' . get_class( $var ) . ')';
			}

			$output = '<div class="debug-container">';

			if ( $title ) {
				$output .= '<div class="debug-header">' . esc_html( $title ) . '</div>';
			}

			$output .= '<div class="debug-section">';
			$output .= '<span class="debug-label">Type:</span>';
			$output .= '<span class="debug-value">' . esc_html( $type ) . '</span>';
			$output .= '</div>';

			$output .= '<div class="debug-section">';
			$output .= '<span class="debug-label">Value:</span>';
			$output .= self::format_value( $var );
			$output .= '</div>';

			if ( is_object( $var ) ) {
				$output  .= '<div class="debug-section">';
				$output  .= '<span class="debug-label">Methods:</span>';
				$output  .= '<div class="debug-tree">';
				$methods = get_class_methods( $var );
				foreach ( $methods as $method ) {
					$output .= '<div class="debug-tree-item">' . esc_html( $method ) . '()</div>';
				}
				$output .= '</div>';
				$output .= '</div>';
			}

			$output .= '</div>';

			echo $output;
		}
	}
endif;