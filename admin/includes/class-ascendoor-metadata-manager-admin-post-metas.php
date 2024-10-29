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
 * Class Ascendoor_Metadata_Manager_Admin_Post_Metas.
 *
 * @since 1.0.0
 */
class Ascendoor_Metadata_Manager_Admin_Post_Metas {

	/**
	 * Class instance holder.
	 *
	 * @since 1.0.0
	 *
	 * @var Ascendoor_Metadata_Manager_Admin_Post_Metas
	 */
	private static $instance;

	/**
	 * Get instance of class.
	 *
	 * @since 1.0.0
	 *
	 * @return Ascendoor_Metadata_Manager_Admin_Post_Metas
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
		if ( is_admin() ) {
			$this->add_actions();
		}
	}

	/**
	 * Add action related to the post type.
	 *
	 * @since 1.0.0
	 */
	private function add_actions() {
		add_action( 'load-post.php', array( $this, 'init_metabox' ) );
		add_action( 'load-post-new.php', array( $this, 'init_metabox' ) );

		add_action( 'wp_ajax_amdm-post-meta-delete', array( $this, 'delete_meta' ) );
		add_action( 'wp_ajax_amdm-post-meta-update', array( $this, 'update_meta' ) );
	}

	/**
	 * Init meta box for posts.
	 *
	 * @since 1.0.0
	 */
	public function init_metabox() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
	}

	/**
	 * Add meta box.
	 *
	 * @since 1.0.0
	 *
	 * @param string $post_type Post type.
	 */
	public function add_meta_box( $post_type ) {
		$ignored_posttypes = apply_filters( 'ascendoor_metadata_manager_ignore_posttype', array() );

		if ( is_array( $ignored_posttypes ) && in_array( $post_type, $ignored_posttypes, true ) ) {
			return;
		}

		add_meta_box(
			'ascendoor_post_meta_manager',
			__( 'Post Metadata Manager', 'ascendoor-metadata-manager' ),
			array( $this, 'render_meta_box_content' ),
			$post_type,
			'advanced',
			'low'
		);
	}

	/**
	 * Render the post meta box contents.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $post Post ID of the post.
	 */
	public function render_meta_box_content( $post ) {
		if ( ! $post instanceof WP_Post ) {
			return;
		}

		global $wpdb;
		// Custom query the metas so that we can list all the duplicate meta keys individually.
		$post_metas = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}postmeta WHERE post_id = {$post->ID}", ARRAY_A ); // phpcs:ignore
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
					if ( is_array( $post_metas ) && 0 < count( $post_metas ) ) {
						$ignore_keys = apply_filters(
							'ascendoor_metadata_manager_ignore_posts_keys',
							array(),
							$post
						);

						$_post_metas = array();

						foreach ( $post_metas as $meta_data_key => $meta_data ) {
							if ( ! in_array( $meta_data['meta_key'], $ignore_keys, true ) ) {
								$_post_metas[ $meta_data_key ] = $meta_data;
							}
						}

						if ( 0 < count( $_post_metas ) ) {
							foreach ( $_post_metas as $index_key => $meta_data ) {
								$meta_value = maybe_unserialize( $meta_data['meta_value'] );
								?>
								<tr
									data-post="<?php echo esc_attr( $post->ID ); ?>"
									data-id="<?php echo esc_attr( $meta_data['meta_id'] ); ?>"
									data-protected="<?php echo esc_attr( is_protected_meta( $meta_data['meta_key'], 'post' ) ); ?>"
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
	 * Delete post meta.
	 *
	 * @since 1.0.0
	 */
	public function delete_meta() {
		$id   = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;
		$post = isset( $_POST['post'] ) ? (int) $_POST['post'] : 0;

		check_ajax_referer( "amdm-delete_{$id}", 'amdm-delete' );
		$meta = get_metadata_by_mid( 'post', $id );

		if ( ! $meta ) {
			wp_die( 1 );
		}

		if ( $post !== (int) $meta->post_id ) {
			wp_die( 0 );
		}

		if ( ! current_user_can( 'edit_post', $post ) ) {
			wp_die( esc_html__( 'You are not allowed to delete this meta data.', 'ascendoor-metadata-manager' ) );
		}

		if ( delete_meta( $id ) ) {
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
		$id    = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;
		$post  = isset( $_POST['post'] ) ? (int) $_POST['post'] : 0;
		$value = isset( $_POST['value'] ) ? $_POST['value'] : ''; // phpcs:ignore

		check_ajax_referer( "amdm-update_{$id}", 'amdm-update' );
		$meta = get_metadata_by_mid( 'post', $id );

		if ( ! $meta ) {
			wp_die( 1 );
		}

		if ( $post !== (int) $meta->post_id ) {
			wp_die( 0 );
		}

		if ( ! current_user_can( 'edit_post', $post ) ) {
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

			update_metadata_by_mid( 'post', $id, $value, $meta->meta_key );

			wp_die( 1 );
		}

		wp_die( 'here' );
	}
}

Ascendoor_Metadata_Manager_Admin_Post_Metas::get_instance();
