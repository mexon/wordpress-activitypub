<?php

namespace Activitypub;

use Activitypub\Collection\Users;
use Activitypub\Collection\Followers;
use Activitypub\Transformer\Post;

/**
 * ActivityPub Scheduler Class
 *
 * @author Matthias Pfefferle
 */
class Scheduler {
	/**
	 * Initialize the class, registering WordPress hooks
	 */
	public static function init() {
		\add_action( 'transition_post_status', array( self::class, 'schedule_post_activity' ), 33, 3 );

		\add_action( 'activitypub_update_followers', array( self::class, 'update_followers' ) );
		\add_action( 'activitypub_cleanup_followers', array( self::class, 'cleanup_followers' ) );

		\add_action( 'admin_init', array( self::class, 'schedule_migration' ) );
	}

	/**
	 * Schedule all ActivityPub schedules.
	 *
	 * @return void
	 */
	public static function register_schedules() {
		if ( ! \wp_next_scheduled( 'activitypub_update_followers' ) ) {
			\wp_schedule_event( time(), 'hourly', 'activitypub_update_followers' );
		}

		if ( ! \wp_next_scheduled( 'activitypub_cleanup_followers' ) ) {
			\wp_schedule_event( time(), 'daily', 'activitypub_cleanup_followers' );
		}
	}

	/**
	 * Unscedule all ActivityPub schedules.
	 *
	 * @return void
	 */
	public static function deregister_schedules() {
		wp_unschedule_hook( 'activitypub_update_followers' );
		wp_unschedule_hook( 'activitypub_cleanup_followers' );
	}


	/**
	 * Schedule Activities.
	 *
	 * @param string  $new_status New post status.
	 * @param string  $old_status Old post status.
	 * @param WP_Post $post       Post object.
	 */
	public static function schedule_post_activity( $new_status, $old_status, $post ) {
		// Do not send activities if post is password protected.
		if ( \post_password_required( $post ) ) {
			return;
		}

		// Check if post-type supports ActivityPub.
		$post_types = \get_post_types_by_support( 'activitypub' );
		if ( ! \in_array( $post->post_type, $post_types, true ) ) {
			return;
		}

		$type = false;

		if ( 'publish' === $new_status && 'publish' !== $old_status ) {
			$type = 'Create';
		} elseif ( 'publish' === $new_status ) {
			$type = 'Update';
		} elseif ( 'trash' === $new_status ) {
			$type = 'Delete';
		}

		if ( ! $type ) {
			return;
		}

		\wp_schedule_single_event(
			\time(),
			'activitypub_send_activity',
			array( $post, $type )
		);

		\wp_schedule_single_event(
			\time(),
			sprintf(
				'activitypub_send_%s_activity',
				\strtolower( $type )
			),
			array( $post )
		);
	}

	/**
	 * Update followers
	 *
	 * @return void
	 */
	public static function update_followers() {
		$followers = Followers::get_outdated_followers();

		foreach ( $followers as $follower ) {
			$meta = get_remote_metadata_by_actor( $follower->get_url(), true );

			if ( empty( $meta ) || ! is_array( $meta ) || is_wp_error( $meta ) ) {
				Followers::add_error( $follower->get__id(), $meta );
			} else {
				$follower->from_array( $meta );
				$follower->update();
			}
		}
	}

	/**
	 * Cleanup followers
	 *
	 * @return void
	 */
	public static function cleanup_followers() {
		$followers = Followers::get_faulty_followers();

		foreach ( $followers as $follower ) {
			$meta = get_remote_metadata_by_actor( $follower->get_url(), true );

			if ( is_tombstone( $meta ) ) {
				$follower->delete();
			} elseif ( empty( $meta ) || ! is_array( $meta ) || is_wp_error( $meta ) ) {
				if ( $follower->count_errors() >= 5 ) {
					$follower->delete();
				} else {
					Followers::add_error( $follower->get__id(), $meta );
				}
			} else {
				$follower->reset_errors();
			}
		}
	}

	/**
	 * Schedule migration if DB-Version is not up to date.
	 *
	 * @return void
	 */
	public static function schedule_migration() {
		if ( ! \wp_next_scheduled( 'activitypub_schedule_migration' ) && ! Migration::is_latest_version() ) {
			\wp_schedule_single_event( \time(), 'activitypub_schedule_migration' );
		}
	}
}
