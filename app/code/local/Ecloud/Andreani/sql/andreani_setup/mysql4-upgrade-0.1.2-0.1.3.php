<?php

$setup = $this;

$setup->startSetup();

try{
  $setup->run("
      ALTER TABLE {$this->getTable('andreani_order')} ADD COLUMN `constancia` VARCHAR(600) NOT NULL;
  ");
} catch (Exception $e) {
  Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
}

$setup->endSetup();