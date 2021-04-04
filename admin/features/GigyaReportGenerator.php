<?php


namespace Gigya\WordPress;


use Gigya\CMSKit\GigyaCMS;
use Gigya\CMSKit\GSApiException;
use Gigya\PHP\GSException;

class GigyaReportGenerator {

	//This is the names of the generated files.
	public static $WP_users_without_UID_that_exist_in_SAP = 'WP_users_without_UID_that_exist_in_SAP';
	public static $WP_users_with_same_email_but_different_UID = 'WP_users_with_same_email_but_different_UID';
	public static $SAP_users_not_existing_in_WP = 'SAP_users_not_existing_in_WP';
	public static $WP_users_not_existing_in_SAP = 'WP_users_not_existing_in_SAP';


	/***
	 * Finding all the WP users (up to GIGYA__SYNC_REPORT_MAX_USERS) that are not found in Gigya, or their UID doesn't match.
	 *
	 * @return array Array of arrays that contains all the users that aren't synchronized.
	 *
	 * @throws GSApiException
	 * @throws GSException
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
	 *Finding the Gigya users (up to GIGYA__SYNC_REPORT_MAX_USERS) that aren't in WP, or their UID doesn't match..
	 * @return array Array of arrays that contains all the users that aren't synchronized.
	 *
	 * @throws GSApiException
	 * @throws GSException
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
			throw $e;

		} catch ( GSException $e ) {
			throw $e;

		}
		$total_users_in_wp = count_users();

		if ( $total_users_in_wp > GIGYA__SYNC_REPORT_MAX_USERS ) {
			return static::searchArrayOfUsersInWP( $gigya_users );
		} else {
			return static::gigyaUsersCompareToWPUsersByArrays( $gigya_users );
		}
	}

	/**
	 * Searches Gigya users in the WP DB on a per-user basis.
	 *
	 * @param $gigya_users_list
	 *
	 * @return array Array of arrays that contains all the users that aren't synchronized.
	 *
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
						$gigya_user['UID'],
						$gigya_user_email
					);
				} else {
					$wp_meta_uid = get_user_meta( $wp_user->ID, 'gigya_uid', true );
					if ( $wp_meta_uid == false ) {
						$user_exists_but_there_is_no_uid_in_wp[ $wp_user->user_email ] = array(
							'id'    => $wp_user->ID,
							'email' => $wp_user->user_email
						);
					} else {
						if ( $wp_meta_uid !== $gigya_user['UID'] ) {
							$user_exists_but_different_uid[ $wp_user->user_email ] = array(
								'id'    => $wp_user->ID,
								'email' => $wp_user->user_email
							);
						}
					}
				}
			}
		}

		return array(
			self::$WP_users_without_UID_that_exist_in_SAP     => $user_exists_but_there_is_no_uid_in_wp,
			self::$WP_users_with_same_email_but_different_UID => $user_exists_but_different_uid,
			self::$SAP_users_not_existing_in_WP               => $user_exists_in_gigya_but_not_in_wordpress
		);
	}

	/**
	 * Searches Gigya users in the WP DB on a collective basis – retrieves all users in the WP DB and compares to Gigya's users
	 *
	 * @param $gigya_users_list
	 *
	 * @return array Array of arrays that contains all the users that aren't synchronized.
	 */
	private static function gigyaUsersCompareToWPUsersByArrays( $gigya_users_list ) {
		$user_exists_but_different_uid             = array();
		$user_exists_in_gigya_but_not_in_wordpress = array();
		$user_exists_but_there_is_no_uid_in_wp     = array();
		$gigya_users_extended                      = array();
		$wp_user_index                             = 0;

		$wp_users = get_users( array(
			'fields'  => array( 'user_email', 'ID' ),
			'orderby' => 'user_email'
		) );

		array_walk( $wp_users, function ( &$user ) {
			strtolower( $user->user_email );
		} );

		/*in case of few emails in the loginIDs this loops getting them out*/
		foreach ( $gigya_users_list as $gigya_user ) {
			foreach ( $gigya_user['loginIDs']['emails'] as $email ) {
				array_push( $gigya_users_extended, array( 'UID' => $gigya_user['UID'], 'email' => $email ) );
			}
		};

		/*sorting the array after getting out all the emails from the loginIDs*/
		if ( count( $gigya_users_extended ) > 1 ) {
			usort( $gigya_users_list, function ( $a, $b ) {
				return strcmp( $a['email'], $b['email'] );
			} );
		}

		/*compare between two sorted array algo*/
		foreach ( $gigya_users_extended as $gigya_user ) {

			$is_user_exists        = false;
			$does_array_key_exists = array_key_exists( $wp_user_index, $wp_users );

			if ( $does_array_key_exists ) {
				$wp_user = array(
					'id'    => $wp_users[ $wp_user_index ]->ID,
					'email' => $wp_users[ $wp_user_index ]->user_email
				);
			}

			while ( ! $is_user_exists and $does_array_key_exists and ( strcmp( $gigya_user['email'], $wp_user['email'] ) >= 0 ) ) {

				if ( $gigya_user['email'] === $wp_users['email'] ) {
					$is_user_exists = true;
					$wp_meta_uid    = get_user_meta( $wp_user['id']->ID, 'gigya_uid', true );

					if ( $wp_meta_uid == false ) {
						$user_exists_but_there_is_no_uid_in_wp[ $wp_user ['email'] ] = $wp_user;
					} else {
						if ( $wp_meta_uid !== $gigya_user['UID'] ) {
							$user_exists_but_different_uid[ $wp_user ['email'] ] = $wp_user;
						}
					}
				} else {
					$wp_user_index ++;
				}

				$does_array_key_exists = array_key_exists( $wp_user_index, $wp_users );

				if ( $does_array_key_exists ) {
					$wp_user = array(
						'id'    => $wp_users[ $wp_user_index ]->ID,
						'email' => $wp_users[ $wp_user_index ]->user_email
					);
				}
			}
			if ( ! $is_user_exists ) {
				$user_exists_in_gigya_but_not_in_wordpress[ $gigya_user['UID'] ] = array(
					$gigya_user['UID'],
					$gigya_user['email']
				);
			}
		}

		return array(
			self::$WP_users_without_UID_that_exist_in_SAP     => $user_exists_but_there_is_no_uid_in_wp,
			self::$WP_users_with_same_email_but_different_UID => $user_exists_but_different_uid,
			self::$SAP_users_not_existing_in_WP               => $user_exists_in_gigya_but_not_in_wordpress
		);

	}

