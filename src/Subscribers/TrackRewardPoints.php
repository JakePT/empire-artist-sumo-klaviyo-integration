<?php
/**
 * TrackRewardPoints class definition.
 *
 * @package Klaviyo for SUMO.
 */
namespace EmpireArtist\KlaviyoSumo\Subscribers;

defined( 'ABSPATH' ) || exit;

use RS_Points_Data;
use WP_User;
use EmpireArtist\KlaviyoSumo\Interfaces\HasActions;
use EmpireArtist\KlaviyoSumo\Clients\Klaviyo;

/**
 * Subscribe to WordPress and WooCommerce hooks to provide reward point tracking
 * functionality.
 */
class TrackRewardPoints implements HasActions {
	/**
	 * Klaviyo API Client.
	 * @var Klaviyo
	 */
	protected $klaviyo;

	/**
	 * Constructor.
	 *
	 * @param Klaviyo $klaviyo Klaviyo API client instance.
	 *
	 * @return void
	 */
	function __construct( Klaviyo $klaviyo ) {
		$this->klaviyo = $klaviyo;
	}

	/**
	 * @see HasActions
	 */
	public function get_actions() {
		return [
			'fp_redeem_reward_points_manually'                => [ 'track_points_redeemed', 10, 2 ],
			'fp_redeem_reward_points_automatically'           => [ 'track_points_redeemed', 10, 2 ],
			'fp_redeem_reward_points_using_rewardgateway'     => [ 'track_points_redeemed', 10, 2 ],
			'fp_reward_point_for_buying_sumo_reward_points'   => [ 'track_points_purchased', 10, 2 ],
			'fp_reward_point_for_using_coupons'               => [ 'track_points_earned' ],
			'fp_reward_point_for_product_review'              => [ 'track_points_earned' ],
			'fp_reward_point_for_using_gateways'              => [ 'track_points_earned' ],
			'fp_reward_point_for_product_purchase'            => [ 'track_points_earned' ],
			'fp_reward_point_for_using_gift_voucher'          => [ 'track_points_earned' ],
			'fp_reward_point_for_facebook_like'               => [ 'track_points_earned' ],
			'fp_reward_point_for_facebook_share'              => [ 'track_points_earned' ],
			'fp_reward_point_for_tweet'                       => [ 'track_points_earned' ],
			'fp_reward_point_for_twitter_follow'              => [ 'track_points_earned' ],
			'fp_reward_point_for_instagram_follow'            => [ 'track_points_earned' ],
			'fp_reward_point_for_vk_like'                     => [ 'track_points_earned' ],
			'fp_reward_point_for_okru_share'                  => [ 'track_points_earned' ],
			'fp_reward_point_for_registration'                => [ 'track_points_earned' ],
			'fp_reward_point_for_login'                       => [ 'track_points_earned' ],
			'fp_signup_points_for_referrer'                   => [ 'track_points_for_referral', 10, 3 ],
			'fp_signup_points_for_getting_referred'           => [ 'track_points_for_getting_referred', 10, 3 ],
			'fp_product_purchase_points_for_referrer'         => [ 'track_points_for_referral', 10, 3 ],
			'fp_product_purchase_points_for_getting_referred' => [ 'track_points_for_getting_referred', 10, 3 ],
			'wp_login'                                        => [ 'identify_points_on_wp_login', 10, 2 ],
			'sumomemberships_plan_status_changed'             => [ 'identify_points_on_membership_status_change', 10, 2 ],
			'woocommerce_save_account_details'                => [ 'identify_points_on_save_account_details', 10, 1 ],
			'woocommerce_thankyou'                            => [ 'identify_points_on_order', 999, 1 ],
		];
	}

	/**
	 * Track points redeemed for order.
	 *
	 * @param int $order_id        Order ID.
	 * @param int $points_redeemed Number of points redeemed.
	 *
	 * @return void
	 */
	public function track_points_redeemed( $order_id, $points_redeemed ) {
		$order = wc_get_order( $order_id );

		$this->track_points(
			__( 'Reward Points Redeemed', 'klaviyo-sumo' ),
			$order->get_customer_id(),
			[
				__( 'Points Redeemed', 'klaviyo-sumo' ) => $points_redeemed,
				__( 'Order ID', 'klaviyo-sumo' )        => $order_id,
			]
		);
	}

	/**
	 * Track points awarded for purchasing products with "Enable Buying of SUMO
	 * Reward Points" enabled.
	 *
	 * The relevant hook does not pass the ID of the user who performed this
	 * action, so if an administrator performs the action on behalf of a customer,
	 * the administrator's point balance will be tracked.
	 *
	 * @param int $product_id    Product ID
	 * @param int $points_earned Number of points earned.
	 *
	 * @return void
	 */
	public function track_points_purchased( $product_id, $points_earned ) {
		$this->track_points(
			__( 'Reward Points Purchased', 'klaviyo-sumo' ),
			null,
			[
				__( 'Points Earned', 'klaviyo-sumo' ) => $points_earned,
				__( 'Product ID', 'klaviyo-sumo' )    => $product_id,
			]
		);
	}

