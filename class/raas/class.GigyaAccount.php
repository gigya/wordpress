<?php

/**
 * @file
 * class.GigyaAccount.php
 * For RAAS login user.
 * Provides a GigyaAccount object type with associated methods.
 */
class GigyaAccount {

	private $profile;
	private $data;

	public function __construct( $uid ) {
		$api = new GigyaApi( $this->uid );
		$res = $api->call( 'accounts.getAccountInfo', array( 'UID' => $uid ) );

		if ( is_array( $res ) ) {
			$this->profile = $res['profile'];
			$this->data    = $res['data'];
		}
	}

	/**
	 * @return mixed
	 */
	public function getData() {
		return $this->data;
	}

	/**
	 * @return mixed
	 */
	public function getProfile() {
		return $this->profile;
	}

	public function getProfileField( $field_name ) {
		return ! empty( $this->profile[$field_name] ) ? $this->profile[$field_name] : NULL;
	}

	public function getDataField( $field_name ) {
		$path   = explode( '.', $field_name );
		$branch = array();
		foreach ( $path as $p ) {
			if ( isset( $this->data[$p] ) ) {
				if ( is_array( $this->data[$p] ) ) {
					$branch = $this->data[$p];
				} else {
					return $this->data[$p];
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
}