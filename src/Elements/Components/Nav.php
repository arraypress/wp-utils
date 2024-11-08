<?php
/**
 * Nav Component Class
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Elements\Components;

use ArrayPress\Utils\Elements\Element;

class Nav extends Base {

	/**
	 * Creates an unordered list of links.
	 *
	 * @param array $links      Array of links where key is the label and value is the URL
	 * @param array $args       Optional. Arguments for customizing the link list.
	 *
	 * @return string The HTML for the list of links
	 */
	public static function link_list( array $links, array $args = [] ): string {
		if ( empty( $links ) ) {
			return '';
		}

		$defaults = [
			'ul_class'   => 'link-list',
			'li_class'   => 'link-item',
			'a_class'    => 'link',
			'properties' => [],
		];

		$args  = wp_parse_args( $args, $defaults );
		$items = [];

		foreach ( $links as $label => $url ) {
			$link_attrs = array_merge(
				[ 'class' => $args['a_class'] ],
				$args['properties']
			);

			$items[] = Element::link( $url, $label, $link_attrs );
		}

		return Element::ul( $items, [
			'class' => $args['ul_class']
		], [
			'class' => $args['li_class']
		] );
	}

	/**
	 * Create an HTML menu from an array of items.
	 *
	 * @param array $items Menu items array.
	 * @param array $attrs Additional attributes for the menu container.
	 *
	 * @return string The HTML string for the menu.
	 */
	public static function menu( array $items, array $attrs = [] ): string {
		$content = '';

		foreach ( $items as $item ) {
			$item_attrs   = $item['attrs'] ?? [];
			$item_content = $item['content'] ?? '';

			if ( ! empty( $item['url'] ) ) {
				$item_content = Element::link(
					$item['url'],
					$item_content,
					$item['link_attrs'] ?? []
				);
			}

			if ( ! empty( $item['children'] ) ) {
				$item_content .= self::menu( $item['children'] );
			}

			$content .= Element::create( 'li', $item_attrs, $item_content );
		}

		$default_attrs = [ 'class' => 'menu' ];
		$attrs         = array_merge( $default_attrs, $attrs );

		return Element::create( 'ul', $attrs, $content );
	}

	/**
	 * Create a breadcrumb navigation.
	 *
	 * @param array $items Array of breadcrumb items.
	 * @param array $attrs Additional attributes for the breadcrumb container.
	 *
	 * @return string The HTML string for the breadcrumbs.
	 */
	public static function breadcrumbs( array $items, array $attrs = [] ): string {
		$content    = '';
		$last_index = count( $items ) - 1;

		foreach ( $items as $index => $item ) {
			$is_last = ( $index === $last_index );

			if ( ! empty( $item['url'] ) && ! $is_last ) {
				$content .= Element::link( $item['url'], esc_html( $item['text'] ) );
			} else {
				$content .= Element::span( esc_html( $item['text'] ), [ 'class' => 'current' ] );
			}

			if ( ! $is_last ) {
				$content .= Element::span( ' / ', [ 'class' => 'separator' ] );
			}
		}

		$default_attrs = [ 'class' => 'breadcrumbs' ];
		$attrs         = array_merge( $default_attrs, $attrs );

		return Element::nav( $content, $attrs );
	}

}