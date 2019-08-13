<?php
class Activated_NewsletterCoupon_Helper_Data extends Mage_Core_Helper_Abstract
{
	/**
	* Check if module is enabled
	*/

	public function isEnabled()
	{
		return (bool) Mage::getStoreConfigFlag('newslettercoupon/view/enabled');
	}
}
?>