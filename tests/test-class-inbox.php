<?php
class Test_Inbox extends WP_UnitTestCase {
	var $inbox;
	var $user_id;
	var $user_url;
	var $post_id;
	var $post_permalink;

	public function set_up() {
		$this->inbox = new \Activitypub\Rest\Inbox();
		$this->user_id = 1;
		$authordata = \get_userdata( $this->user_id );
		$this->user_url = $authordata->user_url;

		$this->post_id = \wp_insert_post(
			array(
				'post_author' => $this->user_id,
				'post_content' => 'test',
			)
		);
		$this->post_permalink = \get_permalink( $this->post_id );

		\add_filter( 'pre_get_remote_metadata_by_actor', array( '\Test_Inbox', 'get_remote_metadata_by_actor' ), 10, 2 );
	}

	public static function get_remote_metadata_by_actor( $value, $actor ) {
		return array(
			"name" => "Example User",
			"icon" => array(
				"url" => "https://example.com/icon",
			),
		);
	}

	public function create_test_object( $id = "https://example.com/123" ) {
		return array(
			"actor" => $this->user_url,
			"to" => [ $this->user_url ],
			"cc" => ["https://www.w3.org/ns/activitystreams#Public"],
			"object" => array(
				"id" => $id,
				"url" => "https://example.com/example",
				"inReplyTo" => $this->post_permalink,
				"content" => "example",
			),
		);
	}
	
	public function test_convert_object_to_comment_data_basic() {
		$converted = $this->inbox->convert_object_to_comment_data($this->create_test_object());

		$this->assertIsArray( $converted );
		$this->assertEquals( $this->post_id, $converted["comment_post_ID"] );
		$this->assertEquals( "Example User", $converted["comment_author"] );
		$this->assertEquals( "http://example.org", $converted["comment_author_url"] );
		$this->assertEquals( "example", $converted["comment_content"] );
		$this->assertEquals( "", $converted["comment_type"] );
		$this->assertEquals( "", $converted["comment_author_email"] );
		$this->assertEquals( 0, $converted["comment_parent"] );
		$this->assertArrayHasKey( "comment_meta", $converted );
		$this->assertEquals( "https://example.com/123", $converted["comment_meta"]["source_id"] );
		$this->assertEquals( "https://example.com/example", $converted["comment_meta"]["source_url"] );
		$this->assertEquals( "https://example.com/icon", $converted["comment_meta"]["avatar_url"] );
		$this->assertEquals( "activitypub", $converted["comment_meta"]["protocol"] );
	}

	public function test_convert_object_to_comment_data_object_unset_rejected() {
		$object = $this->create_test_object();
		unset( $object['object'] );
		$converted = $this->inbox->convert_object_to_comment_data( $object );
		$this->assertFalse( $converted );
	}

	public function test_convert_object_to_comment_data_non_public_rejected() {
		$object = $this->create_test_object();
		$object['cc'] = [];
		$converted = $this->inbox->convert_object_to_comment_data( $object );
		$this->assertFalse( $converted );
	}

	public function test_convert_object_to_comment_data_no_id_rejected() {
		$object = $this->create_test_object();
		unset($object['object']['id']);
		$converted = $this->inbox->convert_object_to_comment_data( $object );
		$this->assertFalse( $converted );
	}

	public function test_convert_object_to_comment_not_reply_rejected() {
		$object = $this->create_test_object();
		unset( $object['object']['inReplyTo'] );
		$converted = $this->inbox->convert_object_to_comment_data( $object );
		$this->assertFalse( $converted );
	}

	public function test_convert_object_to_comment_already_exists_rejected() {
		$object = $this->create_test_object( "https://example.com/test_convert_object_to_comment_already_exists_rejected" );
		$this->inbox->handle_create( $object, $this->user_id );
		$converted = $this->inbox->convert_object_to_comment_data( $object );
		$this->assertFalse( $converted );
	}

	public function test_convert_object_to_comment_with_context() {
		$object = $this->create_test_object();
		$object['object']['context'] = $this->post_permalink;
		$object['object']['inReplyTo'] = "https://example.com/123";
		$converted = $this->inbox->convert_object_to_comment_data( $object );
		$this->assertIsArray( $converted );
		$this->assertEquals( $this->post_id, $converted["comment_post_ID"] );
	}

	public function test_convert_object_to_comment_reply_to_comment() {
		$id = "https://example.com/test_convert_object_to_comment_reply_to_comment";
		$object = $this->create_test_object( $id );
		$this->inbox->handle_create( $object, $this->user_id );
		$comment = \Activitypub\object_id_to_comment( $id );

		$object['object']['inReplyTo'] = $id;
		$object['object']['id'] = "https://example.com/234";
		$converted = $this->inbox->convert_object_to_comment_data( $object );
		$this->assertIsArray( $converted );
		$this->assertEquals( $this->post_id, $converted["comment_post_ID"] );
		$this->assertEquals( $comment->comment_ID, $converted["comment_parent"] );
	}

	public function test_convert_object_to_comment_reply_to_non_existent_comment_rejected() {
		$object = $this->create_test_object();
		$object['object']['inReplyTo'] = "https://example.com/234";
		$converted = $this->inbox->convert_object_to_comment_data( $object );
		$this->assertFalse( $converted );
	}

	public function test_handle_create_basic() {
		$id = "https://example.com/test_handle_create_basic";
		$object = $this->create_test_object( $id );
		$this->inbox->handle_create( $object, $this->user_id );
		$comment = \Activitypub\object_id_to_comment( $id );
		$this->assertInstanceOf( WP_Comment::class, $comment );
	}
}
