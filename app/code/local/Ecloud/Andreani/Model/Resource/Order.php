<?php
/**
 * @version   0.1.7 03.07.2014
 * @author    ecloud solutions http://www.ecloudsolutions.com <info@ecloudsolutions.com>
 * @copyright Copyright (C) 2010 - 2014 ecloud solutions ®
 */
?><?php
class Ecloud_Andreani_Model_Resource_Order extends Mage_Core_Model_Mysql4_Abstract
{
     public function _construct()
     {
         $this->_init('andreani/order', 'id');
     }
}