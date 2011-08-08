<?php
/**
 * Gigya PHP SDK
 * @author Shachar Bar-David
 */

if (!function_exists('curl_init')) {
  throw new Exception('Gigya.Socialize needs the CURL PHP extension.');
}
if (!function_exists('json_decode')) {
  throw new Exception('Gigya.Socialize needs the JSON PHP extension.');
}


/**
 * Gigya Socialize Exception  
 * 
 */
class GSException extends Exception{
	
	public $errorMessage;
}

/**
 * Gigya Socialize Key Not Found Exception
 * 
 */
class GSKeyNotFoundException extends GSException{
	
	public function __construct($key){
		$this->errorMessage = "GSDictionary does not contain a value for key ".$key;
	}
}


/**
 * A Request to Gigya Socialize API
 *
 */

class GSRequest { 	 
	private static $cafile;
  	private $domain;
	private $path;
	private $traceLog = array();
	protected $method;

	private $apiKey; 	
	private $secretKey; 
	private $params; //GSDictionary 
	private $useHTTPS; 

	/**
	 * Constructs a request using an apiKey and secretKey. 
	 * You must provide a user ID (UID) of the tage user.
	 * Suitable for calling our old REST API
	 * @param apiKey
	 * @param secretKey															   
	 * @param apiMethod the api method (including namespace) to call. for example: socialize.getUserInfo
	 * If namespaces is not supplied "socialize" is assumed
	 * @param params the request parameters
	 * @param useHTTPS useHTTPS set this to true if you want to use HTTPS. 
	 */
	public function __construct($apiKey, $secretKey, $apiMethod, $params = null, $useHTTPS = false) 
	{
		if (!isset($apiMethod) || $apiMethod == null || strlen($apiMethod)==0)
			return;
		
		if (substr($apiMethod,0,1) == "/")
			$apiMethod = substr($apiMethod,1);
			
		if (strrpos($apiMethod,".")==0)
		{
			
			$this->domain = "socialize.gigya.com";
			$this->path = "/socialize." . $apiMethod;
		} else
		{
			
			$tokens =  explode(".",$apiMethod);
			$this->domain = $tokens[0].".gigya.com";
			$this->path = "/".$apiMethod;
		}
		$this->method = $apiMethod;

		if (empty($params))
			$this->params = new GSDictionary();
		else
			$this->params = clone $params;
		
		$this->useHTTPS = $useHTTPS;
		
		$this->apiKey = $apiKey;
		$this->secretKey = $secretKey;
		
		$this->traceField("apiMethod",$apiMethod);
		$this->traceField("apiKey",$apiKey);
		
	}	
	
	public function setParam($param, $val) {
		$this->params->put($param, $val);
	}
	
	public function getParams()
	{
		return $this->params;
	}	

	public static function setCAFile($filename)
	{
		GSRequest::$cafile = $filename;
	}
	
	/**
	 * Send the request synchronously
	 */
	public function send() 
	{
		$format = $this->params->getString("format",null);
		//set json as default format.
		if (empty($format))
		{
			$format =  "json";
			$this->setParam("format", $format);
		}
		
		
		if (	(empty($this->apiKey))
			 //|| (empty($this->secretKey))
			 || (empty($this->method))
		 )
		 {
			return new GSResponse($this->method,null,$this->params,400002,null,$this->traceLog);
		 }
		
		try 
		{
			$this->setParam("sdk", "php");
			$this->setParam("httpStatusCodes", "false");
			$this->traceField("params",$this->params);

			$responseStr = $this->sendRequest("POST", $this->domain, $this->path, $this->params, $this->apiKey, $this->secretKey, $this->useHTTPS);
			return new GSResponse($this->method,$responseStr,null,0,null,$this->traceLog);
		}
		catch (Exception $ex) {
			return new GSResponse($this->method,null,$this->params,500000,$ex->getMessage(), $this->traceLog);
		}
	}

	private function sendRequest($method,$domain,$path,$params,$token,$secret,$useHTTPS=false)
	{
		//prepare query params
		$protocol = $useHTTPS || empty($secret) ? "https" : "http";
		$resourceURI = $protocol."://".$domain.$path;
		
		$timestamp = (string)gmmktime();
		
		//timestamp in milliseconds
		list( $msecs, $uts ) = explode( ' ', microtime());
		$nonce  = (string)floor(($uts+$msecs)*1000);

		$httpMethod = "POST";

		
		if (!empty($secret))
		{
			//add query params.
			$params->put("apiKey", $token);
			$params->put("timestamp", $timestamp);
			$params->put("nonce", $nonce);
			
			//signature
			$signature = self::getOAuth1Signature($secret, $httpMethod, $resourceURI, $useHTTPS, $params);
			$params->put("sig", $signature);
		}
		else {
			
			$params->put("oauth_token", $token);
		}
		
		//get rest response.
		$res = $this->curl($resourceURI,$params);
		return $res;
	}


