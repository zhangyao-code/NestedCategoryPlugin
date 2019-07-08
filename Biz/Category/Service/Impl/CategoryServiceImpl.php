<?php
namespace CategoryPlugin\Biz\Category\Service\Impl;


use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\TreeToolkit;
use Biz\BaseService;
use CategoryPlugin\Biz\Category\Dao\Impl\CategoryDaoImpl;
use CategoryPlugin\Biz\Category\Service\CategoryService;

class CategoryServiceImpl extends BaseService implements CategoryService
{
    public function createCategory($category)
    {
        try {
            $category = $this->getCategoryDao()->create($category);
            $parent = $this->getCategory($category['parentId']);
            return $this->updateCategoryCode($category, $parent);
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }

    }

//    public function microtime_float()
//    {
//        list($usec, $sec) = explode(" ", microtime());
//        return ((float)$usec + (float)$sec);
//    }

    public function createChildrenCategory($category,$parentId)
    {
        $parent = $this->getCategory($parentId);

        try {

            $this->beginTransaction();
            $this->batchUpdateRgt($parent['rgt']-1,2);
            $this->batchUpdateLft($parent['rgt'],2);
            $category['lft'] =$parent['rgt'];
            $category['rgt'] =$parent['rgt']+1;
            $category['parentId']=  $parent['id'];
            $result = $this->getCategoryDao()->create($category);
            $this->updateCategoryCode($result, $parent);
            $this->commit();

            return $result;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function updateCategory($categoryId,$category)
    {
        return $this->getCategoryDao()->update($categoryId,$category);
    }

    public function deleteCategory($categoryId)
    {
        try {
            $this->beginTransaction();

            $category = $this->getCategory($categoryId);
            $result = $this->deleteByLftAndRgt($category['lft'],$category['rgt']);
            $this->batchUpdateRgt($category['rgt'],$category['lft']-($category['rgt']+1));
            $this->batchUpdateLft($category['rgt'],$category['lft']-($category['rgt']+1));
            $this->commit();

            return $result;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }


    public function getCategory($id){
        return $this->getCategoryDao()->get($id);

    }

    public function countCategories($conditions){
        return $this->getCategoryDao()->count($conditions);

    }

    public function searchCategories($conditions, $orderBys, $start, $limit, $columns = array())
    {

        return $this->getCategoryDao()->search($conditions, $orderBys, $start, $limit, $columns);
    }

    public function getCategoryMaxRgt(){

        return $this->getCategoryDao()->getMaxRgt();
    }

    public function batchUpdateRgt($rgt,$length){
        return $this->getCategoryDao()->batchChangeRgt($rgt,$length);
    }

    public function batchUpdateLft($lft,$length){
        return $this->getCategoryDao()->batchChangeLft($lft,$length);
    }

    public function getCategoryTree()
    {
        $prepare = function ($categories) {
            $prepared = array();

            foreach ($categories as $category) {
                if (!isset($prepared[$category['parentId']])) {
                    $prepared[$category['parentId']] = array();
                }

                $prepared[$category['parentId']][] = $category;
            }

            return $prepared;
        };

        $categories = $this->findAllCategories();

        $categories = $prepare($categories);

        $tree = array();
        $this->makeCategoryTree($tree, $categories, 0);

        return $tree;
    }

    public function getCategoryStructureTree()
    {
        return TreeToolkit::makeTree($this->getCategoryTree(), 'lft');
    }

    public function findCategoriesByIds(array $ids)
    {
        return ArrayToolkit::index($this->getCategoryDao()->findByIds($ids), 'id');
    }

    public function findAllCategories()
    {
        return $this->getCategoryDao()->findAll();
    }

    /**
     * @param $id
     * @return mixed
     * 嵌套方式检索单一路径
     */
    public function findCategoryParentPath($id)
    {
        return $this->getCategoryDao()->findCategoryParentPath($id);
    }

    /**
     * @param $id
     * @return array
     * 通过Code检索单一路径
     */
    public function findCategoryParentPathByCategoryCode($id)
    {
        $category = $this->getCategory($id);
        $ids = explode('.',$category['categoryCode']);
        $ids= array_filter($ids);
        return $this->getCategoryDao()->findCategoryParentPathByCategoryCode($ids);
    }

    /**
     * @param $id
     * @return array
     * 嵌套方式通过父节点检索底下整棵树
     */
    public function findCategoryChildPath($id)
    {
        return $this->getCategoryDao()->findCategoryChildPath($id);
    }

    /**
     * @param $id
     * @return array
     * Code方式通过父节点检索底下整棵树
     */
    public function findCategoryChildPathByCategoryCode($id)
    {
        $category = $this->getCategory($id);
        if($category['parentId']){
            $categories = $this->searchCategories(array('categoryCodeLike'=> '%'.$category['categoryCode'].'%'),array('id'=>'ASC'),0,PHP_INT_MAX,array('name'));
        }else{
            $categories = array();
            $treeArray = $this->searchCategories(array('parentId'=> $id),array('id'=>'ASC'),0,PHP_INT_MAX,array('categoryCode'));
            foreach ($treeArray as $category){
                $childArray = $this->searchCategories(array('categoryCodeLike'=> '%'.$category['categoryCode'].'%'),array('id'=>'ASC'),0,PHP_INT_MAX,array('name'));
                $categories = array_merge($categories,$childArray);
            }
        }
        return $categories;
    }

    /**
     * @return array
     * 嵌套方式检索叶子节点
     */
    public function findLeafNodeCategories()
    {
        return $this->getCategoryDao()->findLeafNodeCategories();
    }

    public function deleteByLftAndRgt($lft, $rgt)
    {
        return $this->getCategoryDao()->deleteByLftAndRgt($lft, $rgt);
    }

    protected function makeCategoryTree(&$tree, &$categories, $parentId)
    {
        static $depth = 0;

        if (isset($categories[$parentId]) && is_array($categories[$parentId])) {
            foreach ($categories[$parentId] as $category) {
                ++$depth;
                $category['depth'] = $depth;
                $tree[] = $category;
                $this->makeCategoryTree($tree, $categories, $category['id']);
                --$depth;
            }
        }

        return $tree;
    }

    private function updateCategoryCode($category, $parentCategory)
    {
        $fields = array();

        if (empty($parentCategory)) {
            $fields['categoryCode'] = $category['id'].'.';
        } else {
            $fields['categoryCode'] = $parentCategory['categoryCode'].$category['id'].'.';
        }

        return $this->getCategoryDao()->update($category['id'], $fields);
    }

    /**
     * @return CategoryDaoImpl
     */
    protected function getCategoryDao()
    {
        return $this->createDao('CategoryPlugin:Category:CategoryDao');
    }
}