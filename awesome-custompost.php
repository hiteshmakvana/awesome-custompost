<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://justin-vogt.com
 * @since             1.0.0
 * @package           Awesome_Custom_Post
 *
 * @wordpress-plugin
 * Plugin Name:       Awesome Custom Post
 * Description:       Awesome Custom Post WordPress Plugin to manage books custom post type.
 * Version:           1.0.0
 * Requires PHP:      8.0
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       awesome-custompost
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
use Awesome_Custom_Post\Activator;
use Awesome_Custom_Post\Deactivator;
use Awesome_Custom_Post\Awesome_Custom_Post;
use Awesome_Custom_Post\Uninstallor;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Plugin absolute path
 */
require plugin_dir_path( __FILE__ ) . 'constants.php';

/**
 * Use Composer PSR-4 Autoloading
 */
require plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
require plugin_dir_path( __FILE__ ) . 'vendor/vendor-prefixed/autoload.php';

/**
 * The code that runs during plugin activation.
 */
function activate_awesome_custompost(): void {
	Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_awesome_custompost(): void {
	Deactivator::deactivate();
}

/**
 * The code that runs during plugin uninstallation.
 */
function uninstall_awesome_custompost(): void {
	Uninstallor::uninstall();
}

register_activation_hook( __FILE__, 'activate_awesome_custompost' );
register_deactivation_hook( __FILE__, 'deactivate_awesome_custompost' );
register_uninstall_hook( __FILE__, 'uninstall_awesome_custompost' );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_awesome_custompost(): void {
	$plugin = new Awesome_Custom_Post();
	$plugin->run();
}
run_awesome_custompost();
