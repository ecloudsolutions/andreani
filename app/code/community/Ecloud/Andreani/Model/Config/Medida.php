<?php
/**
 * @version   0.1.12 19.03.2015
 * @author    ecloud solutions http://www.ecloudsolutions.com <info@ecloudsolutions.com>
 * @copyright Copyright (C) 2010 - 2015 ecloud solutions ®
 */
?><?php
class Ecloud_Andreani_Model_Config_Medida
{

   /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'gramos', 'label'=>Mage::helper('adminhtml')->__('gramos / cm3')),
            array('value' => 'kilos', 'label'=>Mage::helper('adminhtml')->__('kg / m3')),
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
            'gramos' => Mage::helper('adminhtml')->__('gramos / cm3'),
            'kilos' => Mage::helper('adminhtml')->__('kg / m3'),
        );
    }

}
