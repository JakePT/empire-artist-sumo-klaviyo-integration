<?php
/**
 * This file is based on the SUMO Reward System plugin file
 * includes/frontend/views/class-rs-frontend-my-reward-table.php from
 * version 24.8 of that plugin.
 *
 * It is used in place of the original by loading it in a 'plugged' version
 * of the RSFunctionForMessage class for the purposed of better optimising its
 * presentation on mobile.
 *
 * @package Klaviyo for SUMO
 */

defined( 'ABSPATH' ) || exit;
?>
<form class="rs-my-reward-table-form" method ='POST'>
	<?php if ( '1' == get_option( 'rs_show_or_hide_date_filter' ) && $AvailablePoints ) { ?>
		<table class="rs-my-reward-date-filter">
			<tbody>
				<tr>
					<td class="rs-duration-type-label">
						<label><?php esc_html_e( 'Earned and Redeemed points during' , SRP_LOCALE ) ; ?></label>
					</td>
					<td>
						<select id="rs_duration_type" name="rs_duration_type">
							<option value="0" <?php isset( $_REQUEST[ 'rs_duration_type' ] ) ? selected( '0' == wc_clean( wp_unslash( $_REQUEST[ 'rs_duration_type' ] ) ) , true ) : '' ; ?>><?php esc_html_e( 'Choose Option' , SRP_LOCALE ) ?></option>
							<option value="1" <?php isset( $_REQUEST[ 'rs_duration_type' ] ) ? selected( '1' == wc_clean( wp_unslash( $_REQUEST[ 'rs_duration_type' ] ) ) , true ) : '' ; ?>><?php esc_html_e( 'Last 1 Month' , SRP_LOCALE ) ?></option>
							<option value="2" <?php isset( $_REQUEST[ 'rs_duration_type' ] ) ? selected( '2' == wc_clean( wp_unslash( $_REQUEST[ 'rs_duration_type' ] ) ) , true ) : '' ; ?>><?php esc_html_e( 'Last 3 Month' , SRP_LOCALE ) ; ?></option>
							<option value="3" <?php isset( $_REQUEST[ 'rs_duration_type' ] ) ? selected( '3' == wc_clean( wp_unslash( $_REQUEST[ 'rs_duration_type' ] ) ) , true ) : '' ; ?>><?php esc_html_e( 'Last 6 Month' , SRP_LOCALE ) ; ?></option>
							<option value="4" <?php isset( $_REQUEST[ 'rs_duration_type' ] ) ? selected( '4' == wc_clean( wp_unslash( $_REQUEST[ 'rs_duration_type' ] ) ) , true ) : '' ; ?>><?php esc_html_e( 'Last 12 Month' , SRP_LOCALE ) ; ?></option>
							<option value="5" <?php isset( $_REQUEST[ 'rs_duration_type' ] ) ? selected( '5' == wc_clean( wp_unslash( $_REQUEST[ 'rs_duration_type' ] ) ) , true ) : '' ; ?>><?php esc_html_e( 'Custom Duration' , SRP_LOCALE ) ; ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<td class="rs-from-date-label">
						<label><?php echo wp_kses_post( sprintf( 'From Date %s ' , '<span class="rs-mandatory-field">*</span>' ) , SRP_LOCALE ) ; ?></label>
					</td>
					<td>
						<input type="text"
							   id="rs_custom_from_date_field"
							   name="rs_custom_from_date_field"
							   class = "rs_custom_from_date_field"
							   value=""/>
					</td>
				</tr>
				<tr>
					<td class="rs-to-date-label">
						<label><?php echo wp_kses_post( sprintf( 'To Date %s' , '<span class="rs-mandatory-field">*</span>' ) , SRP_LOCALE ) ; ?></label>
					</td>
					<td>
						<input type="text"
							   id="rs_custom_to_date_field"
							   name="rs_custom_to_date_field"
							   class =""
							   value=""/>
					</td>
				</tr>
				<tr>
					<td/>
					<td>
						<button type='submit'
								id = 'rs_submit'
								name ='rs_submit'
								class='rs_submit' >
									<?php esc_html_e( 'Apply' , SRP_LOCALE ) ; ?>
						</button>
					</td>
				</tr>
				<?php
				if ( isset( $_REQUEST[ 'rs_duration_type' ] ) && '0' != $_REQUEST[ 'rs_duration_type' ] ) {
					?>
					<tr>
						<td class="rs-earned-points-label">
							<p>
								<label><b><?php esc_html_e( 'Earned Points:' , SRP_LOCALE ) ; ?></b></label>
								<span><?php echo esc_html( round_off_type( floatval( $selected_duration_earned_point ) ) ) ; ?></span>
							</p>
						</td>
						<td class="rs-redeemed-points-label">
							<p>
								<label><b><?php esc_html_e( 'Redeemed Points:' , SRP_LOCALE ) ; ?></b></label>
								<span><?php echo esc_html( round_off_type( floatval( $selected_duration_redeemed_point ) ) ) ; ?></span>
							</p>
						</td>
					</tr>
					<tr>
						<td colspan="2" style="text-align: right;">
							<?php
							global $post ;
							$url = isset( $post->ID ) ? get_permalink( $post->ID ) : get_permalink() ;
							?>
							<a href ="<?php echo esc_url( $url ) ; ?>"
							   class="rs-previous-link">
								   <?php esc_html_e( 'Go Back' , SRP_LOCALE ) ; ?>
							</a>
						</td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	<?php } ?>
	<table class = "my_reward_table demo shop_table my_account_orders table-bordered" data-filter = "#filters" data-page-size="5" data-page-previous-text = "prev" data-filter-text-only = "true" data-page-next-text = "next">
		<thead>
			<tr>
				<th><?php echo $TableData[ "label_earned_date" ]; ?></th>
				<th><?php echo $TableData[ "label_reward_for" ]; ?></th>
				<th>Change</th>
			</tr>
		</thead>
		<tbody>
			<?php
			if ( $TableData[ 'points_log_sort' ] == '1' )
				krsort( $UserLog , SORT_NUMERIC ) ;

			$i = 1 ;
			foreach ( $UserLog as $Log ) {
				if ( ! srp_check_is_array( $Log ) )
					continue ;

				$CheckPoint = $Log[ 'checkpoints' ] ;
				if ( isset( $Log[ 'earnedpoints' ] ) && ! empty( $Log[ 'checkpoints' ] ) ) {
					$Points         = empty( $Log[ 'earnedpoints' ] ) ? 0 : round_off_type( $Log[ 'earnedpoints' ] ) ;
					$RedeemedPoints = empty( $Log[ 'redeempoints' ] ) ? 0 : ((get_option( 'rs_enable_round_off_type_for_calculation' ) == 'yes') ? $Log[ 'redeempoints' ] : round_off_type( $Log[ 'redeempoints' ] )) ;
					$TotalPoints    = empty( $Log[ 'totalpoints' ] ) ? 0 : round_off_type( $Log[ 'totalpoints' ] ) ;
					$Username       = get_user_meta( $Log[ 'userid' ] , 'nickname' , true ) ;
					$RefUsername    = get_user_meta( $Log[ 'refuserid' ] , 'nickname' , true ) ;
					$NomineeName    = get_user_meta( $Log[ 'nomineeid' ] , 'nickname' , true ) ;
					$Reason         = RSPointExpiry::msg_for_log( false , true , true , $Log[ 'earnedpoints' ] , $CheckPoint , $Log[ 'productid' ] , $Log[ 'orderid' ] , $Log[ 'variationid' ] , $Log[ 'userid' ] , $RefUsername , $Log[ 'reasonindetail' ] , $Log[ 'redeempoints' ] , false , $NomineeName , $Username , $Log[ 'nomineepoints' ] ) ;
				} else {
					$Points         = empty( $Log[ 'points_earned_order' ] ) ? 0 : round_off_type( $Log[ 'points_earned_order' ] ) ;
					$RedeemedPoints = empty( $Log[ 'points_redeemed' ] ) ? 0 : ((get_option( 'rs_enable_round_off_type_for_calculation' ) == 'yes') ? $Log[ 'points_redeemed' ] : round_off_type( $Log[ 'points_redeemed' ] )) ;
					$TotalPoints    = empty( $Log[ 'totalpoints' ] ) ? 0 : round_off_type( $Log[ 'totalpoints' ] ) ;
					$Reason         = empty( $Log[ 'rewarder_for_frontend' ] ) ? 0 : $Log[ 'rewarder_for_frontend' ] ;
				}

				$DisplayFormat = get_option( 'rs_dispaly_time_format' ) ;
				if ( get_option( 'rs_hide_time_format' ) == 'yes' ) {
					$DateFormat = ($DisplayFormat == '1') ? "d-m-Y" : get_option( 'date_format' ) ;
				} else {
					$DateFormat = ($DisplayFormat == '1') ? "d-m-Y h:i:s A" : get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ;
				}

				if ( $CheckPoint == 'IMPOVR' || $CheckPoint == 'IMPADD' ) {
					$GMTExpDate = $Log[ 'expirydate' ] + get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ;
					$ExpDate    = $Log[ 'expirydate' ] != 999999999999 ? date_i18n( $DateFormat , ( float ) $GMTExpDate ) : '-' ;
				} else {
					$ExpDate = $Log[ 'expirydate' ] != 999999999999 ? date_i18n( $DateFormat , ( float ) $Log[ 'expirydate' ] ) : '-' ;
				}
				$ExpDate = ($ExpDate != '-') ? strftime( $ExpDate ) : '-' ;

				if ( (($Points != 0) && ($RedeemedPoints != 0)) || ((($Points != 0) && ($RedeemedPoints == 0)) || (($Points == 0) && ($RedeemedPoints != 0))) || ( ! empty( $Reason )) ) {
					$EarnedDate          = date_display_format( $Log ) ;
					$DefaultColumnValues = array(
						'sno'             => $i ,
						'points_expiry'   => $ExpDate ,
						'username'        => $Username ,
						'reward_for'      => $Reason ,
						'earned_points'   => $Points ,
						'redeemed_points' => $RedeemedPoints ,
						'total_points'    => $TotalPoints ,
						'earned_date'     => $EarnedDate ,
					);

					$time = strtotime( $EarnedDate );
					?>
					<tr>
						<td data-value="<?php echo $time; ?>">
							<?php
							printf(
								'%s<br><small>%s</small>',
								wp_date( 'jS F Y', $time ),
								wp_date( 'h:ia', $time )
							);
							?>
						</td>

						<td>
							<?php echo $Reason; ?>
						</td>

						<td data-value="<?php echo $Points - $RedeemedPoints; ?>">
							<?php
							echo sprintf(
								'%+d<br><small>Balance:&nbsp;%d</small>',
								$Points - $RedeemedPoints,
								$TotalPoints
							);
							?>
						</td>
					</tr>
					<?php
				}
				$i ++ ;
			}
			?>
		</tbody>


		<tfoot>
			<tr style="clear:both;">
				<td colspan="7">
					<div class="pagination pagination-centered"></div>
				</td>
			</tr>
		</tfoot>
	</table>
</form>
