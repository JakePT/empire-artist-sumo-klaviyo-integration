<?php
/**
 * QRReferralLink class definition.
 *
 * @package Klaviyo for SUMO.
 */
namespace EmpireArtist\KlaviyoSumo\Subscribers;

defined( 'ABSPATH' ) || exit;

use QRcode;
use WP_User;
use EmpireArtist\KlaviyoSumo\Interfaces\HasActions;
use EmpireArtist\KlaviyoSumo\Interfaces\HasFilters;
use function EmpireArtist\KlaviyoSumo\get_plugin_file_path;
use function EmpireArtist\KlaviyoSumo\get_referral_link;
use function EmpireArtist\KlaviyoSumo\get_referral_qr_url;

/**
 * Subscribe to WordPress and WooCommerce hooks to provide membership tracking
 * functionality.
 */
class QRReferralLink implements HasActions, HasFilters {
	/**
	 * Constructor.
	 *
	 * @param Klaviyo $klaviyo Klaviyo API client instance.
	 *
	 * @return void
	 */
	function __construct() {
		if ( ! class_exists( 'QRcode' ) ) {
			require_once get_plugin_file_path( 'lib/phpqrcode/qrlib.php' );
		}
	}

	/**
	 * @see HasActions
	 */
	public function get_actions() {
		return [
			'init'              => [ 'add_rewrite_rule' ],
			'woocommerce_init'  => [ 'hook_display_qr_code' ],
			'template_redirect' => [ 'return_qr_code' ],
		];
	}

	/**
	 * @see HasFilters
	 */
	public function get_filters() {
		return [
			'query_vars' => [ 'add_query_var' ],
		];
	}

	/**
	 * Create rewrite rule for QR code.
	 *
	 * @return void
	 */
	public function add_rewrite_rule() {
		add_rewrite_rule( 'qr/(.{1,16})[/]?$', 'index.php?empire_artist_qr=$matches[1]', 'top' );
	}


	/**
	 * Add query var for QR code.
	 *
	 * @return void
	 */
	public function add_query_var( array $query_vars ) {
		$query_vars[] = 'empire_artist_qr';

		return $query_vars;
	}

	/**
	 * Track user registration.
	 *
	 * @param int $user_id User ID.
	 *
	 * @return void
	 */
	public function return_qr_code() {
		global $wp_query;

		$username = get_query_var( 'empire_artist_qr', false );

		if ( $username === false ) {
			return;
		}

		$user = get_user_by( 'login', urldecode( $username ) );

		if ( ! $user ) {
			status_header( 404 );
			$wp_query->set_404();

			return;
		}

		$url = get_referral_link( $user );

		if ( ! $url ) {
			status_header( 404 );
			$wp_query->set_404();

			return;
		}

		header( 'Pragma: public' );
		header( 'Cache-Control: max-age=86400' );
		header( 'Expires: ' . gmdate( 'D, d M Y H:i:s \G\M\T', time() + 86400 ) );

		QRcode::png( $url, false, QR_ECLEVEL_L, 16 );

		exit;
	}

	/**
	 * Hook QR code display based on plugin settings.
	 *
	 * @return void
	 */
	public function hook_display_qr_code() {
		$hook = ( '2' === get_option( 'rs_display_generate_referral' ) ) ? 'woocommerce_after_my_account' : 'woocommerce_before_my_account';

		add_action( $hook, [ $this, 'display_qr_code' ], 11 );
	}

	/**
	 * Display QR code on account page.
	 *
	 * Re-implements conditions used for displaying the static referral link
	 * table, so that if it is not displayed, the QR code is not displayed.
	 *
	 * @return void
	 */
	public function display_qr_code() {
		$user = wp_get_current_user();

		if ( 'yes' !== get_option( 'rs_reward_content' ) ) {
			return;
		}

		if ( '2' === get_option( 'rs_show_hide_generate_referral' ) ) {
			return;
		}

		if ( in_array( check_banning_type( $user->ID ), [ 'earningonly', 'both' ] ) ) {
			return;
		}

		if ( ! check_if_referral_is_restricted() ) {
			return;
		}

		if ( ! check_if_referral_is_restricted_based_on_history() ) {
			return;
		}

		if ( ! check_referral_count_if_exist( $user->ID ) ) {
			return;
		}

		printf(
			'<img src="%s" alt="QR code for your referral link." width="128" height="128">',
			esc_url( get_referral_qr_url( $user ) )
		);
	}
}