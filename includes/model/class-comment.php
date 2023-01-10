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
		$this->id          = $this->generate_id();
		$this->content     = $this->generate_the_content();
		$this->object_type = $this->generate_object_type();
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

		$filtered_content = \apply_filters( 'activitypub_the_content', $content, $this->post );
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
	public function get_thread_author_ids() {
		\error_log( "@@@ get_thread_author_ids" );
                $author_ids = [ $this->post_author ];
                return $author_ids;
	}
}
