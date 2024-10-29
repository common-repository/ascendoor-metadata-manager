<?php
/**
 * Plugin language definition.
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
 * Class Ascendoor_Metadata_Manager_I18n.
 *
 * @since 1.0.0
 */
class Ascendoor_Metadata_Manager_I18n {

	/**
	 * Ascendoor_Metadata_Manager_I18n class constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string $plugin_name Name of the plugin.
	 * @param string $plugin_version Version of the pluign.
	 */
	public function __construct( $plugin_name, $plugin_version ) {
		add_action( 'init', array( $this, 'load_language' ) );
	}

	/**
	 * Load plugin language files.
	 *
	 * @since 1.0.0
	 */
	public function load_language() {
		load_plugin_textdomain(
			'ascendoor-metadata-manager',
			false,
			ASCENDOOR_METADATA_MANAGER_FOLDER_NAME . '/languages/'
		);
	}

}
