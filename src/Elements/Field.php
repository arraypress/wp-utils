<?php
/**
 * Field Class: WordPress Form Element Generator
 *
 * A comprehensive utilities class for generating HTML form elements in WordPress. This class provides
 * a collection of static methods designed to create standardized, accessible, and secure form fields
 * and form structures.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Elements;

/**
 * Class Field
 *
 * Utility functions for creating field elements.
 */
class Field {

	/**
	 * Common field attributes and their default values
	 *
	 * @var array
	 */
	private static array $common_attributes = [
		'name'     => '',
		'value'    => null,
		'label'    => '',
		'desc'     => '',
		'class'    => '',
		'disabled' => false,
		'required' => false,
		'readonly' => false,
		'data'     => [],
	];

	/**
	 * Common input field attributes
	 *
	 * @var array
	 */
	private static array $input_attributes = [
		'placeholder'  => '',
		'maxlength'    => null,
		'minlength'    => null,
		'pattern'      => null,
		'autocomplete' => null,
	];

	/**
	 * Common number-type field attributes
	 *
	 * @var array
	 */
	private static array $number_attributes = [
		'min'  => null,
		'max'  => null,
		'step' => null,
	];

	/**
	 * Common selection field attributes
	 *
	 * @var array
	 */
	private static array $selection_attributes = [
		'options'  => [],
		'selected' => '',
		'multiple' => false,
	];

	/**
	 * WordPress standard CSS classes
	 *
	 * @var array
	 */
	private static array $wp_classes = [
		'text'     => 'regular-text',
		'number'   => 'small-text',
		'email'    => 'regular-text',
		'url'      => 'regular-text code',
		'textarea' => 'large-text',
		'select'   => 'regular-text',
		'color'    => 'small-text',
		'tel'      => 'regular-text',
	];

	/**
	 * Track if style assets have been enqueued
	 */
	private static bool $styles_enqueued = false;

	/**
	 * Track if script assets have been enqueued
	 */
	private static bool $scripts_enqueued = false;

	/**
	 * Get debug assets directory path
	 *
	 * @return string
	 */
	private static function get_assets_dir(): string {
		return dirname( __FILE__, 3 ) . '/Assets';  // Go up 3 levels to reach Src
	}

	/**
	 * Get debug assets url
	 *
	 * @return string
	 */
	private static function get_assets_url(): string {
		return plugins_url( '../Assets', __FILE__ );  // Go up one level then into Assets
	}

