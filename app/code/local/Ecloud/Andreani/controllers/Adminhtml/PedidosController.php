<?php
/**
 * @version   0.1.8 04.07.2014
 * @author    ecloud solutions http://www.ecloudsolutions.com <info@ecloudsolutions.com>
 * @copyright Copyright (C) 2010 - 2014 ecloud solutions ®
 */
?><?php require_once Mage::getBaseDir('lib') . '/Andreani/wsseAuth.php';
class Ecloud_Andreani_Adminhtml_PedidosController extends Mage_Adminhtml_Controller_Action
{

    public function indexAction()
    {
    	$this->_title($this->__('Andreani'))->_title($this->__('Estado de pedidos'));
        $this->loadLayout();
        $this->_setActiveMenu('andreani/andreani');
        $this->_addContent($this->getLayout()->createBlock('andreani/adminhtml_pedidos'));
        $this->renderLayout();
    }

    public function gridAction()
    {
		$this->_title($this->__('Andreani'))->_title($this->__('Estado de pedidos'));
        $this->loadLayout();
        $this->_setActiveMenu('andreani/andreani');
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('andreani/adminhtml_pedidos_grid')->toHtml()
        );
    }

    public function exportEcloudCsvAction()
    {
        $fileName = 'pedidos_andreani.csv';
        $grid = $this->getLayout()->createBlock('andreani/adminhtml_andreani_pedidos_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    public function exportEcloudExcelAction()
    {
        $fileName = 'pedidos_andreani.xml';
        $grid = $this->getLayout()->createBlock('andreani/adminhtml_andreani_pedidos_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }

    public function massEliminarAction()
	{
		$ids = $this->getRequest()->getParam('id');
		if(!is_array($ids)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('andreani')->__('Por favor seleccionar una orden!'));
		} else {
			try {
				foreach ($ids as $id) {
					//Mage::getModel('andreani/order')->load($id)->delete();
					Mage::getModel('andreani/order')->load($id)->setData("estado","Eliminada")->save();
				}
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('andreani')->__('Se han eliminado %d registro(s).', count($ids)));
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		}
		$this->_redirect('*/*/index');
	}

	public function massEntregadoAction()
	{
		$ids = $this->getRequest()->getParam('id');

		if(!is_array($ids)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('andreani')->__('Por favor seleccionar una orden!'));
		} else {
			try {
				foreach ($ids as $id) {
					Mage::getModel('andreani/order')->load($id)->setData("estado","Entregado")->save();
				}
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('andreani')->__('Se han actualizado %d registro(s).', count($ids)));
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		}
		$this->_redirect('*/*/index');
	}

	public function massPendienteAction()
	{
		$ids = $this->getRequest()->getParam('id');
		if(!is_array($ids)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('andreani')->__('Por favor seleccionar una orden!'));
		} else {
			try {
				foreach ($ids as $id) {
					Mage::getModel('andreani/order')->load($id)->setData("estado","Pendiente")->save();
				}
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('andreani')->__('Se han actualizado %d registro(s).', count($ids)));
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		}
		$this->_redirect('*/*/index');
	}

    public function viewAction()
    {
        $id = (int) $this->getRequest()->getParam('id');

        // 1. Traemos los datos de la tabla "andreani_order" según el OrderId[0] y asignarla a $datos
		$collection = Mage::getModel('andreani/order')->getCollection()
        	->addFieldToFilter('id', $id);
        $collection->getSelect()->limit(1);

        if (!$collection) {
        	Mage::log("Andreani :: no existe la orden en la tabla andreani_order.");
        	return;
        }
        foreach($collection as $thing) {
		    $datos = $thing->getData();
		}

		// 2. Conectarse a eAndreani
		if (Mage::getStoreConfig('carriers/andreaniconfig/testmode',Mage::app()->getStore()) == 1) {
                $url  = 'https://www.e-andreani.com/eAndreaniWSStaging/Service.svc?wsdl';
        } else {
                $url = "https://www.e-andreani.com/eAndreaniWS/Service.svc?wsdl";
        }

		if ( $datos['cod_tracking'] != '' ) {
			try {
				$options = array(
					'soap_version' 	=> SOAP_1_2,
					'exceptions'	=> 1,
					'trace' 		=> 1,
					'style' 		=> SOAP_DOCUMENT,
					'encoding'		=> SOAP_LITERAL,
				);
				
				$optRequest = array();
				$optRequest["ObtenerTrazabilidad"] = array(
					'Pieza' => array(
						'NroPieza'		=> '',
						'NroAndreani'	=> $datos['cod_tracking'],
						'CodigoCliente'	=> $datos['cliente']
					));
				$client = new SoapClient($url, $options);
				$request = $client->__soapCall("ObtenerTrazabilidad", $optRequest);

				$texto	=  $request->Pieza->NroPieza . "\n";
				$texto .= "Nombre del Envio: " . $request->Pieza->Envios->Envio->NombreEnvio .  "\n";
				$texto .= "Código de tracking: " . $request->Pieza->Envios->Envio->NroAndreani . "\n";
				$texto .= "Fecha de alta: " . $request->Pieza->Envios->Envio->FechaAlta . "\n";
				

				foreach( $request->Pieza->Envios->Envio->Eventos as $indice => $valor ) 
				{
					$texto .= "Eventos: " . "\n\n"; 
					$texto .= "Fecha del evento: " . $valor->Fecha . "\n";
					$texto .= "Estado del envio: " . $valor->Estado . "\n";
					$texto .= "Motivo: " . $valor->Motivo . "\n";
					$texto .= "Sucursal: " . $valor->Sucursal . "\n";
					$texto .= "------------------ \n";
				}
			
				Mage::getModel('andreani/order')->load($id)->setData("tracking",$texto)->save();

			} catch (SoapFault $e) {
				Mage::log(print_r($e,true));
			}
		} else {
			$texto =  "El envío se encuentra pendiente. Diríjase a 'Ventas->Pedidos' para dar comienzo al proceso cuando el mismo se haya realizado";
			Mage::getModel('andreani/order')->load($id)->setData("tracking",$texto)->save();
		}

        if ($id) {
            $order = Mage::getModel('andreani/order')->load($id);
            if (!$order || !$order->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('andreani')->__('No se encontró el ID de la orden'));
                $this->_redirect('*/*/');
            }
        }
        
        Mage::register('order_data', $order);
 
		$this->loadLayout();
		$block = $this->getLayout()->createBlock('andreani/adminhtml_pedidos_edit');
		$this->getLayout()->getBlock('content')->append($block);
		$this->renderLayout();
    }

}
?>