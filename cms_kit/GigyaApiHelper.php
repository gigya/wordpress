<?php

namespace Gigya\CMSKit;

use Gigya\PHP\GSException;
use Gigya\PHP\GSKeyNotFoundException;
use Gigya\PHP\GSResponse;
use \Exception;
use Gigya\PHP\JWTUtils;
use Gigya\PHP\SigUtils;
use Gigya\WordPress\GigyaLogger;
use InvalidArgumentException;
use stdClass;

class GigyaApiHelper
{
	private $userKey;
	private $authKey;
	private $authMode;
	private $apiKey;
	private $dataCenter;
	private $defConfigFilePath;
	private $env;
	protected $logger;

	const IV_SIZE = 16;

	/**
	 * GigyaApiHelper constructor.
	 *
	 * @param string $apiKey Gigya API key
	 * @param string $userKey Gigya app/user key
	 * @param string $authKey Gigya app/user secret or RSA private key
	 * @param string $dataCenter Gigya data center
	 * @param string $authMode Authentication method: user_secret or user_rsa
	 */
	public function __construct( $apiKey, $userKey, $authKey, $dataCenter, $authMode = 'user_secret' ) {
		$this->defConfigFilePath = '..' . DIRECTORY_SEPARATOR . 'configuration/DefaultConfiguration.json';
		$defaultConf             = @file_get_contents( $this->defConfigFilePath );
		if ( ! $defaultConf ) {
			$confArray = array();
		} else {
			$confArray = json_decode( file_get_contents( $this->defConfigFilePath ) );
		}
		$this->userKey  = ! empty( $userKey ) ? $userKey : ( $confArray['appKey'] ?? '' );
		$this->authMode = $authMode;
		if ( $authMode === 'user_secret' ) {
			$this->authKey = ! empty( $authKey ) ? self::decrypt( $authKey, SECURE_AUTH_KEY ) : self::decrypt( $confArray['appSecret'], SECURE_AUTH_KEY );
		} else {
			$this->authKey = self::decrypt( $authKey, SECURE_AUTH_KEY );
		}

		$this->apiKey     = ! empty( $apiKey ) ? $apiKey : ( $confArray['apiKey'] ?? '' );
		$this->dataCenter = ! empty( $dataCenter ) ? $dataCenter : ( $confArray['dataCenter'] ?? 'us1.gigya.com' );

		$this->env = '{"cms_name":"WordPress","cms_version":"WordPress_' . get_bloginfo( 'version' ) . '","gigya_version":"Gigya_module_' . GIGYA__VERSION . '","php_version":"' . phpversion() . '"}'; /* WordPress only */
		$this->logger = new GigyaLogger();
	}

	/**
	 * @param $method
	 * @param array|object $params
	 *
	 * @return GSResponse
	 *
	 * @throws GSApiException
	 * @throws GSException
	 * @throws GSKeyNotFoundException
	 */
	public function sendApiCall( $method, $params ) {

		$params['environment'] = $this->env;

		if ( $this->authMode === 'user_rsa' ) {
			$req = GSFactory::createGSRequestPrivateKey( $this->apiKey, $this->userKey, $this->authKey, $method,
				GSFactory::createGSObjectFromArray( $params ), $this->dataCenter );
		} else {
			$req = GSFactory::createGSRequestAppKey( $this->apiKey, $this->userKey, $this->authKey, $method,
				GSFactory::createGSObjectFromArray( $params ), $this->dataCenter );
		}

		$response = $req->send();
		return $response;
	}

	/**
	 * @throws GSApiException
	 * @throws GSException
	 */
	public function sendGetScreenSetsCall() {
		$req_params       = array( 'include' => 'screenSetID' );

		return $this->sendApiCall( 'accounts.getScreenSets', $req_params )->getData()->serialize();
	}

	/**
	 * Validate and get gigya user
	 *
	 * @param       $uid
	 * @param       $uidSignature
	 * @param       $signatureTimestamp
	 * @param $mode
	 * @param $include
	 * @param $extraProfileFields
	 * @param array $org_params
	 *
	 * @return GigyaUser|false
	 *
	 * @throws GSException
	 * @throws GSKeyNotFoundException
	 */
	public function validateUid( $uid, $uidSignature, $signatureTimestamp, $mode, $include = null, $extraProfileFields = null, $org_params = array() ) {
		$params                       = $org_params;
		$params['UID']                = $uid;
		$params['UIDSignature']       = $uidSignature;
		$params['signatureTimestamp'] = $signatureTimestamp;

		try {
			$res = $this->sendApiCall( "socialize.exchangeUIDSignature", $params );
		} catch ( Exception $e ) {
			return false;
		}

		$sig          = $res->getData()->getString( "UIDSignature", null );
		$sigTimestamp = $res->getData()->getString( "signatureTimestamp", null );

		if ( null !== $sig && null !== $sigTimestamp )
		{
			if ( SigUtils::validateUserSignature( $uid, $sigTimestamp, $this->authKey, $sig ) )
			{
				if ($mode === 'raas')
					return $this->fetchGigyaAccount( $uid, $include, $extraProfileFields, $org_params );
				else
					return true;
			}
		}

		return false;
	}

