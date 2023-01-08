<?php
namespace Activitypub\Model;

/**
 * ActivityPub Comment Class
 *
 * @author Matthew Exon
 */
class Comment {
	private $comment;
	private $comment_author;

	public function __construct( $comment = null ) {
		$this->comment = \get_comment( $comment );

		$this->post_author = $this->comment->comment_author;
	}

	public function __call( $method, $params ) {
		$var = \strtolower( \substr( $method, 4 ) );

		if ( \strncasecmp( $method, 'get', 3 ) === 0 ) {
			return $this->$var;
		}

		if ( \strncasecmp( $method, 'set', 3 ) === 0 ) {
			$this->$var = $params[0];
		}
	}

	public function to_array() {
		$comment = $this->comment;

		$array = array(
			'id' => $this->id,
			'type' => $this->comment_type,
			'published' => \gmdate( 'Y-m-d\TH:i:s\Z', \strtotime( $comment->comment_date_gmt ) ),
		);

		return \apply_filters( 'activitypub_comment', $array );
	}

	public function to_json() {
		return \wp_json_encode( $this->to_array(), \JSON_HEX_TAG | \JSON_HEX_AMP | \JSON_HEX_QUOT );
	}

	public function generate_id() {
		$comment      = $this->comment;
		$permalink = \get_comment_link( $comment );

		// replace 'trashed' for delete activity
		return \str_replace( '__trashed', '', $permalink );
	}

	public function generate_attachments() {
	}

	public function generate_tags() {
	}

	/**
	 * Returns the as2 object-type for a given post
	 *
	 * @param string $type the object-type
	 * @param Object $post the post-object
	 *
	 * @return string the object-type
	 */
	public function generate_object_type() {
	}

	public function generate_the_content() {
	}

	public function get_post_content_template() {
	}

	/**
	 * Get the excerpt for a post for use outside of the loop.
	 *
	 * @param int     Optional excerpt length.
	 *
	 * @return string The excerpt.
	 */
	public function get_the_post_excerpt( $excerpt_length = 400 ) {
	}

	/**
	 * Get the content for a post for use outside of the loop.
	 *
	 * @return string The content.
	 */
	public function get_the_post_content() {
	}

	/**
	 * Adds a backlink to the post/summary content
	 *
	 * @param string  $content
	 * @param WP_Post $post
	 *
	 * @return string
	 */
	public function get_the_post_link( $type = 'permalink' ) {
	}

	/**
	 * Adds all tags as hashtags to the post/summary content
	 *
	 * @param string  $content
	 * @param WP_Post $post
	 *
	 * @return string
	 */
	public function get_the_post_hashtags() {
	}
}
