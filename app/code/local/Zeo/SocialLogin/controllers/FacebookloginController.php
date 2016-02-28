<?php
class Zeo_SocialLogin_FacebookloginController extends Mage_Core_Controller_Front_Action
{

  public function IndexAction()
  {
  	
  	
    $loginUrl         = Mage::helper('customer')->getLoginUrl();
    $accountUrl       = Mage::getUrl('customer/account');
    define("APP_ID", Mage::helper("sociallogin")->getFacebookAppID());
    define("APP_SECRET", Mage::helper("sociallogin")->getFacebookAppSecret());

    $FB_REQUEST_URL   = 'https://graph.facebook.com/oauth/access_token?client_id=%s&redirect_uri=&client_secret=%s&code=%s';

    $cookie_name      = "fbsr_".APP_ID;
    //$facebook_ckookie = $_COOKIE[$cookie_name];
    $facebook_ckookie = Mage::getModel('core/cookie')->get($cookie_name);;

    list($encoded_sig, $payload) = explode('.', $facebook_ckookie, 2);

    $sig = base64_decode(strtr($encoded_sig, '-_', '+/'));
    $data= json_decode(base64_decode(strtr($payload, '-_', '+/')), TRUE);


    if (strtoupper($data['algorithm']) !== 'HMAC-SHA256') {
      Mage::getSingleton('core/session')->addError($this->__('Unknown algorithm. Expected HMAC-SHA256, Please contact the website master'));
      $this->_redirectError($loginUrl);
      return;
    }

    $expected_sig = hash_hmac('sha256', $payload, APP_SECRET, TRUE);

    if ($sig !== $expected_sig) {
      Mage::getSingleton('core/session')->addError($this->__('Bad Signed JSON signature!, Please contact the website master'));
      $this->_redirectError($loginUrl);
      return;

    }


    $code    = $data["code"];
    $url     = sprintf($FB_REQUEST_URL, APP_ID, APP_SECRET,$code);


    $request = curl_init();
    curl_setopt($request, CURLOPT_URL, $url);
    curl_setopt($request, CURLOPT_RETURNTRANSFER, TRUE);
    $data         = curl_exec($request);
    curl_close($request);

    if($data=="")
        $data         = file_get_contents($url);
    $data_array   = explode("=",$data);
    $access_token = $data_array[1];

    $user_details = "https://graph.facebook.com/me?fields=first_name,last_name,email&access_token=" .$access_token;

    $response     = file_get_contents($user_details);
    $user         = json_decode($response);
    $result       = Mage::helper('sociallogin')->processFacebookUser($user);
  	  if ($result["error"] == false) {
      Mage::getSingleton('core/session')->addSuccess($this->__('You have succesfully logged in using Facebook'));
      $this->_redirectError($accountUrl);
    }
    else {

      Mage::getSingleton('core/session')->addError($this->__($result["message"]));
      $this->_redirectError($loginUrl);

    }
    return;
  }
}