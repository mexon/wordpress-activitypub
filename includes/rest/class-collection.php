<?php
namespace Activitypub\Rest;

use WP_REST_Server;
use WP_REST_Response;
use Activitypub\Transformer\Post;
use Activitypub\Activity\Activity;
use Activitypub\Collection\Users as User_Collection;

use function Activitypub\esc_hashtag;
use function Activitypub\is_single_user;
use function Activitypub\get_rest_url_by_path;

/**
 * ActivityPub Collections REST-Class
 *
 * @author Matthias Pfefferle
 *
 * @see https://docs.joinmastodon.org/spec/activitypub/#featured
 * @see https://docs.joinmastodon.org/spec/activitypub/#featuredTags
 */
class Collection {
	/**
	 * Initialize the class, registering WordPress hooks
	 */
	public static function init() {
		\add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	/**
	 * Register routes
	 */
	public static function register_routes() {
		\register_rest_route(
			ACTIVITYPUB_REST_NAMESPACE,
			'/users/(?P<user_id>[\w\-\.]+)/collections/tags',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( self::class, 'tags_get' ),
					'args'                => self::request_parameters(),
					'permission_callback' => '__return_true',
				),
			)
		);

		\register_rest_route(
			ACTIVITYPUB_REST_NAMESPACE,
			'/users/(?P<user_id>[\w\-\.]+)/collections/featured',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( self::class, 'featured_get' ),
					'args'                => self::request_parameters(),
					'permission_callback' => '__return_true',
				),
			)
		);
	}

	/**
	 * The Featured Tags endpoint
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response The response object.
	 */
	public static function tags_get( $request ) {
		$user_id = $request->get_param( 'user_id' );
		$user    = User_Collection::get_by_various( $user_id );

		if ( is_wp_error( $user ) ) {
			return $user;
		}

		$number = 4;

		$tags = \get_terms(
			array(
				'taxonomy' => 'post_tag',
				'orderby'  => 'count',
				'order'    => 'DESC',
				'number'   => $number,
			)
		);

		if ( is_wp_error( $tags ) ) {
			$tags = array();
		}

		$response = array(
			'@context'   => Activity::CONTEXT,
			'id'         => get_rest_url_by_path( sprintf( 'users/%d/collections/tags', $user->get__id() ) ),
			'type'       => 'Collection',
			'totalItems' => count( $tags ),
			'items'      => array(),
		);

		foreach ( $tags as $tag ) {
			$response['items'][] = array(
				'type' => 'Hashtag',
				'href' => \esc_url( \get_tag_link( $tag ) ),
				'name' => esc_hashtag( $tag->name ),
			);
		}

		return new WP_REST_Response( $response, 200 );
	}

	/**
	 * Featured posts endpoint
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response The response object.
	 */
	public static function featured_get( $request ) {
		$user_id = $request->get_param( 'user_id' );
		$user    = User_Collection::get_by_various( $user_id );

		if ( is_wp_error( $user ) ) {
			return $user;
		}

		$sticky_posts = \get_option( 'sticky_posts' );

		if ( ! is_single_user() && User_Collection::BLOG_USER_ID === $user->get__id() ) {
			$posts = array();
		} elseif ( $sticky_posts ) {
			$args = array(
				'post__in'            => $sticky_posts,
				'ignore_sticky_posts' => 1,
				'orderby'             => 'date',
				'order'               => 'DESC',
			);

			if ( $user->get__id() > 0 ) {
				$args['author'] = $user->get__id();
			}

			$posts = \get_posts( $args );
		} else {
			$posts = array();
		}

		$response = array(
			'@context'     => Activity::CONTEXT,
			'id'           => get_rest_url_by_path( sprintf( 'users/%d/collections/featured', $user_id ) ),
			'type'         => 'OrderedCollection',
			'totalItems'   => count( $posts ),
			'orderedItems' => array(),
		);

		foreach ( $posts as $post ) {
			$response['orderedItems'][] = Post::transform( $post )->to_object()->to_array();
		}

		return new WP_REST_Response( $response, 200 );
	}

	/**
	 * The supported parameters
	 *
	 * @return array list of parameters
	 */
	public static function request_parameters() {
		$params = array();

		$params['user_id'] = array(
			'required' => true,
			'type' => 'string',
		);

		return $params;
	}
}
