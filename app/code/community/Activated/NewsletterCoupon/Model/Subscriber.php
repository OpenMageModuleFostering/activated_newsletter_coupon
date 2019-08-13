<?php
class Activated_NewsletterCoupon_Model_Subscriber extends Mage_Newsletter_Model_Subscriber
{
    const XML_PATH_NEWSLETTERCOUPON_VIEW_TEMPLATE       = 'newslettercoupon/view/template';
    const XML_PATH_NEWSLETTERCOUPON_VIEW_CONFIRMTEMPLATE       = 'newslettercoupon/view/confirmtemplate';
    const XML_PATH_NEWSLETTERCOUPON_VIEW_IDENTITY       = 'newslettercoupon/view/identity';
	/**
     * Subscribes by email
     *
     * @param string $email
     * @throws Exception
     * @return int
     */
    public function subscribe($email, $coupon_code = null)
    {
        $this->loadByEmail($email);
        $customerSession = Mage::getSingleton('customer/session');

        if(!$this->getId()) {
            $this->setSubscriberConfirmCode($this->randomSequence());
        }

        $isConfirmNeed   = (Mage::getStoreConfig(self::XML_PATH_CONFIRMATION_FLAG) == 1) ? true : false;
        $isOwnSubscribes = false;
        $ownerId = Mage::getModel('customer/customer')
            ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
            ->loadByEmail($email)
            ->getId();
        $isSubscribeOwnEmail = $customerSession->isLoggedIn() && $ownerId == $customerSession->getId();

        if (!$this->getId() || $this->getStatus() == self::STATUS_UNSUBSCRIBED
            || $this->getStatus() == self::STATUS_NOT_ACTIVE
        ) {
            if ($isConfirmNeed === true) {
                // if user subscribes own login email - confirmation is not needed
                $isOwnSubscribes = $isSubscribeOwnEmail;
                if ($isOwnSubscribes == true){
                    $this->setStatus(self::STATUS_SUBSCRIBED);
                } else {
                    $this->setStatus(self::STATUS_NOT_ACTIVE);
                }
            } else {
                $this->setStatus(self::STATUS_SUBSCRIBED);
            }
            $this->setSubscriberEmail($email);
        }

        if ($isSubscribeOwnEmail) {
            $this->setStoreId($customerSession->getCustomer()->getStoreId());
            $this->setCustomerId($customerSession->getCustomerId());
        } else {
            $this->setStoreId(Mage::app()->getStore()->getId());
            $this->setCustomerId(0);
        }

        $this->setIsStatusChanged(true);

        try {
            $this->save();
            if ($isConfirmNeed === true
                && $isOwnSubscribes === false
            ) {
                if (Mage::helper('newslettercoupon')->isEnabled() && $coupon_code != null) {
                    $this->sendConfirmationCouponEmail($coupon_code);
                } else {
                    $this->sendConfirmationRequestEmail();
                }
            } else {
                if (Mage::helper('newslettercoupon')->isEnabled() && $coupon_code != null) {
                    $this->sendCouponEmail($coupon_code);
                } else {
                    $this->sendConfirmationSuccessEmail();
                }
            }

            return $this->getStatus();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Sends out coupon success email
     *
     * @return Mage_Newsletter_Model_Subscriber
     */
    public function sendCouponEmail($coupon_code)
    {
        if ($this->getImportMode()) {
            return $this;
        }

        if(!Mage::getStoreConfig(self::XML_PATH_NEWSLETTERCOUPON_VIEW_TEMPLATE)
           || !Mage::getStoreConfig(self::XML_PATH_NEWSLETTERCOUPON_VIEW_IDENTITY)
        )  {
            return $this;
        }

        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

        $email = Mage::getModel('core/email_template');

        $email->sendTransactional(
            Mage::getStoreConfig(self::XML_PATH_NEWSLETTERCOUPON_VIEW_TEMPLATE),
            Mage::getStoreConfig(self::XML_PATH_NEWSLETTERCOUPON_VIEW_IDENTITY),
            $this->getEmail(),
            $this->getName(),
            array('subscriber'=>$this, 'coupon_code'=>$coupon_code)
        );

        $translate->setTranslateInline(true);

        return $this;
    }

    public function sendConfirmationCouponEmail($coupon_code)
    {
        if ($this->getImportMode()) {
            return $this;
        }

        if(!Mage::getStoreConfig(self::XML_PATH_NEWSLETTERCOUPON_VIEW_CONFIRMTEMPLATE)
           || !Mage::getStoreConfig(self::XML_PATH_NEWSLETTERCOUPON_VIEW_IDENTITY)
        )  {
            return $this;
        }

        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

        $email = Mage::getModel('core/email_template');

        $email->sendTransactional(
            Mage::getStoreConfig(self::XML_PATH_NEWSLETTERCOUPON_VIEW_CONFIRMTEMPLATE),
            Mage::getStoreConfig(self::XML_PATH_NEWSLETTERCOUPON_VIEW_IDENTITY),
            $this->getEmail(),
            $this->getName(),
            array('subscriber'=>$this, 'coupon_code'=>$coupon_code)
        );

        $translate->setTranslateInline(true);

        return $this;
    }
}
?>