	/**
	 *The function contains a loop that builds a query, the query includes the WP user's email. And sending the query
	 * After each gigya call the function tries to find the users that have a different UID or don't exist in Gigya.
	 *
	 * @param $wp_users_list
	 *
	 * @return array Results of the comparing.
	 *
	 * @throws GSApiException
	 * @throws GSException
	 */
	private static function compareWPUsersToGigya( $wp_users_list ) {


		$gigya_cms                 = new GigyaCMS();
		$user_counter              = count( $wp_users_list );
		$max_search_query_length   = GIGYA__SEARCH_MAX_QUERY_LENGTH;
		$general_server_error_code = 500001;
		$should_retry              = true;
		$results                   = array(
			self::$WP_users_without_UID_that_exist_in_SAP     => array(),
			self::$WP_users_with_same_email_but_different_UID => array(),
			self::$WP_users_not_existing_in_SAP               => array()
		);

		array_walk( $wp_users_list, function ( &$user ) {
			strtolower( $user->user_email );
		} );


		//batch of query
		while ( $user_counter > 0 ) {

			$query_builder_results = static::accountSearchQueryBuilder( $wp_users_list, $user_counter, $max_search_query_length );
			$wp_batch_user_list    = $query_builder_results['wp_users_query_list'];

			/*gigya call get users by the query*/
			try {
				$gigya_users = $gigya_cms->searchGigyaUsers( [ 'query' => $query_builder_results['gigya_query'] ] );

			} catch ( GSApiException $e ) {

				if ( ( $e->getErrorCode() === $general_server_error_code ) and $should_retry ) {
					$should_retry            = false;
					$max_search_query_length = ceil( ( 2 * $max_search_query_length ) / 3 );
					$wp_batch_user_list      = array();
					$gigya_users             = array();

				} else {
					throw $e;
				}

			} catch ( GSException $e ) {
				throw $e;
			}

			//validate that there was no error
			if ( ! empty( $wp_batch_user_list ) ) {
				$should_retry = true;
				$last_results = static::findDifferences( $gigya_users, $wp_batch_user_list );
				$user_counter -= count( $wp_batch_user_list );

				$keys = array_keys( $last_results );
				foreach ( $keys as $key ) {
					$results[ $key ] = array_merge( $results[ $key ], $last_results[ $key ] );
				}
			}
		}

		return $results;
	}

