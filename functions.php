<?php
/**
 * General purpose functions.
 *
 * @package EmpireArtist\KlaviyoSumo
 */
namespace EmpireArtist\KlaviyoSumo;

defined( 'ABSPATH' ) || exit;

use WP_User;

/**
 * Get URL to a plugin file.
 *
 * @return string The URL to a file in the plugin.
 */
function get_plugin_file_uri( $file = '' ) {
	return plugin_dir_url( __FILE__ ) . ltrim( $file, '/' );
}

/**
 * Get path to a plugin file.
 *
 * @return string The full path to a file in the plugin.
 */
function get_plugin_file_path( $file = '' ) {
	return plugin_dir_path( __FILE__ ) . ltrim( $file, '/' );
}

/**
 * Get a user's referral link, using logic copied from the SUMO Reward Points
 * plugin.
 *
 * @param WP_User $user User object.
 *
 * @return string User's referral link URL.
 */
function get_referral_link( WP_User $user ) {
	$base_url = get_option( 'rs_static_generate_link' );

	if ( ! $base_url ) {
		return null;
	}

	$refer_by_username = ( '1' === get_option( 'rs_generate_referral_link_based_on_user' ) );
	$ip_restricted     = ( 'yes' === get_option( 'rs_restrict_referral_points_for_same_ip' ) );

	$query_args = [
		'ref' => $refer_by_username ? $user->user_login : $user->ID,
	];

	if ( $ip_restricted ) {
		$query_args['ip']  = base64_encode( get_referrer_ip_address() );
	}

	return add_query_arg( $query_args, $base_url ) ;
}

/**
 * Get referral QR URL.
 *
 * @param WP_User $user User object.
 *
 * @return string URL to user's referral link QR coe.
 */
function get_referral_qr_url( WP_User $user ) {
	$path = sprintf( '/qr/%s', urlencode( $user->user_login ) );

	return home_url( $path );
}