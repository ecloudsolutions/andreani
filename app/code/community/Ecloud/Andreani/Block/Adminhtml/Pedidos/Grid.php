<?php
/**
 * @version   0.1.12 19.03.2015
 * @author    ecloud solutions http://www.ecloudsolutions.com <info@ecloudsolutions.com>
 * @copyright Copyright (C) 2010 - 2015 ecloud solutions ®
 */
?>
<?php
class Ecloud_Andreani_Block_Adminhtml_Pedidos_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('andreani_order');
        $this->setDefaultSort('id_orden');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('andreani/order')->getCollection();
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns()
    {
        /*
        $this->addColumn('id', array(
            'header' => Mage::helper('andreani')->__('ID'),
            'sortable' => true,
            'width' => '5',
            'index' => 'id'
        ));*/
 
        $this->addColumn('id_orden', array(
            'header' => Mage::helper('andreani')->__('ID Orden'),
            'sortable' => true,
            'width' => '2%',
            'index' => 'id_orden',
            'type'  => 'text'
        ));

        $this->addColumn('order_increment_id', array(
            'header' => Mage::helper('andreani')->__('# Pedido'),
            'sortable' => true,
            'width' => '5',
            'index' => 'order_increment_id',
            'type'  => 'text'
        ));

        $this->addColumn('nombre', array(
            'header' => Mage::helper('andreani')->__('Nombre'),
            'sortable' => true,
            'width' => '5',
            'index' => 'nombre',
            'type'  => 'text'
        ));

        $this->addColumn('apellido', array(
            'header' => Mage::helper('andreani')->__('Apellido'),
            'sortable' => true,
            'width' => '5',
            'index' => 'apellido',
            'type'  => 'text'
        ));

        $this->addColumn('sucursal_retiro', array(
            'header' => Mage::helper('andreani')->__('Sucursal'),
            'sortable' => false,
            'width' => '5',
            'index' => 'sucursal_retiro',
            'type'  => 'text'
        ));
        
        $this->addColumn('detalle_productos', array(
            'header' => Mage::helper('andreani')->__('Descripcion Productos'),
            'sortable' => false,
            'width' => '5',
            'index' => 'detalle_productos',
            'type'  => 'text'
        ));
        $this->addColumn('cliente', array(
            'header' => Mage::helper('andreani')->__('Cliente Andreani'),
            'sortable' => true,
            'width' => '60',
            'index' => 'cliente',
            'type'  => 'text'
        ));
 
        $this->addColumn('contrato', array(
            'header' => Mage::helper('andreani')->__('Contrato Andreani'),
            'sortable' => true,
            'width' => '60',
            'index' => 'contrato',
            'type'  => 'text'
        ));

        $this->addColumn('recibo_tracking', array(
            'header' => Mage::helper('andreani')->__('Recibo'),
            'sortable' => true,
            'width' => '5',
            'index' => 'recibo_tracking',
            'type'  => 'text'
        ));

        $this->addColumn('cod_tracking', array(
            'header' => Mage::helper('andreani')->__('Nro Andreani - Tracking'),
            'sortable' => true,
            'width' => '5',
            'index' => 'cod_tracking',
            'type'  => 'text'
        ));

        $this->addColumn('impresion', array(
			'header'=> Mage::helper('catalog')->__('Imprimir Constancia'),
			'sortable'  => false,
			'target' => '_blank',
			'renderer'  => 'andreani/adminhtml_Pedidos_Edit_Renderer_button'
        ));

        $this->addColumn('entrega', array(
            'header' => Mage::helper('andreani')->__('Fecha de entrega'),
            'sortable' => true,
            'width' => '5',
            'index' => 'entrega',
            'type'  => 'text'
        ));

        $this->addColumn('estado', array(
            'header'    => Mage::helper('andreani')->__('Estado'),
            'sortable'  => false,
            'width'     => '5',
            'index'     => 'estado',
            'type'      => 'options',
            'sortable'  => false,
            'options'   => array(
                'Enviado'   => 'Enviado',
                'Eliminar'  => 'Eliminar',
                'Entregado' => 'Entregado',
                'Pendiente' => 'Pendiente'
            )
        ));
 
        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('id');
        $this->getMassactionBlock()->addItem('eliminar', array(
            'label'=> Mage::helper('andreani')->__('Eliminar'),
            'url'  => $this->getUrl('*/*/massEliminar', array('' => '')),
            'confirm' => Mage::helper('andreani')->__('¿Seguro que quieres eliminar la orden?')
        ));
        $this->getMassactionBlock()->addItem('entregado', array(
            'label'=> Mage::helper('andreani')->__('Entregado'),
            'url'  => $this->getUrl('*/*/massEntregado', array('' => '')),
            'confirm' => Mage::helper('andreani')->__('¿Seguro que quieres modificar el estado de la orden?')
        ));
        $this->getMassactionBlock()->addItem('pendiente', array(
            'label'=> Mage::helper('andreani')->__('Pendiente'),
            'url'  => $this->getUrl('*/*/massPendiente', array('' => '')),
            'confirm' => Mage::helper('andreani')->__('¿Seguro que quieres modificar el estado de la orden?')
        ));

        return $this;
    }

    public function getRowUrl($row)
    {
         return $this->getUrl('*/*/view', array('id' => $row->getId()));
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }
	
}
?>