<?php
/**
 * Comment Core Trait
 *
 * Provides fundamental functionality for working with individual WordPress comments.
 * Includes methods for verifying comment existence, retrieving comment data,
 * accessing comment content, and managing author information.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Traits\Comment;

use ArrayPress\Utils\Common\IP;
use ArrayPress\Utils\Common\Email;
use WP_Comment;
use WP_User;

trait Core {

	/**
	 * Check if the comment exists.
	 *
	 * @param int $comment_id The comment ID.
	 *
	 * @return bool True if comment exists, false otherwise.
	 */
	public static function exists( int $comment_id ): bool {
		return get_comment( $comment_id ) instanceof WP_Comment;
	}

	/**
	 * Get a comment by ID.
	 *
	 * @param int $comment_id The comment ID.
	 *
	 * @return WP_Comment|null Comment object if found, null otherwise.
	 */
	public static function get( int $comment_id ): ?WP_Comment {
		$comment = get_comment( $comment_id );

		return ( $comment instanceof WP_Comment ) ? $comment : null;
	}

	/**
	 * Get comment content.
	 *
	 * @param int  $comment_id The comment ID.
	 * @param bool $raw        Whether to return raw content. Default false.
	 *
	 * @return string Comment content.
	 */
	public static function get_content( int $comment_id, bool $raw = false ): string {
		$comment = self::get( $comment_id );
		if ( ! $comment ) {
			return '';
		}

		return $raw ? $comment->comment_content : apply_filters( 'comment_text', $comment->comment_content );
	}

	/**
	 * Get comment author details.
	 *
	 * @param int $comment_id The comment ID.
	 *
	 * @return array Author details including name, email, and URL.
	 */
	public static function get_author_details( int $comment_id ): array {
		$comment = self::get( $comment_id );
		if ( ! $comment ) {
			return [];
		}

		return [
			'name'  => $comment->comment_author,
			'email' => $comment->comment_author_email,
			'url'   => $comment->comment_author_url,
			'ip'    => $comment->comment_author_IP
		];
	}

	/**
	 * Get comment author user object.
	 *
	 * Retrieves the WordPress user object for the comment author if the comment
	 * was made by a registered user.
	 *
	 * @param int $comment_id The comment ID.
	 *
	 * @return WP_User|null User object if comment was made by a registered user, null otherwise.
	 */
	public static function get_author_user( int $comment_id ): ?WP_User {
		$comment = self::get( $comment_id );
		if ( ! $comment || ! $comment->user_id ) {
			return null;
		}

		$user = get_user_by( 'id', $comment->user_id );

		return ( $user instanceof WP_User ) ? $user : null;
	}

	/**
	 * Get comment author's email address.
	 *
	 * Retrieves the email address of the comment author. Works for both
	 * registered users and guest commenters. Can optionally return an
	 * anonymized version of the email address.
	 *
	 * @param int  $comment_id The comment ID.
	 * @param bool $anonymize  Optional. Whether to anonymize the email address. Default false.
	 *
	 * @return string|null The author's email address if available, null otherwise.
	 */
	public static function get_author_email( int $comment_id, bool $anonymize = false ): ?string {
		$comment = self::get( $comment_id );
		if ( ! $comment || empty( $comment->comment_author_email ) ) {
			return null;
		}

		$email = $comment->comment_author_email;

		// Validate the stored email address
		if ( ! Email::is_valid( $email ) ) {
			return null;
		}

		return $anonymize ? Email::anonymize( $email ) : $email;
	}

	/**
	 * Get comment author's IP address.
	 *
	 * Retrieves the IP address from which the comment was made.
	 *
	 * @param int  $comment_id The comment ID.
	 * @param bool $anonymize  Optional. Whether to anonymize the IP address. Default false.
	 *
	 * @return string|null The author's IP address if available, null otherwise.
	 */
	public static function get_author_ip( int $comment_id, bool $anonymize = false ): ?string {
		$comment = self::get( $comment_id );
		if ( ! $comment || empty( $comment->comment_author_IP ) ) {
			return null;
		}

		$ip = $comment->comment_author_IP;

		// Validate the stored IP address
		if ( ! IP::is_valid( $ip ) ) {
			return null;
		}

		return $anonymize ? IP::anonymize( $ip ) : $ip;
	}

	/**
	 * Check if comment is by a registered user.
	 *
	 * Determines whether the comment was made by a registered WordPress user.
	 *
	 * @param int $comment_id The comment ID.
	 *
	 * @return bool True if comment is by a registered user, false otherwise.
	 */
	public static function is_by_registered_user( int $comment_id ): bool {
		$comment = self::get( $comment_id );

		return $comment && $comment->user_id > 0;
	}

	/**
	 * Check if comment is by a guest user.
	 *
	 * Determines whether the comment was made by a non-registered user (guest).
	 *
	 * @param int $comment_id The comment ID.
	 *
	 * @return bool True if comment is by a guest user, false otherwise.
	 */
	public static function is_guest( int $comment_id ): bool {
		$comment = self::get( $comment_id );

		return $comment && empty( $comment->user_id );
	}

}