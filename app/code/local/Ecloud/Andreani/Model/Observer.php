<?php
/**
 * @version   0.1.8 04.07.2014
 * @author    ecloud solutions http://www.ecloudsolutions.com <info@ecloudsolutions.com>
 * @copyright Copyright (C) 2010 - 2014 ecloud solutions ®
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
			$datos 		= Mage::getSingleton('core/session')->getAndreani();
			// 2. Buscamos el ID de la orden 
			$OrderId = $observer->getEvent()->getOrder()->getId();
			// 3. Los almacenamos en la tabla "andreani_order"
			$_dataSave = (array(
						'id_orden' 		=> intval($OrderId),
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
						'Numero' 				=> $datos["direccion"],
						'NumeroCelular' 		=> $datos["telefono"],
						'NumeroDocumento' 		=> $datos["dni"],
						'NumeroTelefono' 		=> $datos["telefono"],
						'NumeroTransaccion' 	=> "Transacción nro: " . $datos["id_orden"],
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

			//Enviamos numero Andreani, nos devolvera el url de la constancia que lo almacenaremos en la tabla andreani_order.
			$NroAndreani = $phpresponse->ConfirmarCompraResult->NumeroAndreani;
			$constanciaResponse = $client->ImprimirConstancia(array(
					'entities' =>array(
								'ParamImprimirConstancia' =>array(
										'NumeroAndreani' => $NroAndreani
									))));
			$ConstanciaURL = $constanciaResponse->ImprimirConstanciaResult->ResultadoImprimirConstancia->PdfLinkFile;
			Mage::log("Constancia de entrega URL " . print_r($ConstanciaURL,true));

			$id = intval($datos["id"]);
			Mage::getModel('andreani/order')->load($id)->setData('cod_tracking',$phpresponse->ConfirmarCompraResult->NumeroAndreani)->save();
			Mage::getModel('andreani/order')->load($id)->setData('recibo_tracking',$phpresponse->ConfirmarCompraResult->Recibo)->save();
			Mage::getModel('andreani/order')->load($id)->setData('estado','Enviado')->save();
			Mage::getModel('andreani/order')->load($id)->setData('constancia',$ConstanciaURL)->save();

		} catch (SoapFault $e) {
			Mage::log("Error: " . $e);
		}

	}

}
?>