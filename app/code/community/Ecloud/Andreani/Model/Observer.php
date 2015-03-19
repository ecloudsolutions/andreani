<?php
/**
 * @version   0.1.12 19.03.2015
 * @author    ecloud solutions http://www.ecloudsolutions.com <info@ecloudsolutions.com>
 * @copyright Copyright (C) 2010 - 2015 ecloud solutions ®
 */
?><?php require_once Mage::getBaseDir('lib') . '/Andreani/wsseAuth.php';
class Ecloud_Andreani_Model_Observer extends Mage_Core_Model_Session_Abstract {

	/**
	* Event: checkout_type_onepage_save_order
	* @param $observer Varien_Event_Observer
	*/

	/**
	* NOTA:
	* - Llama a la funcion cuando la orden fue creada luego del Checkout y almacena los datos en la tabla "andreani_order"
	*/
	public function andreaniObserver($observer) {
		try {
			// 1. Tomamos todos los datos de la orden
			//fix. seteamos el contrato correcto
			$metodoenvio = $observer->getEvent()->getOrder()->getShippingMethod();
			if($metodoenvio == 'andreaniestandar_andreaniestandar'){
				$datos 		= Mage::getSingleton('core/session')->getAndreaniEstandar();
			}
			if($metodoenvio == 'andreaniurgente_andreaniurgente'){
				$datos 		= Mage::getSingleton('core/session')->getAndreaniUrgente();
			}
			if($metodoenvio == 'andreanisucursal_andreanisucursal'){
				$datos 		= Mage::getSingleton('core/session')->getAndreaniSucursal();
			}


			// fix. setteamos datos de ship porque si la orden viene de admin, vienen vacios
			$ship = $observer->getEvent()->getOrder()->getShippingAddress();
			$datos["nombre"] = $ship->getFirstname();
			$datos["apellido"] = $ship->getLastname();
			$datos["telefono"] = $ship->getTelephone();

			// 2. Buscamos el ID de la orden y increment id
			$OrderId = $observer->getEvent()->getOrder()->getId();
			$OrderIncId = $observer->getEvent()->getOrder()->getIncrementId();

			// 3. Los almacenamos en la tabla "andreani_order"
			$_dataSave = (array(
						'id_orden' 		=> intval($OrderId),
						'order_increment_id' => intval($OrderIncId),
			            'contrato' 		=> $datos["contrato"],
			            'cliente'		=> $datos["cliente"],
			            'direccion' 	=> $datos["direccion"],
			            'localidad' 	=> $datos["localidad"],
			            'provincia' 	=> $datos["provincia"],
			            'cp_destino' 	=> $datos["cpDestino"],
			            'sucursal_retiro' 		=> $datos["sucursalRetiro"],
			            'direccion_sucursal'	=> $datos["DireccionSucursal"],
			            'nombre' 		=> $datos["nombre"],
			            'apellido' 		=> $datos["apellido"],
			            'telefono' 		=> $datos["telefono"],
			            'dni' 			=> $datos["dni"],
			            'email' 		=> $datos["email"],
			            'precio' 		=> $datos["precio"],
			            'valor_declarado' 		=> $datos["valorDeclarado"],
			            'volumen' 		=> $datos["volumen"],
			            'peso' 			=> $datos["peso"],
			            'detalle_productos' 	=> $datos["DetalleProductos"],
			            'categoria_distancia_id'=> $datos["CategoriaDistanciaId"],
			            'categoria_peso' 		=> $datos["CategoriaPeso"],
			            'direccion_sucursal'	=> $datos["DireccionSucursal"],
			            'estado'				=> 'Pendiente'
					));
			$model = Mage::getModel('andreani/order')->addData($_dataSave);
            $model->save();

			} catch (Exception $e) {
				Mage::log("Error: " . $e);
			}
		}

