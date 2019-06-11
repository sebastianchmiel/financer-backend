<?php

namespace App\Controller\Api\Bookkeeping\Stat;

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
use App\Service\Bookkeeping\Stat\Stat;

/**
 * @Route("/api/bookkeeping/stat")
 */
class StatController extends AbstractFOSRestController implements ClassResourceInterface {

    /**
     * Gets stat for tag in date range
     * 
     * @Route("/tag", name="get_stat_for_tag", methods={"GET"})
     * 
     * @SWG\Tag(name="Bookkeeping - stat")
     *
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     * ),
     * 
     * @QueryParam(name="tagId", requirements="\d+", default="1", description="tag id")
     * @QueryParam(name="dateFrom", requirements="([12]\d{3}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]))", default="2018-01-01", description="Date from (RRRR-MM-DD)")
     * @QueryParam(name="dateTo", requirements="([12]\d{3}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]))", default="2018-12-31", description="Date to (RRRR-MM-DD)")
     * 
     * @SWG\Response(
     *     response=200,
     *     description="Returned when successful",
     *     @SWG\Schema(
     *          @SWG\Property(property="date", type="integer"),
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
    public function tagAction(Stat $statService, ParamFetcher $params) {
        try {
            // prepare params
            $tagId = (int)$params->get('tagId');
            $dateFrom = new \DateTime($params->get('dateFrom'));
            $dateTo = new \DateTime($params->get('dateTo'));
            
            // get and return values
            return $statService->getTagStats($tagId, $dateFrom, $dateTo);
        } catch (\Exception $ex) {
            return new View('Wrong parameters', Response::HTTP_BAD_REQUEST);
        }
        
        return [];
    }
    
        
}
