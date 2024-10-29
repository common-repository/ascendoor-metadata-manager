<?php
/**
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @since             1.0.0
 * @package           Ascendoor_Metadata_Manager
 *
 * @wordpress-plugin
 * Plugin Name:       Ascendoor Metadata Manager
 * Description:       View and manage posts, terms, users and comments metadata.
 * Version:           1.0.0
 * Author:            Ascendoor
 * Author URI:        https://ascendoor.com/
 * License:           GNU General Public License v3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       ascendoor-metadata-manager
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Current plugin version.
 *
 * @since 1.0.0
 */
define( 'ASCENDOOR_METADATA_MANAGER_VERSION', '1.0.0' );

/**
 * Current plugin constants.
 *
 * @since 1.0.0
 */
define( 'ASCENDOOR_METADATA_MANAGER_DIR_URL', plugin_dir_url( __FILE__ ) );
define( 'ASCENDOOR_METADATA_MANAGER_DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'ASCENDOOR_METADATA_MANAGER_FOLDER_NAME', dirname( plugin_basename( __FILE__ ) ) );

/**
 * Plugin activation.
 *
 * @since 1.0.0
 * @return void
 */
function ascendoor_metadata_manager_activate() {
	if ( ! class_exists( 'Ascendoor_Metadata_Manager_Activate_Deactivate' ) ) {
		require_once ASCENDOOR_METADATA_MANAGER_DIR_PATH . 'includes/class-ascendoor-metadata-manager-activate-deactivate.php';
	}

	Ascendoor_Metadata_Manager_Activate_Deactivate::activate();
}
register_activation_hook( __FILE__, 'ascendoor_metadata_manager_activate' );

/**
 * Plugin deactivation.
 *
 * @since 1.0.0
 * @return void
 */
function ascendoor_metadata_manager_deactivate() {
	if ( ! class_exists( 'Ascendoor_Metadata_Manager_Activate_Deactivate' ) ) {
		require_once ASCENDOOR_METADATA_MANAGER_DIR_PATH . 'includes/class-ascendoor-metadata-manager-activate-deactivate.php';
	}

	Ascendoor_Metadata_Manager_Activate_Deactivate::deactivate();
}
register_deactivation_hook( __FILE__, 'ascendoor_metadata_manager_deactivate' );

/**
 * Begins execution of the plugin.
 *
 * @since 1.0.0
 * @return void
 */
function ascendoor_metadata_manager_plugin_init() {
	/**
	 * The plugin core class.
	 */
	require ASCENDOOR_METADATA_MANAGER_DIR_PATH . 'includes/class-ascendoor-metadata-manager.php';

	/**
	 * Begins execution of the plugin.
	 */
	Ascendoor_Metadata_Manager::get_instance();
}
add_action( 'ascendoor_metadata_manager_plugin_init', 'ascendoor_metadata_manager_plugin_init' );


/**
 * Init and run the plugin.
 */
do_action( 'ascendoor_metadata_manager_plugin_init' );
