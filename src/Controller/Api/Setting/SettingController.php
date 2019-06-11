<?php

namespace App\Controller\Api\Setting;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Setting\Setting;
use App\Repository\Setting\SettingRepository;
use App\Form\Setting\SettingCollectionType;

/**
 * @Route("/api/setting")
 */
class SettingController extends AbstractFOSRestController implements ClassResourceInterface {

    /**
     * Gets a collection of the setings
     * 
     * @Route("/", name="get_settings", methods={"GET"})
     * 
     * @SWG\Tag(name="Setting")
     *
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     * ),
     * 
     * @SWG\Response(
     *     response=200,
     *     description="Returned when successful",
     *     @SWG\Schema(
     *         ref=@Model(type=Setting::class)
     *     )
     * )
     * @SWG\Response(
     *     response=404,
     *     description="Returned when not found"
     * )
     * 
     * @Annotations\View(serializerGroups={
     *   "all"
     * })
     *
     * @return View
     */
    public function cgetAction()
    {
        try {
            return $this->getRepository()->findBy([], ['name' => 'ASC']);
        } catch (\Exception $ex) {
            return new View('Wrong parameters ('.$ex->getMessage().')', Response::HTTP_BAD_REQUEST);
        }
    }
    
    /**
     * Get a single tag
     *
     * @Route("/{name}", name="get_setting", methods={"GET"})
     * 
     * @SWG\Tag(name="Setting")
     * 
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     * ),
     * @SWG\Parameter(
     *     name="name",
     *     in="path",
     *     type="string",
     *     description="setting name"
     * )
     * 
     * @SWG\Response(
     *     response=200,
     *     description="Returned when successful",
     *     @SWG\Schema(
     *         ref=@Model(type=Setting::class)
     *     )
     * )
     * @SWG\Response(
     *     response=404,
     *     description="Returned when not found"
     * )
     * 
     * @Annotations\View(serializerGroups={
     *   "summary"
     * })
     */
    public function getAction(string $name) {
        $item = $this->getRepository()->findOneByName($name);
        if (!$item) {
            throw new NotFoundHttpException('Setting with name ' . $name . ' does not exist');
        }

        return $item;
    }

    /**
     * Update existing Items from the submitted data
     *
     * @Route("/", methods={"PATCH"})
     * 
     * @SWG\Tag(name="Setting")
     * 
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     * )
     * @SWG\Parameter(
     *     name="settings",
     *     in="body",
     *     required=true,
     *     description="setting data",
     *     @SWG\Schema(
     *          @SWG\Items(
     *              type="object",
     *              ref=@Model(type=Setting::class)
     *          )
     *     )
     * )
     * 
     * @SWG\Response(
     *     response=204,
     *     description="Returned when successful"
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Returned when error"
     * )
     * @SWG\Response(
     *     response=404,
     *     description="Returned when not found"
     * )
     * 
     * @Annotations\View(serializerGroups={
     *   "summary"
     * })
     * 
     * @return View
     */
    public function patchAction(Request $request)
    {
        // get params
        $parameters = $request->request->all();
        uasort($parameters, function ($a, $b) { return strcasecmp($a['name'], $b['name']); });
        $items = ['settings' => array_values($parameters)];
        if (empty($items)) {
            return new View(null, Response::HTTP_NO_CONTENT);
        }
        
        // get items object
        $itemsObjects = $this->getRepository()->findBy(['name' => array_column($items['settings'], 'name')], ['name' => 'ASC']);
        // fill empty
        if (count($items['settings']) !== count($itemsObjects)) {
            foreach ($items['settings'] as $item) {
                $exist = false;
                if (!empty($itemsObjects)) {
                    foreach ($itemsObjects as $itemObject) {
                        if ($itemObject->getName() === $item['name']) {
                            $exist = true;
                        }
                    }
                }
                
                if (!$exist) {
                    $newItemObject = new Setting();
                    $newItemObject->setName($item['name']);
                    $itemsObjects[] = $newItemObject;
                }
            }
        }
        uasort($itemsObjects, function ($a, $b) { return strcasecmp($a->getName(), $b->getName()); });
        $itemsObjects = array_values($itemsObjects);

        $form = $this->createForm(SettingCollectionType::class, ['settings' => $itemsObjects], ['csrf_protection' => false, 'method' => Request::METHOD_PATCH]);
        $form->submit($items);
        if (!$form->isValid()) {   
            return $form;
        }

        try {
            $items = $form->getData()['settings'];
            if (!empty($items)) {
                foreach ($items as $item) {
                    $this->getRepository()->save($item);
                }
            }
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $ex) {
            return new View('Duplicate name. Setting name should by unique', Response::HTTP_BAD_REQUEST);
        }

        return new View(null, Response::HTTP_NO_CONTENT);
    }
    
    /**
     * Delete existing Item 
     *
     * @Route("/{name}", methods={"DELETE"})
     * 
     * @SWG\Tag(name="Setting")
     * 
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     * )
     * @SWG\Parameter(
     *     name="name",
     *     in="path",
     *     type="string",
     *     required=true,
     *     description="setting name"
     * )
     * 
     * 
     * @SWG\Response(
     *     response=204,
     *     description="Returned when successful"
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Returned when error"
     * )
     * @SWG\Response(
     *     response=404,
     *     description="Returned when not found"
     * )
     * 
     * @Annotations\View(serializerGroups={
     *   "summary"
     * })
     * 
     * @return View
     */
    public function deleteAction(string $name)
    {
        $item = $this->getRepository()->findOneByName($name);
        if (!$item) {
            return new View('Setting with id '.$name.' does not exist', Response::HTTP_NOT_FOUND);
        }
        
        $this->getRepository()->delete($item);

        return new View(null, Response::HTTP_NO_CONTENT);
    }
    
    
    /**
     * @return TagRepository
     */
    private function getRepository(): SettingRepository {
        return $this->getDoctrine()->getManager()->getRepository(Setting::class);
    }

    
}
