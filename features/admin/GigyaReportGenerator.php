<?php


namespace Gigya\WordPress;


use Gigya\CMSKit\GigyaCMS;
use Gigya\CMSKit\GSApiException;
use Gigya\PHP\GSException;

class GigyaReportGenerator {


	/***
	 * Finding all the WP users (until  GIGYA__SYNC_REPORT_MAX_USERS variable) that not found in Gigya, or their UID doesn't match.
	 *
	 * @return array|string The String will return while there was an error and the string includes the error message.
	 */
	public static function getWPUsersNotInGigya() {

		$args           = array(
			'role__in' => array( 'administrator', 'super_admin' ),
			'order_by' => 'user_email',
			'fields'   => array( 'user_email', 'ID' )
		);
		$wp_admin_users = get_users( $args );

		$args     = array(
			'role__not_in' => array( 'administrator', 'super_admin' ),
			'order_by'     => 'user_email',
			'fields'       => array( 'user_email', 'ID' ),
			'number'       => GIGYA__SYNC_REPORT_MAX_USERS - count( $wp_admin_users )
		);
		$wp_users = array_merge( $wp_admin_users, get_users( $args ) );

		return static::compareWPUsersToGigya( $wp_users );
	}


	/**
	 *Finding the Gigya users(until GIGYA__SYNC_REPORT_MAX_USERS count) at WP DB,
	 * and sending all the user that doesn't synchronized (by Gigya uid or users doesn't exists at WP DB)
	 *
	 * @return array|string The String will return while there was an error and the string includes the error message.
	 */
	public static function getGigyaUsersNotInWP() {

		/*getting 'GIGYA__SYNC_REPORT_MAX_USERS' users from gigya*/
		$gigya_query = "SELECT UID, loginIDs.emails FROM accounts";
		$gigya_query .= " ORDER BY registeredTimestamp ASC LIMIT " . GIGYA__SYNC_REPORT_MAX_PAGE_SIZE;
		$gigya_cms   = new GigyaCMS();

		try {
			$gigya_users = $gigya_cms->searchGigyaUsers( [
				'query'      => $gigya_query,
				'openCursor' => 'true'
			], null, ( GIGYA__ACCOUNT_SEARCH_NUMBER_OF_PAGES - 1 ) );

		} catch ( GSApiException $e ) {
			return "Could not reach SAP server, callID: " . $e->getCallId();

		} catch ( GSException $e ) {
			return "Could not reach SAP server: " . $e->errorMessage;

		}
		$total_users_in_wp = count_users();

		if ( $total_users_in_wp > GIGYA__SYNC_REPORT_MAX_USERS ) {
			return static::searchArrayOfUsersInWP( $gigya_users );
		} else {
			return static:: gigyaUsersCompareToWPUsersByArrays( $gigya_users );

		}
	}

	/**
	 * In case of more than 'GIGYA__SYNC_REPORT_MAX_USERS'users count, each user from Gigya scanned at WP DB separately.
	 *
	 * @param $gigya_users_list
	 *
	 * @return array Of arrays that contain all the users that doesn't synchronized.
	 */
	private static function searchArrayOfUsersInWP( $gigya_users_list ) {

		$user_exists_but_different_uid             = array();
		$user_exists_in_gigya_but_not_in_wordpress = array();
		$user_exists_but_there_is_no_uid_in_wp     = array();

		/*searching each gigya user in WP data*/
		foreach ( $gigya_users_list as $gigya_user ) {
			foreach ( $gigya_user['loginIDs']['emails'] as $gigya_user_email ) {
				$wp_user = get_user_by( 'email', $gigya_user_email );

				if ( $wp_user == false ) {
					$user_exists_in_gigya_but_not_in_wordpress[ $gigya_user['UID'] ] = array(
						( $gigya_user['UID'] ),
						( $gigya_user_email )
					);
				} else {
					$wp_meta_uid = get_user_meta( $wp_user->ID, 'gigya_uid', true );
					if ( $wp_meta_uid == false ) {
						$user_exists_but_there_is_no_uid_in_wp[ $wp_user->ID ] = array(
							( $wp_user['id'] ),
							( $wp_user['email'] )
						);
					} else {
						if ( $wp_meta_uid !== $gigya_user['UID'] ) {
							$user_exists_but_different_uid[ $wp_user->ID ] = array(
								( $wp_user['id'] ),
								( $wp_user['email'] )
							);
						}
					}
				}
			}
		}

		return array(
			'WP_users_without_UID_that_exist_in_SAP'     => $user_exists_but_there_is_no_uid_in_wp,
			'WP_users_with_same_email_but_different_UID' => $user_exists_but_different_uid,
			'SAP_users_not_existing_in_WP'               => $user_exists_in_gigya_but_not_in_wordpress
		);
	}

