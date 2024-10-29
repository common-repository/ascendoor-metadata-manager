<?php
/**
 * Plugin core definition.
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
 * Class Ascendoor_Metadata_Manager.
 *
 * @since 1.0.0
 */
class Ascendoor_Metadata_Manager {
	/**
	 * Ascendoor_Metadata_Manager instance holder.
	 *
	 * @since 1.0.0
	 *
	 * @var Ascendoor_Metadata_Manager
	 */
	private static $instance;

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
	 * Plugin admin (backend) Ascendoor_Metadata_Manager_Admin instance holder.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $plugin_admin;

	/**
	 * Initialize Ascendoor_Metadata_Manager instance.
	 *
	 * @since 1.0.0
	 *
	 * @return Ascendoor_Metadata_Manager Ascendoor_Metadata_Manager instance.
	 */
	public static function get_instance() {
		if ( ! self::$instance instanceof self ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Ascendoor_Metadata_Manager constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->plugin_name = 'ascendoor-metadata-manager';

		if ( defined( 'ASCENDOOR_METADATA_MANAGER_VERSION' ) ) {
			$this->plugin_version = ASCENDOOR_METADATA_MANAGER_VERSION;
		} else {
			$this->plugin_version = '1.0.0';
		}

		$this->load_dependencies();
		$this->load_i18n();
		$this->load_admin();
	}

	/**
	 * Load plugin dependent files.
	 *
	 * @since 1.0.0
	 */
	private function load_dependencies() {
		require_once ASCENDOOR_METADATA_MANAGER_DIR_PATH . 'includes/helper-ascendoor-metadata-manager.php';

	}

	/**
	 * Load plugin language files.
	 *
	 * @since 1.0.0
	 */
	private function load_i18n() {
		require_once ASCENDOOR_METADATA_MANAGER_DIR_PATH . 'includes/class-ascendoor-metadata-manager-i18n.php';
		new Ascendoor_Metadata_Manager_i18n( $this->plugin_name, $this->plugin_version );
	}

	/**
	 * Initialize plugin Admin area.
	 *
	 * @since 1.0.0
	 */
	private function load_admin() {
		// Initialize Admin only in admin area.
		if ( is_admin() ) {
			require_once ASCENDOOR_METADATA_MANAGER_DIR_PATH . 'admin/class-ascendoor-metadata-manager-admin.php';
			$this->plugin_admin = new Ascendoor_Metadata_Manager_Admin( $this->plugin_name, $this->plugin_version );
		}
	}

}
