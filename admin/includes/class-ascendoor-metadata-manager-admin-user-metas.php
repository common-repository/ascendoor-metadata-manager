<?php
/**
 * The admin-specific user meta data manage functionality of the plugin.
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
 * Class Ascendoor_Metadata_Manager_Admin_User_Metas.
 *
 * @since 1.0.0
 */
class Ascendoor_Metadata_Manager_Admin_User_Metas {

	/**
	 * Class instance holder.
	 *
	 * @since 1.0.0
	 *
	 * @var Ascendoor_Metadata_Manager_Admin_User_Metas
	 */
	private static $instance;

	/**
	 * Get instance of class.
	 *
	 * @since 1.0.0
	 *
	 * @return Ascendoor_Metadata_Manager_Admin_User_Metas
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
	 * Add action to render user meta contents.
	 *
	 * @since 1.0.0
	 */
	private function add_actions() {
		add_action( 'edit_user_profile', array( $this, 'add_meta_box' ), 999999 );
		add_action( 'show_user_profile', array( $this, 'add_meta_box' ), 999999 );

		add_action( 'wp_ajax_amdm-user-meta-delete', array( $this, 'delete_meta' ) );
		add_action( 'wp_ajax_amdm-user-meta-update', array( $this, 'update_meta' ) );
	}

	/**
	 * Add the user meta box contents.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_User $user WP_User  to display user metas.
	 */
	public function add_meta_box( $user ) {
		if ( ! $user instanceof WP_User ) {
			return;
		}

		$ignore_users = apply_filters( 'ascendoor_metadata_manager_ignore_user', false );

		if ( true === $ignore_users ) {
			return;
		}

		add_meta_box(
			'ascendoor_metadata_manager_user',
			esc_html__( 'User Metadata Manager', 'ascendoor-metadata-manager' ),
			array( $this, 'render_meta_box_content' ),
			'ascendoor-metadata-manager-user',
			'normal',
			'low'
		);
		?>
		<h2><?php esc_html_e( 'User Metadata Manager', 'ascendoor-metadata-manager' ); ?></h2>
		<div id="poststuff">
			<div class="ascendoor-metadata-manager">
				<?php do_meta_boxes( 'ascendoor-metadata-manager-user', 'normal', $user ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render the user meta box contents.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_User $user WP_User  to display user metas.
	 */
	public function render_meta_box_content( $user ) {
		global $wpdb;
		// Custom query the metas so that we can list all the duplicate meta keys individually.
		$user_metas = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}usermeta WHERE user_id = {$user->ID}", ARRAY_A ); // phpcs:ignore
		?>
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
				if ( is_array( $user_metas ) && 0 < count( $user_metas ) ) {
					$ignore_keys = apply_filters(
						'ascendoor_metadata_manager_ignore_user_keys',
						array(),
						$user
					);

					$_user_metas = array();

					foreach ( $user_metas as $meta_data_key => $meta_data ) {
						if ( ! in_array( $meta_data['meta_key'], $ignore_keys, true ) ) {
							$_user_metas[ $meta_data_key ] = $meta_data;
						}
					}

					if ( 0 < count( $_user_metas ) ) {
						foreach ( $_user_metas as $index_key => $meta_data ) {
							$meta_value = maybe_unserialize( $meta_data['meta_value'] );
							?>
							<tr
								data-user="<?php echo esc_attr( $user->ID ); ?>"
								data-id="<?php echo esc_attr( $meta_data['umeta_id'] ); ?>"
								data-protected="<?php echo esc_attr( is_protected_meta( $meta_data['meta_key'], 'user' ) ); ?>"
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
		<?php
	}

	/**
	 * Delete user meta.
	 *
	 * @since 1.0.0
	 */
	public function delete_meta() {
		$id   = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;
		$user = isset( $_POST['user'] ) ? (int) $_POST['user'] : 0;

		check_ajax_referer( "amdm-delete_{$id}", 'amdm-delete' );
		$meta = get_metadata_by_mid( 'user', $id );

		if ( ! $meta ) {
			wp_die( 1 );
		}

		if ( $user !== (int) $meta->user_id ) {
			wp_die( 0 );
		}

		if ( ! current_user_can( 'edit_user', $user ) ) {
			wp_die( esc_html__( 'You are not allowed to delete this meta data.', 'ascendoor-metadata-manager' ) );
		}

		if ( delete_metadata_by_mid( 'user', $id ) ) {
			wp_die( 1 );
		}

		wp_die( 0 );
	}

	/**
	 * Update user meta.
	 *
	 * @since 1.0.0
	 */
	public function update_meta() {
		$id    = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;
		$user  = isset( $_POST['user'] ) ? (int) $_POST['user'] : 0;
		$value = isset( $_POST['value'] ) ? $_POST['value'] : ''; // phpcs:ignore

		check_ajax_referer( "amdm-update_{$id}", 'amdm-update' );
		$meta = get_metadata_by_mid( 'user', $id );

		if ( ! $meta ) {
			wp_die( 1 );
		}

		if ( $user !== (int) $meta->user_id ) {
			wp_die( 0 );
		}

		if ( ! current_user_can( 'edit_user', $user ) ) {
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

			update_metadata_by_mid( 'user', $id, $value, $meta->meta_key );

			wp_die( 1 );
		}

		wp_die( 0 );
	}
}

Ascendoor_Metadata_Manager_Admin_User_Metas::get_instance();
