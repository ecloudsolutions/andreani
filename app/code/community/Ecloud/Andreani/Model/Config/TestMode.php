<?php
/**
 * @version   0.1.12 19.03.2015
 * @author    ecloud solutions http://www.ecloudsolutions.com <info@ecloudsolutions.com>
 * @copyright Copyright (C) 2010 - 2015 ecloud solutions ®
 */
?>

<?php

class Ecloud_Andreani_Model_Config_TestMode
{

   /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => '1', 'label'=>Mage::helper('adminhtml')->__('Habilitado')),
            array('value' => '0', 'label'=>Mage::helper('adminhtml')->__('Deshabilitado')),
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            '1' => Mage::helper('adminhtml')->__('Habilitado'),
            '0' => Mage::helper('adminhtml')->__('Deshabilitado'),
        );
    }

}
