<?php
/**
 * Status Utilities for WordPress Plugins
 *
 * This file contains the StatusUtilities class, which provides a set of utility functions
 * for working with common status types in WordPress applications. It offers methods
 * for retrieving various status types with localized labels, suitable for use in
 * plugin and theme development.
 *
 * @package     ArrayPress\Utils\Status
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      ArrayPress Team
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\I18n;

class Statuses {

	/**
	 * Get basic statuses.
	 *
	 * @param string|null $context Optional. The context for the statuses.
	 *
	 * @return array
	 */
	public static function get_basic( ?string $context = null ): array {
		return self::get( 'basic', $context );
	}

	/**
	 * Get general statuses.
	 *
	 * @param string|null $context Optional. The context for the statuses.
	 *
	 * @return array
	 */
	public static function get_general( ?string $context = null ): array {
		return self::get( 'general', $context );
	}

	/**
	 * Get payment statuses.
	 *
	 * @param string|null $context Optional. The context for the statuses.
	 *
	 * @return array
	 */
	public static function get_payment( ?string $context = null ): array {
		return self::get( 'payment', $context );
	}

	/**
	 * Get order statuses.
	 *
	 * @param string|null $context Optional. The context for the statuses.
	 *
	 * @return array
	 */
	public static function get_order( ?string $context = null ): array {
		return self::get( 'order', $context );
	}

	/**
	 * Get subscription statuses.
	 *
	 * @param string|null $context Optional. The context for the statuses.
	 *
	 * @return array
	 */
	public static function get_subscription( ?string $context = null ): array {
		return self::get( 'subscription', $context );
	}

	/**
	 * Get user statuses.
	 *
	 * @param string|null $context Optional. The context for the statuses.
	 *
	 * @return array
	 */
	public static function get_user( ?string $context = null ): array {
		return self::get( 'user', $context );
	}

	/**
	 * Get discount statuses.
	 *
	 * @param string|null $context Optional. The context for the statuses.
	 *
	 * @return array
	 */
	public static function get_discount( ?string $context = null ): array {
		return self::get( 'discount', $context );
	}

	/**
	 * Get review statuses.
	 *
	 * @param string|null $context Optional. The context for the statuses.
	 *
	 * @return array
	 */
	public static function get_review( ?string $context = null ): array {
		return self::get( 'review', $context );
	}

	/**
	 * Get ticket statuses.
	 *
	 * @param string|null $context Optional. The context for the statuses.
	 *
	 * @return array
	 */
	public static function get_ticket( ?string $context = null ): array {
		return self::get( 'ticket', $context );
	}

	/**
	 * Get product statuses.
	 *
	 * @param string|null $context Optional. The context for the statuses.
	 *
	 * @return array
	 */
	public static function get_product( ?string $context = null ): array {
		return self::get( 'product', $context );
	}

	/**
	 * Get email statuses.
	 *
	 * @param string|null $context Optional. The context for the statuses.
	 *
	 * @return array
	 */
	public static function get_email( ?string $context = null ): array {
		return self::get( 'email', $context );
	}

	/**
	 * Get invoice statuses.
	 *
	 * @param string|null $context Optional. The context for the statuses.
	 *
	 * @return array
	 */
	public static function get_invoice( ?string $context = null ): array {
		return self::get( 'invoice', $context );
	}

	/**
	 * Get project statuses.
	 *
	 * @param string|null $context Optional. The context for the statuses.
	 *
	 * @return array
	 */
	public static function get_project( ?string $context = null ): array {
		return self::get( 'project', $context );
	}

	/**
	 * Get task statuses.
	 *
	 * @param string|null $context Optional. The context for the statuses.
	 *
	 * @return array
	 */
	public static function get_task( ?string $context = null ): array {
		return self::get( 'task', $context );
	}

	/**
	 * Get membership statuses.
	 *
	 * @param string|null $context Optional. The context for the statuses.
	 *
	 * @return array
	 */
	public static function get_membership( ?string $context = null ): array {
		return self::get( 'membership', $context );
	}

	/**
	 * Get priority statuses.
	 *
	 * @param string|null $context Optional. The context for the statuses.
	 *
	 * @return array
	 */
	public static function get_priority( ?string $context = null ): array {
		return self::get( 'priorities', $context );
	}

	/**
	 * Get fulfilment statuses.
	 *
	 * @param string|null $context Optional. The context for the statuses.
	 *
	 * @return array
	 */
	public static function get_fulfilment( ?string $context = null ): array {
		return self::get( 'fulfilment', $context );
	}

	/**
	 * Get commission statuses.
	 *
	 * @param string|null $context Optional. The context for the statuses.
	 *
	 * @return array
	 */
	public static function get_commission( ?string $context = null ): array {
		return self::get( 'commission', $context );
	}

	/**
	 * Get statuses for a specific type.
	 *
	 * @param string      $type    The type of statuses to retrieve.
	 * @param string|null $context Optional. The context in which the statuses are being retrieved.
	 *
	 * @return array An array of statuses with labels.
	 */
	public static function get( string $type, ?string $context = null ): array {
		$statuses = self::get_default( $type );

		/**
		 * Filters the statuses for a specific type.
		 *
		 * This filter allows modification of the status array for a given type before it's returned.
		 * It can be used to add, remove, or modify statuses based on specific needs or contexts.
		 *
		 * @param array       $statuses The default array of statuses for the given type.
		 * @param string|null $context  The context in which the statuses are being retrieved.
		 *                              This can be used to further customize the statuses based on usage context.
		 *
		 * @return array The modified array of statuses.
		 */
		return apply_filters( "arraypress_{$type}_statuses", $statuses, $context );
	}

	/**
	 * Get default statuses for a specific type.
	 *
	 * @param string $type The type of statuses to retrieve.
	 *
	 * @return array An array of default statuses with labels.
	 */
	private static function get_default( string $type ): array {
		$all_statuses = [
			'basic'        => [
				'active'   => __( 'Active', 'arraypress' ),
				'inactive' => __( 'Inactive', 'arraypress' ),
			],
			'general'      => [
				'active'   => __( 'Active', 'arraypress' ),
				'inactive' => __( 'Inactive', 'arraypress' ),
				'pending'  => __( 'Pending', 'arraypress' ),
				'archived' => __( 'Archived', 'arraypress' ),
			],
			'payment'      => [
				'pending'    => __( 'Pending', 'arraypress' ),
				'processing' => __( 'Processing', 'arraypress' ),
				'completed'  => __( 'Completed', 'arraypress' ),
				'on-hold'    => __( 'On Hold', 'arraypress' ),
				'failed'     => __( 'Failed', 'arraypress' ),
				'refunded'   => __( 'Refunded', 'arraypress' ),
				'cancelled'  => __( 'Cancelled', 'arraypress' ),
			],
			'order'        => [
				'pending'    => __( 'Pending', 'arraypress' ),
				'processing' => __( 'Processing', 'arraypress' ),
				'on-hold'    => __( 'On Hold', 'arraypress' ),
				'completed'  => __( 'Completed', 'arraypress' ),
				'cancelled'  => __( 'Cancelled', 'arraypress' ),
				'refunded'   => __( 'Refunded', 'arraypress' ),
				'failed'     => __( 'Failed', 'arraypress' ),
			],
			'subscription' => [
				'active'    => __( 'Active', 'arraypress' ),
				'on-hold'   => __( 'On Hold', 'arraypress' ),
				'cancelled' => __( 'Cancelled', 'arraypress' ),
				'expired'   => __( 'Expired', 'arraypress' ),
				'pending'   => __( 'Pending', 'arraypress' ),
			],
			'user'         => [
				'active'    => __( 'Active', 'arraypress' ),
				'inactive'  => __( 'Inactive', 'arraypress' ),
				'suspended' => __( 'Suspended', 'arraypress' ),
				'pending'   => __( 'Pending', 'arraypress' ),
			],
			'discount'     => [
				'active'   => __( 'Active', 'arraypress' ),
				'inactive' => __( 'Inactive', 'arraypress' ),
				'expired'  => __( 'Expired', 'arraypress' ),
				'archived' => __( 'Archived', 'arraypress' ),
			],
			'review'       => [
				'approved' => __( 'Approved', 'arraypress' ),
				'pending'  => __( 'Pending', 'arraypress' ),
				'spam'     => __( 'Spam', 'arraypress' ),
				'trash'    => __( 'Trash', 'arraypress' ),
			],
			'ticket'       => [
				'open'        => __( 'Open', 'arraypress' ),
				'in-progress' => __( 'In Progress', 'arraypress' ),
				'resolved'    => __( 'Resolved', 'arraypress' ),
				'closed'      => __( 'Closed', 'arraypress' ),
				'on-hold'     => __( 'On Hold', 'arraypress' ),
			],
			'product'      => [
				'draft'        => __( 'Draft', 'arraypress' ),
				'pending'      => __( 'Pending Review', 'arraypress' ),
				'private'      => __( 'Private', 'arraypress' ),
				'published'    => __( 'Published', 'arraypress' ),
				'scheduled'    => __( 'Scheduled', 'arraypress' ),
				'archived'     => __( 'Archived', 'arraypress' ),
				'out-of-stock' => __( 'Out of Stock', 'arraypress' ),
				'on-backorder' => __( 'On Backorder', 'arraypress' ),
			],
			'email'        => [
				'sent'      => __( 'Sent', 'arraypress' ),
				'delivered' => __( 'Delivered', 'arraypress' ),
				'opened'    => __( 'Opened', 'arraypress' ),
				'clicked'   => __( 'Clicked', 'arraypress' ),
				'bounced'   => __( 'Bounced', 'arraypress' ),
				'failed'    => __( 'Failed', 'arraypress' ),
				'queued'    => __( 'Queued', 'arraypress' ),
				'scheduled' => __( 'Scheduled', 'arraypress' ),
			],
			'invoice'      => [
				'draft'          => __( 'Draft', 'arraypress' ),
				'pending'        => __( 'Pending', 'arraypress' ),
				'sent'           => __( 'Sent', 'arraypress' ),
				'paid'           => __( 'Paid', 'arraypress' ),
				'partially-paid' => __( 'Partially Paid', 'arraypress' ),
				'overdue'        => __( 'Overdue', 'arraypress' ),
				'cancelled'      => __( 'Cancelled', 'arraypress' ),
				'refunded'       => __( 'Refunded', 'arraypress' ),
			],
			'project'      => [
				'planning'    => __( 'Planning', 'arraypress' ),
				'in-progress' => __( 'In Progress', 'arraypress' ),
				'on-hold'     => __( 'On Hold', 'arraypress' ),
				'completed'   => __( 'Completed', 'arraypress' ),
				'cancelled'   => __( 'Cancelled', 'arraypress' ),
				'archived'    => __( 'Archived', 'arraypress' ),
			],
			'task'         => [
				'not-started' => __( 'Not Started', 'arraypress' ),
				'in-progress' => __( 'In Progress', 'arraypress' ),
				'on-hold'     => __( 'On Hold', 'arraypress' ),
				'completed'   => __( 'Completed', 'arraypress' ),
				'cancelled'   => __( 'Cancelled', 'arraypress' ),
				'overdue'     => __( 'Overdue', 'arraypress' ),
			],
			'fulfilment'   => [
				'unfulfilled' => __( 'Unfulfilled', 'arraypress' ),
				'fulfilled'   => __( 'Fulfilled', 'arraypress' ),
				'expired'     => __( 'Expired', 'arraypress' ),
				'cancelled'   => __( 'Cancelled', 'arraypress' )
			],
			'membership'   => [
				'active'    => __( 'Active', 'arraypress' ),
				'pending'   => __( 'Pending', 'arraypress' ),
				'cancelled' => __( 'Cancelled', 'arraypress' ),
				'expired'   => __( 'Expired', 'arraypress' ),
				'on-hold'   => __( 'On Hold', 'arraypress' ),
				'suspended' => __( 'Suspended', 'arraypress' ),
			],
			'priorities'   => [
				'low'    => __( 'Low', 'arraypress' ),
				'medium' => __( 'Medium', 'arraypress' ),
				'high'   => __( 'High', 'arraypress' ),
			],
			'commission'   => [
				'unpaid'  => __( 'Unpaid', 'arraypress' ),
				'paid'    => __( 'Paid', 'arraypress' ),
				'revoked' => __( 'Revoked', 'arraypress' ),
			]
		];


		/**
		 * Filter the available status types.
		 *
		 * Allows adding new status types to the default set. Use this filter to register
		 * custom status types with their respective labels.
		 *
		 * @param array $all_statuses Array of all registered status types and their labels.
		 *
		 * @example
		 * // Add a custom status type
		 * add_filter( 'arraypress_status_types', function( $all_statuses ) {
		 *     $all_statuses['custom'] = [
		 *         'status1' => __( 'Status One', 'text-domain' ),
		 *         'status2' => __( 'Status Two', 'text-domain' ),
		 *     ];
		 *     return $all_statuses;
		 * });
		 */
		$all_statuses = apply_filters( 'arraypress_status_types', $all_statuses );

		return $all_statuses[ $type ] ?? [];
	}

}