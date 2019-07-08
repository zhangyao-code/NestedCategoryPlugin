<?php

namespace CategoryPlugin\Biz\Category\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use CategoryPlugin\Biz\Category\Dao\CategoryDao;

class CategoryDaoImpl extends GeneralDaoImpl implements CategoryDao
{
    protected $table = 'nested_category';

    public function getMaxRgt()
    {
        $sql = "SELECT MAX(rgt) FROM {$this->table()}";

        return $this->db()->fetchColumn($sql);
    }

    public function batchChangeRgt($rgt,$length)
    {
        $sql = "UPDATE {$this->table} set rgt=rgt+? where rgt > ?";

        return $this->db()->executeUpdate($sql,array($length,$rgt));

    }

    public function batchChangeLft($left,$length)
    {
        $sql = "UPDATE {$this->table} set lft=lft+? where lft > ?";

        return $this->db()->executeUpdate($sql,array($length,$left));

    }

    public function findByIds(array $ids)
    {
        return $this->findInField('id', $ids);
    }

    public function findAll()
    {
        $sql = "SELECT * FROM {$this->table()} ORDER BY id ASC";

        return $this->db()->fetchAll($sql) ?: array();
    }

    /**
     * @param $id
     * @return array
     * 嵌套方式检索单一路径
     */
    public function findCategoryParentPath($id)
    {
        $sql = "SELECT parent.name FROM `nested_category` AS node,`nested_category` AS parent WHERE node.lft BETWEEN parent.lft AND parent.rgt AND node.id = ? ORDER BY parent.lft ";
        return $this->db()->fetchAll($sql,array($id)) ?: array();
    }

    /**
     * @param $id
     * @return array
     * 通过Code检索单一路径
     */
    public function findCategoryParentPathByCategoryCode($ids)
    {
        $marks = str_repeat('?,', count($ids) - 1).'?';

        $sql = "SELECT name FROM {$this->table()} WHERE id IN ({$marks}) order by field(id,{$marks}) ";

        return $this->db()->fetchAll($sql,array_merge($ids, $ids)) ?: array();
    }

    /**
     * @param $id
     * @return array
     * 嵌套方式通过父节点检索底下正棵树
     */
    public function findCategoryChildPath($id)
    {
        $sql = "SELECT node.name FROM `nested_category` AS node,`nested_category` AS parent WHERE node.lft BETWEEN parent.lft AND parent.rgt AND parent.id = ? ORDER BY node.lft ";

        return $this->db()->fetchAll($sql,array($id)) ?: array();
    }

    /**
     * @return array
     * 嵌套方式检索叶子节点
     */
    public function findLeafNodeCategories()
    {
        $sql = "SELECT name FROM nested_category WHERE rgt = lft + 1;";

        return $this->db()->fetchAll($sql) ?: array();
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'orderbys' => array('id'),
            'conditions' => array(
                'id = :id',
                'name LIKE :nameLike',
                'categoryCode LIKE :categoryCodeLike',
                'categoryCode = :categoryCode',
                'parentId = :parentId',
                'lft = :lft',
                'id IN ( :ids )',
                'rgt = :rgt',
            ),
        );
    }
}