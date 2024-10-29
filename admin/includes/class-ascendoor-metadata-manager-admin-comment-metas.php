<?php
/**
 * The admin-specific post meta data manage functionality of the plugin.
 *
 * @since 1.0.0
 *
 * @package Ascendoor_Metadata_Manager
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class Ascendoor_Metadata_Manager_Admin_Comment_Metas.
 *
 * @since 1.0.0
 */
class Ascendoor_Metadata_Manager_Admin_Comment_Metas {

	/**
	 * Class instance holder.
	 *
	 * @since 1.0.0
	 *
	 * @var Ascendoor_Metadata_Manager_Admin_Comment_Metas
	 */
	private static $instance;

	/**
	 * Get instance of class.
	 *
	 * @since 1.0.0
	 *
	 * @return Ascendoor_Metadata_Manager_Admin_Comment_Metas
	 */
	public static function get_instance() {
		if ( ! self::$instance instanceof self ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Class constructor.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		$this->add_actions();
	}

	/**
	 * Add action related to the post type.
	 *
	 * @since 1.0.0
	 */
	private function add_actions() {
		add_action( 'add_meta_boxes_comment', array( $this, 'add_meta_box' ), 99999 );

		add_action( 'wp_ajax_amdm-comment-meta-delete', array( $this, 'delete_meta' ) );
		add_action( 'wp_ajax_amdm-comment-meta-update', array( $this, 'update_meta' ) );
	}

	/**
	 * Add meta box.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Comment $comment WordPress Comment object.
	 */
	public function add_meta_box( $comment ) {
		$ignored_comment = apply_filters( 'ascendoor_metadata_manager_ignore_comment', false );

		if ( true === $ignored_comment ) {
			return;
		}

		add_meta_box(
			'ascendoor_comment_meta_manager',
			__( 'Comment Metadata Manager', 'ascendoor-metadata-manager' ),
			array( $this, 'render_meta_box_content' ),
			'comment',
			'normal',
			'low'
		);
	}

	/**
	 * Render the post meta box contents.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Comment $comment WordPress Comment object.
	 */
	public function render_meta_box_content( $comment ) {
		if ( ! $comment instanceof WP_Comment ) {
			return;
		}

		global $wpdb;
		// Custom query the metas so that we can list all the duplicate meta keys individually.
		$comment_metas = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}commentmeta WHERE comment_id = {$comment->comment_ID}", ARRAY_A ); // phpcs:ignore
		?>
		<div class="ascendoor-metadata-manager">
			<table class="form-table ascendoor-metadata-manager-table">
				<thead>
					<tr>
						<th class="meta-key">
							<?php esc_html_e( 'Meta Key', 'ascendoor-metadata-manager' ); ?>
						</th>
						<th class="meta-value">
							<?php esc_html_e( 'Meta Value', 'ascendoor-metadata-manager' ); ?>
						</th>
						<th class="meta-action">
							<?php esc_html_e( 'Action', 'ascendoor-metadata-manager' ); ?>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php
					if ( is_array( $comment_metas ) && 0 < count( $comment_metas ) ) {
						$ignore_keys = apply_filters(
							'ascendoor_metadata_manager_ignore_posts_keys',
							array(),
							$comment
						);

						$_comment_metas = array();

						foreach ( $comment_metas as $meta_data_key => $meta_data ) {
							if ( ! in_array( $meta_data['meta_key'], $ignore_keys, true ) ) {
								$_comment_metas[ $meta_data_key ] = $meta_data;
							}
						}

						if ( 0 < count( $_comment_metas ) ) {
							foreach ( $_comment_metas as $index_key => $meta_data ) {
								$meta_value = maybe_unserialize( $meta_data['meta_value'] );
								?>
								<tr
									data-comment="<?php echo esc_attr( $comment->comment_ID ); ?>"
									data-id="<?php echo esc_attr( $meta_data['meta_id'] ); ?>"
									data-protected="<?php echo esc_attr( is_protected_meta( $meta_data['meta_key'], 'comment' ) ); ?>"
								>
									<?php include ASCENDOOR_METADATA_MANAGER_DIR_PATH . 'admin/partials/ascendoor-metadata-manager-admin.php'; ?>
								</tr>
								<?php
							}
						} else {
							?>
							<tr>
								<td colspan="3" style="text-align: center;">
									<?php echo esc_html__( 'Meta data list is empty.', 'ascendoor-metadata-manager' ); ?>
								</td>
							</tr>
							<?php
						}
					} else {
						?>
						<tr>
							<td colspan="3" style="text-align: center;">
								<?php echo esc_html__( 'Meta data not found.', 'ascendoor-metadata-manager' ); ?>
							</td>
						</tr>
						<?php
					}
					?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Delete comment meta.
	 *
	 * @since 1.0.0
	 */
	public function delete_meta() {
		$id      = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;
		$comment = isset( $_POST['comment'] ) ? (int) $_POST['comment'] : 0;

		check_ajax_referer( "amdm-delete_{$id}", 'amdm-delete' );
		$meta = get_metadata_by_mid( 'comment', $id );

		if ( ! $meta ) {
			wp_die( 1 );
		}

		if ( $comment !== (int) $meta->comment_id ) {
			wp_die( 0 );
		}

		if ( ! current_user_can( 'edit_comment', $comment ) ) {
			wp_die( esc_html__( 'You are not allowed to delete this meta data.', 'ascendoor-metadata-manager' ) );
		}

		if ( delete_metadata_by_mid( 'comment', $id ) ) {
			wp_die( 1 );
		}

		wp_die( 0 );
	}

	/**
	 * Update post meta.
	 *
	 * @since 1.0.0
	 */
	public function update_meta() {
		$id      = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;
		$comment = isset( $_POST['comment'] ) ? (int) $_POST['comment'] : 0;
		$value   = isset( $_POST['value'] ) ? $_POST['value'] : ''; // phpcs:ignore

		check_ajax_referer( "amdm-update_{$id}", 'amdm-update' );
		$meta = get_metadata_by_mid( 'comment', $id );

		if ( ! $meta ) {
			wp_die( 1 );
		}

		if ( $comment !== (int) $meta->comment_id ) {
			wp_die( 0 );
		}

		if ( ! current_user_can( 'edit_comment', $comment ) ) {
			wp_die( esc_html__( 'You are not allowed to update this meta data.', 'ascendoor-metadata-manager' ) );
		}

		$old_value = maybe_unserialize( $meta->meta_value );

		if ( is_array( $old_value ) || is_object( $old_value ) ) {
			wp_die( 0 );
		}

		if ( $meta->meta_value == $value ) { // phpcs:ignore
			wp_die( 1 );
		} else {
			if ( strpos( $value, '<' ) !== false ) {
				$value = wp_kses( $value, 'post' );
			} else {
				$value = sanitize_textarea_field( $value );
			}

			update_metadata_by_mid( 'comment', $id, $value, $meta->meta_key );

			wp_die( 1 );
		}

		wp_die( 0 );
	}
}

Ascendoor_Metadata_Manager_Admin_Comment_Metas::get_instance();
