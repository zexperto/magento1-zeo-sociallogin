<?php

class Zeo_SocialLogin_Model_Google
{

    static function getGoogleClientID()
	{
	    $client_id=Mage::getStoreConfig('zeo_sociallogin_setting/google/client_id');
	    return  $client_id;
	}
	static function getGoogleClientSecret()
	{
	    $secret_id=Mage::getStoreConfig('zeo_sociallogin_setting/google/client_secret');
	    return  $secret_id;
	}
	static function getGoogleCallbackUrl()
	{
	    //$callback_url='http://localhost/facebook/using-js/sfb-callback.php';
	    $callback_url= Mage::getUrl('sociallogin/googlelogin');//'http://localhost/facebook/using-js/sfb-callback.php';
	    return  $callback_url;
	}
	
	static function get_oauth2_token($code) {
        $client_id= Mage::getModel("sociallogin/google")->getGoogleClientID();
        $client_secret= Mage::getModel("sociallogin/google")->getGoogleClientSecret();
        $redirect_uri= Mage::getModel("sociallogin/google")->getGoogleCallbackUrl();
	    
	
	    $oauth2token_url = "https://accounts.google.com/o/oauth2/token";
	    $clienttoken_post = array(
	        "code" => $code,
	        "client_id" => $client_id,
	        "client_secret" => $client_secret,
	        "redirect_uri" => $redirect_uri,
	        "grant_type" => "authorization_code"
	    );
	
	    $curl = curl_init($oauth2token_url);
	
	    curl_setopt($curl, CURLOPT_POST, true);
	    curl_setopt($curl, CURLOPT_POSTFIELDS, $clienttoken_post);
	    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
	    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	       
	    $json_response = curl_exec($curl);
	    curl_close($curl);
	
	    $authObj = json_decode($json_response);
	
	    if (isset($authObj->refresh_token)){
	        global $refreshToken;
	        $refreshToken = $authObj->refresh_token;
	    }
	    $accessToken = $authObj->access_token;
	    return $accessToken;
	}
    static function call_api($accessToken,$url){
	$curl = curl_init($url);
 
	curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);	
	$curlheader[0] = "Authorization: Bearer " . $accessToken;
	curl_setopt($curl, CURLOPT_HTTPHEADER, $curlheader);

	$json_response = curl_exec($curl);
	curl_close($curl);
		
	$responseObj = json_decode($json_response);
	return $responseObj;	    
}
	
}