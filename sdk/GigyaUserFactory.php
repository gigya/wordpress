<?php
/**
 * Created by PhpStorm.
 * User: Yaniv Aran-Shamir
 * Date: 4/6/16
 * Time: 5:09 PM
 */

class GigyaUserFactory
{
	static function createGigyaUserFromJson($json) {
		return new GigyaUser($json);
	}

	static function createGigyaUserFromArray($array) {
		$gigyaUser = new GigyaUser(null);
		foreach ($array as $key => $value)
		{
			$gigyaUser->__set($key, $value);
		}
		$profileArray = $array['profile'];
		$gigyaProfile = self::createGigyaProfileFromArray($profileArray);
		$gigyaUser->setProfile($gigyaProfile);
		return $gigyaUser;
	}

	static function createGigyaProfileFromJson($json) {
		$gigyaArray = json_decode($json);
		return self::createGigyaProfileFromArray($gigyaArray);
	}

	static function createGigyaProfileFromArray($array) {
		$gigyaProfile = new GigyaProfile(null);
		foreach ($array as $key => $value)
		{
			$gigyaProfile->__set($key, $value);
		}
		return $gigyaProfile;
	}

}