<?php

include_once __DIR__.'/BaseInstallScript.php';

class InstallScript extends BaseInstallScript
{
    public function install()
    {
        $connection = $this->getConnection();
        $connection->exec("
          CREATE TABLE `nested_category` (
              `id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
              `name` varchar(255) NOT NULL COMMENT '名称',
              `lft` INT(10) NOT NULL COMMENT '左',
              `rgt` INT(10) NOT NULL COMMENT '右',
              `parentId` int(11) NOT NULL DEFAULT '0',
              `categoryCode` varchar(255) NOT NULL DEFAULT '0' COMMENT '内部编码',
              `createdTime` INT(10) unsigned NOT NULL DEFAULT '0'  COMMENT '创建时间',
              `updatedTime` INT(10) unsigned NOT NULL DEFAULT '0'  COMMENT '最后更新时间',
              PRIMARY KEY (`id`),
              UNIQUE KEY `categoryCode` (`categoryCode`)
              ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='分类';
            ");

    }
}
