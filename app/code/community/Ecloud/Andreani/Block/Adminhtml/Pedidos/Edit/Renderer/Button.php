<?php
/**
 * @version   0.1.12 19.03.2015
 * @author    ecloud solutions http://www.ecloudsolutions.com <info@ecloudsolutions.com>
 * @copyright Copyright (C) 2010 - 2015 ecloud solutions ®
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
    }else{
        $html = '<span>No hay ninguna constancia para ser impresa.</span>';
        if ($estadoenvio != 'Enviado') {
            $html = $html . "El Pedido no ha sido Enviado.";
        }else{
            $html = '<a  href="'.$this->getUrl('*/*/getConstancia', array('id' => $row->getId())).'" target="_blank"><button >Imprimir Constancia</button></a>';
        }
    }
   return $html;
  }
}
?>