	/**
	 * In case of less than 'GIGYA__SYNC_REPORT_MAX_USERS' users count, this function getting all the users from WP
	 * and sorting the Gigya users array and the WP users array by email, and compare between the arrays
	 *
	 * @param $gigya_users_list *
	 *
	 * @return array Of arrays that contain all the users that doesn't synchronized.
	 */
	private static function gigyaUsersCompareToWPUsersByArrays( $gigya_users_list ) {
		$user_exists_but_different_uid             = array();
		$user_exists_in_giyga_but_not_in_wordpress = array();
		$user_exists_but_there_is_no_uid_in_wp     = array();
		$gigya_users_extended                      = array();
		$wp_user_index                             = 0;

		$wp_users = get_users( array(
			'fields'  => array( 'user_email', 'ID' ),
			'orderby' => 'user_email'
		) );

		/*in case of few emails in the loginIDs this loops getting them out*/
		foreach ( $gigya_users_list as $gigya_user ) {
			foreach ( $gigya_user['loginIDs']['emails'] as $email ) {
				array_push( $gigya_users_extended, array( 'UID' => $gigya_user['UID'], 'email' => $email ) );
			}
		};

		/*sorting the array after getting out all the emails from the loginIDs*/
		usort( $gigya_users_list, function ( $a, $b ) {
			return strcmp( $a['email'], $b['email'] );
		} );

		/*compare between two sorted array algo*/
		foreach ( $gigya_users_extended as $gigya_user ) {
			$is_user_exists = false;
			while ( ! $is_user_exists and isset( $wp_users[ $wp_user_index ] ) and strcmp( $gigya_user['email'], $wp_users[ $wp_user_index ]->email ) >= 0 ) {
				if ( $gigya_user['email'] === $wp_users[ $wp_user_index ]->email ) {
					$is_user_exists = true;
					$wp_meta_uid    = get_user_meta( $wp_users[ $wp_user_index ]->ID, 'gigya_uid', true );
					if ( $wp_meta_uid == false ) {
						$user_exists_but_there_is_no_uid_in_wp[ $wp_users[ $wp_user_index ]->ID ] = array(
							( $wp_users[ $wp_user_index ]->ID ),
							( $gigya_user['email'] )
						);
					} else {
						if ( $wp_meta_uid !== $gigya_user['UID'] ) {
							$user_exists_but_different_uid[ $wp_users[ $wp_user_index ]->ID ] = array(
								( $wp_users[ $wp_user_index ]->ID ),
								( $gigya_user['email'] )
							);
						}
					}
				} else {
					$wp_user_index ++;
				}
			}
			if ( ! $is_user_exists ) {
				$user_exists_in_giyga_but_not_in_wordpress[ $gigya_user['UID'] ] = array(
					( $gigya_user['UID'] ),
					( $gigya_user['email'] )
				);
			}
		}

		return array(
			'WP_users_without_UID_that_exist_in_SAP'     => $user_exists_but_there_is_no_uid_in_wp,
			'WP_users_with_same_email_but_different_UID' => $user_exists_but_different_uid,
			'SAP_users_not_existing_in_WP'               => $user_exists_in_giyga_but_not_in_wordpress
		);

	}

