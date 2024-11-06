<?php
/**
 * JavaScript Utilities
 *
 * A comprehensive utility class for handling JavaScript operations in WordPress.
 * Provides methods for formatting, sanitizing, and managing JavaScript code,
 * including inline scripts, JSON data, and script dependencies. Features include
 * minification, sanitization, and proper escaping for WordPress integration.
 *
 * Example usage:
 * ```php
 * // Format inline JavaScript
 * $js = JS::format_inline_js("function   example()  { return 'test'; }");
 *
 * // Convert PHP data for JS
 * $data = ['key' => 'value'];
 * $js_object = JS::to_object($data);
 * ```
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\HTML;

class JS {

	/**
	 * Format inline JavaScript code.
	 *
	 * @param string      $js      JavaScript code to format
	 * @param string|null $context Optional context for filtering
	 *
	 * @return string Formatted JavaScript code
	 */
	public static function format_inline_js( string $js, ?string $context = null ): string {
		// Correct double quotes to single quotes
		$js = str_replace( '"', "'", $js );

		// Trim trailing semicolon
		$js = trim( rtrim( $js, ';' ) );

		// Remove whitespace
		$js = preg_replace( '/\s+/', ' ', $js );

		// Remove zero width spaces and other invisible characters
		$js = preg_replace( '/^[\pZ\pC]+|[\pZ\pC]+$/u', '', $js );

		// Replace line breaks
		$js = str_replace( [ "\r", "\n", PHP_EOL ], '', $js );

		/**
		 * Allows additional minification of inline JS
		 *
		 * @param string      $js      Formatted JavaScript code
		 * @param string|null $context Optional context
		 */
		return apply_filters( 'arraypress_format_inline_js', $js, $context );
	}

	/**
	 * Convert PHP data to a JavaScript object literal.
	 *
	 * @param mixed  $data The PHP data to convert
	 * @param string $name Optional variable name for the object
	 *
	 * @return string JavaScript object representation
	 */
	public static function to_object( $data, string $name = '' ): string {
		$json = wp_json_encode( $data );
		if ( $name ) {
			return sprintf( 'const %s = %s;', $name, $json );
		}

		return $json;
	}

	/**
	 * Create a JavaScript event handler.
	 *
	 * @param string $event    Event name (e.g., 'click', 'submit')
	 * @param string $code     JavaScript code to execute
	 * @param string $selector Optional selector for delegation
	 *
	 * @return string Formatted event handler code
	 */
	public static function event_handler( string $event, string $code, string $selector = '' ): string {
		$js = $selector
			? sprintf( "document.querySelectorAll('%s').forEach(el => el.addEventListener('%s', function(e) { %s }));",
				$selector, $event, self::format_inline_js( $code ) )
			: sprintf( "addEventListener('%s', function(e) { %s });",
				$event, self::format_inline_js( $code ) );

		return self::format_inline_js( $js );
	}

	/**
	 * Create a JavaScript IIFE (Immediately Invoked Function Expression).
	 *
	 * @param string $code   JavaScript code to wrap
	 * @param array  $params Optional parameters to pass to the function
	 * @param bool   $strict Whether to enable strict mode
	 *
	 * @return string Formatted IIFE code
	 */
	public static function iife( string $code, array $params = [], bool $strict = true ): string {
		$strict_directive = $strict ? "'use strict';" : '';
		$parameters       = implode( ', ', array_map( [ self::class, 'format_inline_js' ], $params ) );

		return self::format_inline_js(
			sprintf( "(function(%s) { %s %s })(%s);",
				implode( ',', array_keys( $params ) ),
				$strict_directive,
				$code,
				$parameters
			)
		);
	}

	/**
	 * Create a JavaScript async function.
	 *
	 * @param string $code JavaScript code to make async
	 *
	 * @return string Formatted async function code
	 */
	public static function async_function( string $code ): string {
		return self::format_inline_js(
			sprintf( "(async function() { %s })();", $code )
		);
	}

	/**
	 * Create a JavaScript promise.
	 *
	 * @param string $resolve_code Code to execute on resolve
	 * @param string $reject_code  Optional code to execute on reject
	 *
	 * @return string Formatted promise code
	 */
	public static function promise( string $resolve_code, string $reject_code = '' ): string {
		$promise = sprintf(
			"new Promise((resolve, reject) => { try { %s } catch(e) { %s } });",
			$resolve_code,
			$reject_code ?: 'reject(e);'
		);

		return self::format_inline_js( $promise );
	}

	/**
	 * Create a JavaScript debounced function.
	 *
	 * @param string $code      Code to debounce
	 * @param int    $wait      Wait time in milliseconds
	 * @param bool   $immediate Execute on the leading edge
	 *
	 * @return string Formatted debounced function code
	 */
	public static function debounce( string $code, int $wait = 250, bool $immediate = false ): string {
		return self::format_inline_js(
			sprintf(
				"function() { let timeout; return function() { const context = this, args = arguments; " .
				"const later = function() { timeout = null; if(!%s) { %s } }; " .
				"const callNow = %s && !timeout; clearTimeout(timeout); " .
				"timeout = setTimeout(later, %d); if(callNow) { %s } }; }()",
				$immediate ? 'true' : 'false',
				$code,
				$immediate ? 'true' : 'false',
				$wait,
				$code
			)
		);
	}

	/**
	 * Format JavaScript code for inline script tag.
	 *
	 * @param string $code  JavaScript code
	 * @param bool   $defer Whether to defer execution
	 * @param bool   $async Whether to load async
	 * @param string $id    Optional script ID
	 * @param array  $attrs Additional attributes
	 *
	 * @return string Formatted script tag
	 */
	public static function script_tag(
		string $code,
		bool $defer = false,
		bool $async = false,
		string $id = '',
		array $attrs = []
	): string {
		$attributes = [];

		if ( $defer ) {
			$attributes[] = 'defer';
		}
		if ( $async ) {
			$attributes[] = 'async';
		}
		if ( $id ) {
			$attributes[] = sprintf( 'id="%s"', esc_attr( $id ) );
		}

		foreach ( $attrs as $key => $value ) {
			$attributes[] = sprintf( '%s="%s"', esc_attr( $key ), esc_attr( $value ) );
		}

		$attr_string = $attributes ? ' ' . implode( ' ', $attributes ) : '';

		return sprintf(
			"<script%s>\n%s\n</script>",
			$attr_string,
			self::format_inline_js( $code )
		);
	}

}