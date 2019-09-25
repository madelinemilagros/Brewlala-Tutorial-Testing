<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       MadelineMilagros.com
 * @since      1.0.0
 *
 * @package    Brewlalatesting
 * @subpackage Brewlalatesting/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Brewlalatesting
 * @subpackage Brewlalatesting/includes
 * @author     Madeline Milagros <madelinemmilagros@gmail.com>
 */
class Brewlalatesting_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'brewlalatesting',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
