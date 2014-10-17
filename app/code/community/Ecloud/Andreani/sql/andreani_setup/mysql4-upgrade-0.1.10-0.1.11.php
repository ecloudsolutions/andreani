<?php

$setup = $this;

$setup->startSetup();

try{
  $setup->run("
      ALTER TABLE {$this->getTable('andreani_order')} ADD `entrega` VARCHAR(255) NOT NULL ;
  ");
} catch (Exception $e) {
  Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
}

$setup->endSetup();