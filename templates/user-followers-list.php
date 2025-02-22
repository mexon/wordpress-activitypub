<div class="wrap">
	<h1><?php \esc_html_e( 'Followers', 'activitypub' ); ?></h1>
	<?php // translators: The follower count. ?>
	<p><?php \printf( \esc_html__( 'You currently have %s followers.', 'activitypub' ), \esc_attr( \Activitypub\Collection\Followers::count_followers( \get_current_user_id() ) ) ); ?></p>

	<?php $table = new \Activitypub\Table\Followers(); ?>

	<form method="get">
		<input type="hidden" name="page" value="activitypub-followers-list" />
		<?php
		$table->prepare_items();
		$table->display();
		?>
		<?php wp_nonce_field( 'activitypub-followers-list', '_apnonce' ); ?>
		</form>
</div>
