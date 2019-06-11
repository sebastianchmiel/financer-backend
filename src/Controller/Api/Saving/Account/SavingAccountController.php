<?php

namespace App\Controller\Api\Saving\Account;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\Saving\Account\SavingAccountAmountType;
use App\Service\Saving\Item\SavingItemService;
use App\Service\Saving\Account\SavingAccountService;

/**
 * @Route("/api/saving/account")
 */
class SavingAccountController extends AbstractFOSRestController implements ClassResourceInterface {

    /**
     * Get data
     *
     * @Route("/data", name="saving_account_data", methods={"GET"})
     * 
     * @SWG\Tag(name="Saving - account")
     * 
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     * ),

     * @SWG\Response(
     *     response=200,
     *     description="Returned when successful",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="bankName", type="string"),
     *         @SWG\Property(property="accountNumber", type="boolean"),
     *         @SWG\Property(property="percent", type="integer"),
     *         @SWG\Property(property="balance", type="integer"),
     *         @SWG\Property(property="balanceDistributed", type="integer"),
     *         @SWG\Property(property="balanceForDistribution", type="integer")
     *     )
     * )
     * 
     * @Annotations\View(serializerGroups={
     *   "summary"
     * })
     */
    public function getDataAction(SavingAccountService $savingAccountService) {
        $data = [];
        try {
            $data = $savingAccountService->getAllData();
        } catch (\Exception $ex) {
            return new View('Something wrong', Response::HTTP_BAD_REQUEST);
        }
        
        return $data;
    }
    
    /**
     * Update saving account amounts
     *
     * @Route("/data", methods={"PATCH"})
     * 
     * @SWG\Tag(name="Saving - account")
     * 
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     * )
     * @SWG\Parameter(
     *     name="item",
     *     in="body",
     *     required=true,
     *     description="saving account amounts",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="balance", type="integer"),
     *         @SWG\Property(property="balanceDistributed", type="integer"),
     *         @SWG\Property(property="balanceForDistribution", type="integer")
     *     )
     * )
     * 
     * @SWG\Response(
     *     response=200,
     *     description="Returned when successful",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="bankName", type="string"),
     *         @SWG\Property(property="accountNumber", type="boolean"),
     *         @SWG\Property(property="percent", type="integer"),
     *         @SWG\Property(property="balance", type="integer"),
     *         @SWG\Property(property="balanceDistributed", type="integer"),
     *         @SWG\Property(property="balanceForDistribution", type="integer")
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
    public function updateAction(SavingAccountService $savingAccountService, Request $request) {
        $parameters = $request->request->all();
        $data = [];

        $item = [];
        $form = $this->createForm(SavingAccountAmountType::class, $item, ['csrf_protection' => false, 'method' => Request::METHOD_POST]);
        $form->submit($parameters);
        if (!$form->isValid(true)) {          
            return $form;
        }

        try {
            // udpate
            $savingAccountService->updateAmounts($form->getData());
            
            $data = $savingAccountService->getAllData();
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $ex) {
            return new View('Duplicate name. Saving item name should by unique', Response::HTTP_BAD_REQUEST);
        }

        return $data;
    }
    
    /**
     * Get histories for all saving item
     *
     * @Route("/history", name="saving_account_history", methods={"GET"})
     * 
     * @SWG\Tag(name="Saving - account")
     * 
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Returned when successful"
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
    public function getHistories(SavingItemService $savingItemService) {
        try {
            // get data and return
            return $savingItemService->getGrouppedHistories();
        } catch (\Exception $ex) {
            return new View('Something wrong'.$ex->getMessage().' '.$ex->getFile().':'.$ex->getLine(), Response::HTTP_BAD_REQUEST);
        }

        return new View('Something wrong', Response::HTTP_BAD_REQUEST);
    }    
}
