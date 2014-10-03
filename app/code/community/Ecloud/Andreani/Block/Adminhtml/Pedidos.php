<?php
/**
 * @version   0.1.10 04.08.2014
 * @author    ecloud solutions http://www.ecloudsolutions.com <info@ecloudsolutions.com>
 * @copyright Copyright (C) 2010 - 2014 ecloud solutions ®
 */
?><?php
class Ecloud_Andreani_Block_Adminhtml_Pedidos extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'andreani';
        $this->_controller = 'adminhtml_pedidos';
        $this->_headerText = Mage::helper('adminhtml')->__('Estado de Pedidos de Andreani');
 
        parent::__construct();
        $this->_removeButton('add');
    }

}
?>
