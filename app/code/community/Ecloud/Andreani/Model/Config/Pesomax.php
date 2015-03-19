<?php
/**
 * @version   0.1.12 19.03.2015
 * @author    ecloud solutions http://www.ecloudsolutions.com <info@ecloudsolutions.com>
 * @copyright Copyright (C) 2010 - 2015 ecloud solutions ®
 */
?><?php
class Ecloud_Andreani_Model_Config_Pesomax
{

   /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => '30000', 'label'=>Mage::helper('adminhtml')->__('30 Kg')),
            array('value' => '50000', 'label'=>Mage::helper('adminhtml')->__('50 Kg')),
            array('value' => '100000', 'label'=>Mage::helper('adminhtml')->__('100 Kg')),
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
            '30000' => Mage::helper('adminhtml')->__('30 Kg'),
            '50000' => Mage::helper('adminhtml')->__('50 Kg'),
            '100000' => Mage::helper('adminhtml')->__('100 Kg'),
        );
    }

}
