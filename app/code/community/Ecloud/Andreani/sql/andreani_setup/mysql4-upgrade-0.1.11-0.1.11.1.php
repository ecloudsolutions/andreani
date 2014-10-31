<?php

$setup = $this;

$setup->startSetup();

try{
  $setup->run("
      ALTER TABLE {$this->getTable('andreani_order')} ADD `order_increment_id` int(11) NOT NULL ;
  ");
} catch (Exception $e) {
  Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
}

$setup->endSetup();