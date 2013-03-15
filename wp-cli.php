<?php
/**
 * Implements regenerate-thumbnails command.
 *
 * @package wp-cli
 * @subpackage commands/third-party
 */
class RegenerateThumbnail_Command extends WP_CLI_Command {

	/**
	 * Regenerate all images
	 */
	function all() {
		global $wpdb;

		$instance = RegenerateThumbnails();
		if ( ! $images = $wpdb->get_results( "SELECT ID FROM $wpdb->posts WHERE post_type = 'attachment' AND post_mime_type LIKE 'image/%' ORDER BY ID DESC" ) ) {
			WP_CLI::error( __( "Unable to find any images. Are you sure some exist?", 'regenerate-thumbnails' ) );
		}

		$count = count( $images );
		$success = 0;
		$failures = 0;
		$notify = new \cli\progress\Bar( 'Processing images', $count );
		foreach ( $images as $image ) {
			$result = $instance->process_image( $image->ID );
			if ( isset( $result['error'] ) ) {
				WP_CLI::error( $result['error'] );
				$failures++;
			} else {
				$success++;
			}
			$notify->tick();
		}
		$notify->finish();

		$text = sprintf( __( 'All done! %1$s image(s) were successfully resized in %2$s seconds and there were %3$s failure(s).', 'regenerate-thumbnails' ), $success, timer_stop(), $failures );
		
		if ( $success > 0 )
			WP_CLI::success( $text );
		else
			WP_CLI::error( $text );
	}

	/**
	 * Regenerate specific image
	 *
	 * @synopsis <id>
	 */
	function image( $args ) {
		$id = (int) $args[0];

		$instance = RegenerateThumbnails();
		$result = $instance->process_image( $id );

		if ( isset( $result['success'] ) )
			WP_CLI::success( html_entity_decode( $result['success'] ) );
		else
			WP_CLI::error( $result['error'] );
		
	}


}

WP_CLI::add_command( 'regen-thumbs', 'RegenerateThumbnail_Command' );