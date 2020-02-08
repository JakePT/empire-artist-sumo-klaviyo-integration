<?php
/**
 * Plugin bootstrap file.
 *
 * @package PluginName
 *
 * Plugin Name: Plugin Name
 * Description: Plugin description.
 * Author: Jacob Peattie
 * Contributors: JakePT
 * Text Domain: plugin-name
 */
namespace PluginName;

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/autoload.php';
require_once __DIR__ . '/functions.php';

/**
 * Run the plugin.
 */
$plugin_name = PluginFactory::create();

register_activation_hook( __FILE__, [ $plugin_name, 'activate' ] );
register_deactivation_hook( __FILE__, [ $plugin_name, 'deactivate' ] );

$plugin_name->run();