	/**
	 * Track reward points being earned. Pass an event property for the reason
	 * that the points were earned, based on which hook was fired.
	 *
	 * All hooks fired when points are earned do not pass the number of points
	 * earned, so this is not tracked.
	 *
	 * @return void
	 */
	public function track_reward_points_earned() {
		/**
		 * Determine reason points were earned based on the current hook.
		 */
		$hook = current_action();

		switch ( $hook ) {
			/**
			 * Coupon Use.
			 */
			case 'fp_reward_point_for_using_coupons':
				$earned_for = __( 'Coupon Use', 'klaviyo-sumo' );
				break;
			/**
			 * Product Review.
			 */
			case 'fp_reward_point_for_product_review':
				$earned_for = __( 'Product Review', 'klaviyo-sumo' );
				break;
			/**
			 * Payment Gateway Use.
			 */
			case 'fp_reward_point_for_using_gateways':
				$earned_for = __( 'Payment Gateway Use', 'klaviyo-sumo' );
				break;
			/**
			 * Product Purchase.
			 */
			case 'fp_reward_point_for_product_purchase':
				$earned_for = __( 'Product Purchase', 'klaviyo-sumo' );
				break;
			/**
			 * Gift Voucher Use.
			 */
			case 'fp_reward_point_for_using_gift_voucher':
				$earned_for = __( 'Gift Voucher Use', 'klaviyo-sumo' );
				break;
			/**
			 * Facebook Like.
			 */
			case 'fp_reward_point_for_facebook_like':
				$earned_for = __( 'Facebook Like', 'klaviyo-sumo' );
				break;
			/**
			 * Facebook Share.
			 */
			case 'fp_reward_point_for_facebook_share':
				$earned_for = __( 'Facebook Share', 'klaviyo-sumo' );
				break;
			/**
			 * Tweet.
			 */
			case 'fp_reward_point_for_tweet':
				$earned_for = __( 'Tweet', 'klaviyo-sumo' );
				break;
			/**
			 * Twitter Follow.
			 */
			case 'fp_reward_point_for_twitter_follow':
				$earned_for = __( 'Twitter Follow', 'klaviyo-sumo' );
				break;
			/**
			 * Instagram Follow.
			 */
			case 'fp_reward_point_for_instagram_follow':
				$earned_for = __( 'Instagram Follow', 'klaviyo-sumo' );
				break;
			/**
			 * VK Like.
			 */
			case 'fp_reward_point_for_vk_like':
				$earned_for = __( 'VK Like', 'klaviyo-sumo' );
				break;
			/**
			 * OK.RU Share.
			 */
			case 'fp_reward_point_for_okru_share':
				$earned_for = __( 'OK.RU Share', 'klaviyo-sumo' );
				break;
			/**
			 * Registration.
			 *
			 * This event cannot be tracked, as it runs before the user has been
			 * logged in, and the hook does not provide a user ID, meaning we don't
			 * have a customer we can track the event for.
			 */
			case 'fp_reward_point_for_registration':
				return;
			/**
			 * Login.
			 *
			 * This event cannot be tracked, as it the actual hook runs on every page
			 * load, which is too often to make API requests, and it doesn't provide any
			 * useful information about the number of points earned.
			 *
			 * Instead we will separately track the user's points balance using the
			 * Identify API on the actual login hook.
			 */
			case 'fp_reward_point_for_login':
				return;
		}

		/**
		 * Send tracking request.
		 */
		$this->track_points(
			__( 'Reward Points Earned', 'klaviyo-sumo' ),
			null,
			[
				__( 'Earned For', 'klaviyo-sumo' ) => $earned_for,
			]
		);
	}

	/**
	 * Track reward points earned for referring a user.
	 *
	 * @param int $referrer_user_id ID of the referring user.
	 * @param int $user_id          ID of the user being referred.
	 * @param int $points_earned    Number of points earned.
	 *
	 * @return void
	 */
	public function track_points_for_referral( $referrer_user_id, $referred_user_id, $points_earned ) {
		$referred_user = get_userdata( $referred_user_id );

		/**
		 * Track points for user being referred.
		 */
		$this->track_points(
			__( 'Reward Points Earned', 'klaviyo-sumo' ),
			$referrer_user_id,
			[
				__( 'Earned For', 'klaviyo-sumo' )    => __( 'Referral ', 'klaviyo-sumo' ),
				__( 'Referred', 'klaviyo-sumo' )      => $referred_user->user_email,
				__( 'Points Earned', 'klaviyo-sumo' ) => $points_earned,
			]
		);
	}

