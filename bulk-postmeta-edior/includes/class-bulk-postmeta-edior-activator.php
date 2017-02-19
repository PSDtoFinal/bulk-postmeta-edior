<?php
/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Bulk_Postmeta_Edior
 * @subpackage Bulk_Postmeta_Edior/includes
 * @author     PSD to Final <info@psdtofinal.com>
 */
class Bulk_Postmeta_Edior_Activator {

	/**
	 * Creates the database table required to run
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		global $wpdb;
		
		$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}bulk_edit` (
					`ID` INT(32) UNSIGNED NOT NULL AUTO_INCREMENT,
					`meta_key` VARCHAR(255) NOT NULL COLLATE 'utf8_general_ci',
					`meta_label` VARCHAR(255) NOT NULL COLLATE 'utf8_general_ci',
					`post_types` VARCHAR(255) NOT NULL COLLATE 'utf8_general_ci',
					`field_type` VARCHAR(50) NOT NULL COLLATE 'utf8_general_ci',
					`field_values` TEXT NULL COLLATE 'utf8_general_ci',
					PRIMARY KEY (`ID`),
					UNIQUE INDEX `meta_key` (`meta_key`)
				)
				ENGINE=InnoDB
				AUTO_INCREMENT=1";	
		$wpdb->query($sql);
	}
}
