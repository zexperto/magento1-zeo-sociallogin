<?php

class Zeo_SocialLogin_GoogleloginController extends Mage_Core_Controller_Front_Action
{

    public function IndexAction()
    {
        $loginUrl = Mage::helper('customer')->getLoginUrl();
        $accountUrl = Mage::getUrl('customer/account');
        
        $client_id = Mage::getModel("sociallogin/google")->getGoogleClientID();
        $client_secret = Mage::getModel("sociallogin/google")->getGoogleClientSecret();
        $redirect_uri = Mage::getModel("sociallogin/google")->getGoogleCallbackUrl();
        
        $scope = "https://www.googleapis.com/auth/userinfo.email"; // google scope to access
        $state = "profile"; // optional
                            // $access_type = "offline"; //optional - allows for retrieval of refresh_token for offline access
                            // $google_loginUrl = sprintf("https://accounts.google.com/o/oauth2/auth?scope=%s&state=%s&redirect_uri=%s&response_type=code&client_id=%s",$scope,$state,$redirect_uri,$client_id);
        
        $code = Mage::app()->getRequest()->getParam("code");
        
        if (isset($code)) {
            $accessToken = Mage::getModel("sociallogin/google")->get_oauth2_token($code);
            Mage::getSingleton('core/session')->setZeoGoogleAccessToken($accessToken);
        }
        
        $accessToken = Mage::getSingleton('core/session')->getZeoGoogleAccessToken();
        if (isset($accessToken)) {
            $accountObj = Mage::getModel("sociallogin/google")->call_api($accessToken, "https://www.googleapis.com/oauth2/v1/userinfo");
            $your_name = $accountObj->name;
            $email = $accountObj->email;
            $first_namee = $accountObj->given_name;
            $last_name = $accountObj->family_name;
            
            $user_data=array(
                "first_name"=>$first_namee,
                "last_name"=>$last_name,
                "email"=>$email,
            );
          
            $result       = Mage::helper('sociallogin')->processUser($user_data);
            if ($result["error"] == false) {
                Mage::getSingleton('core/session')->addSuccess($this->__('You have succesfully logged in using Google'));
                $this->_redirectError($accountUrl);
            }
            else {
            
                Mage::getSingleton('core/session')->addError($this->__($result["message"]));
                $this->_redirectError($loginUrl);
            
            }
            
            Mage::getSingleton('core/session')->setZeoGoogleAccessToken(null);
        }
        $error = Mage::app()->getRequest()->getParam("error");
        if (isset($error)) {
            Mage::getSingleton('core/session')->addError($error);
        }
        $this->_redirectError($loginUrl);
        return;
    }
}