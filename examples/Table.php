<?php
/**
 * Array Helper Utilities
 *
 * This class provides utility functions for working with arrays in PHP. It includes
 * methods for sorting, shuffling, selecting random elements, checking conditions across
 * elements, managing keys, and converting arrays into other formats such as JSON, XML,
 * or delimited strings. Additionally, it offers advanced operations for flattening, filtering,
 * normalizing, and recursive array manipulations.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils;

add_action( 'edd_edit_discount_form_bottom', function ( $discount_id, $discount ) {
	render_custom_table( [
		'key'           => 'recent_orders',
		'title'         => __( 'Recent Orders', 'arraypress' ),
		'table_class'   => 'discount-orders',
		'columns'       => [
			'number'  => [
				'label'           => __( 'Number', 'arraypress' ),
				'prefix'          => '<strong>',
				'suffix'          => '</strong>',
				'render_callback' => function ( $row, $column_key ) {
					$view_url = edd_get_admin_url( [
						'page' => 'edd-payment-history',
						'view' => 'view-order-details',
						'id'   => absint( $row->id ),
					] );
					$state    = ( 'complete' !== $row->status ) ? ' &mdash; ' . edd_get_payment_status_label( $row->status ) : '';

					return sprintf(
						'<a class="row-title" href="%s">%s</a>%s',
						esc_url( $view_url ),
						esc_html( $row->number ),
						esc_html( $state )
					);
				},
			],
			'total'   => [
				'label'           => __( 'Total', 'arraypress' ),
				'render_callback' => function ( $row, $column_key ) {
					return edd_currency_filter( edd_format_amount( $row->total ), $row->currency );
				},
			],
			'savings' => [
				'label'           => __( 'Savings', 'arraypress' ),
				'render_callback' => function ( $row, $column_key ) use ( $discount ) {
					$total = current(
						edd_get_order_adjustments( [
							'number'      => 1,
							'object_id'   => $row->id,
							'object_type' => 'order',
							'type_id'     => $discount->id,
							'type'        => 'discount',
							'fields'      => 'total',
							'order'       => 'DESC'
						] )
					);
					if ( ! empty( $total ) ) {
						return edd_currency_filter(
							edd_format_amount( (float) $total ),
							$row->currency
						);
					}

					return null;
				}
			],
			'date'    => [
				'label'           => __( 'Date', 'arraypress' ),
				'prefix'          => function ( $row, $column_key ) {
					return '<time datetime="' . esc_attr( EDD()->utils->date( $row->date_created, null, true )->toDateTimeString() ) . '">';
				},
				'suffix'          => '</time>',
				'render_callback' => function ( $row, $column_key ) {
					return edd_date_i18n( $row->date_created, 'M. d, Y' ) . '<br>' .
					       edd_date_i18n( strtotime( $row->date_created ), 'H:i' ) . ' ' .
					       edd_get_timezone_abbr();
				},
			],
		],
		'empty_message' => __( 'No orders found for this discount', 'arraypress' ),
		'data_callback' => function () use ( $discount ) {
			return edd_get_orders( [
				'discount_id' => $discount->id,
				'number'      => 10,
				'type'        => 'sale',
				'status__in'  => edd_get_complete_order_statuses(),
			] );
		},
	] );
}, 10, 2 );