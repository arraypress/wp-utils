<?php
/**
 * Translate Polyfills
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

/**
 * HTML escapes a translated string with sprintf formatting.
 *
 * Provides a convenient way to combine esc_html__() and sprintf() while ensuring
 * proper translation string extraction. The first parameter must be a string literal
 * for proper translation scanning.
 *
 * @param string $text    Text to translate with placeholders (must be string literal).
 * @param string $domain  Text domain to use for translation.
 * @param mixed  ...$args Values to replace placeholders.
 *
 * @return string The translated, escaped, and formatted string.
 */
if ( ! function_exists( 'esc_html_f' ) ) {
	function esc_html_f( string $text, string $domain, ...$args ): string {
		return sprintf( esc_html__( $text, $domain ), ...$args );
	}
}