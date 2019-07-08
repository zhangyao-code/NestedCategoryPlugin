<?php

namespace CategoryPlugin\Controller;

use AppBundle\Controller\BaseController;
use CategoryPlugin\Biz\Category\Service\Impl\CategoryServiceImpl;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends BaseController
{
    public function indexAction(Request $request)
    {
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

    public function updateAction(Request $request,$categoryId)
    {
        $category = $this->getCategoryService()->getCategory($categoryId);
        if($request->getMethod()== 'POST'){
            $this->getCategoryService()->updateCategory($categoryId,$request->request->all());
            return $this->createJsonResponse(true);
        }

        return $this->render('CategoryPlugin:Default:update-modal.html.twig', array(
            'category' => $category
        ));
    }

    public function deleteAction(Request $request,$categoryId)
    {
        $this->getCategoryService()->deleteCategory($categoryId);

        return $this->createJsonResponse(true);

    }

    /**
     * @return CategoryServiceImpl
     */
    protected function getCategoryService()
    {
        return $this->createService('CategoryPlugin:Category:CategoryService');
    }
}