	/**
	 * Getting array of gigya users, array of wp users,and finding the differences by comparing each user from WP to Gigya.
	 *
	 * @param $gigya_users
	 * @param $wp_users
	 *
	 * @return array Array of arrays, each array contain different case of unsuitability.
	 */
	private static function findDifferences( $gigya_users, $wp_users ) {

		$user_exists_but_different_uid         = array();
		$user_exists_in_wp_but_not_in_gigya    = array();
		$user_exists_but_there_is_no_uid_in_wp = array();
		$gigya_users_extended                  = array();

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
		foreach ( $wp_users as $wp_user ) {
			$wp_user          = array( 'id' => $wp_user->ID, 'email' => $wp_user->user_email );
			$does_user_exist  = false;
			$gigya_user_index = 0;

			while ( ! $does_user_exist and array_key_exists( $gigya_user_index, $gigya_users_extended ) ) {
				$gigya_user = $gigya_users_extended[ $gigya_user_index ++ ];
				if ( $gigya_user['email'] === $wp_user['email'] ) {
					$does_user_exist = true;
					$wp_uid          = get_user_meta( $wp_user['id'], 'gigya_uid', true );

					if ( ! $wp_uid ) {
						$user_exists_but_there_is_no_uid_in_wp[ $wp_user['email'] ] = $wp_user;

					} else if ( $gigya_user['UID'] != $wp_uid ) {
						$user_exists_but_different_uid[ $wp_user['email'] ] = $wp_user;
					}
					unset( $gigya_user );
				}
			}
			if ( ! $does_user_exist ) {
				$user_exists_in_wp_but_not_in_gigya[ $wp_user['email'] ] = $wp_user;
			}
		}

		return array(
			self::$WP_users_without_UID_that_exist_in_SAP     => $user_exists_but_there_is_no_uid_in_wp,
			self::$WP_users_with_same_email_but_different_UID => $user_exists_but_different_uid,
			self::$WP_users_not_existing_in_SAP               => $user_exists_in_wp_but_not_in_gigya,
		);
	}

	/**Build an accounts.search query by getting list of WP users, and each user email will be inserted into the query.
	 * Be aware that the max result can contain only  GIGYA__SYNC_REPORT_MAX_PAGE_SIZE users.
	 * example of query: SELECT UID, loginIDs.emails FROM account WHERE loginIDs.emails = "first@example.com"
	 *                        OR loginIDs.emails="second@example.com" OR loginIDs.emails="third@example.com"  LIMIT 2000
	 *
	 * @param $wp_user_list
	 * @param $user_counter , Total amount of users that needed to enter, if the total amount is too large the programmer should use in loop.
	 *
	 * @param $max_search_query_length , The max length the query 'account.search' can be.
	 *
	 * @return array The array contain the full gigya query, and an array of all the WP users that entered to the the query.
	 */

	private static function accountSearchQueryBuilder( $wp_user_list, $user_counter, $max_search_query_length ) {

		$end_query                  = ' LIMIT ' . GIGYA__SYNC_REPORT_MAX_PAGE_SIZE;
		$next_email_adding_to_query = '';
		$wp_users_query_list        = array();

		/*building the query*/
		array_push( $wp_users_query_list, $wp_user_list[ -- $user_counter ] );

		$gigya_query = "SELECT UID, loginIDs.emails FROM account WHERE loginIDs.emails = " . '"' . $wp_user_list[ $user_counter ]->user_email . '"';

		if ( $user_counter > 0 ) {
			$next_email_adding_to_query = ' OR loginIDs.emails=' . '"' . $wp_user_list[ $user_counter - 1 ]->user_email . '"';
		}
		$total_query_length = strlen( $next_email_adding_to_query ) + strlen( $gigya_query ) + strlen( $end_query );

		while ( ( $user_counter > 0 ) and ( $total_query_length < $max_search_query_length ) ) {
			array_push( $wp_users_query_list, $wp_user_list[ $user_counter - 1 ] );
			$gigya_query        .= ' OR loginIDs.emails=' . '"' . $wp_user_list[ -- $user_counter ]->user_email . '"';
			$total_query_length = strlen( $next_email_adding_to_query ) + strlen( $gigya_query ) + strlen( $end_query );

			if ( $user_counter > 0 ) {
				$next_email_adding_to_query = ' OR loginIDs.emails = ' . '"' . $wp_user_list[ $user_counter - 1 ]->user_email . '"';
			};
		}

		$gigya_query .= $end_query;

		return array( 'gigya_query' => $gigya_query, 'wp_users_query_list' => $wp_users_query_list );
	}

}