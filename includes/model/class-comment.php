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

		$this->parent_post = \WP_Post::get_instance( $this->comment->comment_post_ID );
		$this->parent_comment = \WP_Comment::get_instance( $this->comment->comment_parent );

		$this->comment_author = $this->generate_comment_author();
		$this->id             = $this->generate_id();
		$this->inReplyTo      = $this->generate_in_reply_to();
		$this->content        = $this->generate_the_content();
		$this->object_type    = $this->generate_object_type();
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
			'type' => $this->object_type,
			'published' => \gmdate( 'Y-m-d\TH:i:s\Z', \strtotime( $comment->comment_date_gmt ) ),
			'content' => $this->content,
			'inReplyTo' => $this->inReplyTo,
			'attributedTo' => $this->comment_author,
		);

		return \apply_filters( 'activitypub_comment', $array );
	}

	public function to_json() {
		return \wp_json_encode( $this->to_array(), \JSON_HEX_TAG | \JSON_HEX_AMP | \JSON_HEX_QUOT );
	}

	public function generate_id_for_permalink( $permalink ) {
		// replace 'trashed' for delete activity
		return \str_replace( '__trashed', '', $permalink );
	}

	public function generate_id_for_comment( $comment ) {
		return $this->generate_id_for_permalink( \get_comment_link( $comment ) );
	}

	public function generate_id_for_post( $post ) {
		return $this->generate_id_for_permalink( \get_permalink( $post ) );
	}

	public function generate_id() {
		return $this->generate_id_for_comment( $this->comment );
	}

	public function generate_comment_author() {
		if ( $this->comment->user_id != 0 ) {
			\get_author_posts_url( intval( $this->comment->user_id ) );
		}
		return $this->comment->author_url;
	}

	public function generate_in_reply_to() {
		if ( $this->parent_comment ) {
			return $this->generate_id_for_comment( $this->parent_comment );
		}
		if ( $this->parent_post ) {
			return $this->generate_id_for_post( $this->parent_post );
		}
	}

	/**
	 * Returns the as2 object-type for a given post
	 *
	 * @return string the object-type
	 */
	public function generate_object_type() {
		return 'Note';
	}

	public function generate_the_content() {
		$content = $this->comment->comment_content;

		$content = \trim( \preg_replace( '/[\r\n]{2,}/', '', $content ) );

		$filtered_content = \apply_filters( 'activitypub_comment_content', $content, $this->comment );
		$decoded_content = \html_entity_decode( $filtered_content, \ENT_QUOTES, 'UTF-8' );

		$allowed_html = \apply_filters( 'activitypub_allowed_html', \get_option( 'activitypub_allowed_html', ACTIVITYPUB_ALLOWED_HTML ) );

		if ( $allowed_html ) {
			return \strip_tags( $decoded_content, $allowed_html );
		}

		return $decoded_content;
	}

	/**
	 * Get IDs of all authors in the thread back to the original post.  Includes only authors that are registered.
	 *
	 * @return array Array of integer IDs
	 */
	private function get_thread_author_ids_from_comment( $comment ) {
		\error_log( "@@@ get_thread_author_ids, user " . $comment->user_id . ", parent " . $this->comment->comment_parent );
		if ( $this->parent_comment ) {
			$author_ids = $this->get_thread_author_ids_from_comment( $this->parent_comment );
		}
		else {
			$author_ids = [];
		}
		if ( $comment->user_id ) {
			array_push( $author_ids, $comment->user_id );
		}
		return $author_ids;
	}

	/**
	 * Get IDs of all authors in the thread back to the original post.  Includes only authors that are registered.
	 *
	 * @return array Array of integer IDs
	 */
	public function get_thread_author_ids() {
		\error_log( "@@@ get_thread_author_ids post id " . $this->comment->comment_post_ID );
		$author_ids = $this->get_thread_author_ids_from_comment( $this->comment );
		if ( $this->parent_post ) {
			array_push( $author_ids, $this->parent_post->post_author );
		}
		\error_log( "@@@ get_thread_author_ids " . print_r( $author_ids, true ) );
		return $author_ids;
	}
}
