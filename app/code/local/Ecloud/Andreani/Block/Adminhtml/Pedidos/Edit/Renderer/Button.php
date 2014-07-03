<?php
/**
 * @version   0.1.7 03.07.2014
 * @author    ecloud solutions http://www.ecloudsolutions.com <info@ecloudsolutions.com>
 * @copyright Copyright (C) 2010 - 2014 ecloud solutions ®
 */
?>
<?php
class Ecloud_Andreani_Block_Adminhtml_Pedidos_Edit_Renderer_Button extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

  public function render(Varien_Object $row) {
  	$columnaID = $row->getId();
    //You can write html for your button here
    $model = Mage::getModel('andreani/order')->load($columnaID);
    $constanciaURL = $model->getData('constancia');
    $constancia = $model->getData('constancia');
    $estadoenvio = $model->getData('estado');

    if ($constancia != '') {
		$html = '<a  href="'.$constanciaURL.'" target="_blank"><button >Imprimir Constancia</button></a>';
    }
    else{
    	$html = '<span>No hay ninguna constancia para ser impresa.</span>';
    	if ($estadoenvio != 'Enviado') {

    		$html = $html . "El Pedido no ha sido Enviado.";
    		
    	}
    }
    
   return $html;

  }
}
?>