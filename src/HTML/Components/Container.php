<?php
/**
 * Container Component Class
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\HTML\Components;

use ArrayPress\Utils\HTML\Element;

class Container extends Base {

	/**
	 * Create a card element.
	 *
	 * @param string $title   The title of the card.
	 * @param string $content The content of the card.
	 * @param array  $attrs   Additional attributes for the outer div.
	 *
	 * @return string The HTML for the card.
	 */
	public static function card( string $title, string $content, array $attrs = [] ): string {
		$attrs['class'] = isset( $attrs['class'] ) ? $attrs['class'] . ' wp-card' : 'wp-card';

		$title_html   = Element::create( 'h4', [ 'class' => 'wp-card-title' ], esc_html( $title ) );
		$content_html = Element::create( 'div', [ 'class' => 'wp-card-content' ], wp_kses_post( $content ) );

		self::ensure_styles();

		return Element::div( $title_html . $content_html, $attrs );
	}

}