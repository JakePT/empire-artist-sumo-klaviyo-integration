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

require_once 'vendor/autoload.php';
require_once 'functions.php';

$plugin_name = Factories\PluginFactory::create();

register_activation_hook( __FILE__, [ $plugin_name, 'activate' ] );
register_deactivation_hook( __FILE__, [ $plugin_name, 'deactivate' ] );

$plugin_name->run();