	private function curl($url, $params = NULL, $options = array())
	{   
		foreach($params->getKeys() as $key)
		{
			$postData[$key] = $params->getString($key);
		}
		
		$qs = http_build_query($postData);
		$this->traceField("URL",$url);
		$this->traceField("postData",$qs);
		
		/* POST */
		$defaults = array(
			CURLOPT_URL => $url,
			CURLOPT_POST=>1,
			CURLOPT_HEADER => 1,
			CURLOPT_POSTFIELDS=>$postData,
			CURLOPT_HTTPHEADER => array( 'Expect:' ),
			CURLOPT_RETURNTRANSFER => TRUE,
			//CURLOPT_TIMEOUT => 10,
			CURLOPT_SSL_VERIFYPEER => TRUE, 
			CURLOPT_SSL_VERIFYHOST => 2,
			CURLOPT_CAINFO => GSRequest::$cafile
		);
		
		
		$ch = curl_init();
		curl_setopt_array($ch, ($options + $defaults));
		if(!$result = curl_exec($ch))
		{
			$err = curl_error($ch) ;
			throw new Exception($err);
		}
		curl_close($ch);
		
		
		list($header, $body) = explode("\r\n\r\n", $result, 2); 
		$headers = explode("\r\n", $header);
		foreach($headers as $value) 
		{
			$kvp = explode(":", $value);
			if($kvp[0] == "x-server")
			{
				$this->traceField("server",$kvp[1]);
				break;	
			}
		}	
		
		return $body;
	} 

	/**
	* Converts a GSDictionary to a query string
	* @param params
	* @return
	*/
	public static function buildQS($params) 
	{
		$val;
		$ret = "";
		foreach($params->getKeys() as $key) 
		{
			$val = $params->getString($key);
			if (isset($val))
			{
				$ret .="$key=".$val;
			}
			$ret .='&';
		}
		return $ret;
	}

	private static function getOAuth1Signature($key, $httpMethod, $url, $isSecureConnection, $requestParams) 
	{
		// Create the BaseString.
		$baseString = self::calcOAuth1BaseString($httpMethod, $url, $isSecureConnection, $requestParams);
		return SigUtils::calcSignature($baseString,$key);
	}
	
	private static function calcOAuth1BaseString($httpMethod, $url, $isSecureConnection, $requestParams) 
	{

		
		$normalizedUrl = "";
		$u = parse_url($url);
		$protocol = strtolower($u["scheme"]);
		
		if(array_key_exists('port',$u))
		{
			$port = $u['port'];
		}
		else		
			$port = null;
		
		$normalizedUrl .= $protocol."://";
		$normalizedUrl .= strtolower($u["host"]);
		
		if  ( $port != ""  && (($protocol=="http" && $port!=80) || ($protocol=="https" && $port!=443))) 
		{
			$normalizedUrl .= ':'.$port;
        }	        
		$normalizedUrl .= $u["path"];
		
		// Create a sorted list of query parameters
		$amp = "";
		$queryString = "";
		$keys = $requestParams->getKeys();
		sort($keys);
		foreach($keys as $key) 
		{
			$value = $requestParams->getString($key);
			if (!empty($value))
			{
				$queryString .= $amp.$key."=".self::UrlEncode($value);
				$amp = "&";
			}
		}
		
		// Construct the base string from the HTTP method, the URL and the parameters 
		$baseString = strtoupper($httpMethod)."&".self::UrlEncode($normalizedUrl)."&".self::UrlEncode($queryString);
		return $baseString;

	}	
	
	public static function UrlEncode($value) 
	{
		if ($value === false)
		{
			return $value;
		}
		else
		{
			return str_replace('%7E', '~', rawurlencode($value));
		}
	}
	
	private function traceField($name,$value)
	{
		array_push($this->traceLog,$name."=". $value);
	}
	
}


/**
 * Wraps the server's response.
 * If the request was sent with the format set to "xml", the getData() will return null and you should use getResponseText() instead.
 * We only parse response text into GSDictionary if request format is set "json" which is the default. 
 *
 */
class GSResponse 
{
	private $errorCode = 0;
	private $errorMessage = null;
	private $rawData = "";
	private $data; //GSDictionary 
	private static $errorMsgDic;
	private $params = null;
	private $method = null;
	private $traceLog = null;
	