	/**
	 * Track reward points earned for being referred.
	 *
	 * @param int $referrer_user_id ID of the referring user.
	 * @param int $user_id          ID of the user being referred.
	 * @param int $points_earned    Number of points earned.
	 *
	 * @return void
	 */
	public function track_points_for_getting_referred( $referrer_user_id, $referred_user_id, $points_earned ) {
		$referrer_user = get_userdata( $referrer_user_id );

		/**
		 * Track points for user being referred.
		 */
		$this->track_points(
			__( 'Reward Points Earned', 'klaviyo-sumo' ),
			$referred_user_id,
			[
				__( 'Earned For', 'klaviyo-sumo' )    => __( 'Being Referred', 'klaviyo-sumo' ),
				__( 'Referred By', 'klaviyo-sumo' )   => $referrer_user->user_email,
				__( 'Points Earned', 'klaviyo-sumo' ) => $points_earned,
			]
		);
	}

	/**
	 * Update rewards points balance when a user logs in.
	 *
	 * @param string  $user_login User's username.
	 * @param WP_User $user       User data.
	 *
	 * @return void
	 */
	public function identify_points_on_wp_login( $user_login, $user ) {
		$this->identify_points( $user->ID );
	}

	/**
	 * Update user's rewards points balance when a their membership status changes.
	 *
	 * @param int    $membership_id Membership post ID.
	 * @param int    $plan_id       Membership Plan post ID.
	 * @param string $status        New status for the membership.
	 *
	 * @return void
	 */
	public function identify_points_on_membership_status_change( $membership_id, $plan_id, $new_status ) {
		$user_id = get_post_meta( $membership_id, 'sumomemberships_userid', true );

		$this->identify_points( $user_id );
	}

	/**
	 * Update user's rewards points balance when their account details are saved.
	 *
	 * @param int $user_id User ID.
	 *
	 * @return void
	 */
	public function identify_points_on_save_account_details( $user_id ) {
		$this->identify_points( $user_id );
	}

	/**
	 * Update customer's rewards points balance when an order is placed.
	 *
	 * @param int $order_id Order ID.
	 *
	 * @return void
	 */
	public function identify_points_on_order( $order_id ) {
		$order = wc_get_order( $order_id );

		$this->identify_points( $order->get_customer_id() );
	}

	/**
	 * Track the points balances for a user.
	 *
	 * @param string $event   The reason the points balance is being updated.
	 * @param int    $user_id The user who's points balance to track.
	 *
	 * @return void
	 */
	protected function track_points( $event_name, $user_id = null, $event_properties = [] ) {
		$customer_properties = $this->get_customer_properties( $user_id );

		if ( ! $customer_properties ) {
			return;
		}

		/**
		 * Send tracking request.
		 */
		$this->klaviyo->track(
			$event_name,
			$customer_properties,
			$event_properties
		);
	}

	/**
	 * Identify points balance for a given user.
	 */
	protected function identify_points( $user_id = null ) {
		$customer_properties = $this->get_customer_properties( $user_id );

		if ( ! $customer_properties ) {
			return;
		}

		/**
		 * Send tracking request.
		 */
		$this->klaviyo->identify( $customer_properties );
	}

	/**
	 * Get an array of points balances for a given user.
	 *
	 * @param int $user_id User ID.
	 *
	 * @return array Array of points balances.
	 */
	protected function get_customer_properties( $user_id = null ) {
		/**
		 * Get the user object for the given user, or the current user if no
		 * ID is provided.
		 */
		$user = $user_id ? get_userdata( $user_id ) : wp_get_current_user();

		/**
		 * If the user does not exist, or does not have an email, we cannot track
		 * anything for them.
		 */
		if ( ! $user || ! $user->user_email ) {
			return false;
		}

		/**
		 * Get points data.
		 */
		$points_data = new RS_Points_Data( $user_id );

		/**
		 * Return an array of customer properties to track.
		 */
		return [
			'$email'               => $user->user_email,
			'Points Balance'       => $points_data->total_available_points(),
			'Points Earned'        => $points_data->total_earned_points(),
			'Points Redeemed'      => $points_data->total_redeemed_points(),
			'Points Expired'       => $points_data->total_expired_points(),
			'Referral Link'        => $this->get_referral_link( $user ),
		];
	}

	/**
	 * Get a customer's referral link.
	 *
	 * Method based on code in SUMO Reward Points 24.3.
	 *
	 * @param WP_User $user User object.
	 */
	protected function get_referral_link( WP_User $user ) {
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
}