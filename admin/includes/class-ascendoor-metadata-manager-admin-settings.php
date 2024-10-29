<?php
/**
 * The admin-specific settings/options functionality of the plugin.
 * Uses WordPress Settings API to register the sttting options.
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
 * Class Ascendoor_Metadata_Manager_Admin_Settings.
 *
 * @since 1.0.0
 */
class Ascendoor_Metadata_Manager_Admin_Settings {
	/**
	 * Plugin option values.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	private $options = array();

	/**
	 * Ascendoor_Metadata_Manager_Admin_Settings class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->options = get_option( 'amdm_settings', array() );

		add_action( 'admin_menu', array( $this, 'plugin_settings_page' ) );
		add_action( 'admin_init', array( $this, 'settings_init' ) );

		add_filter( 'plugin_action_links_ascendoor-metadata-manager/ascendoor-metadata-manager.php', array( $this, 'settings_link' ) );
	}

	/**
	 * Callback for the admin_menu action.
	 * Register plugin settings page as subpage under "Settings" menu.
	 *
	 * @since 1.0.0
	 */
	public function plugin_settings_page() {
		add_submenu_page(
			'options-general.php',
			'Ascendoor Metadata Manager',
			'Ascendoor Metadata Manager',
			'manage_options',
			'ascendoor_metadata_manager',
			array( $this, 'settings_ascendoor_metadata_manager_cb' )
		);
	}

	/**
	 * Callback for admin_init action.
	 * Setup/init plugin custom option and settings.
	 *
	 * @since 1.0.0
	 */
	public function settings_init() {
		// Register a new setting for "ascendoor_metadata_manager" page.
		register_setting( 'amdms', 'amdm_settings' );

		$posttypes = get_post_types( array( 'show_ui' => true ) );

		if ( is_array( $posttypes ) ) {
			// Register a posttype section in the "ascendoor_metadata_manager" page.
			add_settings_section(
				'amm_settings_posttypes',
				__( 'Disable Metadata Manager for Post Types', 'ascendoor-metadata-manager' ),
				'',
				'amdms'
			);

			foreach ( $posttypes as $post_type => $_post_type ) {
				if ( 'wp_block' === $post_type ) {
					continue;
				}

				$post_type_object = get_post_type_object( $post_type );

				add_settings_field(
					"amdms-posttype-{$post_type}",
					$post_type_object->labels->name,
					array( $this, 'post_type_field' ),
					'amdms',
					'amm_settings_posttypes',
					array(
						'label_for' => "amdms-posttype-{$post_type}",
						'post_type' => $post_type,
					)
				);
			}
		}

		$taxonomies = get_taxonomies( array( 'show_ui' => true ) );
		if ( is_array( $taxonomies ) ) {
			// Register a taxonomy section in the "ascendoor_metadata_manager" page.
			add_settings_section(
				'amm_settings_taxonomies',
				__( 'Disable Metadata Manager for Taxonomies', 'ascendoor-metadata-manager' ),
				'',
				'amdms'
			);

			foreach ( $taxonomies as $taxonomy => $tax ) {
				if ( 'link_category' === $taxonomy ) {
					continue;
				}

				$taxonomy_object = get_taxonomy( $taxonomy );

				add_settings_field(
					"amdms-taxonomy-{$taxonomy}",
					$taxonomy_object->labels->name,
					array( $this, 'taxonomy_field' ),
					'amdms',
					'amm_settings_taxonomies',
					array(
						'label_for' => "amdms-taxonomy-{$taxonomy}",
						'taxonomy'  => $taxonomy,
					)
				);
			}
		}

		// Register a user and comment section in the "ascendoor_metadata_manager" page.
		add_settings_section(
			'amm_settings_user_comment',
			__( 'Disable Metadata Manager for Users or Comments', 'ascendoor-metadata-manager' ),
			'',
			'amdms'
		);

		add_settings_field(
			'amdms-users',
			__( 'Users', 'ascendoor-metadata-manager' ),
			array( $this, 'user_comment_field' ),
			'amdms',
			'amm_settings_user_comment',
			array(
				'label_for' => 'amdms-users',
				'field'     => 'users',
			)
		);

		add_settings_field(
			'amdms-comments',
			__( 'Comments', 'ascendoor-metadata-manager' ),
			array( $this, 'user_comment_field' ),
			'amdms',
			'amm_settings_user_comment',
			array(
				'label_for' => 'amdms-comments',
				'field'     => 'comments',
			)
		);
	}

	/**
	 * Callback function for "ascendoor_metadata_manager" menu.
	 *
	 * @since 1.0.0
	 */
	public function settings_ascendoor_metadata_manager_cb() {
		// check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'amdms' );

				do_settings_sections( 'amdms' );

				submit_button( __( 'Save Settings', 'ascendoor-metadata-manager' ) );
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Post type field callbakc function.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Setting field.
	 */
	public function post_type_field( $args ) {
		$checked = '';
		if ( is_array( $this->options ) && isset( $this->options['posttype'] ) && is_array( $this->options['posttype'] ) ) {
			if ( in_array( $args['post_type'], $this->options['posttype'], true ) ) {
				$checked = 'checked';
			}
		}
		?>
		<input
			type="checkbox"
			id="<?php echo esc_attr( $args['label_for'] ); ?>"
			value="<?php echo esc_attr( $args['post_type'] ); ?>"
			name="amdm_settings[posttype][]"
			<?php echo esc_attr( $checked ); ?>
		/>
		<?php
	}

	/**
	 * Taxonomy field callbakc function.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Setting field.
	 */
	public function taxonomy_field( $args ) {
		$checked = '';
		if ( is_array( $this->options ) && isset( $this->options['taxonomy'] ) && is_array( $this->options['taxonomy'] ) ) {
			if ( in_array( $args['taxonomy'], $this->options['taxonomy'], true ) ) {
				$checked = 'checked';
			}
		}
		?>
		<input
			type="checkbox"
			id="<?php echo esc_attr( $args['label_for'] ); ?>"
			value="<?php echo esc_attr( $args['taxonomy'] ); ?>"
			name="amdm_settings[taxonomy][]"
			<?php echo esc_attr( $checked ); ?>
		/>
		<?php
	}

	/**
	 * User or comment field callbakc function.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Setting field.
	 */
	public function user_comment_field( $args ) {
		$checked = '';
		if ( is_array( $this->options ) && isset( $this->options['user_comment'] ) && is_array( $this->options['user_comment'] ) ) {
			if ( in_array( $args['field'], $this->options['user_comment'], true ) ) {
				$checked = 'checked';
			}
		}
		?>
		<input
			type="checkbox"
			id="<?php echo esc_attr( $args['label_for'] ); ?>"
			value="<?php echo esc_attr( $args['field'] ); ?>"
			name="amdm_settings[user_comment][]"
			<?php echo esc_attr( $checked ); ?>
		/>
		<?php
	}

	/**
	 * Add plugin settings link in plugins list area.
	 *
	 * @since 1.0.0
	 *
	 * @param array $links Array of available links.
	 * @return array
	 */
	public function settings_link( $links ) {
		$url = esc_url(
			add_query_arg(
				'page',
				'ascendoor_metadata_manager',
				admin_url( 'options-general.php' )
			)
		);

		// Create the link.
		$settings_link = "<a href=\"{$url}\">" . __( 'Settings', 'ascendoor-metadata-manager' ) . '</a>';

		// Adds the link to the end of the array.
		array_push(
			$links,
			$settings_link
		);

		return $links;
	}
}

new Ascendoor_Metadata_Manager_Admin_Settings();
