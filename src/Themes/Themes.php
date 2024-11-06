<?php
/**
 * WordPress Theme Utilities
 *
 * This file contains utility functions for managing WordPress theme-related operations.
 * It provides a set of static methods encapsulated in the Theme class to handle various
 * theme-specific tasks and retrieve theme information.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Themes;

/**
 * Class Themes
 *
 * Utility functions for working with multiple themes.
 */
class Themes {

	/**
	 * Get available themes and return them in label/value format.
	 *
	 * @return array An array of available themes in label/value format.
	 */
	public static function get_options(): array {
		$themes = wp_get_themes();

		if ( empty( $themes ) || ! is_array( $themes ) ) {
			return [];
		}

		$options = [];

		foreach ( $themes as $theme_slug => $theme ) {
			$options[] = [
				'value' => esc_attr( $theme_slug ),
				'label' => esc_html( $theme->get( 'Name' ) ),
			];
		}

		return $options;
	}

	/**
	 * Get page templates and return them in label/value format.
	 *
	 * @return array An array of page templates in label/value format.
	 */
	public static function get_template_options(): array {
		$theme = wp_get_theme();

		if ( ! $theme->exists() ) {
			return [];
		}

		$page_templates = $theme->get_page_templates();

		$options = [
			[
				'value' => 'default',
				'label' => esc_html__( 'Default Template', 'arraypress' ),
			]
		];

		foreach ( $page_templates as $template_filename => $template_name ) {
			$options[] = [
				'value' => esc_attr( $template_filename ),
				'label' => esc_html( $template_name ),
			];
		}

		return $options;
	}

}