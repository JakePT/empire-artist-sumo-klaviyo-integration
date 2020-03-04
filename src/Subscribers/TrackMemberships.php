<?php
/**
 * TrackMemberships class definition.
 *
 * @package Klaviyo for SUMO.
 */
namespace EmpireArtist\KlaviyoSumo\Subscribers;

defined( 'ABSPATH' ) || exit;

use WP_User;
use EmpireArtist\KlaviyoSumo\Interfaces\HasActions;
use EmpireArtist\KlaviyoSumo\Clients\Klaviyo;

/**
 * Subscribe to WordPress and WooCommerce hooks to provide membership tracking
 * functionality.
 */
class TrackMemberships implements HasActions {
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
			'sumomemberships_plan_status_changed'            => [ 'track_membership_status_change', 10, 3 ],
			'sumomemberships_add_new_plan_upon_order_status' => [ 'track_new_membership_from_order', 10, 4 ],
			'sumomemberships_manual_plan_updation'           => [ 'track_membership_plan_update', 10, 4 ],
		];
	}

	/**
	 * Track membership status change.
	 *
	 * If there's no user available, we cannot track the event. This happens
	 * when a membership is created for the first time from purchasing a
	 * product, because it runs the status change hook before setting the user ID.
	 * This will be tracked with the sumomemberships_add_new_plan_upon_order_status
	 * hook, which runs after, instead.
	 *
	 * @param int    $membership_id Membership post ID.
	 * @param int    $plan_id       Membership Plan post ID.
	 * @param string $status        New status for the membership.
	 *
	 * @return void
	 */
	public function track_membership_status_change( $membership_id, $plan_id, $new_status ) {
		/**
		 * Prepare properties for tracking request.
		 */
		$email = $this->get_membership_user_email( $membership_id );

		if ( ! $email ) {
			return;
		}

		$unique_key      = sumo_get_plan_key( $membership_id, $plan_id );
		$plan_slug       = $this->get_membership_plan_slug( $plan_id );
		$previous_status = $this->get_membership_status( $membership_id, $unique_key );

		/**
		 * Send tracking request.
		 */
		$this->klaviyo->track(
			__( 'Membership Status Changed', 'klaviyo-sumo' ),
			[
				'$email'   => $email,
				$plan_slug => $new_status,
			],
			[
				'Plan'            => $plan_slug,
				'Previous Status' => $previous_status,
				'New Status'      => $new_status,
			]
		);
	}

	/**
	 * Track membership plan being granted for a purchase.
	 *
	 * @param array  $member_plans   New membership plans added to the user.
	 * @param int    $plan_id        Membership Plan post ID.
	 * @param string $unique_key     Unique key for the membership for this plan.
	 * @param int    $membership_id  Membership post ID.
	 *
	 * @return void
	 */
	public function track_new_membership_from_order( $new_member_plans, $plan_id, $unique_key, $membership_id ) {
		/**
		 * Prepare properties for tracking request.
		 */
		$email              = $this->get_membership_user_email( $membership_id );
		$plan_slug          = $this->get_membership_plan_slug( $plan_id );
		$membership_status  = $this->get_membership_status( $membership_id, $unique_key, $new_member_plans );

		/**
		 * Send tracking request.
		 */
		$this->klaviyo->track(
			__( 'New Membership From Order', 'klaviyo-sumo' ),
			[
				'$email'   => $email,
				$plan_slug => $membership_status,
			],
			[
				'Plan' => $plan_slug,
			]
		);
	}

	/**
	 * Track membership plan being manually updated.
	 *
	 * @param int    $previous_plan_id Previous Membership Plan post ID.
	 * @param int    $new_plan_id      New Membership Plan post ID.
	 * @param int    $membership_id    Membership post ID.
	 * @param string $unique_key       Unique key for the membership for this plan.
	 *
	 * @return void
	 */
	public function track_membership_plan_update( $previous_plan_id, $new_plan_id, $membership_id, $unique_key ) {
		/**
		 * Prepare properties for tracking request.
		 */
		$email              = $this->get_membership_user_email( $membership_id );
		$previous_plan_slug = $this->get_membership_plan_slug( $previous_plan_id );
		$new_plan_slug      = $this->get_membership_plan_slug( $new_plan_id );
		$membership_status  = $this->get_membership_status( $membership_id, $unique_key );

		/**
		 * Send tracking request.
		 */
		$this->klaviyo->track(
			__( 'Membership Plan Changed', 'klaviyo-sumo' ),
			[
				'$email'            => $email,
				$new_plan_slug      => $membership_status,
				$previous_plan_slug => 'cancelled',
			],
			$event_properties = [
				'Previous Plan' => $previous_plan_slug,
				'New Plan'      => $new_plan_slug,
			]
		);
	}

	/**
	 * Return the user email for a given membership.
	 *
	 * @param int $membership_id ID of the membership.
	 *
	 * @return string|bool The user's email address, if found, or false otherwise.
	 */
	protected function get_membership_user_email( $membership_id ) {
		$user_id = get_post_meta( $membership_id, 'sumomemberships_userid', true );
		$user    = get_userdata( $user_id );

		if ( ! $user ) {
			return false;
		}

		return $user->user_email;
	}

	/**
	 * Return the slug for a given membership plan.
	 *
	 * @param int $plan_id Membership plan ID.
	 *
	 * @return string Membership plan slug, or false if none found.
	 */
	protected function get_membership_plan_slug( $plan_id ) {
		return get_post_meta( $plan_id, 'sumomemberships_plan_slug', true );
	}

	/**
	 * Get the status for a given membership.
	 *
	 * @param int    $membership_id    Membership ID.
	 * @param string $unique_key       Membership plan key.
	 * @param array  $membership_plans Membership plans to check in, if not the currently saved plans.
	 *
	 * @return string The membership status, or an empty string, if none found.
	 */
	protected function get_membership_status( $membership_id, $unique_key, $membership_plans = null ) {
		if ( ! $membership_plans ) {
			$membership_plans = get_post_meta( $membership_id, 'sumomemberships_saved_plans' , true );
		}

		if ( ! isset( $membership_plans[$unique_key] ) ) {
			return '';
		}

		return $membership_plans[$unique_key]['choose_status'];
	}
}