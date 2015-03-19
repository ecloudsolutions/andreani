<?php
/**
 * @version   0.1.12 19.03.2015
 * @author    ecloud solutions http://www.ecloudsolutions.com <info@ecloudsolutions.com>
 * @copyright Copyright (C) 2010 - 2015 ecloud solutions ®
 */
?>
<?php require_once Mage::getBaseDir('lib') . '/Andreani/wsseAuth.php';
class Ecloud_Andreani_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function getTrackingpopup($tracking) {

	        $collection = Mage::getModel('andreani/order')->getCollection()
	        	->addFieldToFilter('cod_tracking', $tracking);
	        $collection->getSelect()->limit(1);

	        if (!$collection) {
	        	Mage::log("Andreani :: no existe la orden en la tabla andreani_order.");
	        	return false;
	        }

	        foreach($collection as $thing) {
			    $datos = $thing->getData();
			}

			if (Mage::getStoreConfig('carriers/andreaniconfig/testmode',Mage::app()->getStore()) == 1) {
	            $url  = "https://www.e-andreani.com/eAndreaniWSStaging/Service.svc?wsdl";
	        } else {
	            $url  = "https://www.e-andreani.com/eAndreaniWS/Service.svc?wsdl";
	        }

			$datos["username"]	= Mage::getStoreConfig('carriers/andreaniconfig/usuario',Mage::app()->getStore());
			$datos["password"]  = Mage::getStoreConfig('carriers/andreaniconfig/password',Mage::app()->getStore());

			if ($datos["username"] == "" OR $datos["password"] == "") {
				Mage::log("Andreani :: no existe nombre de usuario o contraseña para eAndreani");
				return;
			}
	     		
	     	try {
				$options = array(
					'soap_version'	=> SOAP_1_2,
					'exceptions'	=> 1,
					'trace'			=> 1,
					'style'			=> SOAP_DOCUMENT,
					'encoding'		=> SOAP_LITERAL
				);

				$optRequest["ObtenerTrazabilidad"] = array(
					'Pieza' => array(
						'NroPieza'		=> '',
						'NroAndreani'	=> $tracking,
						'CodigoCliente'	=> $datos['cliente']
					));

				$client 	= new SoapClient($url, $options);
				$request 	= $client->__soapCall("ObtenerTrazabilidad", $optRequest);

				foreach( $request->Pieza->Envios->Envio->Eventos as $indice => $valor ) 
				{ 
					$eventos[$indice]["Fecha"] 		= $valor->Fecha;
					$eventos[$indice]["Estado"] 	= $valor->Estado;
					$eventos[$indice]["Motivo"] 	= $valor->Motivo;
					$eventos[$indice]["Sucursal"] 	= $valor->Sucursal;		
				}

				$estadoenvio = array(
					"Nropieza" 					=> 		$request->Pieza->NroPieza,
					"NombreEnvio" 				=> 		$request->Pieza->Envios->Envio->NombreEnvio,
					"Codigotracking" 			=> 		$request->Pieza->Envios->Envio->NroAndreani,
					"FechAlta"					=>		$request->Pieza->Envios->Envio->FechaAlta,
					"Eventos" 					=> 		$eventos
				);

				return $estadoenvio;
			
			} 	catch (SoapFault $e) {
				Mage::log(print_r($e,true));
			}

		}

		public function getWeight() {
			$peso 	= 11;
			$medida = 1000;

	        $cart = Mage::getModel('checkout/cart')->getQuote();
	        foreach ($cart->getAllItems() as $item) {
	            $datos["cantidad"][] 	= $item->getProduct()->getQty();
	            $datos["peso"][] 		= $item->getProduct()->getWeight();
	            $datos["name"][]		= $item->getProduct()->getName();

	            $datos["total"]		 = ($item->getProduct()->getQty() * $item->getProduct()->getWeight() * $medida) + $datos["total"];

	        }

			return $datos;
		}

}
?>