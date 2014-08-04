<?php
/**
 * @version   0.1.10 04.08.2014
 * @author    ecloud solutions http://www.ecloudsolutions.com <info@ecloudsolutions.com>
 * @copyright Copyright (C) 2010 - 2014 ecloud solutions ®
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
                'method'    => 'post'
        ));

        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('edit_form', array('legend'=>Mage::helper('andreani')->__('Datos del pedido')));
   
        $fieldset->addField('nombre', 'text', array(
            'label'     => Mage::helper('andreani')->__('Nombre de Cliente'),
            'required'  => false,
            'name'      => 'nombre',
            'readonly'  => true,
        ));

       $fieldset->addField('apellido', 'text', array(
            'label'     => Mage::helper('andreani')->__('Apellido'),
            'required'  => false,
            'name'      => 'apellido',
            'readonly'  => true,
            'tabindex'  => 1,
        ));
        $fieldset->addField('dni', 'text', array(
            'label'     => Mage::helper('andreani')->__('DNI'),
            'required'  => false,
            'name'      => 'dni',
            'readonly'  => true,
        ));
        $fieldset->addField('telefono', 'text', array(
            'label'     => Mage::helper('andreani')->__('Telefono'),
            'required'  => false,
            'name'      => 'telefono',
            'readonly'  => true,
        ));
        $fieldset->addField('email', 'text', array(
            'label'     => Mage::helper('andreani')->__('E-mail'),
            'required'  => false,
            'name'      => 'email',
            'readonly'  => true,
        ));

        $fieldset->addField('provincia', 'text', array(
            'label'     => Mage::helper('andreani')->__('Provincia'),
            'required'  => false,
            'name'      => 'provincia',
            'readonly'  => true,
        ));
        $fieldset->addField('localidad', 'text', array(
            'label'     => Mage::helper('andreani')->__('Localidad'),
            'required'  => false,
            'name'      => 'localidad',
            'readonly'  => true,
        ));
        $fieldset->addField('cp_destino', 'text', array(
            'label'     => Mage::helper('andreani')->__('Codigo postal'),
            'required'  => false,
            'name'      => 'cp_destino',
            'readonly'  => true,
        ));
        $fieldset->addField('direccion', 'text', array(
            'label'     => Mage::helper('andreani')->__('Direccion'),
            'required'  => false,
            'name'      => 'direccion',
            'readonly'  => true,
        ));
       
        $fieldset->addField('detalle_productos', 'textarea', array(
            'label'     => Mage::helper('andreani')->__('Detalle Productos'),
            'required'  => false,
            'name'      => 'detalle_productos',
            'readonly'  => true,
        ));

        $fieldset->addField('valor_declarado', 'text', array(
            'label'     => Mage::helper('andreani')->__('Valor Declarado'),
            'required'  => false,
            'name'      => 'valor_declarado',
            'readonly'  => true,
        ));
        $fieldset->addField('volumen', 'text', array(
            'label'     => Mage::helper('andreani')->__('Volumen'),
            'required'  => false,
            'name'      => 'volumen',
            'readonly'  => true,
        ));
        $fieldset->addField('peso', 'text', array(
            'label'     => Mage::helper('andreani')->__('Peso'),
            'required'  => false,
            'name'      => 'peso',
            'readonly'  => true,
        ));
        $fieldset->addField('categoria_distancia_id', 'text', array(
            'label'     => Mage::helper('andreani')->__('Categoria Distancia'),
            'required'  => false,
            'name'      => 'categoria_distancia_id',
            'readonly'  => true,
        ));
        $fieldset->addField('categoria_peso', 'text', array(
            'label'     => Mage::helper('andreani')->__('Categoria Peso'),
            'required'  => false,
            'name'      => 'categoria_peso',
            'readonly'  => true,
        ));
        $fieldset->addField('precio', 'text', array(
            'label'     => Mage::helper('andreani')->__('Precio de Envio'),
            'required'  => false,
            'name'      => 'precio',
            'readonly'  => true,
        ));
        $fieldset->addField('cliente', 'text', array(
            'label'     => Mage::helper('andreani')->__('Nro. Cliente Andreani'),
            'required'  => false,
            'name'      => 'cliente',
            'readonly'  => true,
            'sort_order'=> 30,
        ));

        $fieldset->addField('contrato', 'text', array(
            'label'     => Mage::helper('andreani')->__('Contrato Andreani'),
            'required'  => false,
            'name'      => 'contrato',
            'readonly'  => true,
            'sort_order'=> 30,
        ));

        $fieldset->addField('id_orden', 'text', array(
            'label'     => Mage::helper('andreani')->__('Id Orden'),
            'required'  => false,
            'name'      => 'id_orden',
            'readonly'  => true,
        ));

        $fieldset->addField('recibo_tracking', 'text', array(
            'label'     => Mage::helper('andreani')->__('Recibo Andreani'),
            'required'  => false,
            'name'      => 'recibo_tracking',
            'readonly'  => true,
        ));

        $fieldset->addField('sucursal_retiro', 'text', array(
            'label'     => Mage::helper('andreani')->__('Sucursal de Retiro'),
            'required'  => false,
            'name'      => 'sucursal_retiro',
            'readonly'  => true,
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

        if (Mage::registry('order_data')){
            $form->setValues(Mage::registry('order_data')->getData());
        }
        
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

}
?>