	/**
	 * Ensure styles are loaded when needed
	 */
	public static function ensure_styles(): void {
		if ( self::$styles_enqueued ) {
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
	 * Ensure scripts are loaded when needed
	 */
	public static function ensure_scripts(): void {
		if ( self::$scripts_enqueued ) {
			return;
		}

		// Check if admin_enqueue_scripts has already fired
		if ( did_action( 'admin_enqueue_scripts' ) ) {
			self::enqueue_scripts();
		} else {
			add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_scripts' ] );
		}

		self::$scripts_enqueued = true;
	}

	/**
	 * Ensure all assets are loaded when needed
	 */
	public static function ensure_assets(): void {
		self::ensure_styles();
		self::ensure_scripts();
	}

	/**
	 * Enqueue styles
	 *
	 * @return void
	 */
	public static function enqueue_styles(): void {
		$assets_dir = self::get_assets_dir();
		$assets_url = self::get_assets_url();

		wp_enqueue_style(
			'arraypress-form-fields',
			$assets_url . '/css/form-fields.css',
			[],
			filemtime( $assets_dir . '/css/form-fields.css' )
		);
	}

	/**
	 * Enqueue scripts
	 *
	 * @return void
	 */
	public static function enqueue_scripts(): void {
		$assets_dir = self::get_assets_dir();
		$assets_url = self::get_assets_url();

		wp_enqueue_media();

		wp_enqueue_script(
			'arraypress-form-fields',
			$assets_url . '/js/form-fields.js',
			[ 'jquery' ],
			filemtime( $assets_dir . '/js/form-fields.js' ),
			true
		);
	}

	/**
	 * Builds common field attributes.
	 *
	 * @param array $args                Field arguments
	 * @param array $additional_defaults Additional default values
	 *
	 * @return array Parsed arguments
	 */
	private static function parse_field_args( array $args, array $additional_defaults = [] ): array {
		$defaults = array_merge( self::$common_attributes, $additional_defaults );

		return wp_parse_args( $args, $defaults );
	}

	/**
	 * Builds HTML attributes array from args.
	 *
	 * @param array  $args             Parsed arguments
	 * @param string $type             Input type
	 * @param array  $additional_attrs Additional attributes to include
	 *
	 * @return array HTML attributes
	 */
	private static function build_attributes( array $args, string $type, array $additional_attrs = [] ): array {
		// Start with base attributes
		$attrs = array_merge( [
			'type'  => $type,
			'name'  => $args['name'],
			'id'    => $args['name'],
			'class' => $args['class'],
		], $additional_attrs );

		// Add description ID if description exists
		if ( ! empty( $args['desc'] ) ) {
			$attrs['aria-describedby'] = $args['name'] . '-description';
		}

		// Add common boolean attributes
		foreach ( [ 'disabled', 'required', 'readonly', 'multiple' ] as $attr ) {
			if ( ! empty( $args[ $attr ] ) ) {
				$attrs[ $attr ] = true;
			}
		}

		// Add value if set (and not null)
		if ( $args['value'] !== null ) {
			$attrs['value'] = $args['value'];
		}

		// Add optional string/numeric attributes if they exist and aren't null
		$optional_attrs = [
			'placeholder',
			'maxlength',
			'minlength',
			'pattern',
			'min',
			'max',
			'step',
			'autocomplete',
			'accept',
			'rows',
			'cols',
			'title',
			'form'
		];

		foreach ( $optional_attrs as $attr ) {
			if ( isset( $args[ $attr ] ) && $args[ $attr ] !== null ) {
				$attrs[ $attr ] = $args[ $attr ];
			}
		}

		// Add any data attributes
		foreach ( $args['data'] as $key => $value ) {
			$attrs["data-$key"] = $value;
		}

		return $attrs;
	}

	/**
	 * Wraps field with label and description.
	 *
	 * @param string $field       Field HTML
	 * @param array  $args        Field arguments
	 * @param bool   $label_after Whether to put the label after the field
	 *
	 * @return string Wrapped field HTML
	 */
	private static function wrap( string $field, array $args, bool $label_after = false ): string {
		// Add description if provided
		if ( $args['desc'] ) {
			$field .= Element::p(
				$args['desc'],
				[
					'class' => 'description',
					'id'    => $args['name'] . '-description'
				]
			);
		}

		// Wrap in label if provided
		if ( $args['label'] ) {
			if ( $label_after ) {
				$field = Element::label( $args['name'], $field . ' ' . $args['label'] );
			} else {
				$field = Element::label( $args['name'], $args['label'] ) . $field;
			}
		}

		return $field;
	}

	/**
	 * Creates a basic input field.
	 *
	 * @param string $type                Input type
	 * @param array  $args                Field arguments
	 * @param array  $additional_defaults Additional defaults specific to this type
	 * @param array  $additional_attrs    Additional attributes specific to this type
	 * @param bool   $label_after         Whether to put the label after the field
	 *
	 * @return string HTML input field
	 */
	private static function create_input(
		string $type,
		array $args = [],
		array $additional_defaults = [],
		array $additional_attrs = [],
		bool $label_after = false
	): string {
		// If no class specified and we have a WP default, use it
		if ( empty( $args['class'] ) && isset( self::$wp_classes[ $type ] ) ) {
			$additional_defaults['class'] = self::$wp_classes[ $type ];
		}

		$args = self::parse_field_args(
			$args,
			array_merge( self::$input_attributes, $additional_defaults )
		);

		$attrs = self::build_attributes( $args, $type, $additional_attrs );
		$field = Element::create_void( 'input', $attrs );

		return self::wrap( $field, $args, $label_after );
	}

	/**
	 * Creates a text input field.
	 *
	 * @param array $args Configuration arguments for the text field
	 *
	 * @return string HTML text input field
	 */
	public static function text( array $args = [] ): string {
		return self::create_input( 'text', $args );
	}

	/**
	 * Creates a number input field.
	 *
	 * @param array $args Configuration arguments for the number field
	 *
	 * @return string HTML number input field
	 */
	public static function number( array $args = [] ): string {
		return self::create_input( 'number', $args, self::$number_attributes );
	}

	/**
	 * Creates an email input field.
	 *
	 * @param array $args Configuration arguments for the email field
	 *
	 * @return string HTML email input field
	 */
	public static function email( array $args = [] ): string {
		return self::create_input( 'email', $args );
	}

	/**
	 * Creates a URL input field.
	 *
	 * @param array $args Configuration arguments for the URL field
	 *
	 * @return string HTML URL input field
	 */
	public static function url( array $args = [] ): string {
		return self::create_input( 'url', $args );
	}

	/**
	 * Creates a tel input field.
	 *
	 * @param array $args Configuration arguments for the tel field
	 *
	 * @return string HTML tel input field
	 */
	public static function tel( array $args = [] ): string {
		return self::create_input( 'tel', $args );
	}

	/**
	 * Creates a search input field.
	 *
	 * @param array $args Configuration arguments for the search field
	 *
	 * @return string HTML search input field
	 */
	public static function search( array $args = [] ): string {
		$defaults = [
			'placeholder' => __( 'Search...', 'arraypress' ),
		];

		return self::create_input( 'search', $args, $defaults );
	}

	/**
	 * Creates a password input field.
	 *
	 * @param array $args Configuration arguments for the password field
	 *
	 * @return string HTML password input field
	 */
	public static function password( array $args = [] ): string {
		$defaults = [
			'show_toggle' => true,
			'minlength'   => null,
		];

		// Create wrapper for better styling control
		$wrapper_attrs = [ 'class' => 'wp-password-wrapper regular-text' ];

		// Create password input
		$field = self::create_input( 'password', $args, $defaults );

		// Add password visibility toggle if enabled
		if ( ! empty( $args['show_toggle'] ) ) {
			$toggle = self::checkbox( [
				'name'  => $args['name'] . '_toggle',
				'label' => __( 'Show password', 'arraypress' ),
				'class' => 'password-toggle',
			] );
			$field  = Element::div( $field . $toggle, $wrapper_attrs );
		}

		// Add the styles and scripts
		self::ensure_assets();

		return $field;
	}

	/**
	 * Creates a color input field.
	 *
	 * @param array $args Configuration arguments for the color field
	 *
	 * @return string HTML color input field
	 */
	public static function color( array $args = [] ): string {
		$defaults = [
			'default' => null,
			'class'   => 'small-text' // Change to small-text
		];

		$additional_attrs = [];
		if ( isset( $args['default'] ) ) {
			$additional_attrs['data-default-color'] = $args['default'];
		}

		return self::create_input( 'color', $args, $defaults, $additional_attrs );
	}

	/**
	 * Creates a date input field.
	 *
	 * @param array $args Configuration arguments for the date field
	 *
	 * @return string HTML date input field
	 */
	public static function date( array $args = [] ): string {
		return self::create_input( 'date', $args, self::$number_attributes );
	}

	/**
	 * Creates a time input field.
	 *
	 * @param array $args Configuration arguments for the time field
	 *
	 * @return string HTML time input field
	 */
	public static function time( array $args = [] ): string {
		return self::create_input( 'time', $args, self::$number_attributes );
	}


	/**
	 * Creates a standard file upload field.
	 *
	 * @param array $args Configuration arguments for the file field
	 *
	 * @return string HTML file upload field
	 */
	public static function file( array $args = [] ): string {
		$defaults = [
			'accept'   => '',
			'multiple' => false,
			'class'    => 'regular-text'
		];

		return self::create_input( 'file', $args, $defaults );
	}

	/**
	 * Creates a WordPress media upload field.
	 *
	 * @param array $args Configuration arguments for the media upload field
	 *
	 * @return string HTML media upload field
	 */
	public static function wp_media_upload( array $args = [] ): string {
		$defaults = [
			'name'         => '',
			'value'        => '',
			'label'        => '',
			'desc'         => '',
			'class'        => 'small-text',
			'preview_size' => 'thumbnail',
			'multiple'     => false,
			'type'         => 'image', // image, video, audio, or any
			'button_text'  => __( 'Choose File', 'arraypress' ),
			'remove_text'  => __( 'Remove', 'arraypress' ),
			'data'         => [],
		];

		$args = self::parse_field_args( $args, $defaults );

		// Create hidden input for storing the attachment ID
		$hidden_input = Element::create_void( 'input', [
			'type'  => 'hidden',
			'name'  => $args['name'],
			'id'    => $args['name'],
			'value' => $args['value'],
			'class' => 'wp-media-input'
		] );

		// Create preview container
		$preview_class = 'wp-media-preview' . ( $args['value'] ? '' : ' hidden' );
		$preview       = Element::div( '', [
			'id'    => $args['name'] . '_preview',
			'class' => $preview_class
		] );

		if ( $args['value'] ) {
			if ( $args['type'] === 'image' ) {
				$preview = wp_get_attachment_image( $args['value'], $args['preview_size'] );
			} else {
				$preview = wp_get_attachment_link( $args['value'], $args['preview_size'] );
			}
		}

		// Create buttons
		$select_button = Element::create( 'button', [
			'type'          => 'button',
			'class'         => 'button wp-media-select',
			'data-name'     => $args['name'],
			'data-type'     => $args['type'],
			'data-multiple' => $args['multiple'] ? '1' : '0',
		], $args['button_text'] );

		$remove_button = Element::create( 'button', [
			'type'      => 'button',
			'class'     => 'button wp-media-remove' . ( $args['value'] ? '' : ' hidden' ),
			'data-name' => $args['name'],
		], $args['remove_text'] );

		// Combine elements
		$field = $hidden_input . $preview . $select_button . ' ' . $remove_button;

		// Add the styles and scripts
		self::ensure_assets();

		return self::wrap( $field, $args );
	}

	/**
	 * Creates a range slider field.
	 *
	 * @param array $args Configuration arguments for the range field
	 *
	 * @return string HTML range slider field
	 */
	public static function range( array $args = [] ): string {
		$defaults = array_merge( self::$number_attributes, [
			'show_value' => true,
			'min'        => 0,
			'max'        => 100,
			'step'       => 1,
			'value'      => 50,
		] );

		// Create wrapper for better styling control
		$wrapper_attrs = [ 'class' => 'wp-range-wrapper' ];

		// Create range input
		$range_input = self::create_input( 'range', $args, $defaults );

		// Create number input for value if enabled
		$value_input = '';
		if ( ! empty( $args['show_value'] ) ) {
			$value_args  = [
				'name'       => $args['name'] . '_display',
				'value'      => $args['value'] ?? $defaults['value'],
				'min'        => $args['min'] ?? $defaults['min'],
				'max'        => $args['max'] ?? $defaults['max'],
				'step'       => $args['step'] ?? $defaults['step'],
				'class'      => 'small-text range-value',
				'aria-label' => __( 'Range value', 'arraypress' )
			];
			$value_input = self::create_input( 'number', $value_args );
		}

		$field = Element::div( $range_input . $value_input, $wrapper_attrs );

		self::ensure_assets();

		return $field;
	}

	/**
	 * Creates a textarea field.
	 *
	 * @param array $args Configuration arguments for the textarea field
	 *
	 * @return string HTML textarea field
	 */
	public static function textarea( array $args = [] ): string {
		$defaults = [
			'rows' => 5,
			'cols' => 50,
		];

		$args = self::parse_field_args( $args, $defaults );
		if ( empty( $args['class'] ) ) {
			$args['class'] = self::$wp_classes['textarea'];
		}

		$attrs = self::build_attributes( $args, 'textarea' );
		unset( $attrs['type'], $attrs['value'] ); // Remove inappropriate attributes

		$field = Element::create( 'textarea', $attrs, esc_textarea( $args['value'] ?? '' ) );

		return self::wrap( $field, $args );
	}

	/**
	 * Creates a select dropdown field.
	 *
	 * @param array $args Configuration arguments for the select field
	 *
	 * @return string HTML select field
	 */
	public static function select( array $args = [] ): string {
		$defaults = array_merge( self::$selection_attributes, [
			'placeholder' => '',
		] );

		$args = self::parse_field_args( $args, $defaults );
		if ( empty( $args['class'] ) ) {
			$args['class'] = self::$wp_classes['select'];
		}

		$attrs = self::build_attributes( $args, 'select', [
			'name' => $args['multiple'] ? $args['name'] . '[]' : $args['name']
		] );
		unset( $attrs['type'] ); // Remove inappropriate attribute

		// Build options
		$options = '';

		// Add placeholder option if specified
		if ( $args['placeholder'] ) {
			$options .= Element::create( 'option', [
				'value'    => '',
				'disabled' => true,
				'selected' => empty( $args['selected'] )
			], esc_html( $args['placeholder'] ) );
		}

		foreach ( $args['options'] as $value => $label ) {
			$option_attrs = [ 'value' => $value ];

			if ( $args['multiple'] && is_array( $args['selected'] ) ) {
				if ( in_array( $value, $args['selected'], true ) ) {
					$option_attrs['selected'] = true;
				}
			} elseif ( $value == $args['selected'] ) {
				$option_attrs['selected'] = true;
			}

			$options .= Element::create( 'option', $option_attrs, esc_html( $label ) );
		}

		$field = Element::create( 'select', $attrs, $options );

		return self::wrap( $field, $args );
	}

	/**
	 * Creates a radio group field.
	 *
	 * @param array $args Configuration arguments for the radio group
	 *
	 * @return string HTML radio buttons group
	 */
	public static function radio_group( array $args = [] ): string {
		$defaults = self::$selection_attributes;
		$args     = self::parse_field_args( $args, $defaults );

		$radio_group = '';
		if ( $args['label'] ) {
			$radio_group .= Element::create( 'legend', [], esc_html( $args['label'] ) );
		}

		foreach ( $args['options'] as $value => $label ) {
			$input_attrs = [
				'type'  => 'radio',
				'name'  => $args['name'],
				'id'    => $args['name'] . '_' . sanitize_key( $value ),
				'value' => $value,
				'class' => $args['class']
			];

			if ( $value == $args['selected'] ) {
				$input_attrs['checked'] = true;
			}
			if ( ! empty( $args['disabled'] ) ) {
				$input_attrs['disabled'] = true;
			}
			if ( ! empty( $args['required'] ) ) {
				$input_attrs['required'] = true;
			}

			$radio       = Element::create_void( 'input', $input_attrs );
			$radio       .= Element::label( $input_attrs['id'], esc_html( $label ) );
			$radio_group .= Element::div( $radio, [ 'class' => 'radio-option' ] );
		}

		if ( $args['desc'] ) {
			$radio_group .= Element::p( $args['desc'], [
				'class' => 'description',
				'id'    => $args['name'] . '-description'
			] );
		}

		return self::fieldset( $radio_group );
	}

	/**
	 * Creates a checkbox field.
	 *
	 * @param array $args Configuration arguments for the checkbox field
	 *
	 * @return string HTML checkbox field
	 */
	public static function checkbox( array $args = [] ): string {
		$defaults = [
			'checked' => false,
			'value'   => '1',
		];

		$args = self::parse_field_args( $args, $defaults );

		$attrs = self::build_attributes( $args, 'checkbox' );
		if ( $args['checked'] ) {
			$attrs['checked'] = true;
		}

		return self::create_input( 'checkbox', $args, $defaults, [], true );
	}

	/**
	 * Creates a checkbox group field.
	 *
	 * @param array $args Configuration arguments for the checkbox group
	 *
	 * @return string HTML checkbox group
	 */
	public static function checkbox_group( array $args = [] ): string {
		$defaults = self::$selection_attributes;
		$args     = self::parse_field_args( $args, $defaults );
		$selected = (array) $args['selected'];

		$group = '';
		if ( $args['label'] ) {
			$group .= Element::create( 'legend', [], esc_html( $args['label'] ) );
		}

		foreach ( $args['options'] as $value => $label ) {
			$unique_id = $args['name'] . '_' . sanitize_key( $value );

			$checkbox_args = [
				'name'     => $args['name'] . '[]',
				'value'    => $value,
				'label'    => $label,
				'checked'  => in_array( $value, $selected, true ),
				'disabled' => $args['disabled'],
				'class'    => $args['class'],
				'data'     => $args['data'],
				'id'       => $unique_id // Use the unique ID
			];

			$group .= Element::div(
				self::checkbox( $checkbox_args ),
				[ 'class' => 'checkbox-option' ]
			);
		}

		if ( $args['desc'] ) {
			$group .= Element::p( $args['desc'], [
				'class' => 'description',
				'id'    => $args['name'] . '-description'
			] );
		}

		return self::fieldset( $group );
	}

	/**
	 * Creates a toggle switch field.
	 *
	 * @param array $args Configuration arguments for the toggle switch
	 *
	 * @return string HTML toggle switch field
	 */
	public static function toggle_switch( array $args = [] ): string {
		$defaults = [
			'checked' => false,
			'value'   => '1',
		];

		$args = self::parse_field_args( $args, $defaults );

		// Build the checkbox input
		$input_attrs = [
			'type'  => 'checkbox',
			'name'  => $args['name'],
			'id'    => $args['name'],
			'value' => $args['value'],
		];

		if ( $args['checked'] ) {
			$input_attrs['checked'] = true;
		}
		if ( ! empty( $args['disabled'] ) ) {
			$input_attrs['disabled'] = true;
		}
		if ( ! empty( $args['required'] ) ) {
			$input_attrs['required'] = true;
		}

		// Add any data attributes
		foreach ( $args['data'] as $key => $value ) {
			$input_attrs["data-$key"] = $value;
		}

		$checkbox = Element::create_void( 'input', $input_attrs );

		// Create the toggle wrapper with label
		$toggle = Element::div(
			$checkbox . ( $args['label'] ? Element::label( $args['name'], $args['label'] ) : '' ),
			[ 'class' => 'wp-toggle' ]
		);

		// Add description if provided
		if ( $args['desc'] ) {
			$toggle .= Element::p(
				$args['desc'],
				[
					'class' => 'description',
					'id'    => $args['name'] . '-description'
				]
			);
		}

		self::ensure_assets();

		return $toggle;
	}

	/**
	 * Creates a form fieldset.
	 *
	 * @param string $content The content of the fieldset
	 * @param array  $args    Configuration arguments for the fieldset
	 *
	 * @return string HTML fieldset element
	 */
	public static function fieldset( string $content, array $args = [] ): string {
		$defaults = [
			'legend'   => '',
			'class'    => '',
			'id'       => '',
			'data'     => [],
			'disabled' => false,
			'form'     => '',
			'name'     => '',
		];

		$args = wp_parse_args( $args, $defaults );

		$attrs = [ 'class' => $args['class'] ];

		if ( $args['id'] ) {
			$attrs['id'] = $args['id'];
		}
		if ( $args['disabled'] ) {
			$attrs['disabled'] = true;
		}
		if ( $args['form'] ) {
			$attrs['form'] = $args['form'];
		}
		if ( $args['name'] ) {
			$attrs['name'] = $args['name'];
		}

		foreach ( $args['data'] as $key => $value ) {
			$attrs["data-$key"] = $value;
		}

		if ( $args['legend'] ) {
			$content = Element::create( 'legend', [], esc_html( $args['legend'] ) ) . $content;
		}

		return Element::create( 'fieldset', $attrs, $content );
	}

	/**
	 * Creates a form row.
	 *
	 * @param string $field The form field HTML
	 * @param array  $args  Configuration arguments for the form row
	 *
	 * @return string HTML form row
	 */
	public static function row( string $field, array $args = [] ): string {
		$defaults = [
			'label'      => '',
			'desc'       => '',
			'class'      => '',
			'required'   => false,
			'label_for'  => '',
			'type'       => 'table',
			'wrap_class' => ''
		];

		$args = wp_parse_args( $args, $defaults );

		if ( $args['type'] === 'table' ) {
			$label = '';
			if ( $args['label'] ) {
				$label = Element::create( 'label',
					[ 'for' => $args['label_for'] ],
					esc_html( $args['label'] ) .
					( $args['required'] ? Element::span( '*', [ 'class' => 'required' ] ) : '' )
				);
			}

			$th = Element::create( 'th',
				[ 'scope' => 'row' ],
				$label
			);

			$content = $field;
			if ( $args['desc'] ) {
				$content .= Element::p(
					$args['desc'],
					[ 'class' => 'description' ]
				);
			}

			$td = Element::create( 'td', [], $content );

			return Element::create( 'tr',
				[ 'class' => $args['wrap_class'] . ( $args['class'] ? ' ' . $args['class'] : '' ) ],
				$th . $td
			);
		}

		// Div format
		$content = '';
		if ( $args['label'] ) {
			$content .= Element::create( 'label',
				[ 'for' => $args['label_for'] ],
				esc_html( $args['label'] ) .
				( $args['required'] ? Element::span( '*', [ 'class' => 'required' ] ) : '' )
			);
		}

		$content .= $field;

		if ( $args['desc'] ) {
			$content .= Element::p(
				$args['desc'],
				[ 'class' => 'description' ]
			);
		}

		return Element::create( 'div',
			[ 'class' => $args['wrap_class'] . ( $args['class'] ? ' ' . $args['class'] : '' ) ],
			$content
		);
	}

	/**
	 * Creates a form section.
	 *
	 * @param string $content The form fields HTML
	 * @param array  $args    Configuration arguments for the form section
	 *
	 * @return string HTML form section
	 */
	public static function section( string $content, array $args = [] ): string {
		$defaults = [
			'title'      => '',
			'desc'       => '',
			'type'       => 'table',
			'class'      => '',
			'wrap_class' => 'form-table',
			'id'         => ''
		];

		$args = wp_parse_args( $args, $defaults );

		$section = '';

		if ( $args['title'] ) {
			$section .= Element::create( 'h2',
				[ 'class' => 'title' ],
				esc_html( $args['title'] )
			);
		}

		if ( $args['desc'] ) {
			$section .= Element::p(
				$args['desc'],
				[ 'class' => 'description' ]
			);
		}

		$wrap_attrs = [ 'class' => $args['wrap_class'] ];
		if ( $args['id'] ) {
			$wrap_attrs['id'] = $args['id'];
		}
		if ( $args['class'] ) {
			$wrap_attrs['class'] .= ' ' . $args['class'];
		}

		if ( $args['type'] === 'table' ) {
			$section .= Element::create( 'table',
				$wrap_attrs,
				Element::create( 'tbody', [], $content )
			);
		} else {
			$section .= Element::div( $content, $wrap_attrs );
		}

		return $section;
	}

	/**
	 * Creates a form group.
	 *
	 * @param string $field The form field HTML
	 * @param array  $args  Configuration arguments for the form group
	 *
	 * @return string HTML form group
	 */
	public static function group( string $field, array $args = [] ): string {
		$defaults = [
			'label'       => '',
			'desc'        => '',
			'class'       => '',
			'wrap_class'  => 'form-group',
			'label_class' => 'form-group-label',
			'desc_class'  => 'form-group-desc',
			'id'          => '',
			'required'    => false,
		];

		$args = wp_parse_args( $args, $defaults );

		$content = '';

		if ( $args['label'] ) {
			$label_attrs = [ 'class' => $args['label_class'] ];
			if ( $args['required'] ) {
				$args['label'] .= Element::span( '*', [ 'class' => 'required' ] );
			}
			$content .= Element::label( '', $args['label'], $label_attrs );
		}

		$content .= $field;

		if ( $args['desc'] ) {
			$content .= Element::div( $args['desc'], [ 'class' => $args['desc_class'] ] );
		}

		$wrap_attrs = [
			'class' => $args['wrap_class'] . ( $args['class'] ? ' ' . $args['class'] : '' )
		];

		if ( $args['id'] ) {
			$wrap_attrs['id'] = $args['id'];
		}

		return Element::div( $content, $wrap_attrs );
	}

	/**
	 * Creates a form actions section (buttons area).
	 *
	 * @param string $content The buttons/actions HTML
	 * @param array  $args    Configuration arguments for the actions section
	 *
	 * @return string HTML form actions section
	 */
	public static function actions( string $content, array $args = [] ): string {
		$defaults = [
			'class'      => '',
			'wrap_class' => 'form-actions',
			'align'      => 'left',
			'sticky'     => false,
			'id'         => '',
			'data'       => [],
		];

		$args = wp_parse_args( $args, $defaults );

		$wrap_attrs = [
			'class' => $args['wrap_class'] . ( $args['class'] ? ' ' . $args['class'] : '' )
		];

		if ( $args['align'] !== 'left' ) {
			$wrap_attrs['class'] .= ' align-' . $args['align'];
		}

		if ( $args['sticky'] ) {
			$wrap_attrs['class'] .= ' sticky';
		}

		if ( $args['id'] ) {
			$wrap_attrs['id'] = $args['id'];
		}

		foreach ( $args['data'] as $key => $value ) {
			$wrap_attrs["data-$key"] = $value;
		}

		return Element::div( $content, $wrap_attrs );
	}

}