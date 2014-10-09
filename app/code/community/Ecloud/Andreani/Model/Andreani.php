<?php
/**
 * @version   0.1.11 09.10.2014
 * @author    ecloud solutions http://www.ecloudsolutions.com <info@ecloudsolutions.com>
 * @copyright Copyright (C) 2010 - 2014 ecloud solutions ®
 */
?><?php
class Ecloud_Andreani_Model_Andreani extends Mage_Core_Model_Abstract
{
     public function _construct()
     {
         parent::_construct();
         $this->_init('andreani/andreani');
     }

}
?>