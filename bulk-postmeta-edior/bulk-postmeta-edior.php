<?php
/**
 *
 * @link              https://profiles.wordpress.org/psdtofinal
 * @since             1.0.0
 * @package           Bulk_Postmeta_Edior
 *
 * @wordpress-plugin
 * Plugin Name:       Bulk Postmeta Editor
 * Plugin URI:        https://wordpress.org/plugins/bulk-postmeta-editor/
 * Description:       Allows you to bulk-edit postmeta inforamtion for all posts, in a single place
 * Version:           1.0.0
 * Author:            PSD to Final
 * Author URI:        https://www.psdtofinal.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       bulk-postmeta-edior
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Runs the activation / installation service
 * 
 * @since 1.0.0
 */
function activate_bulk_postmeta_edior() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-bulk-postmeta-edior-activator.php';
	Bulk_Postmeta_Edior_Activator::activate();
}
register_activation_hook( __FILE__, 'activate_bulk_postmeta_edior' );

/**
 * Adds a settings link to the plugin page
 * 
 * @since 1.0.0
 */
function bulk_postmeta_editor_settings_link($links) { 
  $settings_link = '<a href="admin.php?page=bulk_edit_admin_settings">Settings</a>'; 
  array_unshift($links, $settings_link); 
  return $links; 
}
add_filter("plugin_action_links", 'bulk_postmeta_editor_settings_link' );


/**
 * Runs the deactivation / uninstall service
 * 
 * @since 1.0.0
 */
function deactivate_bulk_postmeta_edior() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-bulk-postmeta-edior-deactivator.php';
	Bulk_Postmeta_Edior_Deactivator::deactivate();
}
register_deactivation_hook( __FILE__, 'deactivate_bulk_postmeta_edior' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-bulk-postmeta-edior.php';

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function run_bulk_postmeta_edior() {

	$plugin = new Bulk_Postmeta_Edior();
	$plugin->run();

}
run_bulk_postmeta_edior();
