<?php

class Magegiant_Magegiantcore_Model_Mysql4_Feedback extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('magegiantcore/feedback', 'feedback_id');
    }
}