<?php

namespace App\Controller\Api\Bookkeeping\Contractor;

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
use App\Entity\Bookkeeping\Contractor\Contractor;
use App\Repository\Bookkeeping\Contractor\ContractorRepository;
use App\Form\Bookkeeping\Contractor\ContractorType;
use App\Domain\Bookkeeping\Contractor\Filter\ContractorFilter;

/**
 * @Route("/api/bookkeeping/contractor")
 */
class ContractorController extends AbstractFOSRestController implements ClassResourceInterface {

/**
     * Gets a collection of the contractors for autocomplete
     * 
     * @Route("/autocomplete", name="get_contractors_for_autocomplete", methods={"GET"})
     * 
     * @SWG\Tag(name="Bookkeeping - contractor")
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
     *              @SWG\Property(property="title", type="string"),
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
     * Get a single contractor
     *
     * @Route("/{id}", name="get_contractor", methods={"GET"})
     * 
     * @SWG\Tag(name="Bookkeeping - contractor")
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
     *     description="contractor id"
     * )
     * 
     * @SWG\Response(
     *     response=200,
     *     description="Returned when successful",
     *     @SWG\Schema(
     *         ref=@Model(type=Contractor::class)
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
            throw new NotFoundHttpException('Contractor with id ' . $id . ' does not exist');
        }

        return $item;
    }
    
    /**
     * Gets a collection of the contractors
     * 
     * @Route("/", name="get_contractors", methods={"GET"})
     * 
     * @SWG\Tag(name="Bookkeeping - contractor")
     *
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     * ),
     * @QueryParam(name="currentPage", requirements="\d+", default="1", description="current page to show")
     * @QueryParam(name="perPage", requirements="\d+", default="50", description="Items count per page")
     * @QueryParam(name="sortBy", requirements="[a-zA-Z]+", default="name", description="Field to sort by")
     * @QueryParam(name="sortDesc", requirements="(true|false)", default="false", description="Sort descrement order anabled")
     * @QueryParam(name="filter", default="{}", description="Serialized filters.")
     * 
     * @SWG\Response(
     *     response=200,
     *     description="Returned when successful",
     *     @SWG\Schema(
     *         ref=@Model(type=Contractor::class)
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
    public function cgetAction(ParamFetcher $paramFetcher)
    {
        $result = [
            'totalRows' => 0,
            'items' => []
        ];
        
        try {
            $filter = new ContractorFilter();
            $filter->readFromJson($paramFetcher->get('filter'));
            
            $currentPage = (int)$paramFetcher->get('currentPage');
            $perPage = (int)$paramFetcher->get('perPage');
            $sortBy = $paramFetcher->get('sortBy');
            $sortDirection = 'true' === $paramFetcher->get('sortDesc') ? 'desc' : 'asc';

            $result['totalRows'] = (int)$this->getRepository()->findByMultiParameters(true, $filter);
            $result['items'] = $this->getRepository()->findByMultiParameters(false, $filter, (($currentPage - 1) * $perPage), $perPage, $sortBy, $sortDirection);

            return $result;
            
        } catch (\Exception $ex) {
            return new View('Wrong parameters', Response::HTTP_BAD_REQUEST);
        }
        
        return $result;
    }

    /**
     * Create a single contractor
     *
     * @Route("/", methods={"POST"})
     * 
     * @SWG\Tag(name="Bookkeeping - contractor")
     * 
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     * )
     * @SWG\Parameter(
     *     name="contractor",
     *     in="body",
     *     required=true,
     *     description="contractor data",
     *     @SWG\Schema(
     *         ref=@Model(type=Contractor::class)
     *     )
     * )
     * 
     * @SWG\Response(
     *     response=201,
     *     description="Returned when successful",
     *     @SWG\Schema(
     *         ref=@Model(type=Contractor::class)
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

        $item = new Contractor();
        $form = $this->createForm(ContractorType::class, $item, ['csrf_protection' => false, 'method' => Request::METHOD_POST]);
        $form->submit($parameters);

        if (!$form->isValid()) {          
            return $form;
        }

        try {
            $this->getRepository()->save($item);
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $ex) {
            return new View('Duplicate name. Contractor name should by unique', Response::HTTP_BAD_REQUEST);
        }

        return new View($item, Response::HTTP_CREATED);
    }

    
    /**
     * Update existing Item from the submitted data
     *
     * @Route("/", methods={"PATCH"})
     * 
     * @SWG\Tag(name="Bookkeeping - contractor")
     * 
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     * )
     * @SWG\Parameter(
     *     name="contractor",
     *     in="body",
     *     required=true,
     *     description="contractor data",
     *     @SWG\Schema(
     *         ref=@Model(type=Contractor::class)
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
            return new View('Wrong parameters, id in contractor data is required', Response::HTTP_BAD_REQUEST);
        }
        $id = $parameters['id'];
        
        // get item
        $item = $this->getRepository()->findOneById($id);
        if (!$item) {
            return new View('Contractor with id '.$id.' does not exist', Response::HTTP_NOT_FOUND);
        }

        $form = $this->createForm(ContractorType::class, $item, ['csrf_protection' => false, 'method' => Request::METHOD_PATCH]);
        unset($parameters['id']);
        $form->submit($parameters);
        if (!$form->isValid()) {   
            return $form;
        }

        try {
            $this->getRepository()->save($item);
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $ex) {
            return new View('Duplicate name. Contractor name should by unique', Response::HTTP_BAD_REQUEST);
        }

        return new View(null, Response::HTTP_NO_CONTENT);
    }
    
    /**
     * Delete existing Item 
     *
     * @Route("/{id}", methods={"DELETE"})
     * 
     * @SWG\Tag(name="Bookkeeping - contractor")
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
     *     description="contractor id"
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
            return new View('Contractor with id '.$id.' does not exist', Response::HTTP_NOT_FOUND);
        }
        
        $this->getRepository()->delete($item);

        return new View(null, Response::HTTP_NO_CONTENT);
    }
    
    
    /**
     * @return ContractorRepository
     */
    private function getRepository(): ContractorRepository {
        return $this->getDoctrine()->getManager()->getRepository(Contractor::class);
    }

    
}
