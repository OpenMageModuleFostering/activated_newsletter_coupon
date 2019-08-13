<?php
class Activated_NewsletterCoupon_Model_NewsletterCoupon
{
	protected function _construct()
	{
		$this->_init('newslettercoupon/newslettercoupon');
	}

	public function toOptionArray()
	{
		return $this->createRuleArray();
	}

	private function createRuleArray()
	{
		$rules = Mage::getModel('salesrule/rule')->getCollection();
		$array = array();

		foreach ($rules as $rule) {
			$array[] = array('value' => $rule->getRuleId(), 'label' => $rule->getName());
		}

		return $array;
	}
}