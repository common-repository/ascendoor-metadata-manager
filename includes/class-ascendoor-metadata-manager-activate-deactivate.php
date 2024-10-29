<?php
/**
 * Plugin activate/deactivate definition.
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
 * Class Ascendoor_Metadata_Manager_Activate_Deactivate.
 *
 * @since 1.0.0
 */
class Ascendoor_Metadata_Manager_Activate_Deactivate {

	/**
	 * Plugin activated.
	 *
	 * @since 1.0.0
	 */
	public static function activate() {

		if ( function_exists( 'phpversion' ) ) {
			if ( ! version_compare( phpversion(), '7.0', '>=' ) ) {
				deactivate_plugins( plugin_basename( ASCENDOOR_METADATA_MANAGER_DIR_PATH . 'ascendoor-metadata-manager.php' ) );
				wp_die(
					/* translators: %s - PHP Version. */
					sprintf( esc_html__( 'Ascendoor Metadata Manager plugin requires PHP version %s or higher.', 'ascendoor-metadata-manager' ), '7.0' ),
					'Plugin Activation PHP Version Error',
					array(
						'response'  => 200,
						'back_link' => true,
					)
				);
			}
		}

		global $wp_version;
		if ( ! version_compare( $wp_version, '5.0', '>=' ) ) {
			deactivate_plugins( plugin_basename( ASCENDOOR_METADATA_MANAGER_DIR_PATH . 'ascendoor-metadata-manager.php' ) );
			wp_die(
				/* translators: %s - WordPress Version. */
				sprintf( esc_html__( 'Ascendoor Metadata Manager plugin requires WordPress version %s or higher.', 'ascendoor-metadata-manager' ), '5.0' ),
				'Plugin Activation WordPress Version Error',
				array(
					'response'  => 200,
					'back_link' => true,
				)
			);
		}

	}

	/**
	 * Plugin deactivated.
	 *
	 * @since 1.0.0
	 */
	public static function deactivate() {

	}

}
