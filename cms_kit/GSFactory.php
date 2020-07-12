<?php

namespace Gigya\CMSKit;

use Exception;
use Gigya\PHP\GSException;
use Gigya\PHP\GSKeyNotFoundException;
use Gigya\PHP\GSObject;

class GSFactory {
	/**
	 * @param $apiKey
	 * @param $secret
	 * @param $apiMethod
	 * @param $params
	 * @param string $dataCenter
	 * @param bool $useHTTPS
	 *
	 * @return GigyaApiRequest
	 * @throws GSKeyNotFoundException
	 */
	public static function createGsRequest($apiKey, $secret, $apiMethod, $params, $dataCenter = "us1.gigya.com", $useHTTPS = true) {
		return new GigyaApiRequest($apiKey, $secret, $apiMethod, $params, $dataCenter, $useHTTPS);
	}

	/**
	 * @param $apiKey
	 * @param $key
	 * @param $secret
	 * @param $apiMethod
	 * @param $params
	 * @param string $dataCenter
	 * @param bool $useHTTPS
	 *
	 * @return GigyaApiRequest
	 * @throws GSKeyNotFoundException
	 */
	public static function createGSRequestAppKey($apiKey, $key, $secret, $apiMethod, $params, $dataCenter = "us1.gigya.com", $useHTTPS = true) {
		return new GigyaApiRequest($apiKey, $secret, $apiMethod, $params, $dataCenter, $useHTTPS, $key);
	}

	/**
	 * @param $apiKey
	 * @param $key
	 * @param $privateKey
	 * @param $apiMethod
	 * @param $params
	 * @param string $dataCenter
	 * @param bool $useHTTPS
	 *
	 * @return GigyaAuthRequest
	 * @throws GSKeyNotFoundException
	 */
	public static function createGSRequestPrivateKey($apiKey, $key, $privateKey, $apiMethod, $params, $dataCenter = "us1.gigya.com", $useHTTPS = true) {
		return new GigyaAuthRequest($apiKey, $privateKey, $apiMethod, $params, $dataCenter, $useHTTPS, $key);
	}

	/**
	 * @param $token
	 * @param $apiMethod
	 * @param $params
	 * @param string $dataCenter
	 * @param bool $useHTTPS
	 *
	 * @return GigyaApiRequest
	 * @throws GSKeyNotFoundException
	 */
	public static function createGSRequestAccessToken($token, $apiMethod, $params, $dataCenter = "us1.gigya.com", $useHTTPS = true) {
		return new GigyaApiRequest($token, null, $apiMethod, $params, $dataCenter, $useHTTPS);
	}

	/**
	 * @param array $array
	 *
	 * @return GSObject
	 * @throws Exception
	 * @throws GSException
	 */
	public static function createGSObjectFromArray($array) {
		if (!is_array($array)) {
			throw new GSException("Array is expected got " . gettype($array) );
		}
		$json = json_encode($array, JSON_UNESCAPED_SLASHES);
		if ($json === false) {
			throw new GSException("Error converting array to json see json errno in error code", json_last_error());
		}
		return new GSObject($json);
	}

}