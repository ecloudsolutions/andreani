<?php
/**
 * @version   0.1.12 19.03.2015
 * @author    ecloud solutions http://www.ecloudsolutions.com <info@ecloudsolutions.com>
 * @copyright Copyright (C) 2010 - 2015 ecloud solutions ®
 */
?>
<?php 
class Ecloud_Andreani_Adminhtml_OrdersController extends Mage_Adminhtml_Controller_Action
{
	public function impandreaniAction()
	{
		$orderIds = $this->getRequest()->getParam('order_ids');
		foreach ($orderIds as $orderId) {
			$order = Mage::getModel('sales/order')->load($orderId);
			$metodo = $order->getShippingMethod();
			if( $metodo == "andreaniestandar_andreaniestandar" OR 
				$metodo == "andreaniurgente_andreaniurgente" OR 
				$metodo == "andreanisucursal_andreanisucursal") {
				try {						
						$itemQty =  $order->getItemsCollection()->count();
						$shipment = Mage::getModel('sales/service_order', $order)->prepareShipment($itemQty);
						$shipment = new Mage_Sales_Model_Order_Shipment_Api();
						$shipmentId = $shipment->create( $order->getIncrementId(), array(), 'Enviado por Andreani', true, true);
						Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('andreani')->__('La orden n° '.$order->getIncrementId().' ha sido impuesta correctamente en Andreani'));
					
				}catch (Exception $e) {
					Mage::getSingleton('adminhtml/session')->addError('Ha ocurrido un error al tratar de imponer la orden n° '.$order->getIncrementId());
				}
			}else{
				Mage::getSingleton('adminhtml/session')->addError('La orden n° '.$order->getIncrementId().' no corresponde a ser enviada por Andreani');
			}
		}

		$this->_redirect('adminhtml/sales_order/index');

	}
}

?>
