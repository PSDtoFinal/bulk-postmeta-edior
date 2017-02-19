<?php
/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Bulk_Postmeta_Edior
 * @subpackage Bulk_Postmeta_Edior/includes
 * @author     PSD to Final <info@psdtofinal.com>
 */
class Bulk_Postmeta_Edior_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'bulk-postmeta-edior',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
