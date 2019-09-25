<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              MadelineMilagros.com
 * @since             1.0.0
 * @package           Brewlalatesting
 *
 * @wordpress-plugin
 * Plugin Name:       brewlalatesting
 * Plugin URI:        madelinemilagros.com
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Madeline Milagros
 * Author URI:        MadelineMilagros.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       brewlalatesting
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'BREWLALATESTING_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-brewlalatesting-activator.php
 */
function activate_brewlalatesting() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-brewlalatesting-activator.php';
	Brewlalatesting_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-brewlalatesting-deactivator.php
 */
function deactivate_brewlalatesting() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-brewlalatesting-deactivator.php';
	Brewlalatesting_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_brewlalatesting' );
register_deactivation_hook( __FILE__, 'deactivate_brewlalatesting' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-brewlalatesting.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_brewlalatesting() {

	$plugin = new Brewlalatesting();
	$plugin->run();

}

function register_brewery_cpt() {
  register_post_type( 'brewery', array(
    'label' => 'Breweries',
    'public' => true,
    'capability_type' => 'post',
  ));
}


add_action( 'init', 'register_brewery_cpt' );
add_action( 'wp_ajax_nopriv_get_breweries_from_api', 'get_breweries_from_api' );
add_action( 'wp_ajax_get_breweries_from_api', 'get_breweries_from_api' );

function get_breweries_from_api() {
  $current_page = ( ! empty( $_POST['current_page'] ) ) ? $_POST['current_page'] : 1;
  $breweries = [];

  // Should return an array of objects
  $results = wp_remote_retrieve_body( wp_remote_get('https://api.openbrewerydb.org/breweries/?page=' . $current_page . '&per_page=50') );
  // turn it into a PHP array from JSON string
  $results = json_decode( $results );   
  
  // Either the API is down or something else spooky happened. Just be done.
  if( ! is_array( $results ) || empty( $results ) ){
    return false;
  }

  $breweries[] = $results;
  
  foreach( $breweries[0] as $brewery ){
    
    $brewery_slug = sanitize_title($brewery->name . '-' . $brewery_id);
    $existing_brewery = get_page_by_path( $brewery_slug, 'OBJECT', 'brewery' );

  	 if( $existing_brewery === null  ){
      
      $inserted_brewery = wp_insert_post( [
        'post_name' => $brewery_slug,
        'post_title' => $brewery_slug,
        'post_type' => 'brewery',
        'post_status' => 'publish'
      ] );

      if( is_wp_error( $inserted_brewery ) || $inserted_brewery === 0 ) {
     
        continue;
      }
      // add meta fields
      $fillable = [
        'field_5d8b2b7b573d7' => 'name',
        'field_5d8b2b81573d8' => 'brewery_type',
        'field_5d8b2b90573d9' => 'street',
        'field_5d8b2b99573da' => 'city',
        'field_5d8b2ba0573db' => 'state',
        'field_5d8b2ba8573dc' => 'postal_code',
        'field_5d8b2bb2573dd' => 'country',
        'field_5d8b2bbb573de' => 'longitude',
        'field_5d8b2bc3573df' => 'latitude',
        'field_5d8b2bd2573e0' => 'phone',
        'field_5d8b2bda573e1' => 'website',
        'field_5d8b2be7573e2' => 'updated_at',
      ];

      foreach( $fillable as $key => $name ) {
        update_field( $key, $brewery->$name, $inserted_brewery );
      }

       } else {
      
      $existing_brewery_id = $existing_brewery->ID;
      $exisiting_brewerey_timestamp = get_field('updated_at', $existing_brewery_id);
      if( $brewery->updated_at >= $exisiting_brewerey_timestamp ){
        $fillable = [
		'field_5d8b2b7b573d7' => 'name',
        'field_5d8b2b81573d8' => 'brewery_type',
        'field_5d8b2b90573d9' => 'street',
        'field_5d8b2b99573da' => 'city',
        'field_5d8b2ba0573db' => 'state',
        'field_5d8b2ba8573dc' => 'postal_code',
        'field_5d8b2bb2573dd' => 'country',
        'field_5d8b2bbb573de' => 'longitude',
        'field_5d8b2bc3573df' => 'latitude',
        'field_5d8b2bd2573e0' => 'phone',
        'field_5d8b2bda573e1' => 'website',
        'field_5d8b2be7573e2' => 'updated_at',
        ];
        foreach( $fillable as $key => $name ){
          update_field( $name, $brewery->$name, $existing_brewery_id);
        }
      }
    }
  }
  
 $current_page = $current_page + 1;
  wp_remote_post( admin_url('admin-ajax.php?action=get_breweries_from_api'), [
    'blocking' => false,
    'sslverify' => false, // we are sending this to ourselves, so trust it.
    'body' => [
      'current_page' => $current_page
    ]
  ] );
}


run_brewlalatesting();