	public static function Init(){
		self::$errorMsgDic = new GSDictionary();
		self::$errorMsgDic->put(400002, "Required parameter is missing");
		self::$errorMsgDic->put(500000, "General server error");
	}
	

	public function getErrorCode() 
	{ 
		return $this->errorCode; 
	}
	
	public function getErrorMessage() {
		if (isset($this->errorMessage)) 
			return $this->errorMessage;
		else
		{

			if ($this->errorCode==0 || !self::$errorMsgDic->containsKey((int)$this->errorCode)) 
				return "";
			else
				return self::$errorMsgDic->getString($this->errorCode);
		}
	}
	
	public function getResponseText() 
	{ 
		return $this->rawData; 
	}
	
	public function getData() 
	{ 
		return $this->data; 
	}
	
  	/* GET BOOLEAN */
	public function getBool($key, $defaultValue=GSDictionary::DEFAULT_VALUE) 
	{
		return $this->data->getBool($key,$defaultValue);
	}

	/* GET INTEGER */
	public function getInt($key, $defaultValue=GSDictionary::DEFAULT_VALUE) 
	{
		return $this->data->getInt($key,$defaultValue);
	}
	
	/* GET LONG */
	public function getLong($key, $defaultValue=GSDictionary::DEFAULT_VALUE) 
	{
		return $this->data->getLong($key,$defaultValue);
	}
		

	/* GET STRING */
	public function getString($key, $defaultValue=GSDictionary::DEFAULT_VALUE) 
	{		
		return $this->data->getString($key,$defaultValue);
	}

	/* GET GSOBJECT */
	public function getObject($key) 
	{
		return $this->data->getObject($key);
	}
	
	/* GET GSOBJECT[] */
	public function getArray($key)
	{
		return $this->data->getArray($key);	
	}
	
	
	/* C'tor */
    public function __construct($method,$responseText=null,$params=null,$errorCode=null,$errorMessage=null,$traceLog=null)  
    {
    	
    	$this->traceLog = $traceLog;
    	$this->method = $method;
    	if(empty($params))
    		$this->params = new GSDictionary();
    	else
			$this->params=$params;

		if(!empty($responseText))
		{
			$this->rawData = $responseText;
			if(strpos(ltrim($responseText),"{") !== false)
			{
				
				$this->data = new GSDictionary($responseText);
				if(isset($this->data))
				{
					if ($this->data->containsKey("errorCode"))
					{
						$this->errorCode = $this->data->getInt("errorCode");
					}
					if ($this->data->containsKey("errorMessage"))
					{
						$this->errorMessage = $this->data->getString("errorMessage");
					}
				}
			}
			else
	        {   
	            $matches= array();
	            preg_match("~<errorCode\s*>([^<]+)~", $this->rawData, $matches);     
	            $errCodeStr =  $matches[1];
	            if ($errCodeStr!=null)
	            {
	            	preg_match("~<errorMessage\s*>([^<]+)~", $this->rawData, $matches);     
	            	$this->errorCode = (int)$errCodeStr;
	            	$this->errorMessage = $matches[1];
	            }
	        }

		}
		else{

			$this->errorCode = $errorCode;
			$this->errorMessage = $errorMessage != null ? $errorMessage : self::getErrorMessage();
			$this->populateClientResponseText();
		}
    }
    
    private function populateClientResponseText()
    {
        if ($this->params->getString("format","json"))
        {
            $this->rawData = "{errorCode:" . $this->errorCode . ",errorMessage:\"" . $this->errorMessage . "\"}";
        }
        else
        {
        	$sb = array(
        		"<?xml version=\"1.0\" encoding=\"utf-8\"?>"
        		,"<".$this->method."Response xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"urn:com:gigya:api http://socialize-api.gigya.com/schema\" xmlns=\"urn:com:gigya:api\">"
        		,"<errorCode>".$this->errorCode."</errorCode>"
        		,"<errorMessage>".$this->errorMessage."</errorMessager>"
        		,"</".$this->method."Response>"
        	);
 

        	$this->rawData = implode("\r\n",$sb);
        }
    }
    
	public function getLog()
	{
		return implode("\r\n",$this->traceLog);
	}
    
	
	public function __toString()
	{
		$sb = "";
		$sb .= "\terrorCode:";
		$sb .= $this->errorCode;
		$sb .= "\n\terrorMessage:";
		$sb .= $this->errorMessage;
		$sb .= "\n\tdata:";
		$sb .= $this->data;
		return $sb;
	}
}
GSResponse::Init();


/**
 * Used for passing parameters when issueing requests e.g. GSRequest.send
 * As well as returning response data e.g. GSResponse.getData
* @version    1.0
*/

class GSDictionary { 	 
  private $map;
  
