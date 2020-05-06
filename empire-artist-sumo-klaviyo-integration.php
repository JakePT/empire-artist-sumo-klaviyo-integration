<?php
/**
 * Plugin bootstrap file.
 *
 * @package EmpireArtist\KlaviyoSumo
 *
 * Plugin Name: Klaviyo Integration for SUMO
 * Description: Klaviyo integration for SUMO Reward Points and SUMO Memberships for Empire Artist.
 * Author: Jacob Peattie
 * Contributors: JakePT
 * Text Domain: empire-artist-klaviyo-sumo
 */
namespace EmpireArtist\KlaviyoSumo;

defined( 'ABSPATH' ) || exit;

require_once 'vendor/autoload.php';
require_once 'functions.php';

$klaviyo_sumo = Factories\PluginFactory::create();

register_activation_hook( __FILE__, [ $klaviyo_sumo, 'activate' ] );
register_deactivation_hook( __FILE__, [ $klaviyo_sumo, 'deactivate' ] );

$klaviyo_sumo->run();