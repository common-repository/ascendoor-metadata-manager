<?php
/**
 * The admin-specific term meta data manage functionality of the plugin.
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
 * Class Ascendoor_Metadata_Manager_Admin_Term_Metas.
 *
 * @since 1.0.0
 */
class Ascendoor_Metadata_Manager_Admin_Term_Metas {

	/**
	 * Class instance holder.
	 *
	 * @since 1.0.0
	 *
	 * @var Ascendoor_Metadata_Manager_Admin_Term_Metas
	 */
	private static $instance;

	/**
	 * Get instance of class.
	 *
	 * @since 1.0.0
	 *
	 * @return Ascendoor_Metadata_Manager_Admin_Term_Metas
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
	 * Add action to render term meta contents.
	 *
	 * @since 1.0.0
	 */
	private function add_actions() {
		add_action( 'admin_init', array( $this, 'add_term_meta_box' ) );

		add_action( 'wp_ajax_amdm-term-meta-delete', array( $this, 'delete_meta' ) );
		add_action( 'wp_ajax_amdm-term-meta-update', array( $this, 'update_meta' ) );
	}

	/**
	 * If current screen is taxonomy term edit page, add meta box.
	 *
	 * @since 1.0.0
	 */
	public function add_term_meta_box() {
		$current_taxonomy = filter_input( INPUT_GET, 'taxonomy' );

		$ignored_taxonomies = apply_filters( 'ascendoor_metadata_manager_ignore_taxonomy', array() );

		if ( is_array( $ignored_taxonomies ) && in_array( $current_taxonomy, $ignored_taxonomies, true ) ) {
			return;
		}

		add_action( "{$current_taxonomy}_edit_form", array( $this, 'add_meta_box' ), 999999 );
	}

	/**
	 * Add the term meta box contents.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Term $term WP_Term to display term metas.
	 */
	public function add_meta_box( $term ) {
		if ( ! $term instanceof WP_Term ) {
			return;
		}

		add_meta_box(
			'ascendoor_metadata_manager_term',
			esc_html__( 'Term Metadata Manager', 'ascendoor-metadata-manager' ),
			array( $this, 'render_meta_box_content' ),
			'ascendoor-metadata-manager-term',
			'normal',
			'low'
		);
		?>
		<h2><?php esc_html_e( 'Term Metadata Manager', 'ascendoor-metadata-manager' ); ?></h2>
		<div id="poststuff">
			<div class="ascendoor-metadata-manager">
				<?php do_meta_boxes( 'ascendoor-metadata-manager-term', 'normal', $term ); ?>
			</div>
		</div>
		<?php
	}


	/**
	 * Render the term meta box contents.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Term $term WP_Term to display term metas.
	 */
	public function render_meta_box_content( $term ) {
		global $wpdb;
		// Custom query the metas so that we can list all the duplicate meta keys individually.
		$term_metas = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}termmeta WHERE term_id = {$term->term_id}", ARRAY_A ); // phpcs:ignore
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
				if ( is_array( $term_metas ) && 0 < count( $term_metas ) ) {
					$ignore_keys = apply_filters(
						'ascendoor_metadata_manager_ignore_term_keys',
						array(),
						$term
					);

					$_term_metas = array();

					foreach ( $term_metas as $meta_data_key => $meta_data ) {
						if ( ! in_array( $meta_data['meta_key'], $ignore_keys, true ) ) {
							$_term_metas[ $meta_data_key ] = $meta_data;
						}
					}

					if ( 0 < count( $_term_metas ) ) {
						foreach ( $_term_metas as $index_key => $meta_data ) {
							$meta_value = maybe_unserialize( $meta_data['meta_value'] );
							?>
							<tr
								data-term="<?php echo esc_attr( $term->term_id ); ?>"
								data-id="<?php echo esc_attr( $meta_data['meta_id'] ); ?>"
								data-protected="<?php echo esc_attr( is_protected_meta( $meta_data['meta_key'], 'term' ) ); ?>"
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
	 * Delete term meta.
	 *
	 * @since 1.0.0
	 */
	public function delete_meta() {
		$id   = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;
		$term = isset( $_POST['term'] ) ? (int) $_POST['term'] : 0;

		check_ajax_referer( "amdm-delete_{$id}", 'amdm-delete' );
		$meta = get_metadata_by_mid( 'term', $id );

		if ( ! $meta ) {
			wp_die( 1 );
		}

		if ( $term !== (int) $meta->term_id ) {
			wp_die( 0 );
		}

		if ( ! current_user_can( 'edit_term', $term ) ) {
			wp_die( esc_html__( 'You are not allowed to delete this meta data.', 'ascendoor-metadata-manager' ) );
		}

		if ( delete_metadata_by_mid( 'term', $id ) ) {
			wp_die( 1 );
		}

		wp_die( 0 );
	}

	/**
	 * Update term meta.
	 *
	 * @since 1.0.0
	 */
	public function update_meta() {
		$id    = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;
		$term  = isset( $_POST['term'] ) ? (int) $_POST['term'] : 0;
		$value = isset( $_POST['value'] ) ? $_POST['value'] : ''; // phpcs:ignore

		check_ajax_referer( "amdm-update_{$id}", 'amdm-update' );
		$meta = get_metadata_by_mid( 'term', $id );

		if ( ! $meta ) {
			wp_die( 1 );
		}

		if ( $term !== (int) $meta->term_id ) {
			wp_die( 0 );
		}

		if ( ! current_user_can( 'edit_term', $term ) ) {
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

			update_metadata_by_mid( 'term', $id, $value, $meta->meta_key );

			wp_die( 1 );
		}

		wp_die( 0 );
	}
}

Ascendoor_Metadata_Manager_Admin_Term_Metas::get_instance();
