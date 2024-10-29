<?php
/**
 * Plugin admin (backend) definition.
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
 * Class Ascendoor_Metadata_Manager_Admin.
 *
 * @since 1.0.0
 */
class Ascendoor_Metadata_Manager_Admin {
	/**
	 * Plugin name.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	private $plugin_name;

	/**
	 * Plugin version.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	private $plugin_version;

	/**
	 * Plugin option values.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	private $options = array();

	/**
	 * Plugin Ascendoor_Metadata_Manager_Admin constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string $plugin_name Plugin name.
	 * @param string $plugin_version Plugin version.
	 */
	public function __construct( $plugin_name, $plugin_version ) {
		$this->plugin_name    = $plugin_name;
		$this->plugin_version = $plugin_version;

		$this->options = get_option( 'amdm_settings', array() );

		$this->load_depencencies();
		$this->add_hooks();
	}

	/**
	 * Load admin dependent files.
	 *
	 * @since 1.0.0
	 */
	private function load_depencencies() {
		require_once ASCENDOOR_METADATA_MANAGER_DIR_PATH . 'admin/includes/class-ascendoor-metadata-manager-admin-settings.php';
		require_once ASCENDOOR_METADATA_MANAGER_DIR_PATH . 'admin/includes/class-ascendoor-metadata-manager-admin-term-metas.php';
		require_once ASCENDOOR_METADATA_MANAGER_DIR_PATH . 'admin/includes/class-ascendoor-metadata-manager-admin-post-metas.php';
		require_once ASCENDOOR_METADATA_MANAGER_DIR_PATH . 'admin/includes/class-ascendoor-metadata-manager-admin-user-metas.php';
		require_once ASCENDOOR_METADATA_MANAGER_DIR_PATH . 'admin/includes/class-ascendoor-metadata-manager-admin-comment-metas.php';
	}

	/**
	 * Request enqueue admin facing styles and scripts.
	 *
	 * @since 1.0.0
	 */
	private function add_hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		add_filter( 'ascendoor_metadata_manager_ignore_posttype', array( $this, 'option_post_type' ), 1, 1 );
		add_filter( 'ascendoor_metadata_manager_ignore_taxonomy', array( $this, 'option_taxonomy' ), 1, 1 );
		add_filter( 'ascendoor_metadata_manager_ignore_user', array( $this, 'option_user' ), 1, 1 );
		add_filter( 'ascendoor_metadata_manager_ignore_comment', array( $this, 'option_comment' ), 1, 1 );
	}

	/**
	 * Enqueue admin styles and scripts.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {
		// Enqueue style.
		wp_enqueue_style( "{$this->plugin_name}-admin", ASCENDOOR_METADATA_MANAGER_DIR_URL . 'admin/css/ascendoor-metadata-manager-admin.css', array(), $this->plugin_version );

		// Enqueue script.
		wp_enqueue_script( "{$this->plugin_name}-admin", ASCENDOOR_METADATA_MANAGER_DIR_URL . 'admin/js/ascendoor-metadata-manager-admin.js', array( 'jquery' ), $this->plugin_version, true );
		wp_localize_script(
			"{$this->plugin_name}-admin",
			'amdm',
			array(
				'ajax'                => admin_url( 'admin-ajax.php' ),
				'confirmDelete'       => esc_html__( 'Are you sure to delete this meta data?', 'ascendoor-metadata-manager' ),
				'invalidRequest'      => esc_html__( 'Invalid Request!!', 'ascendoor-metadata-manager' ),
				'protectedMeta'       => esc_html__( 'This meta data seems to be private. Are you sure to delete this meta data?', 'ascendoor-metadata-manager' ),
				'doReload'            => esc_html__( 'Reload page?', 'ascendoor-metadata-manager' ),
				'protectedMetaUpdate' => esc_html__( 'This meta data seems to be private. Are you sure to update this meta data?', 'ascendoor-metadata-manager' ),
				'confirmUpdate'       => esc_html__( 'Are you sure to update this meta data?', 'ascendoor-metadata-manager' ),
			)
		);
	}

	/**
	 * Filter to ignore post types.
	 *
	 * @since 1.0.0
	 *
	 * @param array $post_types Post types to ignore.
	 *
	 * @return array
	 */
	public function option_post_type( $post_types ) {
		if ( is_array( $this->options ) && isset( $this->options ) ) {
			if ( isset( $this->options['posttype'] ) && is_array( $this->options['posttype'] ) ) {
				return array_merge( $post_types, $this->options['posttype'] );
			}
		}

		return $post_types;
	}

	/**
	 * Filter to ignore taxonomies.
	 *
	 * @since 1.0.0
	 *
	 * @param array $taxonomies Taxonomies to ignore.
	 *
	 * @return array
	 */
	public function option_taxonomy( $taxonomies ) {
		if ( is_array( $this->options ) && isset( $this->options ) ) {
			if ( isset( $this->options['taxonomy'] ) && is_array( $this->options['taxonomy'] ) ) {
				return array_merge( $taxonomies, $this->options['taxonomy'] );
			}
		}

		return $taxonomies;
	}

	/**
	 * Filter to ignore for users.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $ignore To ignore for users.
	 *
	 * @return bool
	 */
	public function option_user( $ignore ) {
		if ( is_array( $this->options ) && isset( $this->options ) ) {
			if ( isset( $this->options['user_comment'] ) && is_array( $this->options['user_comment'] ) ) {
				return in_array( 'users', $this->options['user_comment'], true );
			}
		}

		return $ignore;
	}

	/**
	 * Filter to ignore for comments.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $ignore To ignore for comments.
	 *
	 * @return bool
	 */
	public function option_comment( $ignore ) {
		if ( is_array( $this->options ) && isset( $this->options ) ) {
			if ( isset( $this->options['user_comment'] ) && is_array( $this->options['user_comment'] ) ) {
				return in_array( 'comments', $this->options['user_comment'], true );
			}
		}

		return $ignore;
	}
}
