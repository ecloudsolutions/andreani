<?php
/**
 * @version   0.1.12 19.03.2015
 * @author    ecloud solutions http://www.ecloudsolutions.com <info@ecloudsolutions.com>
 * @copyright Copyright (C) 2010 - 2015 ecloud solutions ®
 */
?><?php 
class Ecloud_Andreani_Adminhtml_Tracking_PedidosController extends Mage_Adminhtml_Controller_Action
{
 
    public function indexAction()
    {
        $this->loadLayout()->_setActiveMenu('andreani/pedidos');
        $this->_addContent($this->getLayout()->createBlock('andreani/adminhtml_pedidos'));
        $this->renderLayout();
    }
}
?>