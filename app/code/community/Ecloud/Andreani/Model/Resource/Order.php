<?php
/**
 * @version   0.1.12 19.03.2015
 * @author    ecloud solutions http://www.ecloudsolutions.com <info@ecloudsolutions.com>
 * @copyright Copyright (C) 2010 - 2015 ecloud solutions ®
 */
?><?php
class Ecloud_Andreani_Model_Resource_Order extends Mage_Core_Model_Mysql4_Abstract
{
     public function _construct()
     {
         $this->_init('andreani/order', 'id');
     }
}