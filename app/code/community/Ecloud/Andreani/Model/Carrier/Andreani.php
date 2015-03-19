<?php
/**
 * @version   0.1.12 19.03.2015
 * @author    ecloud solutions http://www.ecloudsolutions.com <info@ecloudsolutions.com>
 * @copyright Copyright (C) 2010 - 2015 ecloud solutions ®
 */
?><?php require_once Mage::getBaseDir('lib') . '/Andreani/wsseAuth.php';
    class Ecloud_Andreani_Model_Carrier_Andreani extends Mage_Shipping_Model_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface {  

        protected $_code = '';
        protected $distancia_final_txt  = '';
        protected $duracion_final       = '';
        protected $mode  = '';
        protected $envio = '';

        /** 
        * Recoge las tarifas del método de envío basados ​​en la información que recibe de $request
        * 
        * @param Mage_Shipping_Model_Rate_Request $data 
        * @return Mage_Shipping_Model_Rate_Result 
        */ 

        public function collectRates(Mage_Shipping_Model_Rate_Request $request) {
            $datos["peso"]              = 0;
            $datos["valorDeclarado"]    = 0;
            $datos["volumen"]           = 0;
            $datos["DetalleProductos"]  = "";
            $sku                        = "";
            $freeBoxes                  = 0;
            $pesoMaximo = Mage::getStoreConfig('carriers/andreaniconfig/pesomax',Mage::app()->getStore());

            Mage::getSingleton('core/session')->unsAndreani();

            // Reiniciar variable Sucursales para descachear las Sucursales.
            if(!Mage::getStoreConfig('carriers/andreaniconfig/cache',Mage::app()->getStore())) {
                Mage::getSingleton('core/session')->unsSucursales();
            }

            // Tomamos el attr "medida" segun la configuracion del cliente
            if (Mage::getStoreConfig('carriers/andreaniconfig/medida',Mage::app()->getStore())=="") {
                $datos["medida"] = "gramos";
            } else {
                $datos["medida"] = Mage::getStoreConfig('carriers/andreaniconfig/medida',Mage::app()->getStore());
            }

            if ($datos["medida"]=="kilos") {
                $datos["medida"] = 1000;
            } elseif ($datos["medida"]=="gramos") {
                $datos["medida"] = 1;
            } else {
                $datos["medida"] = 1; //si está vacio: "gramos"
            }
            
            foreach ($request->getAllItems() as $_item) {
                if($sku != $_item->getSku()) {
                    $sku                     = $_item->getSku();
            $price           = floor($_item->getPrice());
                    $datos["peso"]           = ($_item->getQty() * $_item->getWeight() * $datos["medida"]) + $datos["peso"];
                    $datos["valorDeclarado"] = ($_item->getQty() * $price) + $datos["valorDeclarado"];
                    
                    $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $_item->getSku(), array('volumen'));
                    $datos["volumen"] += ($_item->getQty() * $product->getVolumen() * $datos["medida"]);
                    
                    //$datos["volumenstring"] = $_item->getQty() . " x " . $product->getVolumen()  . " x " .  $datos["medida"];
                    //Mage::log("Volumen String: " . print_r($datos["volumenstring"],true));
                    //Mage::log("Volumen: " . print_r($datos["volumen"],true));

                    // Creamos un string con el detalle de cada producto
                    $datos["DetalleProductos"] = "(" . $_item->getQty() . ") " .$_item->getName() . " + " . $datos["DetalleProductos"];

                    // Si la condicion de free shipping está seteada en el producto
                    if ($_item->getFreeShippingDiscount() && !$_item->getProduct()->isVirtual()) {
                        Mage::log("getFreeShippingDiscount: " . print_r($_item->getQty(),true));
                        $freeBoxes += $_item->getQty();
                    }
                }
            }

            // Seteamos las reglas
            if(isset($freeBoxes))   $this->setFreeBoxes($freeBoxes);
            
            $cart   = Mage::getSingleton('checkout/cart');
            $quote  = $cart->getQuote();
            $shippingAddress        = $quote->getShippingAddress();
            $datos["cpDestino"]     = intval($request->getDestPostcode());
            $datos["localidad"]     = $request->getDestCity();
            $datos["provincia"]     = $request->getDestRegionCode();
            $datos["direccion"]     = $request->getDestStreet();
            $datos["nombre"]        = $shippingAddress->getData('firstname');
            $datos["apellido"]      = $shippingAddress->getData('lastname');
            $datos["telefono"]      = $shippingAddress->getData('telephone');
            $datos["email"]         = $shippingAddress->getData('email');
            $datos["dni"]           = $shippingAddress->getData('dni');

            $datos["username"]      = Mage::getStoreConfig('carriers/andreaniconfig/usuario',Mage::app()->getStore());
            $datos["password"]      = Mage::getStoreConfig('carriers/andreaniconfig/password',Mage::app()->getStore());
            $datos["cliente"]       = Mage::getStoreConfig('carriers/andreaniconfig/nrocliente',Mage::app()->getStore());
            $datos["contrato"]      = Mage::getStoreConfig('carriers/andreaniconfig/contrato',Mage::app()->getStore());

            $result = Mage::getModel('shipping/rate_result');
            $method = Mage::getModel('shipping/rate_result_method');

            $error_msg = Mage::helper('andreani')->__("Completá los datos para poder calcular el costo de su pedido.");

            // Optimizacion con OneStepCheckout
            if ($datos["cpDestino"]=="" && $datos["localidad"]=="" && $datos["provincia"]=="" && $datos["direccion"]=="") {
                $error = Mage::getModel('shipping/rate_result_error'); 
                $error->setCarrier($this->_code); 
                $error->setCarrierTitle($this->getConfigData('title')); 
                $error->setErrorMessage($error_msg); 
                return $error;
            }

            $error_msg = Mage::helper('andreani')->__("Su pedido supera el peso máximo permitido por Andreani. Por favor divida su orden en más pedidos o consulte al administrador de la tienda. Gracias y disculpe las molestias.");

            if ($this->_code == "andreaniestandar" & Mage::getStoreConfig('carriers/andreaniestandar/active',Mage::app()->getStore()) == 1) {
                if($datos["peso"] >= $pesoMaximo){
                    $error = Mage::getModel('shipping/rate_result_error'); 
                    $error->setCarrier($this->_code); 
                    $error->setCarrierTitle($this->getConfigData('title')); 
                    $error->setErrorMessage($error_msg); 
                    return $error;
                } else {
                    $response = $this->_getAndreaniEstandar($datos,$request);
                    if(is_string($response)){
                        $error = Mage::getModel('shipping/rate_result_error'); 
                        $error->setCarrier($this->_code); 
                        $error->setCarrierTitle($this->getConfigData('title'));
                        $error->setErrorMessage($response);
                        return $error;
                    } else {
                        $result->append($response);
                    }
                }
            }
            if ($this->_code == "andreaniurgente" & Mage::getStoreConfig('carriers/andreaniurgente/active',Mage::app()->getStore()) == 1) {
                if($datos["peso"] >= $pesoMaximo){
                    $error = Mage::getModel('shipping/rate_result_error'); 
                    $error->setCarrier($this->_code); 
                    $error->setCarrierTitle($this->getConfigData('title')); 
                    $error->setErrorMessage($error_msg); 
                    return $error;
                } else {
                    $response = $this->_getAndreaniUrgente($datos,$request);
                    if(is_string($response)){
                        $error = Mage::getModel('shipping/rate_result_error'); 
                        $error->setCarrier($this->_code); 
                        $error->setCarrierTitle($this->getConfigData('title'));
                        $error->setErrorMessage($response); 
                        return $error;
                    } else {
                        $result->append($response);
                    }
                }
            }
            if ($this->_code == "andreanisucursal" & Mage::getStoreConfig('carriers/andreanisucursal/active',Mage::app()->getStore()) == 1) {
                if($datos["peso"] >= $pesoMaximo){
                    $error = Mage::getModel('shipping/rate_result_error'); 
                    $error->setCarrier($this->_code); 
                    $error->setCarrierTitle($this->getConfigData('title')); 
                    $error->setErrorMessage($error_msg); 
                    return $error;
                } else {
                    $response = $this->_getAndreaniSucursal($datos,$request);
                    if(is_string($response)){
                        $error = Mage::getModel('shipping/rate_result_error'); 
                        $error->setCarrier($this->_code); 
                        $error->setCarrierTitle($this->getConfigData('title')); 
                        $error->setErrorMessage($response); 
                        return $error;
                    } else {
                        $result->append($response);
                    }
                }
            }
 
            return $result;
        }  

        /** 
        * Arma el precio y la información del servicio "Estandar" de Andreani según el parametro $data
        * 
        * @param Datos del usuario y el carrito de compras $data 
        * @return Los datos para armar el Método de envío $rate 
        */  
        protected function _getAndreaniEstandar($datos,$request){
            Mage::log("Andreani Estandar");

            $rate = Mage::getModel('shipping/rate_result_method');
            /* @var $rate Mage_Shipping_Model_Rate_Result_Method */
            $rate->setCarrier($this->_code);
            $rate->setCarrierTitle("Andreani");
            $rate->setMethod($this->_code);

            $datos["contrato"]      = Mage::getStoreConfig('carriers/andreaniestandar/contrato',Mage::app()->getStore());

            if (Mage::getStoreConfig('carriers/andreaniconfig/testmode',Mage::app()->getStore()) == 1) {
                $datos["urlCotizar"]        = 'https://www.e-andreani.com/CasaStaging/eCommerce/CotizacionEnvio.svc?wsdl';
                $datos["urlSucursal"]       = 'https://www.e-andreani.com/CasaStaging/ecommerce/ConsultaSucursales.svc?wsdl';
            } else {
                $datos["urlCotizar"]        = 'https://www.e-andreani.com/CASAWS/eCommerce/CotizacionEnvio.svc?wsdl';
                $datos["urlSucursal"]       = 'https://www.e-andreani.com/CASAWS/ecommerce/ConsultaSucursales.svc?wsdl';
            }

            // Buscamos en eAndreani el costo del envio segun los parametros enviados
            $datos["precio"]                = $this->cotizarEnvio($datos);
            $datos["CategoriaDistanciaId"]  = $this->envio->CategoriaDistanciaId;
            $datos["CategoriaPeso"]         = $this->envio->CategoriaPeso;

            Mage::getSingleton('core/session')->setAndreaniEstandar($datos);

            if ($datos["precio"] == 0) {
                return $texto  = Mage::helper('andreani')->__("Error en la conexión con Andreani. Por favor chequee los datos ingresados en la información de envio y vuelva a intentar.");
            } else {
                $texto  = Mage::getStoreConfig('carriers/andreaniestandar/description',Mage::app()->getStore()) . " {$this->envio->CategoriaDistancia}.";
            }

            $rate->setMethodTitle($texto);

            if($request->getFreeShipping() == true || $request->getPackageQty() == $this->getFreeBoxes()) {
                $shippingPrice = '0.00';
                // cambiamos el titulo para indicar que el envio es gratis
                $rate->setMethodTitle(Mage::helper('andreani')->__('Envío gratis.'));
            } else { 
                $shippingPrice = $this->getFinalPriceWithHandlingFee($datos["precio"]);
            }

            $shippingPrice = $shippingPrice + ($shippingPrice * Mage::getStoreConfig('carriers/andreaniestandar/regla') / 100);
            
            $rate->setPrice($shippingPrice);
            $rate->setCost($shippingPrice);

            return $rate;
        }

        /** 
        * Arma el precio y la información del servicio "Urgente" de Andreani según el parametro $data
        * 
        * @param Datos del usuario y el carrito de compras $data 
        * @return Los datos para armar el Método de envío $rate 
        */ 
        protected function _getAndreaniUrgente($datos,$request){
            Mage::log("Andreani Urgente");

            $rate = Mage::getModel('shipping/rate_result_method');
            /* @var $rate Mage_Shipping_Model_Rate_Result_Method */
            $rate->setCarrier($this->_code);
            $rate->setCarrierTitle("Andreani");
            $rate->setMethod($this->_code);

            $datos["contrato"]      = Mage::getStoreConfig('carriers/andreaniurgente/contrato',Mage::app()->getStore());

            if (Mage::getStoreConfig('carriers/andreaniconfig/testmode',Mage::app()->getStore()) == 1) {
                $datos["urlCotizar"]        = 'https://www.e-andreani.com/CasaStaging/eCommerce/CotizacionEnvio.svc?wsdl';
                $datos["urlSucursal"]       = 'https://www.e-andreani.com/CasaStaging/ecommerce/ConsultaSucursales.svc?wsdl';
            } else {
                $datos["urlCotizar"]        = 'https://www.e-andreani.com/CASAWS/eCommerce/CotizacionEnvio.svc?wsdl';
                $datos["urlSucursal"]       = 'https://www.e-andreani.com/CASAWS/ecommerce/ConsultaSucursales.svc?wsdl';
            }

            // Buscamos en eAndreani el costo del envio segun los parametros enviados
            $datos["precio"]                = $this->cotizarEnvio($datos);
            $datos["CategoriaDistanciaId"]  = $this->envio->CategoriaDistanciaId;
            $datos["CategoriaPeso"]         = $this->envio->CategoriaPeso;

            Mage::getSingleton('core/session')->setAndreaniUrgente($datos);

            if ($datos["precio"] == 0) {
                return $texto  = Mage::helper('andreani')->__("Error en la conexión con Andreani. Por favor chequee los datos ingresados en la información de envio y vuelva a intentar.");
            } else {
                $texto  = Mage::getStoreConfig('carriers/andreaniurgente/description',Mage::app()->getStore()) . " {$this->envio->CategoriaDistancia}.";
            }

            $rate->setMethodTitle($texto); 
            
            if($request->getFreeShipping() == true || $request->getPackageQty() == $this->getFreeBoxes()) {
                $shippingPrice = '0.00';
                // cambiamos el titulo para indicar que el envio es gratis
                $rate->setMethodTitle(Mage::helper('andreani')->__('Envío gratis.'));
            } else { 
                $shippingPrice = $this->getFinalPriceWithHandlingFee($datos["precio"]);
            }

            $shippingPrice = $shippingPrice + ($shippingPrice * Mage::getStoreConfig('carriers/andreaniurgente/regla') / 100);
            
            $rate->setPrice($shippingPrice);
            $rate->setCost($shippingPrice);

            return $rate;
        }

        /** 
        * Arma el precio y la información del servicio "Sucursal" de Andreani según el parametro $data
        * 
        * @param Datos del usuario y el carrito de compras $data 
        * @return Los datos para armar el Método de envío $rate 
        */  
        protected function _getAndreaniSucursal($datos,$request){
            Mage::log("Andreani Sucursal");

            $rate = Mage::getModel('shipping/rate_result_method');
            /* @var $rate Mage_Shipping_Model_Rate_Result_Method */
            $rate->setCarrier($this->_code);
            $rate->setCarrierTitle("Andreani");
            $rate->setMethod($this->_code);
            $metodo = Mage::getStoreConfig('carriers/andreaniconfig/metodo',Mage::app()->getStore());

            $datos["contrato"]      = Mage::getStoreConfig('carriers/andreanisucursal/contrato',Mage::app()->getStore());

            if (Mage::getStoreConfig('carriers/andreaniconfig/testmode',Mage::app()->getStore()) == 1) {
                    $datos["urlCotizar"]        = 'https://www.e-andreani.com/CasaStaging/eCommerce/CotizacionEnvio.svc?wsdl';
                    $datos["urlSucursal"]       = 'https://www.e-andreani.com/CasaStaging/ecommerce/ConsultaSucursales.svc?wsdl';
            } else {
                    $datos["urlCotizar"]        = 'https://www.e-andreani.com/CASAWS/eCommerce/CotizacionEnvio.svc?wsdl';
                    $datos["urlSucursal"]       = 'https://www.e-andreani.com/CASAWS/ecommerce/ConsultaSucursales.svc?wsdl';
            }

            // Buscamos la sucursal mas cercana del cliente segun el CP ingresado
            $sucursales             = $this->consultarSucursales($datos,"sucursal");

            if($sucursales=="nosucursal"){
                return "No hay sucursales cerca de tu domicilio.";
            }elseif ($sucursales->Sucursal == 0) {
                return "Lo siento ha fallado la comunicación con Andreani, por favor vuelve a intentarlo.";
            }          

            $datos["sucursalRetiro"]        = $sucursales->Sucursal;
            $datos["DireccionSucursal"]     = $sucursales->Direccion;

            // Buscamos en eAndreani el costo del envio segun los parametros enviados
            $datos["precio"]                = $this->cotizarEnvio($datos);

            if ($datos["precio"] == 0) {
                return $texto  = Mage::helper('andreani')->__("Error en la conexión con Andreani. Por favor chequee los datos ingresados en la información de envio y vuelva a intentar.");
            } else {
                if($metodo != 'basico'){
                    $texto  = Mage::getStoreConfig('carriers/andreanisucursal/description',Mage::app()->getStore()) . " {$sucursales->Descripcion} ({$sucursales->Direccion}). Estas a {$this->distancia_final_txt} {$this->mode} ({$this->duracion_final}).";
                }else{
                    $texto  = Mage::getStoreConfig('carriers/andreanisucursal/description',Mage::app()->getStore()) . " {$sucursales->Descripcion} ({$sucursales->Direccion}).";
                }
            }

            $datos["CategoriaDistanciaId"]  = $this->envio->CategoriaDistanciaId;
            $datos["CategoriaPeso"]         = $this->envio->CategoriaPeso;

            Mage::getSingleton('core/session')->setAndreaniSucursal($datos);

            $rate->setMethodTitle($texto);
            
            if($request->getFreeShipping() == true || $request->getPackageQty() == $this->getFreeBoxes()) {
                $shippingPrice = '0.00';
                // cambiamos el titulo para indicar que el envio es gratis
                $direSucu  = " Sucursal: {$sucursales->Descripcion} ({$sucursales->Direccion}).";
                $rate->setMethodTitle(Mage::helper('andreani')->__('Envío gratis.') . $direSucu);
            } else { 
                $shippingPrice = $this->getFinalPriceWithHandlingFee($datos["precio"]);
            }

            $shippingPrice = $shippingPrice + ($shippingPrice * Mage::getStoreConfig('carriers/andreanisucursal/regla') / 100);
            
            $rate->setPrice($shippingPrice);
            $rate->setCost($shippingPrice);

            return $rate;
        }

        /**
         * Get allowed shipping methods
         *
         * @return array
         */
        public function getAllowedMethods() {
            return array($this->_code    => $this->getConfigData('name'));
        }

        /**
         * Cotiza el envio de los productos segun los parametros
         *
         * @param $params 
         * @return $costoEnvio
         */
        public function cotizarEnvio($params) {
            try {
                
                $options = array(
                    'soap_version' => SOAP_1_2,
                    'exceptions' => true,
                    'trace' => 1,
                    'wdsl_local_copy' => true
                );

                $wsse_header = new WsseAuthHeader($params["username"], $params["password"]);
                $client = new SoapClient($params["urlCotizar"], $options);
                $client->__setSoapHeaders(array($wsse_header));

                $sucursalRetiro     = array('sucursalRetiro' => "");
                $params = array_merge($sucursalRetiro, $params);
                
                $phpresponse = $client->CotizarEnvio(array(
                    'cotizacionEnvio' =>array(
                        'CPDestino' =>$params["cpDestino"],
                        'Cliente'   =>$params["cliente"],
                        'Contrato'  =>$params["contrato"],
                        'Peso'      =>$params["peso"],
                        'SucursalRetiro'=>$params["sucursalRetiro"],
                        'ValorDeclarado'=>$params["valorDeclarado"],
                        'Volumen'   =>$params["volumen"]
                    )));

                $costoEnvio  = floatval($phpresponse->CotizarEnvioResult->Tarifa);
                $this->envio = $phpresponse->CotizarEnvioResult;

                Mage::log("Cotizar envio: " . print_r($phpresponse->CotizarEnvioResult,true));

                return $costoEnvio;

            } catch (SoapFault $e) {
                Mage::log("Error: " . $e);
                //Mage::getSingleton('core/session')->addError('Error en la conexión con eAndreani. Disculpe las molestias.. vuelva a intentar! <br> En caso de persistir el error contacte al administrador de la tienda.');
            }
        }

        /**
         * Trae las sucursales de Andreani segun los parametros
         *
         * @param $params 
         * @return $costoEnvio
         */
        public function consultarSucursales($params,$metodo) {

            $metodo = Mage::getStoreConfig('carriers/andreaniconfig/metodo',Mage::app()->getStore());
            try {
                // Nos fijamos si ya consultamos la sucursal en Andreani
                if(is_object(Mage::getSingleton('core/session')->getSucursales())) {
                    if($metodo != "sucursal") {
                        Mage::log("Ya buscó la sucursal en Andreani");
                        return Mage::getSingleton('core/session')->getSucursales();
                    } else {
                        //Mage::getSingleton('core/session')->unsGoogleDistance();
                        Mage::log("Google Distance: " . print_r(Mage::getSingleton('core/session')->getGoogleDistance(),true));
                        if(is_object(Mage::getSingleton('core/session')->getGoogleDistance())) {
                            Mage::log("Ya buscó la sucursal en Google Maps");
                            $this->distancia_final_txt = Mage::getSingleton('core/session')->getDistancia();
                            $this->duracion_final      = Mage::getSingleton('core/session')->getDuracion();
                            $this->mode                = Mage::getSingleton('core/session')->getMode();

                            return Mage::getSingleton('core/session')->getGoogleDistance();
                        }
                    }
                }

                $options = array(
                    'soap_version' => SOAP_1_2,
                    'exceptions' => true,
                    'trace' => 1,
                    'wdsl_local_copy' => true
                );

                $wsse_header    = new WsseAuthHeader($params["username"], $params["password"]);
                $client         = new SoapClient($params["urlSucursal"], $options);
                $client->__setSoapHeaders(array($wsse_header));
                
                $phpresponse = $client->ConsultarSucursales(array(
                    'consulta' => array(
                        'CodigoPostal'  =>  $params["cpDestino"],
                        'Localidad'     =>  NULL,
                        'Provincia'     =>  NULL
                )));

                if (is_object($phpresponse->ConsultarSucursalesResult->ResultadoConsultarSucursales)) {
                    Mage::log("Entra si encuentra el CP");
                     // Si no tenemos la direccion del cliente pero SI el CP, deberia mostrarnos la sucursal de nuestra localidad sin calcular la distancia a la misma.
                    $sucursales = $phpresponse->ConsultarSucursalesResult->ResultadoConsultarSucursales;
                    if ($this->_code == "andreanisucursal" && ($metodo == 'medio' || $metodo == 'completo') == 1) { 
                        $sucursales = $this->distancematrix(array('0' => $sucursales),$params["direccion"],$params["localidad"],$params["provincia"]);
                    }
                } else {
                    if($metodo != 'completo'){
                        $sucursales = "nosucursal";                        
                    } else {
                        $phpresponse = $client->ConsultarSucursales(array(
                            'consulta' => array(
                                'CodigoPostal'  =>  NULL,
                                'Localidad'     =>  $params["localidad"],
                                'Provincia'     =>  NULL
                        )));
                        if (is_object($phpresponse->ConsultarSucursalesResult->ResultadoConsultarSucursales) OR is_array($phpresponse->ConsultarSucursalesResult->ResultadoConsultarSucursales)) {
                            Mage::log("Encontro localidad");
                            $sucursales = $phpresponse->ConsultarSucursalesResult->ResultadoConsultarSucursales;
                            if (is_array($sucursales)) {
                                Mage::log("Encontro mas de una localidad");
                                // Consultamos por "localidad" y encontro varios resultados
                                // buscamos en GoogleAPI cual es la sucursal mas cercana segun la direccion del cliente
                                $sucursales = $this->distancematrix($sucursales,$params["direccion"],$params["localidad"],$params["provincia"]); 
                            } else {
                                if ($this->_code == "andreanisucursal") { $sucursales = $this->distancematrix(array('0' => $sucursales),$params["direccion"],$params["localidad"],$params["provincia"]); }
                            }
                        } else {
                            Mage::log("No encontro la localidad busca por provincia");
                            if ($params["provincia"]=="") {
                                $params["provincia"] = NULL;
                                Mage::log("Entra si la provincia esta vacia");
                            }
                            $phpresponse = $client->ConsultarSucursales(array(
                                'consulta' => array(
                                    'CodigoPostal'  =>  NULL,
                                    'Localidad'     =>  NULL,
                                    'Provincia'     =>  $params["provincia"]
                            )));


                            if (is_object($phpresponse->ConsultarSucursalesResult->ResultadoConsultarSucursales) OR is_array($phpresponse->ConsultarSucursalesResult->ResultadoConsultarSucursales)) {
                                Mage::log("Encontro sucursales en la provincia. Si está vacia.. nos trae todas las provincias");
                                $sucursales = $phpresponse->ConsultarSucursalesResult->ResultadoConsultarSucursales;
                                if (is_array($sucursales)) {
                                    Mage::log("Encontro muchas sucursales en la provincia");
                                    // Consultamos por "provincia" y encontro varios resultados
                                    // buscamos en GoogleAPI cual es la sucursal mas cercana segun la direccion del cliente
                                    $sucursales = $this->distancematrix($sucursales,$params["direccion"],$params["localidad"],$params["provincia"]);
                                } else {
                                    if ($this->_code == "andreanisucursal") { $sucursales = $this->distancematrix(array('0' => $sucursales),$params["direccion"],$params["localidad"],$params["provincia"]); }
                                }
                            } else {
                                Mage::log("No encontro la provincia y busca todas las localidades para determinar la mas cercana");
                                // buscar todas las sucursales
                                // buscamos en GoogleAPI cual es la sucursal mas cercana segun la direccion del cliente
                                $phpresponse = $client->ConsultarSucursales(array(
                                    'consulta' => array(
                                        'CodigoPostal'  =>  NULL,
                                        'Localidad'     =>  NULL,
                                        'Provincia'     =>  NULL
                                )));
                                $sucursales = $phpresponse->ConsultarSucursalesResult->ResultadoConsultarSucursales;

                                $sucursales = $this->distancematrix($sucursales,$params["direccion"],$params["localidad"],$params["provincia"]);
                            }
                        }
                    }
                }
                
                Mage::log("Sucursal: " . print_r($sucursales, true));
                Mage::getSingleton('core/session')->setSucursales($sucursales);

                return $sucursales;

            } catch (SoapFault $e) {
                Mage::log("Error: " . $e);
                //Mage::getSingleton('core/session')->addError('Error en la conexión con eAndreani. Disculpe las molestias.. vuelva a intentar! <br> En caso de persistir el error contacte al administrador de la tienda.');
            }
        }

         /**
         * Determina la menor distancia entre un array de sucursales y la direccion del cliente
         *
         * @param $sucursales,$direccion,$localidad,$provincia
         * @return $sucursales
         */
        public function distancematrix($sucursales,$direccion,$localidad,$provincia) {
            try {
                $direccion_cliente  = $direccion . "+" . $localidad . "+" .  $provincia;

                Mage::log("Direccion del cliente: " . $direccion_cliente);
                //Mage::log("Array sucursales: " . print_r($sucursales,true));

                $distancia_final = 100000000;
                $posicion        = "default";
                foreach ($sucursales as $key => $sucursal) {
                    $direccion = explode(',', $sucursal->Direccion);
                    $direccion_sucursal = $direccion[0] . "+" . $direccion[2] . "+" . $direccion[3];

                    Mage::log("Data: " . print_r($sucursal , true));
                    Mage::log("Sucursal: " . $sucursal->Direccion);
                    Mage::log("Direccion del cliente: " . str_replace(" ","%20",$direccion_cliente));
                    Mage::log("Direccion de sucursal: " . str_replace(" ","%20",$direccion_sucursal));

                    $originales     = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ';
                    $modificadas    = 'aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr';
                    $direccion_cliente = utf8_decode($direccion_cliente);
                    $direccion_cliente = strtr($direccion_cliente, utf8_decode($originales), $modificadas);
                    $direccion_cliente = strtolower($direccion_cliente);
                    $direccion_cliente = utf8_encode($direccion_cliente);
                    $direccion_sucursal = utf8_decode($direccion_sucursal);
                    $direccion_sucursal = strtr($direccion_sucursal, utf8_decode($originales), $modificadas);
                    $direccion_sucursal = strtolower($direccion_sucursal);
                    $direccion_sucursal = utf8_encode($direccion_sucursal);

                    //$mode = "walking";
                    //$mode = "bicycling";
                    $mode = "driving";
                    $url  = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=" . str_replace(" ","%20",$direccion_cliente) . "&destinations=" . str_replace(" ","%20",$direccion_sucursal) . "&mode={$mode}&language=es-ES&sensor=false";

                    $api  = file_get_contents($url);
                    $data = json_decode(utf8_encode($api),true);

                    $rows       = $data["rows"][0];
                    $elements   = $rows["elements"][0];

                    $distancia  = $elements["distance"]["value"];
                    $distancia_txt  = $elements["distance"]["text"];
                    $duracion       = $elements["duration"]["text"];
                    
                    if ($distancia_final >= $distancia && !empty($distancia)) {
                        $distancia_final        = $distancia;
                        $distancia_final_txt    = $distancia_txt;
                        $duracion_final         = $duracion;
                        $posicion               = $key;
                    }
                }

                // Desahbiltar método sucursal en el Shipping Method
                if($posicion === "default") {
                    Mage::log("No se encontro la sucursal.");
                    return false;
                }

                $this->distancia_final_txt   = $distancia_final_txt;
                $this->duracion_final        = $duracion_final;
                if($mode=="driving") $this->mode="en auto";

                // Guardamos las variables en session para no tener que volver a llamar a la API de Google
                Mage::getSingleton('core/session')->setGoogleDistance($sucursales[$posicion]);
                Mage::getSingleton('core/session')->setDistancia($distancia_final_txt);
                Mage::getSingleton('core/session')->setDuracion($duracion_final);
                Mage::getSingleton('core/session')->setMode($this->mode);
                return $sucursales[$posicion];

            } catch (SoapFault $e) {
                Mage::log("Error: " . $e);
            }
        }

    }
?>