	/**
	 *The function contain loop that building a query that include WP user's email.
	 * After each query the function trying to find the users that have a different UID or doesn't exists in Gigya
	 *
	 * @param $wp_user_list
	 *
	 * @return array|string In case of array:Array Of arrays that contain all the users that doesn't synchronized. In case of string, the string contain the error message.
	 */
	private static function compareWPUsersToGigya( $wp_user_list ) {

		$user_exists_but_different_uid         = array();
		$user_exists_in_wp_but_not_in_gigya    = array();
		$user_exists_but_there_is_no_uid_in_wp = array();
		$gigya_users_extended                  = array();
		$gigya_cms                             = new GigyaCMS();
		$user_counter                          = count( $wp_user_list );
		$max_search_query_length               = GIGYA__SEARCH_MAX_QUERY_LENGTH;
		$too_long_query_error_code             = 500001;
		$next_email_adding_to_query            = '';
		$do_have_another_retry                 = true;

		//batch of query
		while ( $user_counter > 0 ) {

			$wp_temp_user_list       = array();
			$curr_batch_user_counter = 0;

			/*building the query*/
			array_push( $wp_temp_user_list, $wp_user_list[ -- $user_counter ] );
			$curr_batch_user_counter ++;
			$gigya_query = "SELECT UID, loginIDs.emails FROM account WHERE loginIDs.emails = " .'"'. $wp_user_list[ $user_counter ]->user_email . '"';
			if ( $user_counter > 0 ) {
				$next_email_adding_to_query = ' OR loginIDs.emails = ' . '"' . $wp_user_list[ $user_counter - 1 ]->user_email . '"';
			}
			while ( $user_counter > 0 and strlen( $next_email_adding_to_query ) + strlen( $gigya_query ) < $max_search_query_length ) {
				array_push( $wp_temp_user_list, $wp_user_list[ $user_counter - 1 ] );
				$gigya_query .= ' OR loginIDs.emails = ' . '"' . $wp_user_list[ -- $user_counter ]->user_email . '"';
				$curr_batch_user_counter ++;
				if ( $user_counter > 0 ) {
					$next_email_adding_to_query = ' OR loginIDs.emails = ' . '"' . $wp_user_list[ $user_counter - 1 ]->user_email . '"';
				} else {
					$user_counter --;
				}

			}
			/*gigya call get users by the query*/
			try {
				$gigya_users = $gigya_cms->searchGigyaUsers( [ 'query' => $gigya_query ] );

			} catch ( GSApiException $e ) {

				if ( $e->getErrorCode() == $too_long_query_error_code and $do_have_another_retry ) {
					$do_have_another_retry   = false;
					$max_search_query_length = ceil( $max_search_query_length / 2 );
					$wp_temp_user_list       = array();
					$gigya_users             = array();
					$user_counter            += $curr_batch_user_counter;

				} else {
					return "There was an error with the request, callID: " . $e->getCallId() . ' Error Code: ' . $e->getErrorCode();
				}

			} catch ( GSException $e ) {

				return "Could not reach SAP server: " . $e->errorMessage;
			}

			//validate that there was no error
			if ( ! empty( $wp_temp_user_list ) ) {
				$do_have_another_retry = true;
			}

			/*getting out all the loginIDS.Emails in the same user*/
			foreach ( $gigya_users as $gigya_user ) {
				foreach ( $gigya_user['loginIDs']['emails'] as $email ) {
					array_push( $gigya_users_extended, array( 'UID' => $gigya_user['UID'], 'email' => $email ) );
				}
			}
			/*sorting the gigya users array by email*/
			if ( count( $gigya_users_extended ) > 1 ) {
				usort( $gigya_users_extended, function ( $a, $b ) {
					return strcmp( $a['email'], $b['email'] );
				} );
			}
			/*compare all the WP in the batch to query*/
			foreach ( $wp_temp_user_list as $wp_user ) {
				$wp_user          = array( 'id' => $wp_user->ID, 'email' => $wp_user->user_email );
				$does_user_exist  = false;
				$gigya_user_index = 0;

				while ( ! $does_user_exist and isset( $gigya_users_extended[ $gigya_user_index ] ) ) {
					$gigya_user = $gigya_users_extended[ $gigya_user_index ++ ];
					if ( $gigya_user['email'] == $wp_user['email'] ) {
						$does_user_exist = true;
						$wp_uid          = get_user_meta( $wp_user['id'], 'gigya_uid', true );

						if ( ! $wp_uid ) {
							$user_exists_but_there_is_no_uid_in_wp[ $wp_user['id'] ] =
								array(
									( $wp_user['id'] ),
									( $wp_user['email'] )
								);

						} else if ( $gigya_user['UID'] != $wp_uid ) {

							$user_exists_but_different_uid[ $wp_user['id'] ] = array(
								( $wp_user['id'] ),
								( $wp_user['email'] )
							);
						}
						unset( $gigya_user );
					}
				}
				if ( ! $does_user_exist ) {
					$user_exists_in_wp_but_not_in_gigya[ $wp_user['id'] ] = array(
						( $wp_user['id'] ),
						( $wp_user['email'] )
					);
				}
			}

		}

		return array(
			'WP_users_without_UID_that_exist_in_SAP'     => $user_exists_but_there_is_no_uid_in_wp,
			'WP_users_with_same_email_but_different_UID' => $user_exists_but_different_uid,
			'WP_users_not_existing_in_SAP'               => $user_exists_in_wp_but_not_in_gigya,
		);
	}


}