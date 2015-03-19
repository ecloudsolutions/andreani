<?php
/**
 * @version   0.1.12 19.03.2015
 * @author    ecloud solutions http://www.ecloudsolutions.com <info@ecloudsolutions.com>
 * @copyright Copyright (C) 2010 - 2015 ecloud solutions ®
 */
?><?php
class Ecloud_Andreani_Model_Config_Metodo
{

   /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'basico', 'label'=>Mage::helper('adminhtml')->__('Básico')),
            array('value' => 'medio', 'label'=>Mage::helper('adminhtml')->__('Medio')),
            array('value' => 'completo', 'label'=>Mage::helper('adminhtml')->__('Completo')),
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
            'basico' => Mage::helper('adminhtml')->__('Básico'),
            'medio' => Mage::helper('adminhtml')->__('Medio'),
            'completo' => Mage::helper('adminhtml')->__('Completo'),
        );
    }

}
