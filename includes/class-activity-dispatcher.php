<?php
namespace Activitypub;

/**
 * ActivityPub Activity_Dispatcher Class
 *
 * @author Matthias Pfefferle
 *
 * @see https://www.w3.org/TR/activitypub/
 */
class Activity_Dispatcher {
	/**
	 * Initialize the class, registering WordPress hooks.
	 */
	public static function init() {
		\add_action( 'activitypub_send_post_activity', array( '\Activitypub\Activity_Dispatcher', 'send_post_activity' ) );
		\add_action( 'activitypub_send_update_activity', array( '\Activitypub\Activity_Dispatcher', 'send_update_activity' ) );
		\add_action( 'activitypub_send_delete_activity', array( '\Activitypub\Activity_Dispatcher', 'send_delete_activity' ) );
		\add_action( 'activitypub_send_comment_activity', array( '\Activitypub\Activity_Dispatcher', 'send_comment_activity' ) );
		\add_action( 'activitypub_send_update_comment_activity', array( '\Activitypub\Activity_Dispatcher', 'send_update_comment_activity' ) );
		\add_action( 'activitypub_send_delete_comment_activity', array( '\Activitypub\Activity_Dispatcher', 'send_delete_comment_activity' ) );
	}

	/**
	 * Send "create" activities.
	 *
	 * @param \Activitypub\Model\Post $activitypub_post
	 */
	public static function send_post_activity( $activitypub_post ) {
		// get latest version of post
		$user_id = $activitypub_post->get_post_author();

		$activitypub_activity = new \Activitypub\Model\Activity( 'Create', \Activitypub\Model\Activity::TYPE_FULL );
		$activitypub_activity->from_post( $activitypub_post->to_array() );

		foreach ( \Activitypub\get_follower_inboxes( $user_id ) as $inbox => $to ) {
			$activitypub_activity->set_to( $to );

			$activity = $activitypub_activity->to_json(); // phpcs:ignore

			\Activitypub\safe_remote_post( $inbox, $activity, $user_id );
		}
	}

	/**
	 * Send "update" activities.
	 *
	 * @param \Activitypub\Model\Post $activitypub_post
	 */
	public static function send_update_activity( $activitypub_post ) {
		// get latest version of post
		$user_id = $activitypub_post->get_post_author();

		$activitypub_activity = new \Activitypub\Model\Activity( 'Update', \Activitypub\Model\Activity::TYPE_FULL );
		$activitypub_activity->from_post( $activitypub_post->to_array() );

		foreach ( \Activitypub\get_follower_inboxes( $user_id ) as $inbox => $to ) {
			$activitypub_activity->set_to( $to );
			$activity = $activitypub_activity->to_json(); // phpcs:ignore

			\Activitypub\safe_remote_post( $inbox, $activity, $user_id );
		}
	}

	/**
	 * Send "delete" activities.
	 *
	 * @param \Activitypub\Model\Post $activitypub_post
	 */
	public static function send_delete_activity( $activitypub_post ) {
		// get latest version of post
		$user_id = $activitypub_post->get_post_author();

		$activitypub_activity = new \Activitypub\Model\Activity( 'Delete', \Activitypub\Model\Activity::TYPE_FULL );
		$activitypub_activity->from_post( $activitypub_post->to_array() );

		foreach ( \Activitypub\get_follower_inboxes( $user_id ) as $inbox => $to ) {
			$activitypub_activity->set_to( $to );
			$activity = $activitypub_activity->to_json(); // phpcs:ignore

			\Activitypub\safe_remote_post( $inbox, $activity, $user_id );
		}
	}

	/**
	 * Send "create comment" activities.
	 *
	 * @param \Activitypub\Model\Comment $activitypub_comment
	 */
	public static function send_comment_activity( $activitypub_comment ) {
		\error_log( "@@@ send_comment_activity " . print_r($activitypub_comment, true) );

		$activitypub_activity = new \Activitypub\Model\Activity( 'Create', \Activitypub\Model\Activity::TYPE_FULL );
		$activitypub_activity->from_post( $activitypub_comment->to_array() );

		foreach ( $activitypub_comment->get_thread_authors_ids() as $user_id ) {
			foreach ( \Activitypub\get_follower_inboxes( $user_id ) as $inbox => $to ) {
				// TODO: exclude author who originally posted comment

				$activitypub_activity->set_to( $to );

				$activity = $activitypub_activity->to_json(); // phpcs:ignore

				\error_log( "@@@ send_comment_activity would send " . print_r($activity, true) );
				// \Activitypub\safe_remote_post( $inbox, $activity, $user_id );
			}
		}
	}

	/**
	 * Send "update comment" activities.
	 *
	 * @param \Activitypub\Model\Comment $activitypub_comment
	 */
	public static function send_update_comment_activity( $activitypub_comment ) {
		\error_log( "@@@ send_update_comment_activity " . print_r($activitypub_comment, true) );
	}

	/**
	 * Send "delete comment" activities.
	 *
	 * @param \Activitypub\Model\Post $activitypub_comment
	 */
	public static function send_delete_comment_activity( $activitypub_comment ) {
		\error_log( "@@@ send_delete_comment_activity " . print_r($activitypub_comment, true) );
	}
}
