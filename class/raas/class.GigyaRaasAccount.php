<?php

/**
 * @file
 * class.GigyaAccount.php
 * For RAAS login user.
 * Provides a GigyaAccount object type with associated methods.
 */
class GigyaAccount {

	public function __construct( $uid ) {
		$req_params = array(
				'UID'     => $uid,
				'include' => 'profile, data, loginIDs'
		);

		// Because we can only trust the UID parameter from the origin object,
		// We'll ask Gigya's API for account-info straight from the server.
		$api           = new GigyaApi( $uid );
		$this->account = $api->call( 'accounts.getAccountInfo', $req_params );

	}

	/**
	 * @return array
	 */
	public function getAccount() {
		return $this->account;
	}

	/**
	 * @return array
	 */
	public function getData() {
		return $this->account['data'];
	}

	/**
	 * @return array
	 */
	public function getProfile() {
		return $this->account['profile'];
	}

	/**
	 * @param $field_name
	 *
	 * @return null
	 */
	public function getProfileField( $field_name ) {
		return ! empty( $this->account['profile'][$field_name] ) ? $this->account['profile'][$field_name] : NULL;
	}

	/**
	 * @param $field_name
	 *
	 * @return mixed
	 */
	public function getDataField( $field_name ) {
		$path   = explode( '.', $field_name );
		$branch = array();
		foreach ( $path as $p ) {
			if ( isset( $this->account['data'][$p] ) ) {
				if ( is_array( $this->account['data'][$p] ) ) {
					$branch = $this->account['data'][$p];
				} else {
					return $this->account['data'][$p];
				}
			} elseif ( isset( $branch[$p] ) ) {
				if ( is_array( $branch[$p] ) ) {
					$branch = $branch[$p];
				} else {
					return $branch[$p];
				}
			} else {
				return NULL;
			}
		}
	}

	public function delete( $uid ) {
		$api = new GigyaApi( $uid );
		$api->call( 'accounts.deleteAccount', array( 'UID' => $uid ) );
	}

	/**
	 * @param $account
	 *
	 * @return array
	 */
	public function getProviders( $account ) {

		// Get info about the primary account.
		$query = 'select loginProvider from accounts where loginIDs.emails = ' . $account['profile']['email'];

		$api        = new GigyaApi( $account['UID'] );
		$search_res = $api->call( 'accounts.search', array( 'query' => $query ) );

		// Returns the primary provider, and the secondary (current).
		return array(
				'primary'   => $search_res['results'][0]['loginProvider'],
				'secondary' => $account['loginProvider']
		);
	}

	/**
	 * Checks if this email is the primary user email
	 *
	 * @param $gigya_emails
	 * @param $wp_email The email from WP DB.
	 *
	 * @internal param \The $userInfo user info from accounts.getUserInfo api call
	 * @return bool
	 */
	public static function isPrimaryUser( $gigya_emails, $wp_email ) {

		if ( in_array( $wp_email, $gigya_emails ) ) {

			return TRUE;

		}

		return FALSE;
	}
}