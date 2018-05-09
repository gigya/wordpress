<?php
/**
 * Created by PhpStorm.
 * User: Yaniv Aran-Shamir, Yan Nasonov
 * Date: 4/5/16
 * Time: 5:06 PM
 */

class GigyaApiHelper
{
	private $key;
	private $secret;
	private $apiKey;
	private $dataCenter;
	private $defConfigFilePath;

	const IV_SIZE = 16;

	/**
	 * GigyaApiHelper constructor.
	 *
	 * @param string $apiKey     Gigya API key
	 * @param string $key        Gigya app/user key
	 * @param string $secret     Gigya app/user secret
	 * @param string $dataCenter Gigya data center
	 */
	public function __construct( $apiKey, $key, $secret, $dataCenter ) {
		$this->defConfigFilePath = ".." . DIRECTORY_SEPARATOR . "configuration/DefaultConfiguration.json";
		$defaultConf             = @file_get_contents( $this->defConfigFilePath );
		if ( ! $defaultConf )
		{
			$confArray = array();
		}
		else
		{
			$confArray = json_decode( file_get_contents( $this->defConfigFilePath ) );
		}
		$this->key        = ! empty( $key ) ? $key : $confArray['appKey'];
		$this->secret     = ! empty( $secret ) ? self::decrypt( $secret, SECURE_AUTH_KEY ) : self::decrypt( $confArray['appSecret'], SECURE_AUTH_KEY );
		$this->apiKey     = ! empty( $apiKey ) ? $apiKey : $confArray['apiKey'];
		$this->dataCenter = ! empty( $dataCenter ) ? $dataCenter : $confArray['dataCenter'];
	}

	/**
	 * @param $method
	 * @param array $params
	 *
	 * @return GSResponse
	 *
	 * @throws Exception
	 * @throws GSApiException
	 * @throws GSException
	 */
	public function sendApiCall( $method, $params ) {
		$params['environment'] = '{"cms_name":"WordPress","cms_version":"WordPress_' . get_bloginfo( 'version' ) . '","gigya_version":"Gigya_module_' . GIGYA__VERSION . '","php_version":"' . phpversion() . '"}'; /* WordPress only */

		$req = GSFactory::createGSRequestAppKey( $this->apiKey, $this->key, $this->secret, $method,
		                                         GSFactory::createGSObjectFromArray( $params ), $this->dataCenter );

		return $req->send();
	}

	/**
	 * Validate and get gigya user
	 *
	 * @param       $uid
	 * @param       $uidSignature
	 * @param       $signatureTimestamp
	 * @param null  $include
	 * @param null  $extraProfileFields
	 * @param array $org_params
	 *
	 * @return GigyaUser|false
	 *
	 * @throws Exception
	 * @throws GSException
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
			if ( SigUtils::validateUserSignature( $uid, $sigTimestamp, $this->secret, $sig ) )
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
		}
		catch (GSApiException $e)
		{
			error_log( 'Error fetching Gigya account: ' . $e->getErrorCode() . ': ' . $e->getMessage() . '. Call ID: ' . $e->getCallId() );
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
			throw new \InvalidArgumentException( "uid can not be empty" );
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
	 * @throws Exception
	 * @throws GSApiException
	 * @throws GSException
	 */
	public function getSiteSchema() {
		$params = GSFactory::createGSObjectFromArray( array( "apiKey" => $this->apiKey ) );
		$this->sendApiCall( "accounts.getSchema", $params );
		// $res    = $this->sendApiCall("accounts.getSchema", $params);
		// TODO: implement
	}

	/**
	 * @param null $apiKey
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
			error_log( $e->getMessage() );
		}

		return false;
	}

	public function userObjFromArray( $user_arr ) {
		$obj = GigyaUserFactory::createGigyaUserFromArray( $user_arr );

		return $obj;
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
			$plaintext_dec = openssl_decrypt( $text_only, 'AES-256-CBC', $key, 0, $iv );

			return $plaintext_dec;
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
		$key  = hash_pbkdf2( "sha256", $str, $salt, 1000, 32 );

		return $key;
	}
}
