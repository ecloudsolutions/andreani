<?php
/**
 * @version   0.1.12 19.03.2015
 * @author    ecloud solutions http://www.ecloudsolutions.com <info@ecloudsolutions.com>
 * @copyright Copyright (C) 2010 - 2015 ecloud solutions ®
 */
?>
<?php
class Ecloud_Andreani_Block_Adminhtml_Pedidos_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            array(
                'id'        => 'edit_form',
                'action'    => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
                'method'    => 'post',
                'enctype' => 'multipart/form-data'
        ));

        $form->setUseContainer(true);
        $this->setForm($form);
        
        $fieldset = $form->addFieldset('edit_form', array('legend'=>Mage::helper('andreani')->__('Datos del pedido')));
   
        $fieldset->addField('order_increment_id', 'text', array(
            'label'     => Mage::helper('andreani')->__('Pedido #'),
            'required'  => false,
            'name'      => 'order_increment_id',
            'readonly'  => true,
        ));

        $fieldset->addField('nombre', 'text', array(
            'label'     => Mage::helper('andreani')->__('Nombre de Cliente'),
            'required'  => false,
            'name'      => 'nombre',
        ));

       $fieldset->addField('apellido', 'text', array(
            'label'     => Mage::helper('andreani')->__('Apellido'),
            'required'  => false,
            'name'      => 'apellido',
            //'tabindex'  => 1,
        ));
        $fieldset->addField('dni', 'text', array(
            'label'     => Mage::helper('andreani')->__('DNI'),
            'required'  => false,
            'name'      => 'dni',
        ));
        $fieldset->addField('telefono', 'text', array(
            'label'     => Mage::helper('andreani')->__('Telefono'),
            'required'  => false,
            'name'      => 'telefono',
        ));
        $fieldset->addField('email', 'text', array(
            'label'     => Mage::helper('andreani')->__('E-mail'),
            'required'  => false,
            'name'      => 'email',
        ));

        $fieldset->addField('provincia', 'text', array(
            'label'     => Mage::helper('andreani')->__('Provincia'),
            'required'  => false,
            'name'      => 'provincia',
        ));
        $fieldset->addField('localidad', 'text', array(
            'label'     => Mage::helper('andreani')->__('Localidad'),
            'required'  => false,
            'name'      => 'localidad',
        ));
        $fieldset->addField('cp_destino', 'text', array(
            'label'     => Mage::helper('andreani')->__('Codigo postal'),
            'required'  => false,
            'name'      => 'cp_destino',
        ));
        $fieldset->addField('direccion', 'text', array(
            'label'     => Mage::helper('andreani')->__('Direccion'),
            'required'  => false,
            'name'      => 'direccion',
        ));
       
        $fieldset->addField('detalle_productos', 'textarea', array(
            'label'     => Mage::helper('andreani')->__('Detalle Productos'),
            'required'  => false,
            'name'      => 'detalle_productos',
        ));

        $fieldset->addField('valor_declarado', 'text', array(
            'label'     => Mage::helper('andreani')->__('Valor Declarado'),
            'required'  => false,
            'name'      => 'valor_declarado',
        ));
        $fieldset->addField('volumen', 'text', array(
            'label'     => Mage::helper('andreani')->__('Volumen'),
            'required'  => false,
            'name'      => 'volumen',
        ));
        $fieldset->addField('peso', 'text', array(
            'label'     => Mage::helper('andreani')->__('Peso'),
            'required'  => false,
            'name'      => 'peso',
        ));
        $fieldset->addField('categoria_distancia_id', 'text', array(
            'label'     => Mage::helper('andreani')->__('Categoria Distancia'),
            'required'  => false,
            'name'      => 'categoria_distancia_id',
        ));
        $fieldset->addField('categoria_peso', 'text', array(
            'label'     => Mage::helper('andreani')->__('Categoria Peso'),
            'required'  => false,
            'name'      => 'categoria_peso',
        ));
        $fieldset->addField('precio', 'text', array(
            'label'     => Mage::helper('andreani')->__('Precio de Envio'),
            'required'  => false,
            'name'      => 'precio',
        ));
        $fieldset->addField('cliente', 'text', array(
            'label'     => Mage::helper('andreani')->__('Nro. Cliente Andreani'),
            'required'  => false,
            'name'      => 'cliente',
            'sort_order'=> 30,
        ));

        $fieldset->addField('contrato', 'text', array(
            'label'     => Mage::helper('andreani')->__('Contrato Andreani'),
            'required'  => false,
            'name'      => 'contrato',
            'sort_order'=> 30,
        ));

        $fieldset->addField('id_orden', 'text', array(
            'label'     => Mage::helper('andreani')->__('Id Orden'),
            'required'  => false,
            'name'      => 'id_orden',
            'readonly'  => true,
        ));

        $fieldset->addField('cod_tracking', 'text', array(
            'label'     => Mage::helper('andreani')->__('Nro Andreani - Tracking'),
            'required'  => false,
            'name'      => 'cod_tracking',
        ));

        $fieldset->addField('recibo_tracking', 'text', array(
            'label'     => Mage::helper('andreani')->__('Recibo Andreani'),
            'required'  => false,
            'name'      => 'recibo_tracking',
        ));

        $fieldset->addField('sucursal_retiro', 'text', array(
            'label'     => Mage::helper('andreani')->__('Sucursal de Retiro'),
            'required'  => false,
            'name'      => 'sucursal_retiro',
        ));

        $fieldset->addField('estado', 'text', array(
            'label'     => Mage::helper('andreani')->__('Estado del envio'),
            'required'  => false,
            'name'      => 'estado',
            'readonly'  => true,
        ));

        //muestro "tracking", los detalles del estado del envio.

        $fieldset->addField('tracking', 'textarea', array(
            'label'     => Mage::helper('andreani')->__('Detalles del Envio'),
            'required'  => false,
            'name'      => 'tracking',
            'readonly'  => true
        ));

        $fieldset->addField('entrega', 'text', array(
            'label'     => Mage::helper('andreani')->__('Fecha de entrega a Andreani'),
            'required'  => false,
            'name'      => 'entrega',
        ));

        if (Mage::registry('order_data')){
            $form->setValues(Mage::registry('order_data')->getData());
        }
        
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

}
?>