	/**
	 * @param $uid
	 * @param $idToken
	 *
	 * @return bool|stdClass
	 * @throws Exception
	 */
	public function validateJwtAuth( $uid, $idToken ) {
		$jwtDetails = JWTUtils::validateSignature( $idToken, $this->apiKey, $this->dataCenter );

		if ($jwtDetails !== false and !empty($jwtDetails->sub) and $jwtDetails->sub === $uid) {
			return $jwtDetails;
		}

		return false;
	}

	/**
	 * @param       $uid
	 * @param null  $include
	 * @param null  $extraProfileFields
	 * @param array $params
	 *
	 * @return GigyaUser|false
	 *
	 * @throws Exception
	 * @throws GSException
	 */
	public function fetchGigyaAccount($uid, $include = null, $extraProfileFields = null, $params = array()) {
		if (null == $include)
		{
			$include
				= "identities-active,identities-all,loginIDs,emails,profile,data,password,lastLoginLocation,rba,
            regSource,irank";
		}
		if (null == $extraProfileFields)
		{
			$extraProfileFields
				= "languages,address,phones,education,honors,publications,patents,certifications,
            professionalHeadline,bio,industry,specialties,work,skills,religion,politicalView,interestedIn,
            relationshipStatus,hometown,favorites,followersCount,followingCount,username,locale,verified,timezone,likes,
            samlData";
		}
		$params['UID'] = $uid;
		$params['include'] = $include;
		$params['extraProfileFields'] = $extraProfileFields;

		try
		{
			$res = $this->sendApiCall("accounts.getAccountInfo", $params);
		} catch ( GSApiException $e ) {
			$this->logger->error( 'Error fetching SAP Customer Data Cloud account: ' . $e->getErrorCode() . ': ' . $e->getMessage() . '. Call ID: ' . $e->getCallId(), $uid );
			return false;
		}

		$dataArray = $res->getData()->serialize();
		$profileArray = $dataArray['profile'];
		$gigyaUser = GigyaUserFactory::createGigyaUserFromArray($dataArray);
		$gigyaProfile = GigyaUserFactory::createGigyaProfileFromArray($profileArray);
		$gigyaUser->setProfile($gigyaProfile);

		return $gigyaUser;
	}

	/**
	 * @param string $uid
	 * @param array  $profile
	 * @param array  $data
	 *
	 * @throws Exception
	 * @throws GSException
	 * @throws GSApiException
	 */
	public function updateGigyaAccount( $uid, $profile = array(), $data = array() ) {
		if ( empty( $uid ) )
		{
			throw new InvalidArgumentException( "uid can not be empty" );
		}
		$paramsArray['UID'] = $uid;
		if ( ! empty( $profile ) && count( $profile ) > 0 )
		{
			$paramsArray['profile'] = $profile;
		}
		if ( ! empty( $data ) && count( $data ) > 0 )
		{
			$paramsArray['data'] = $data;
		}
		$this->sendApiCall("accounts.setAccountInfo", $paramsArray);
	}

	/**
	 * @param string $apiKey
	 *
	 * @return bool
	 *
	 * @throws Exception
	 * @throws GSException
	 */
	public function isRaasEnabled( $apiKey = null ) {
		if ( null === $apiKey )
		{
			$apiKey = $this->apiKey;
		}
		$params = GSFactory::createGSObjectFromArray( array( "apiKey" => $apiKey ) );
		try
		{
			$this->sendApiCall( "accounts.getGlobalConfig", $params );

			return true;
		}
		catch ( GSApiException $e )
		{
			if ( $e->getErrorCode() == 403036 )
			{
				return false;
			}
			$this->logger->error( 'Failed to get global configuration from SAP CDC: ' . $e->getMessage()  );
		}

		return false;
	}

	public function userObjFromArray( $user_arr ) {
		return GigyaUserFactory::createGigyaUserFromArray( $user_arr );
	}

	//-------- static --------//

	/**
	 * @param string        $str
	 * @param null | string $key
	 * @return string
	 */
	static public function decrypt( $str, $key = null ) {
		if ( null == $key )
		{
			$key = getenv( "KEK" );
		}
		if ( ! empty( $key ) )
		{
			$strDec        = base64_decode( $str );
			$iv            = substr( $strDec, 0, self::IV_SIZE );
			$text_only     = substr( $strDec, self::IV_SIZE );

			return openssl_decrypt( $text_only, 'AES-256-CBC', $key, 0, $iv );
		}

		return $str;
	}

	/**
	 * @param string        $str
	 * @param null | string $key
	 * @return string
	 */
	public static function encrypt( $str, $key = null ) {
		if ( null == $key )
		{
			$key = getenv( "KEK" );
		}
		$iv    = openssl_random_pseudo_bytes( self::IV_SIZE );
		$crypt = openssl_encrypt( $str, 'AES-256-CBC', $key, null, $iv );

		return trim( base64_encode( $iv . $crypt ) );
	}

	/**
	 * @param null | string $str
	 * @return mixed
	 */
	static public function genKeyFromString( $str = null ) {
		if ( null == $str )
		{
			$str = openssl_random_pseudo_bytes( 32 );
		}
		$salt = openssl_random_pseudo_bytes( 32 );

		return hash_pbkdf2( "sha256", $str, $salt, 1000, 32 );
	}
}
