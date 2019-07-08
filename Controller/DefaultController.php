<?php

namespace CategoryPlugin\Controller;

use AppBundle\Common\Paginator;
use AppBundle\Controller\BaseController;
use CategoryPlugin\Biz\Category\Service\Impl\CategoryServiceImpl;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends BaseController
{
    public function indexAction(Request $request)
    {
        var_dump( $this->getCategoryService()->findLeafNodeCategoriesByCategoryCode());
        exit();
        $categories = $this->getCategoryService()->getCategoryStructureTree();

        return $this->render('CategoryPlugin:Default:index.html.twig', array(
            'categories' => $categories,
        ));
    }

    public function createAction(Request $request,$parentId)
    {
         if($request->getMethod()== 'POST'){

            if(empty($parentId)){
               $maxRgt = $this->getCategoryService()->getCategoryMaxRgt();
                $category = array(
                    'name' => $request->request->get('name'),
                    'lft'=>$maxRgt+1,
                    'rgt' => $maxRgt+2,
                );
                $this->getCategoryService()->createCategory($category);

            }else{
                $category = array(
                    'name' => $request->request->get('name'),
                );
                $this->getCategoryService()->createChildrenCategory($category,$parentId);

            }
             return $this->createJsonResponse(true);
         }
        return $this->render('CategoryPlugin:Default:create-modal.html.twig', array(
            'parentId' => $parentId
        ));
    }

    /**
     * @return CategoryServiceImpl
     */
    protected function getCategoryService()
    {
        return $this->createService('CategoryPlugin:Category:CategoryService');
    }
}
