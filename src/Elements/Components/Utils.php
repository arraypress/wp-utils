<?php
/**
 * Utils Component Class
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Elements\Components;

use ArrayPress\Utils\Elements\Element;

class Utils extends Base {

	/**
	 * Create a tooltip element.
	 *
	 * @param string $content      The content to display.
	 * @param string $tooltip_text The text to show in the tooltip.
	 * @param array  $attrs        Additional attributes for the outer span.
	 *
	 * @return string The HTML for the tooltip.
	 */
	public static function tooltip( string $content, string $tooltip_text, array $attrs = [] ): string {
		$attrs['class'] = isset( $attrs['class'] ) ? $attrs['class'] . ' wp-tooltip' : 'wp-tooltip';

		$tooltip_content = Element::span(
			esc_html( $tooltip_text ),
			[ 'class' => 'tooltiptext' ]
		);

		self::ensure_styles();

		return Element::span( $content . $tooltip_content, $attrs );
	}

}