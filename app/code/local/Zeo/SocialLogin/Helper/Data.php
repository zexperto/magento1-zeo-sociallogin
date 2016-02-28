<?php
class Zeo_SocialLogin_Helper_Data extends Mage_Core_Helper_Abstract
{
	static public function IsActive(){
		return !(boolean)Mage::getStoreConfig("advanced/modules_disable_output/Zeo_SocialLogin");
	}
	static function getFacebookAppID()
	{
		$app_id=Mage::getStoreConfig('zeo_sociallogin_setting/facebook/app_id');
		return  $app_id;
	}
	static function getFacebookAppSecret()
	{
		$app_secret=Mage::getStoreConfig('zeo_sociallogin_setting/facebook/app_secret');
		return  $app_secret;
	}
	static function getFacebookCallbackUrl()
	{
		//$callback_url='http://localhost/facebook/using-js/sfb-callback.php';
		$callback_url= Mage::getUrl('sociallogin/facebooklogin');//'http://localhost/facebook/using-js/sfb-callback.php';
		return  $callback_url;
	}
	
	static function processUser($user_data){
	    $result=array("error"=>false,"message"=>"");
	    $email=$user_data["email"];
	    $oCustomer=Mage::getModel('customer/customer')->getCollection()
	    ->addAttributeToFilter("email",$email)->getFirstItem()	;
	    $customer_id=$oCustomer->getEntityId();
	    if($customer_id==0){
	        $customer_id= Mage::helper('sociallogin')->CreateNewCustomer($user_data);
	    }
	
	    if($customer_id>0){
	        Mage::getSingleton ( 'customer/session' )->loginById ( $customer_id );
	    }else{
	        $result["error"]=true;
	        $result["message"]="Error in login";
	    }
	
	
	    return $result;
	}
	static function CreateNewCustomer($user_data){
		$email=$user_data["email"];
		$first_name=$user_data["first_name"];
		$last_name=$user_data["last_name"];
		$customer=Mage::getModel('customer/customer')
						->setEmail($email)
						->setFirstname($first_name)
						->setLastname($last_name)
						->save();
		$password=$customer->generatePassword();
		$customer->setPassword($password)->save();
		$customer->sendNewAccountEmail('registered', '', $customer->getStoreId());
		
		//Mage::getSingleton('core/session')->addError($password);
		return $customer->getEntityId();
	}
}