	/**
	* NOTA: Llama a la funcion cuando desde el Admin Panel se ejecuta el "Ship" y luego "Submit Shipment"
	*/
	public function salesOrderShipmentSaveBefore($observer) {

		// 1. Tomamos los datos de la orden segun el ID en la tabla "andreani_order"
		$shipment = $observer->getEvent()->getShipment();
		$order 	  = $shipment->getOrder();
		$OrderId  = $order->getId();

		// Traemos los datos de la tabla "andreani_order" según el OrderId[0] y asignarla a $datos
		$collection = Mage::getModel('andreani/order')->getCollection()
        	->addFieldToFilter('id_orden', $OrderId);
        $collection->getSelect()->limit(1);

        if (!$collection) {
        	Mage::log("Andreani :: no existe la orden en la tabla andreani_order.");
        	return;
        }

        foreach($collection as $thing) {
		    $datos = $thing->getData();
		}

		if (Mage::getStoreConfig('carriers/andreaniconfig/testmode',Mage::app()->getStore()) == 1) {
                $datos["urlConfirmar"]  = "https://www.e-andreani.com/CASAStaging/eCommerce/ImposicionRemota.svc?wsdl";
        } else {
                $datos["urlConfirmar"]	= "https://www.e-andreani.com/CASAWS/eCommerce/ImposicionRemota.svc?wsdl";
        }

		$datos["username"] = Mage::getStoreConfig('carriers/andreaniconfig/usuario',Mage::app()->getStore());
		$datos["password"] = Mage::getStoreConfig('carriers/andreaniconfig/password',Mage::app()->getStore());


		if ($datos["username"] == "" OR $datos["password"] == "") {
			Mage::log("Andreani :: no existe nombre de usuario o contraseña para eAndreani");
			return;
		}

		// Si el envio ya tiene un codigo de tracking no hacemos nada
		if ($datos["cod_tracking"] != ""){
			return;
		}

		// 2. Conectarse a eAndreani
		try {
			$options = array(
				'soap_version'		=> SOAP_1_2,
				'exceptions' 		=> true,
				'trace' 			=> 1,
				'wdsl_local_copy'	=> true
			);
			$wsse_header    = new WsseAuthHeader($datos["username"], $datos["password"]);
            $client         = new SoapClient($datos["urlConfirmar"], $options);
            $client->__setSoapHeaders(array($wsse_header));

			// Limitamos el detalle de productos a 90 caracteres para que lo tome el WS de Andreani
			if (strlen($datos["detalle_productos"]) >= 90){
				$datos["detalle_productos"] = substr($datos["detalle_productos"],0,80) . "...";
			}

			$phpresponse = $client->ConfirmarCompra(array(
				'compra' =>array(
						'Calle'					=> $datos["direccion"],
						'CategoriaDistancia'	=> $datos["categoria_distancia_id"],
						'CategoriaFacturacion'	=> NULL,
						'CategoriaPeso' 		=> $datos["categoria_peso"],
						'CodigoPostalDestino' 	=> $datos["cp_destino"],
						'Contrato' 				=> $datos["contrato"],
						'Departamento' 			=> NULL,
						'DetalleProductosEntrega'=> $datos["detalle_productos"],
						'DetalleProductosRetiro' => $datos["detalle_productos"],
						'Email' 				=> $datos["email"],
						'Localidad' 			=> $datos["localidad"],
						'NombreApellido' 		=> $datos["nombre"] . " " . $datos["apellido"],
						'NombreApellidoAlternativo' => NULL,
						'Numero' 				=> ".",
						'NumeroCelular' 		=> $datos["telefono"],
						'NumeroDocumento' 		=> $datos["dni"],
						'NumeroTelefono' 		=> $datos["telefono"],
						'NumeroTransaccion' 	=> "Orden nro: " . $datos["order_increment_id"],
						'Peso' 					=> $datos["peso"],
						'Piso' 					=> NULL,
						'Provincia' 			=> $datos["provincia"],
						'SucursalCliente' 		=> NULL,
						'SucursalRetiro' 		=> $datos["sucursal_retiro"],
						'Tarifa' 				=> $datos["precio"],
						'TipoDocumento' 		=> "DNI",
						'ValorACobrar' 			=> "", // Si es contrarembolso deberiamos sumar el "ValorDeclarado" -- $datos["precio"]
						'ValorDeclarado' 		=> $datos["valor_declarado"],
						'Volumen' 				=> $datos["volumen"]
					)));

			// 4. Tomamos "NroAndreani" y lo almacenamos como "Tracking number"
			$shipment 	= $observer->getEvent()->getShipment();
			$track = Mage::getModel('sales/order_shipment_track')
			    ->setNumber($phpresponse->ConfirmarCompraResult->NumeroAndreani)
			    ->setCarrierCode('andreani') //carrier code
			    ->setTitle('Andreani');
			$shipment->addTrack($track);
			
			$id = intval($datos["id"]);
			Mage::getModel('andreani/order')->load($id)->setData('cod_tracking',$phpresponse->ConfirmarCompraResult->NumeroAndreani)->save();
			Mage::getModel('andreani/order')->load($id)->setData('recibo_tracking',$phpresponse->ConfirmarCompraResult->Recibo)->save();
			Mage::getModel('andreani/order')->load($id)->setData('estado','Enviado')->save();

		} catch (SoapFault $e) {
			Mage::log("Error: " . $e);
			Mage::throwException(Mage::helper('andreani')->__('Algo ha ido mal con la conexión a Andreani. Intente nuevamente. (envío no generado).'));

		}

	}

	/**
	* NOTA: Despues de guardar el shippment, enviamos el mail al comprador con su tracking code
	*/
	public function salesOrderShipmentSaveAfter($observer) {
		$shipment 	= $observer->getEvent()->getShipment();
		// enviamos el mail con el tracking code
		if($shipment){
			if(!$shipment->getEmailSent()){
				$shipment->sendEmail(true,'');
				$shipment->setEmailSent(true);
				$shipment->save();
			}
		}
	}

	/**
	* Agregar massAction al sales_order
	*/
	public function addMassAction($observer) {
        $block = $observer->getEvent()->getBlock();
        if(($block instanceof Mage_Adminhtml_Block_Widget_Grid_Massaction || $block instanceof Enterprise_SalesArchive_Block_Adminhtml_Sales_Order_Grid_Massaction)
            && $block->getRequest()->getControllerName() == 'sales_order')
        {
            $block->addItem('andreani', array(
                'label' => 'Imponer en Andreani',
                'url' => $block->getUrl('andreani/adminhtml_orders/impandreani'),
                'confirm' => Mage::helper('sales')->__('Desea imponer las ordenes en Andreani?')
            ));
        }
    }

}
?>