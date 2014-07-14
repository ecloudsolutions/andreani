<?php
/**
 * @version   0.1.8 04.07.2014
 * @author    ecloud solutions http://www.ecloudsolutions.com <info@ecloudsolutions.com>
 * @copyright Copyright (C) 2010 - 2014 ecloud solutions ®
 */
?><?php
class Ecloud_Andreani_Model_Andreani extends Mage_Core_Model_Abstract
{
     public function _construct()
     {
         parent::_construct();
         $this->_init('andreani/andreani');

         foreach ($request->getAllItems() as $_item) {
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
            $datos["peso"]           = ($_item->getQty() * $_item->getWeight() * $datos["medida"]) + $datos["peso"];
            $datos["valorDeclarado"] = ($_item->getQty() * $_item->getPrice()) + $datos["valorDeclarado"];
            
            $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $_item->getSku(), array('volumen'));
            $datos["volumen"] = ($_item->getQty() * $product->getVolumen() * $datos["medida"]) + $datos["volumen"];

            // Creamos un string con el detalle de cada producto
            $datos["DetalleProductos"] = "(" . $_item->getQty() . ") " .$_item->getName() . " + " . $datos["DetalleProductos"];
        }

        die(var_dump($datos["volumen"]));

     }

}
?>