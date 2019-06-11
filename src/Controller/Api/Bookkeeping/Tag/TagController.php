<?php

namespace App\Controller\Api\Bookkeeping\Tag;

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
use App\Entity\Bookkeeping\Tag\Tag;
use App\Repository\Bookkeeping\Tag\TagRepository;
use App\Form\Bookkeeping\Tag\TagType;
use App\Domain\Bookkeeping\Tag\Filter\TagFilter;
use App\Domain\Bookkeeping\Tag\Type\SettlementTypeCollection;

/**
 * @Route("/api/bookkeeping/tag")
 */
class TagController extends AbstractFOSRestController implements ClassResourceInterface {

    /**
     * Gets a collection of the tags for autocomplete
     * 
     * @Route("/autocomplete", name="get_tags_for_autocomplete", methods={"GET"})
     * 
     * @SWG\Tag(name="Bookkeeping - tag")
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
     *          @SWG\Items(
     *              type="object",
     *              @SWG\Property(property="id", type="integer"),
     *              @SWG\Property(property="name", type="string"),
     *              @SWG\Property(property="background_color", type="string"),
     *              @SWG\Property(property="font_color", type="string"),
     *              @SWG\Property(property="bank_statement_phrases", @SWG\Items(
     *                  type="object",
     *                  @SWG\Property(property="phrase", type="stringnteger"),
     *              ),
     *          )
     *     )
     * )
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
    public function cgetForAutocompleteAction()
    {
        try {
            return $this->getRepository()->findAllForAutocomplete();
        } catch (\Exception $ex) {
            return new View('Wrong parameters', Response::HTTP_BAD_REQUEST);
        }
        
        return [];
    }
    
    
    
    /**
     * Gets a collection of the tags
     * 
     * @Route("/", name="get_tags", methods={"GET"})
     * 
     * @SWG\Tag(name="Bookkeeping - tag")
     *
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     * ),
     * @QueryParam(name="currentPage", requirements="\d+", default="1", description="current page to show")
     * @QueryParam(name="perPage", requirements="\d+", default="50", description="Items count per page")
     * @QueryParam(name="sortBy", requirements="[a-zA-Z_]+", default="name", description="Field to sort by")
     * @QueryParam(name="sortDesc", requirements="(true|false)", default="false", description="Sort descrement order anabled")
     * @QueryParam(name="filter", default="{}", description="Serialized filters.")
     *  
     * @SWG\Response(
     *     response=200,
     *     description="Returned when successful",
     *     @SWG\Schema(
     *         ref=@Model(type=Tag::class)
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
     *
     * @return View
     */
    public function cgetAction(ParamFetcher $paramFetcher)
    {
        $result = [
            'totalRows' => 0,
            'items' => []
        ];
        
        try {
            $filter = new TagFilter();
            $filter->readFromParamFetcher($paramFetcher);

            $result['totalRows'] = (int)$this->getRepository()->findByMultiParameters(true, $filter);
            $result['items'] = $this->getRepository()->findByMultiParameters(false, $filter);
            $result['types'] = (new SettlementTypeCollection())->getTypesData();
            return $result;
            
        } catch (\Exception $ex) {
            return new View('Wrong parameters ('.$ex->getMessage().')', Response::HTTP_BAD_REQUEST);
        }
        
        return $result;
    }
    
    /**
     * Get a single tag
     *
     * @Route("/{id}", name="get_tag", methods={"GET"})
     * 
     * @SWG\Tag(name="Bookkeeping - tag")
     * 
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     * ),
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="tag id"
     * )
     * 
     * @SWG\Response(
     *     response=200,
     *     description="Returned when successful",
     *     @SWG\Schema(
     *         ref=@Model(type=Tag::class)
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
    public function getAction(int $id) {
        $item = $this->getRepository()->findOneById($id);
        if (!$item) {
            throw new NotFoundHttpException('Tag with id ' . $id . ' does not exist');
        }

        return $item;
    }

    /**
     * Create a single tag
     *
     * @Route("/", methods={"POST"})
     * 
     * @SWG\Tag(name="Bookkeeping - tag")
     * 
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     * )
     * @SWG\Parameter(
     *     name="tag",
     *     in="body",
     *     required=true,
     *     description="tag data",
     *     @SWG\Schema(
     *         ref=@Model(type=Tag::class)
     *     )
     * )
     * 
     * @SWG\Response(
     *     response=201,
     *     description="Returned when successful",
     *     @SWG\Schema(
     *         ref=@Model(type=Tag::class)
     *     )
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Returned when error"
     * )
     * 
     * @Annotations\View(serializerGroups={
     *   "summary"
     * })
     * 
     * @return View
     */
    public function postAction(Request $request) {
        $parameters = $request->request->all();

        $item = new Tag();
        $form = $this->createForm(TagType::class, $item, ['csrf_protection' => false, 'method' => Request::METHOD_POST]);
        $form->submit($parameters);

        if (!$form->isValid()) {          
            return $form;
        }

        try {
            $this->getRepository()->save($item);
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $ex) {
            return new View('Duplicate name. Tag name should by unique', Response::HTTP_BAD_REQUEST);
        }

        return new View($item, Response::HTTP_CREATED);
    }

    
    /**
     * Update existing Item from the submitted data
     *
     * @Route("/", methods={"PATCH"})
     * 
     * @SWG\Tag(name="Bookkeeping - tag")
     * 
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     * )
     * @SWG\Parameter(
     *     name="tag",
     *     in="body",
     *     required=true,
     *     description="tag data",
     *     @SWG\Schema(
     *         ref=@Model(type=Tag::class)
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
        if (!isset($parameters['id'])) {
            return new View('Wrong parameters, id in tag data is required', Response::HTTP_BAD_REQUEST);
        }
        $id = $parameters['id'];
        
        // get item
        $item = $this->getRepository()->findOneById($id);
        if (!$item) {
            return new View('Tag with id '.$id.' does not exist', Response::HTTP_NOT_FOUND);
        }

        $form = $this->createForm(TagType::class, $item, ['csrf_protection' => false, 'method' => Request::METHOD_PATCH]);
        unset($parameters['id']);
        $form->submit($parameters);
        if (!$form->isValid()) {   
            return $form;
        }

        try {
            $this->getRepository()->save($item);
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $ex) {
            return new View('Duplicate name. Tag name should by unique', Response::HTTP_BAD_REQUEST);
        }

        return new View(null, Response::HTTP_NO_CONTENT);
    }
    
    /**
     * Delete existing Item 
     *
     * @Route("/{id}", methods={"DELETE"})
     * 
     * @SWG\Tag(name="Bookkeeping - tag")
     * 
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     required=true,
     *     description="tag id"
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
    public function deleteAction(int $id)
    {
        $item = $this->getRepository()->findOneById($id);
        if (!$item) {
            return new View('Tag with id '.$id.' does not exist', Response::HTTP_NOT_FOUND);
        }
        
        $this->getRepository()->delete($item);

        return new View(null, Response::HTTP_NO_CONTENT);
    }
    
    
    /**
     * @return TagRepository
     */
    private function getRepository(): TagRepository {
        return $this->getDoctrine()->getManager()->getRepository(Tag::class);
    }

    
}