  /* PUBLIC INTERFACE */
  /**
  * Construct a GSDictionary from json string, throws excpetion.
  * @param json the json formatted string
  * @throws Exception if unable to parse json
  */
  public function __construct($json=null)  
  {
    $this->map =  array();   
	if(!empty($json)){
		
		//parse json string.
		if(gettype($json) == 'string')
		{
			$obj = json_decode($json,false);
		}
		else
		{
			$obj = $json;
		}
		self::processJsonObject($obj,$this);

		
	}
  }
  
  
   /* Put */
   const DEFAULT_VALUE = '@@EMPTY@@';
   
   public function put($key,$value)
   {
	  $this->map[$key] = $value;
   }
  
  
   private function get($key,$defaultValue) 
   {
   		
		if (array_key_exists($key, $this->map)) {
			return $this->map[$key];
		}

		if($defaultValue !== GSDictionary::DEFAULT_VALUE)
		{
			return $defaultValue;
		}
		throw new GSKeyNotFoundException($key);
   }
  	/* GET BOOLEAN */
	public function getBool($key, $defaultValue=GSDictionary::DEFAULT_VALUE) 
	{
		return (bool)$this->get($key,$defaultValue);
	}

	/* GET INTEGER */
	public function getInt($key, $defaultValue=GSDictionary::DEFAULT_VALUE) 
	{
		return (int)$this->get($key,$defaultValue);
	}
	
	/* GET LONG */
	public function getLong($key, $defaultValue=GSDictionary::DEFAULT_VALUE) 
	{
		return (float)$this->get($key,$defaultValue);
	}
		

	/* GET STRING */
	public function getString($key, $defaultValue=GSDictionary::DEFAULT_VALUE) 
	{		
		return $this->get($key,$defaultValue);
	}

	/* GET GSOBJECT */
	public function getObject($key) 
	{
		return (object)$this->get($key,null);
	}
	
	/* GET GSOBJECT[] */
	public function getArray($key)
	{
		return $this->get($key,null);	
	}
	
	/**
	 * Parse parameters from URL into the dictionary
	 * @param url 
	 */
	public function parseURL($url)
	{
		try {
			$u = parse_url($url);
			if(isset($u["query"]))
	        	$this->parseQueryString($u["query"]);
	        if(isset($u["fragment"]))
	        	$this->parseQueryString($u["fragment"]);
	    } catch (Exception $e) {
	    } 
	}
	
	
	/**
	 * Parse parameters from query string
	 * @param qs
	 */
	public function parseQueryString($qs)
	{
		if (!isset($qs)) return;
		parse_str($qs, $this->map);
	}
	
	public function containsKey($key)
	{
		 return array_key_exists($key, $this->map);
	}
	
	public function remove($key)
	{
		unset($this->map[$key]);
	}
	
	public function clear()
	{
		unset($this->map);
		$this->map = array();
	}
	
	public function getKeys(){
		return array_keys($this->map);
	}
	
	public function __toString() {
		return $this->toJsonString();
	}
	
	public function toString() {
		return $this->toJsonString();
	}
	
	public function toJsonString()
	{
		try {
			return json_encode($this->map);
		} catch (Exception $e)
		{
			return null;
		}
	}

	private static function processJsonObject($jo, $parentObj)
	{
		foreach ($jo as $name=>$value) {
			
			//array
			if(is_array(($value)))
			{

				$childArray = array();
				foreach($value as $key=>$val)
				{
					$childArray[] = new GSDictionary($val);
				}
				
				$parentObj->put($name, $childArray);
			}
			//object
			elseif (is_object($value)) 
			{
				$childObj  = new GSDictionary();
				$parentObj->put($name, $childObj);
				self::processJsonObject($value, $childObj);
				

			}
			//primitive
			else{
				$parentObj->put($name, $value);
			}
		}
		
		return $parentObj;

	}
	
}

class SigUtils
{
	 public static function validateUserSignature($UID, $timestamp, $secret, $signature) 
	{
		$baseString = $timestamp."_".$UID;
		$expectedSig = self::calcSignature($baseString, $secret); 
		return $expectedSig == $signature;
	}
	
	public static function validateFriendSignature($UID, $timestamp, $friendUID, $secret, $signature)
	{
		$baseString = $timestamp."_".$friendUID."_".$UID;
		$expectedSig = self::calcSignature($baseString, $secret); 
		return $expectedSig == $signature;
	}	
	
	public static function calcSignature($baseString,$key)
	{
		$baseString = utf8_encode($baseString);
		$rawHmac = hash_hmac("sha1", utf8_encode($baseString), base64_decode($key), true);
		$signature = base64_encode($rawHmac); 
		return $signature;
	}
}
?>