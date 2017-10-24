<?php
/**
 * Apptha
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.apptha.com/LICENSE.txt
 *
 * ==============================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * ==============================================================
 * This package designed for Magento COMMUNITY edition
 * Apptha does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * Apptha does not provide extension support in case of
 * incorrect edition usage.
 * ==============================================================
 *
 * @category    Apptha
 * @package     Apptha_Marketplace
 * @version     1.9.2
 * @author      Apptha Team <developers@contus.in>
 * @copyright   Copyright (c) 2016 Apptha. (http://www.apptha.com)
 * @license     http://www.apptha.com/LICENSE.txt
 *
 */
/**
 * Billing information manangement
 * This class is used for managing billing information like
 * billing address
 * payment methods
 * country
 * logged in status
 */
class Apptha_Onestepcheckout_Block_Onestep_Billing extends Mage_Checkout_Block_Onepage_Billing {
    protected $_address;

    /**
     * Initialize billing address step
     */
    protected function _construct() {
        $this->getCheckout ()->setStepData ( 'billing', array (
                'label' => Mage::helper ( 'checkout' )->__ ( 'Billing Information' ),
                'is_show' => $this->isShow ()
        ) );
        /**
         * check the customer is logged in
         */
        if ($this->isCustomerLoggedIn ()) {
            $this->getCheckout ()->setStepData ( 'billing', 'allow', true );
        }
        $this->settings = Mage::helper ( 'onestepcheckout/checkout' )->loadSettings ();
        parent::_construct ();
    }
    public function getCountryHtmlSelect($type) {
        $helper = Mage::helper ( 'onestepcheckout/checkout' );
        /**
         * check condition default country id is not equal to empty
         */
        if (isset ( $this->settings ['default_country_id'] )) {
            /**
             * check condition enable geoip is equal to 1
             */
            if ($this->settings ['enable_geoip'] == 1) {
                /**
                 * check condition customer is logged in
                 */
                if (Mage::helper ( 'customer' )->isLoggedIn () == 1) {

                    $countryId = $this->getCountryCode ();
                } else {
                    $countryId = $helper->getGeoIp ()->countryCode;
                }
            } else {
                /**
                 * check the customer logged in status in equal to 1
                 */
                if (Mage::helper ( 'customer' )->isLoggedIn () == 1) {

                    $countryId = $this->getCountryData ();
                } else {
                    $countryId = $this->settings ['default_country_id'];
                }
            }
        }
        /**
         * check condition country id is equal to null
         */
        if (is_null ( $countryId )) {
            $countryId = $this->settings ['default_country_id'];
        }
        $select = $this->getLayout ()->createBlock ( 'core/html_select' )->setName ( $type . '[country_id]' )->setId ( $type . ':country_id' )->setTitle ( Mage::helper ( 'checkout' )->__ ( 'Country' ) )->setClass ( 'validate-select' )->setValue ( $countryId )->setOptions ( $this->getCountryOptions () );
        /**
         * check condition type is equal to shipping
         */
        if ($type === 'shipping') {
            $select->setExtraParams ( 'onchange="shipping.setSameAsBilling(false);"' );
        }

        return $select->getHtml ();
    }
    /**
     * get country data
     */
    public function getCountryData() {
        $customeraddressId = Mage::getSingleton ( 'customer/session' )->getCustomer ()->getDefaultBilling ();
        /**
         * check condition customer address id is equal to not empty
         */
        if ($customeraddressId) {
            $address = Mage::getModel ( 'customer/address' )->load ( $customeraddressId );
            return $address ['country_id'];
        } else {
            return $this->settings ['default_country_id'];
        }
    }

    /**
     * function to get country code
     */
    public function getCountryCode() {
        $customeraddressId = Mage::getSingleton ( 'customer/session' )->getCustomer ()->getDefaultBilling ();
        $helper = Mage::helper ( 'onestepcheckout/checkout' );
        /**
         * check condition customer address id is equal to not empty
         */
        if ($customeraddressId) {
            $address = Mage::getModel ( 'customer/address' )->load ( $customeraddressId );
            return $address ['country_id'];
        } else {
            return $helper->getGeoIp ()->countryCode;
        }
    }
    public function isUseBillingAddressForShipping() {
        /**
         * check condition virtual value is equal to not empty
         */
        if (($this->getQuote ()->getIsVirtual ()) || ! $this->getQuote ()->getShippingAddress ()->getSameAsBilling ()) {
            return true;
        }
        return true;
    }
    public function BillingAddressForShipping() {
        /**
         * check condition virtual value is not empty
         */
        if (($this->getQuote ()->getIsVirtual ())) {
            return false;
        }
        return true;
    }
    /**
     * Return country collection
     *
     * @return Mage_Directory_Model_Mysql4_Country_Collection
     */
    public function getCountries() {
        return Mage::getResourceModel ( 'directory/country_collection' )->loadByStore ();
    }

    /**
     * Return checkout method
     *
     * @return string
     */
    public function getMethod() {
        return $this->getQuote ()->getCheckoutMethod ();
    }

    /**
     * Return Sales Quote Address model
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    public function getAddress() {
        /**
         * check address is equal to null
         */
        if (is_null ( $this->_address )) {
            /**
             * checking customer is logged in
             */
            if ($this->isCustomerLoggedIn ()) {
                $this->_address = $this->getQuote ()->getBillingAddress ();
            } else {
                $this->_address = Mage::getModel ( 'sales/quote_address' );
            }
        }

        return $this->_address;
    }

    /**
     * Return Customer Address First Name
     * If Sales Quote Address First Name is not defined - return Customer First Name
     *
     * @return string
     */
    public function getFirstname() {
        $firstname = $this->getAddress ()->getFirstname ();
        /**
         * check condition first name is empty
         *
         * @return firstname
         */
        if (empty ( $firstname ) && $this->getQuote ()->getCustomer ()) {
            return $this->getQuote ()->getCustomer ()->getFirstname ();
        }
        return $firstname;
    }

    /**
     * Return Customer Address Last Name
     * If Sales Quote Address Last Name is not defined - return Customer Last Name
     *
     * @return string
     */
    public function getLastname() {
        $lastname = $this->getAddress ()->getLastname ();
        /**
         * check condition last name is empty
         *
         * @return lastname
         */
        if (empty ( $lastname ) && $this->getQuote ()->getCustomer ()) {
            return $this->getQuote ()->getCustomer ()->getLastname ();
        }
        return $lastname;
    }

    /**
     * Check is Quote items can ship to
     *
     * @return boolean
     */
    public function canShip() {
        return ! $this->getQuote ()->isVirtual ();
    }
    public function getSaveUrl() {
    }
    /**
     * get the ajax savebilling fields
     *
     * @return boolean
     */
    public function AjaxSaveBillingFields($name) {
        $fields = explode ( ',', $this->settings ['ajax_save_billing_fields'] );

        if (in_array ( $name, $fields )) {
            return true;
        }

        return false;
    }
}