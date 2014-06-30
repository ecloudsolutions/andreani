<?php
// @var $setup Mage_Eav_Model_Entity_Setup
$setup = $this;

$setup->startSetup();

$setup->addAttribute('customer_address', 'dni', array(
    'type'              => 'varchar',
    'input'             => 'text',
    'label'             => 'DNI',
    'visible'           => true,
    'required'          => true,
    'unique'            => false,
    'sort_order'        => 75, // Positions of the other attributes are listed in
    'position'          => 75, // Mage_Customer_Model_Resource_Setup
    'is_user_defined'   => 1,
    'is_system'         => 0,
    'validate_rules'    => array(
        'max_text_length'   => 255,
        ),
    )
);

$eavConfig = Mage::getSingleton('eav/config');

$store = Mage::app()->getStore(Mage_Core_Model_App::ADMIN_STORE_ID);
$attribute = $eavConfig->getAttribute('customer_address', 'dni');
$attribute->setWebsite($store->getWebsite());
        
$usedInForms = array(
    'adminhtml_customer_address',
    'customer_address_edit',
    'customer_register_address'
);
$attribute->setData('used_in_forms', $usedInForms);
$attribute->save();

/*
 *  CREA LOS ATRIBUTOS MEDIDAS Y VOLUMEN EN LOS PRODUCTOS
 */

$applyTo = array(
    Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
    Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE
);

$setup->addAttribute('catalog_product', 'volumen', array(
        'group'         => 'General',
        'type'          => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'backend'       => '',
        'frontend'      => '',
        'class'         => '',
        'default'       => '',
        'label'         => 'Volumen',
        'input'         => 'text',
        'source'        => '',
        'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'visible'       => 1,
        'required'      => 1,
        'searchable'    => 0,
        'filterable'    => 1,
        'unique'        => 0,
        'comparable'    => 0,
        'visible_on_front'          => 0,
        'is_html_allowed_on_front'  => 1,
        'user_defined'  => 1,
        'apply_to'      => implode(',',$applyTo)
));


$setup->run("
    CREATE TABLE IF NOT EXISTS `{$setup->getTable('andreani_order')}` (
      `id` int(11) NOT NULL AUTO_INCREMENT UNIQUE PRIMARY KEY,
      `id_orden` int(11) NOT NULL,
      `contrato` int(11) NOT NULL,
      `direccion` varchar(255) NOT NULL,
      `cliente` varchar(255) NOT NULL,
      `localidad` varchar(255) NOT NULL,
      `provincia` varchar(255) NOT NULL,
      `cp_destino` varchar(255) NOT NULL,
      `sucursal_retiro` int(11) NOT NULL,
      `direccion_sucursal` varchar(255) NOT NULL,
      `nombre` varchar(255) NOT NULL,
      `apellido` varchar(255) NOT NULL,
      `telefono` varchar(255) NOT NULL,
      `dni` varchar(255) NOT NULL,
      `email` varchar(255) NOT NULL,
      `precio` float NOT NULL,
      `valor_declarado` float NOT NULL,
      `volumen` float NOT NULL,
      `peso` float NOT NULL,
      `detalle_productos` TEXT NOT NULL,
      `categoria_distancia_id` int(11) NOT NULL,
      `categoria_peso` int(11) NOT NULL,
      `cod_tracking` VARCHAR( 255 ) NOT NULL,
      `recibo_tracking` VARCHAR( 255 ) NOT NULL,
      `estado` VARCHAR( 255 ) NOT NULL,
      `tracking` TEXT NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

// Change the column name into your own attribute name
try{
  $setup->run("
      ALTER TABLE {$this->getTable('sales_flat_quote_address')} ADD COLUMN `dni` VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL;
      ALTER TABLE {$this->getTable('sales_flat_order_address')} ADD COLUMN `dni` VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL;
  ");
} catch (Exception $e) {
  Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
}

$setup->endSetup();