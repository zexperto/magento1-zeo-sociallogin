<?php
class Zeo_SocialLogin_Model_Observer{
	public function addCustomBlockToCustomerLogin(Varien_Event_Observer $observer){
		$block = $observer->getBlock();
		if(($block->getNameInLayout() == 'customer_form_login') 
				&& ($child = $block->getChild('zeo.sociallogin.buttons'))
			)
		{
			//echo $block->getNameInLayout()."<br/>";
			//echo $block->getChild('zeo.sociallogin.buttons')->toHtml();
			$transport = $observer->getTransport();
			if($transport){
				$html = $transport->getHtml();
				$html .= $child->toHtml();
				$transport->setHtml($html);
			}
		}
		
	}
}