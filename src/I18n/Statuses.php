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

/**
 * Check if the class `Statuses` is defined, and if not, define it.
 */
if ( ! class_exists( 'Statuses' ) ) :

	/**
	 * Status Utilities
	 *
	 * Provides utility functions for managing common status types in WordPress applications.
	 * This class offers methods for retrieving localized status labels for various scenarios
	 * such as general statuses, payment statuses, order statuses, and more.
	 */
	class Statuses {

		/**
		 * Get basic statuses.
		 *
		 * @param string|null $context Optional. The context for the statuses.
		 *
		 * @return array
		 */
		public static function get_basic_statuses( ?string $context = null ): array {
			return Statuses::get_statuses( 'basic', $context );
		}

		/**
		 * Get general statuses.
		 *
		 * @param string|null $context Optional. The context for the statuses.
		 *
		 * @return array
		 */
		public static function get_general_statuses( ?string $context = null ): array {
			return Statuses::get_statuses( 'general', $context );
		}

		/**
		 * Get payment statuses.
		 *
		 * @param string|null $context Optional. The context for the statuses.
		 *
		 * @return array
		 */
		public static function get_payment_statuses( ?string $context = null ): array {
			return Statuses::get_statuses( 'payment', $context );
		}

		/**
		 * Get order statuses.
		 *
		 * @param string|null $context Optional. The context for the statuses.
		 *
		 * @return array
		 */
		public static function get_order_statuses( ?string $context = null ): array {
			return Statuses::get_statuses( 'order', $context );
		}

		/**
		 * Get subscription statuses.
		 *
		 * @param string|null $context Optional. The context for the statuses.
		 *
		 * @return array
		 */
		public static function get_subscription_statuses( ?string $context = null ): array {
			return Statuses::get_statuses( 'subscription', $context );
		}

		/**
		 * Get user statuses.
		 *
		 * @param string|null $context Optional. The context for the statuses.
		 *
		 * @return array
		 */
		public static function get_user_statuses( ?string $context = null ): array {
			return Statuses::get_statuses( 'user', $context );
		}

		/**
		 * Get discount statuses.
		 *
		 * @param string|null $context Optional. The context for the statuses.
		 *
		 * @return array
		 */
		public static function get_discount_statuses( ?string $context = null ): array {
			return Statuses::get_statuses( 'discount', $context );
		}

		/**
		 * Get review statuses.
		 *
		 * @param string|null $context Optional. The context for the statuses.
		 *
		 * @return array
		 */
		public static function get_review_statuses( ?string $context = null ): array {
			return Statuses::get_statuses( 'review', $context );
		}

		/**
		 * Get ticket statuses.
		 *
		 * @param string|null $context Optional. The context for the statuses.
		 *
		 * @return array
		 */
		public static function get_ticket_statuses( ?string $context = null ): array {
			return Statuses::get_statuses( 'ticket', $context );
		}

		/**
		 * Get product statuses.
		 *
		 * @param string|null $context Optional. The context for the statuses.
		 *
		 * @return array
		 */
		public static function get_product_statuses( ?string $context = null ): array {
			return Statuses::get_statuses( 'product', $context );
		}

		/**
		 * Get email statuses.
		 *
		 * @param string|null $context Optional. The context for the statuses.
		 *
		 * @return array
		 */
		public static function get_email_statuses( ?string $context = null ): array {
			return Statuses::get_statuses( 'email', $context );
		}

		/**
		 * Get invoice statuses.
		 *
		 * @param string|null $context Optional. The context for the statuses.
		 *
		 * @return array
		 */
		public static function get_invoice_statuses( ?string $context = null ): array {
			return Statuses::get_statuses( 'invoice', $context );
		}

		/**
		 * Get project statuses.
		 *
		 * @param string|null $context Optional. The context for the statuses.
		 *
		 * @return array
		 */
		public static function get_project_statuses( ?string $context = null ): array {
			return Statuses::get_statuses( 'project', $context );
		}

		/**
		 * Get task statuses.
		 *
		 * @param string|null $context Optional. The context for the statuses.
		 *
		 * @return array
		 */
		public static function get_task_statuses( ?string $context = null ): array {
			return Statuses::get_statuses( 'task', $context );
		}

		/**
		 * Get membership statuses.
		 *
		 * @param string|null $context Optional. The context for the statuses.
		 *
		 * @return array
		 */
		public static function get_membership_statuses( ?string $context = null ): array {
			return Statuses::get_statuses( 'membership', $context );
		}

		/**
		 * Get statuses for a specific type.
		 *
		 * @param string      $type    The type of statuses to retrieve.
		 * @param string|null $context Optional. The context in which the statuses are being retrieved.
		 *
		 * @return array An array of statuses with labels.
		 */
		public static function get_statuses( string $type, ?string $context = null ): array {
			$statuses = self::get_default_statuses( $type );

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
		private static function get_default_statuses( string $type ): array {
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
				'membership'   => [
					'active'    => __( 'Active', 'arraypress' ),
					'pending'   => __( 'Pending', 'arraypress' ),
					'cancelled' => __( 'Cancelled', 'arraypress' ),
					'expired'   => __( 'Expired', 'arraypress' ),
					'on-hold'   => __( 'On Hold', 'arraypress' ),
					'suspended' => __( 'Suspended', 'arraypress' ),
				],
			];

			return $all_statuses[ $type ] ?? [];
		}

	}
endif;