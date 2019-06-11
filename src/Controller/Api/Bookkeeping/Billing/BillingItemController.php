<?php

namespace App\Controller\Api\Bookkeeping\Billing;

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
use App\Entity\Bookkeeping\Billing\BillingItem;
use App\Repository\Bookkeeping\Billing\BillingItemRepository;
use App\Form\Bookkeeping\Billing\BillingItemCostType;
use App\Form\Bookkeeping\Billing\BillingItemUsType;
use App\Form\Bookkeeping\Billing\BillingItemZusType;
use App\Form\Bookkeeping\Billing\BillingItemIncomeType;
use App\Service\Bookkeeping\Billing\BillingMonthData;
use App\Domain\Bookkeeping\Billing\Type\Types\CostType;
use App\Domain\Bookkeeping\Billing\Type\Types\IncomeType;
use App\Domain\Bookkeeping\Billing\Type\Types\UsType;
use App\Domain\Bookkeeping\Billing\Type\Types\ZusType;
use Mpdf\Mpdf;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use App\Service\Bookkeeping\Billing\BillingMonthSettlementService;
use App\Service\Bookkeeping\Billing\BillingMonthDateResolver;
use App\Service\Setting\SettingService;
use App\Service\Bookkeeping\Billing\AmountText;


/**
 * @Route("/api/bookkeeping/billing/item")
 */
class BillingItemController extends AbstractFOSRestController implements ClassResourceInterface {

    /**
     * Change billing item field status
     *
     * @Route("/change-status", name="bookkeeping_billing_item_change_status", methods={"POST"})
     * 
     * @SWG\Tag(name="Bookkeeping - billing")
     * 
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     * ),
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     description="parameters",
     *     required=true,
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="id", type="integer"),
     *          @SWG\Property(property="field", type="string"),
     *          @SWG\Property(property="value", type="boolean")
     *     )
     * )
     * 
     * @SWG\Response(
     *     response=204,
     *     description="Returned when successful",
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
    public function changeStatus(Request $request) {
        $parameters = $request->request->all();
        
        if (!isset($parameters['id']) || !isset($parameters['field']) || !isset($parameters['value'])) {
            return new View('Wrong parameters, id, field and value are required', Response::HTTP_BAD_REQUEST);
        }
        
        $id = (int)$parameters['id'];
        $field = (string)$parameters['field'];
        $value = (bool)$parameters['value'];
        
        try {
            $this->getRepository()->changeItemFieldStatus($id, $field, $value);
        } catch (\Doctrine\ORM\NoResultException $ex) {
            return new View('Item with id '.$id.' does not exist', Response::HTTP_NOT_FOUND);
        } catch (\InvalidArgumentException $ex) {
            return new View('Unrecognized field name', Response::HTTP_BAD_REQUEST);
        } catch (\Exception $ex) {
            return new View('Something goes wrong', Response::HTTP_BAD_REQUEST);
        }
        
        return new View(null, Response::HTTP_NO_CONTENT);
    }
    
    /**
     * @return BillingItemRepository
     */
    private function getRepository(): BillingItemRepository {
        return $this->getDoctrine()->getManager()->getRepository(BillingItem::class);
    }
    
    /**
     * Create a single cost billing item
     *
     * @Route("/cost", methods={"POST"})
     * 
     * @SWG\Tag(name="Bookkeeping - billing")
     * 
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     * )
     * @SWG\Parameter(
     *     name="billing_cost_item",
     *     in="body",
     *     required=true,
     *     description="billing cost item data",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="monthDate", type="date (YYYY-MM-DDD)"),
     *          @SWG\Property(property="date", type="date (YYYY-MM-DDD)"),
     *          @SWG\Property(property="invoiceNumber", type="string"),
     *          @SWG\Property(property="description", type="string"),
     *          @SWG\Property(property="contractor", type="integer"),
     *          @SWG\Property(property="amountNet", type="integer"),
     *          @SWG\Property(property="amountGross", type="integer"),
     *          @SWG\Property(property="taxValue", type="integer"),
     *          @SWG\Property(property="taxPercent", type="integer"),
     *     )
     * )
     * 
     * @SWG\Response(
     *     response=201,
     *     description="Returned when successful",
     *     @SWG\Schema(
     *         ref=@Model(type=BillingItem::class)
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
    public function costPostAction(
            BillingMonthDateResolver $dateResolver,
            BillingMonthData $dataProvider,
            BillingMonthSettlementService $settlementService,
            Request $request
    ) { 
        $parameters = $request->request->all();
        $monthDate = null;
        
        if (!isset($parameters['monthDate'])) {
            return new View('Niepoprawna data miesiaca', Response::HTTP_BAD_REQUEST);
        }
        try {
            $monthDate = $dateResolver->getMonthDate(new \DateTime($parameters['monthDate']));
        } catch (\Exception $ex) {
            return new View('Niepoprawna data miesiaca', Response::HTTP_BAD_REQUEST);
        }
        $billingMonth = $dataProvider->getBillingMonthOrCreate($monthDate);
        unset($parameters['monthDate']);
        
        $item = new BillingItem();
        $item->setBillingMonth($billingMonth);
        $item->setType(CostType::TYPE_ID);
        $form = $this->createForm(BillingItemCostType::class, $item, ['csrf_protection' => false, 'method' => Request::METHOD_POST]);
        $form->submit($parameters);

        if (!$form->isValid()) {          
            return $form;
        }

        try {
            // save item
            $this->getRepository()->save($item);
            
            // recalc settlement
            $settlementService->calcSettlementForMonthDate($monthDate);
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $ex) {
            return new View('Duplicate name. Contractor name should by unique', Response::HTTP_BAD_REQUEST);
        }

        return new View($item, Response::HTTP_CREATED);
    }
    
    /**
     * Update a single cost billing item
     *
     * @Route("/cost", methods={"PATCH"})
     * 
     * @SWG\Tag(name="Bookkeeping - billing")
     * 
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     * )
     * @SWG\Parameter(
     *     name="billing_cost_item",
     *     in="body",
     *     required=true,
     *     description="billing cost item data",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="monthDate", type="date (YYYY-MM-DDD)"),
     *          @SWG\Property(property="date", type="date (YYYY-MM-DDD)"),
     *          @SWG\Property(property="invoiceNumber", type="string"),
     *          @SWG\Property(property="description", type="string"),
     *          @SWG\Property(property="contractor", type="integer"),
     *          @SWG\Property(property="amountNet", type="integer"),
     *          @SWG\Property(property="amountGross", type="integer"),
     *          @SWG\Property(property="taxValue", type="integer"),
     *          @SWG\Property(property="taxPercent", type="integer"),
     *     )
     * )
     * 
     * @SWG\Response(
     *     response=204,
     *     description="Returned when successful",
     *     @SWG\Schema(
     *         ref=@Model(type=BillingItem::class)
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
    public function costPatchAction(
            BillingMonthDateResolver $dateResolver,
            BillingMonthData $dataProvider,
            BillingMonthSettlementService $settlementService,
            Request $request
    ) {
        $parameters = $request->request->all();
        $monthDate = null;
        
        // get params
        $parameters = $request->request->all();
        if (!isset($parameters['id'])) {
            return new View('Wrong parameters, id in contractor data is required', Response::HTTP_BAD_REQUEST);
        }
        $id = $parameters['id'];
        
        // get item
        $item = $this->getRepository()->findOneById($id);
        if (!$item) {
            return new View('Billing item with id '.$id.' does not exist', Response::HTTP_NOT_FOUND);
        }
        $item->setType(CostType::TYPE_ID);
        $item->clearBillingItemPositions();
        
        if (!isset($parameters['monthDate'])) {
            return new View('Niepoprawna data miesiaca', Response::HTTP_BAD_REQUEST);
        }
        try {
            $monthDate = $dateResolver->getMonthDate(new \DateTime($parameters['monthDate']));
        } catch (\Exception $ex) {
            return new View('Niepoprawna data miesiaca', Response::HTTP_BAD_REQUEST);
        }
        $billingMonth = $dataProvider->getBillingMonthOrCreate($monthDate);
        unset($parameters['monthDate']);
        
        $form = $this->createForm(BillingItemCostType::class, $item, ['csrf_protection' => false, 'method' => Request::METHOD_PATCH]);
        unset($parameters['id']);
        $form->submit($parameters);

        if (!$form->isValid()) {          
            return $form;
        }

        try {
            // save item
            $this->getRepository()->save($item);
            
            // recalc settlement
            $settlementService->calcSettlementForMonthDate($monthDate);
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $ex) {
            return new View('Duplicate name. Contractor name should by unique', Response::HTTP_BAD_REQUEST);
        }

        return new View($item, Response::HTTP_CREATED);
    }
    
    
    /**
     * Create a single income billing item
     *
     * @Route("/income", methods={"POST"})
     * 
     * @SWG\Tag(name="Bookkeeping - billing")
     * 
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     * )
     * @SWG\Parameter(
     *     name="billing_income_item",
     *     in="body",
     *     required=true,
     *     description="billing income item data",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="monthDate", type="date (YYYY-MM-DDD)"),
     *          @SWG\Property(property="date", type="date (YYYY-MM-DDD)"),
     *          @SWG\Property(property="invoiceNumber", type="string"),
     *          @SWG\Property(property="description", type="string"),
     *          @SWG\Property(property="contractor", type="integer"),
     *          @SWG\Property(property="amountNet", type="integer"),
     *          @SWG\Property(property="amountGross", type="integer"),
     *          @SWG\Property(property="taxValue", type="integer"),
     *          @SWG\Property(property="taxPercent", type="integer"),
     *     )
     * )
     * 
     * @SWG\Response(
     *     response=201,
     *     description="Returned when successful",
     *     @SWG\Schema(
     *         ref=@Model(type=BillingItem::class)
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
    public function incomePostAction(
            BillingMonthDateResolver $dateResolver,
            BillingMonthData $dataProvider,
            BillingMonthSettlementService $settlementService,
            Request $request
    ) {
        $parameters = $request->request->all();
        $monthDate = null;
        
        if (!isset($parameters['monthDate'])) {
            return new View('Niepoprawna data miesiaca', Response::HTTP_BAD_REQUEST);
        }
        try {
            $monthDate = $dateResolver->getMonthDate(new \DateTime($parameters['monthDate']));
        } catch (\Exception $ex) {
            return new View('Niepoprawna data miesiaca', Response::HTTP_BAD_REQUEST);
        }
        $billingMonth = $dataProvider->getBillingMonthOrCreate($monthDate);
        unset($parameters['monthDate']);
        
        $item = new BillingItem();
        $item->setBillingMonth($billingMonth);
        $item->setType(IncomeType::TYPE_ID);
        $form = $this->createForm(BillingItemIncomeType::class, $item, ['csrf_protection' => false, 'method' => Request::METHOD_POST]);
        // remove ids
        unset($parameters['id']);
        if (isset($parameters['billingItemPositions']) && !empty($parameters['billingItemPositions'])) {
            foreach ($parameters['billingItemPositions'] as $key => $data) {
                unset($parameters['billingItemPositions'][$key]['id']);
            }
        }
        $form->submit($parameters);

        if (!$form->isValid()) {          
            return $form;
        }

        try {
            // save item
            $this->getRepository()->save($item);
            
            // recalc settlement
            $settlementService->calcSettlementForMonthDate($monthDate);
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $ex) {
            return new View('Duplicate name. Contractor name should by unique', Response::HTTP_BAD_REQUEST);
        }

        return new View($item, Response::HTTP_CREATED);
    }
    
    /**
     * Update a single income billing item
     *
     * @Route("/income", methods={"PATCH"})
     * 
     * @SWG\Tag(name="Bookkeeping - billing")
     * 
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     * )
     * @SWG\Parameter(
     *     name="billing_income_item",
     *     in="body",
     *     required=true,
     *     description="billing income item data",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="monthDate", type="date (YYYY-MM-DDD)"),
     *          @SWG\Property(property="date", type="date (YYYY-MM-DDD)"),
     *          @SWG\Property(property="invoiceNumber", type="string"),
     *          @SWG\Property(property="description", type="string"),
     *          @SWG\Property(property="contractor", type="integer"),
     *          @SWG\Property(property="amountNet", type="integer"),
     *          @SWG\Property(property="amountGross", type="integer"),
     *          @SWG\Property(property="taxValue", type="integer"),
     *          @SWG\Property(property="taxPercent", type="integer"),
     *     )
     * )
     * 
     * @SWG\Response(
     *     response=204,
     *     description="Returned when successful",
     *     @SWG\Schema(
     *         ref=@Model(type=BillingItem::class)
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
    public function incomePatchAction(
            BillingMonthDateResolver $dateResolver,
            BillingMonthData $dataProvider,
            BillingMonthSettlementService $settlementService,
            Request $request
    ) {
        $parameters = $request->request->all();
        $monthDate = null;
        
        // get params
        $parameters = $request->request->all();
        if (!isset($parameters['id'])) {
            return new View('Wrong parameters, id in contractor data is required', Response::HTTP_BAD_REQUEST);
        }
        $id = $parameters['id'];
        
        // get item
        $item = $this->getRepository()->findOneById($id);
        if (!$item) {
            return new View('Billing item with id '.$id.' does not exist', Response::HTTP_NOT_FOUND);
        }
        $item->setType(IncomeType::TYPE_ID);
        
        if (!isset($parameters['monthDate'])) {
            return new View('Niepoprawna data miesiaca', Response::HTTP_BAD_REQUEST);
        }
        try {
            $monthDate = $dateResolver->getMonthDate(new \DateTime($parameters['monthDate']));
        } catch (\Exception $ex) {
            return new View('Niepoprawna data miesiaca', Response::HTTP_BAD_REQUEST);
        }
        $billingMonth = $dataProvider->getBillingMonthOrCreate($monthDate);
        unset($parameters['monthDate']);
        
        $form = $this->createForm(BillingItemIncomeType::class, $item, ['csrf_protection' => false, 'method' => Request::METHOD_PATCH]);
        // remove ids
        unset($parameters['id']);
        if (isset($parameters['billingItemPositions']) && !empty($parameters['billingItemPositions'])) {
            foreach ($parameters['billingItemPositions'] as $key => $data) {
                unset($parameters['billingItemPositions'][$key]['id']);
            }
        }
        $form->submit($parameters);

        if (!$form->isValid()) {          
            return $form;
        }

        try {
            // save item
            $this->getRepository()->save($item);
            
            // recalc settlement
            $settlementService->calcSettlementForMonthDate($monthDate);
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $ex) {
            return new View('Duplicate name. Contractor name should by unique', Response::HTTP_BAD_REQUEST);
        }

        return new View($item, Response::HTTP_CREATED);
    }
    
    
    /**
     * Create a single US billing item
     *
     * @Route("/us", methods={"POST"})
     * 
     * @SWG\Tag(name="Bookkeeping - billing")
     * 
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     * )
     * @SWG\Parameter(
     *     name="billing_us_item",
     *     in="body",
     *     required=true,
     *     description="billing us item data",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="monthDate", type="date (YYYY-MM-DDD)"),
     *          @SWG\Property(property="date", type="date (YYYY-MM-DDD)"),
     *          @SWG\Property(property="invoiceNumber", type="string"),
     *          @SWG\Property(property="description", type="string"),
     *          @SWG\Property(property="contractor", type="integer"),
     *          @SWG\Property(property="amountGross", type="integer"),
     *     )
     * )
     * 
     * @SWG\Response(
     *     response=201,
     *     description="Returned when successful",
     *     @SWG\Schema(
     *         ref=@Model(type=BillingItem::class)
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
    public function usPostAction(
            BillingMonthDateResolver $dateResolver,
            BillingMonthData $dataProvider,
            BillingMonthSettlementService $settlementService,
            Request $request
    ) {
        $parameters = $request->request->all();
        $monthDate = null;
        
        if (!isset($parameters['monthDate'])) {
            return new View('Niepoprawna data miesiaca', Response::HTTP_BAD_REQUEST);
        }
        try {
            $monthDate = $dateResolver->getMonthDate(new \DateTime($parameters['monthDate']));
        } catch (\Exception $ex) {
            return new View('Niepoprawna data miesiaca', Response::HTTP_BAD_REQUEST);
        }
        $billingMonth = $dataProvider->getBillingMonthOrCreate($monthDate);
        unset($parameters['monthDate']);
        
        $item = new BillingItem();
        $item->setBillingMonth($billingMonth);
        $item->setType(UsType::TYPE_ID);
        $form = $this->createForm(BillingItemUsType::class, $item, ['csrf_protection' => false, 'method' => Request::METHOD_POST]);
        $form->submit($parameters);

        if (!$form->isValid()) {          
            return $form;
        }

        try {
            $item->setAmountNet($item->getAmountGross());
            // save item
            $this->getRepository()->save($item);
            
            // recalc settlement
            $settlementService->calcSettlementForMonthDate($monthDate);
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $ex) {
            return new View('Duplicate name. Contractor name should by unique', Response::HTTP_BAD_REQUEST);
        }

        return new View($item, Response::HTTP_CREATED);
    }
    
    
    /**
     * Update a single US billing item
     *
     * @Route("/us", methods={"PATCH"})
     * 
     * @SWG\Tag(name="Bookkeeping - billing")
     * 
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     * )
     * @SWG\Parameter(
     *     name="billing_us_item",
     *     in="body",
     *     required=true,
     *     description="billing us item data",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="monthDate", type="date (YYYY-MM-DDD)"),
     *          @SWG\Property(property="date", type="date (YYYY-MM-DDD)"),
     *          @SWG\Property(property="invoiceNumber", type="string"),
     *          @SWG\Property(property="description", type="string"),
     *          @SWG\Property(property="contractor", type="integer"),
     *          @SWG\Property(property="amountGross", type="integer"),
     *     )
     * )
     * 
     * @SWG\Response(
     *     response=204,
     *     description="Returned when successful",
     *     @SWG\Schema(
     *         ref=@Model(type=BillingItem::class)
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
    public function usPatchAction(
            BillingMonthDateResolver $dateResolver,
            BillingMonthData $dataProvider,
            BillingMonthSettlementService $settlementService,
            Request $request
    ) {
        $parameters = $request->request->all();
        $monthDate = null;
        
        // get params
        $parameters = $request->request->all();
        if (!isset($parameters['id'])) {
            return new View('Wrong parameters, id in contractor data is required', Response::HTTP_BAD_REQUEST);
        }
        $id = $parameters['id'];
        
        // get item
        $item = $this->getRepository()->findOneById($id);
        if (!$item) {
            return new View('Billing item with id '.$id.' does not exist', Response::HTTP_NOT_FOUND);
        }
        $item->setType(UsType::TYPE_ID);
        $item->clearBillingItemPositions();
        
        if (!isset($parameters['monthDate'])) {
            return new View('Niepoprawna data miesiaca', Response::HTTP_BAD_REQUEST);
        }
        try {
            $monthDate = $dateResolver->getMonthDate(new \DateTime($parameters['monthDate']));
        } catch (\Exception $ex) {
            return new View('Niepoprawna data miesiaca', Response::HTTP_BAD_REQUEST);
        }
        $billingMonth = $dataProvider->getBillingMonthOrCreate($monthDate);
        unset($parameters['monthDate']);
        
        $form = $this->createForm(BillingItemUsType::class, $item, ['csrf_protection' => false, 'method' => Request::METHOD_PATCH]);
        unset($parameters['id']);
        $form->submit($parameters);

        if (!$form->isValid()) {          
            return $form;
        }

        try {          
            $item->setAmountNet($item->getAmountGross());
            $item->setTaxPercent(0);
            $item->setTaxValue(0);
            
            // save item
            $this->getRepository()->save($item);
            
            // recalc settlement
            $settlementService->calcSettlementForMonthDate($monthDate);
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $ex) {
            return new View('Duplicate name. Contractor name should by unique', Response::HTTP_BAD_REQUEST);
        }

        return new View($item, Response::HTTP_CREATED);
    }
    
    
    /**
     * Create a single ZUS billing item
     *
     * @Route("/zus", methods={"POST"})
     * 
     * @SWG\Tag(name="Bookkeeping - billing")
     * 
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     * )
     * @SWG\Parameter(
     *     name="billing_zus_item",
     *     in="body",
     *     required=true,
     *     description="billing zus item data",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="monthDate", type="date (YYYY-MM-DDD)"),
     *          @SWG\Property(property="date", type="date (YYYY-MM-DDD)"),
     *          @SWG\Property(property="invoiceNumber", type="string"),
     *          @SWG\Property(property="description", type="string"),
     *          @SWG\Property(property="contractor", type="integer"),
     *          @SWG\Property(property="amountGross", type="integer"),
     *     )
     * )
     * 
     * @SWG\Response(
     *     response=201,
     *     description="Returned when successful",
     *     @SWG\Schema(
     *         ref=@Model(type=BillingItem::class)
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
    public function zusPostAction(
            BillingMonthDateResolver $dateResolver,
            BillingMonthData $dataProvider,
            BillingMonthSettlementService $settlementService,
            Request $request
    ) {
        $parameters = $request->request->all();
        $monthDate = null;
        
        if (!isset($parameters['monthDate'])) {
            return new View('Niepoprawna data miesiaca', Response::HTTP_BAD_REQUEST);
        }
        try {
            $monthDate = $dateResolver->getMonthDate(new \DateTime($parameters['monthDate']));
        } catch (\Exception $ex) {
            return new View('Niepoprawna data miesiaca', Response::HTTP_BAD_REQUEST);
        }
        $billingMonth = $dataProvider->getBillingMonthOrCreate($monthDate);
        unset($parameters['monthDate']);
        
        $item = new BillingItem();
        $item->setBillingMonth($billingMonth);
        $item->setType(ZusType::TYPE_ID);
        $form = $this->createForm(BillingItemZusType::class, $item, ['csrf_protection' => false, 'method' => Request::METHOD_POST]);
        $form->submit($parameters);

        if (!$form->isValid()) {          
            return $form;
        }

        try {
            $item->setAmountNet($item->getAmountGross());
            // save item
            $this->getRepository()->save($item);
            
            // recalc settlement
            $settlementService->calcSettlementForMonthDate($monthDate);
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $ex) {
            return new View('Duplicate name. Contractor name should by unique', Response::HTTP_BAD_REQUEST);
        }

        return new View($item, Response::HTTP_CREATED);
    }
    
    /**
     * Update a single ZUS billing item
     *
     * @Route("/zus", methods={"PATCH"})
     * 
     * @SWG\Tag(name="Bookkeeping - billing")
     * 
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     * )
     * @SWG\Parameter(
     *     name="billing_zus_item",
     *     in="body",
     *     required=true,
     *     description="billing zus item data",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="monthDate", type="date (YYYY-MM-DDD)"),
     *          @SWG\Property(property="date", type="date (YYYY-MM-DDD)"),
     *          @SWG\Property(property="invoiceNumber", type="string"),
     *          @SWG\Property(property="description", type="string"),
     *          @SWG\Property(property="contractor", type="integer"),
     *          @SWG\Property(property="amountGross", type="integer"),
     *     )
     * )
     * 
     * @SWG\Response(
     *     response=204,
     *     description="Returned when successful",
     *     @SWG\Schema(
     *         ref=@Model(type=BillingItem::class)
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
    public function zusPatchAction(
            BillingMonthDateResolver $dateResolver,
            BillingMonthData $dataProvider,
            BillingMonthSettlementService $settlementService,
            Request $request
    ) {
        $parameters = $request->request->all();
        $monthDate = null;
        
        // get params
        $parameters = $request->request->all();
        if (!isset($parameters['id'])) {
            return new View('Wrong parameters, id in contractor data is required', Response::HTTP_BAD_REQUEST);
        }
        $id = $parameters['id'];
        
        // get item
        $item = $this->getRepository()->findOneById($id);
        if (!$item) {
            return new View('Billing item with id '.$id.' does not exist', Response::HTTP_NOT_FOUND);
        }
        $item->setType(ZusType::TYPE_ID);
        $item->clearBillingItemPositions();
        
        if (!isset($parameters['monthDate'])) {
            return new View('Niepoprawna data miesiaca', Response::HTTP_BAD_REQUEST);
        }
        try {
            $monthDate = $dateResolver->getMonthDate(new \DateTime($parameters['monthDate']));
        } catch (\Exception $ex) {
            return new View('Niepoprawna data miesiaca', Response::HTTP_BAD_REQUEST);
        }
        $billingMonth = $dataProvider->getBillingMonthOrCreate($monthDate);
        unset($parameters['monthDate']);
        
        $form = $this->createForm(BillingItemZusType::class, $item, ['csrf_protection' => false, 'method' => Request::METHOD_PATCH]);
        unset($parameters['id']);
        $form->submit($parameters);

        if (!$form->isValid()) {          
            return $form;
        }

        try {          
            $item->setAmountNet($item->getAmountGross());
            $item->setTaxPercent(0);
            $item->setTaxValue(0);
            
            // save item
            $this->getRepository()->save($item);
            
            // recalc settlement
            $settlementService->calcSettlementForMonthDate($monthDate);
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $ex) {
            return new View('Duplicate name. Contractor name should by unique', Response::HTTP_BAD_REQUEST);
        }

        return new View($item, Response::HTTP_CREATED);
    }
    
    
    /**
     * Delete existing Billing item 
     *
     * @Route("/{id}", methods={"DELETE"})
     * 
     * @SWG\Tag(name="Bookkeeping - billing")
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
     *     description="billing item id"
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
    public function deleteAction(
            BillingMonthSettlementService $settlementService,
            int $id
    ){
        /* @var $item BillingItem */
        $item = $this->getRepository()->findOneById($id);
        if (!$item) {
            return new View('Billing item with id '.$id.' does not exist', Response::HTTP_NOT_FOUND);
        }
        
        $monthDate = $item->getBillingMonth()->getDate();
        
        $this->getRepository()->delete($item);
            
        // recalc settlement
        $settlementService->calcSettlementForMonthDate($monthDate);

        return new View(null, Response::HTTP_NO_CONTENT);
    }
    
    /**
     * generate PDF 
     *
     * @Route("/income/pdf/{id}", methods={"GET"})
     * 
     * @SWG\Tag(name="Bookkeeping - billing")
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
     *     description="billing item id"
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
     */
    public function pdfAction(SettingService $settingService, AmountText $billingAmountText, int $id)
    {
        /* @var $item BillingItem */
        $item = $this->getRepository()->findOneById($id);
        if (!$item) {
            return new View('Billing item with id '.$id.' does not exist', Response::HTTP_NOT_FOUND);
        }
        
        if ($item->getType() !== IncomeType::TYPE_ID) {
            return new View('Cannot generate pdf for items other then income', Response::HTTP_NOT_FOUND);
        }
        
        // generate dir source
        $tmpPath = $this->getParameter('temp_dir') . 'tmp_invoice.pdf';
        
        // get company data
        $companyData = $settingService->getCompanyData();
        
        $amountGross = number_format(($item->getAmountGross() / 100), 2, '.', '');
        
        $amountGrossText = $billingAmountText->convertAmountToText($amountGross);
        
        try {           
            $mpdf = new Mpdf();
            $html = $this->renderView('Bookkeeping/Billing/Invoice/invoice_arcyreklama_template.html.twig', [
                'item' => $item,
                'companyData' => $companyData,
                'amountGrossText' => $amountGrossText
            ]);
            $mpdf->WriteHTML($html);
            $result = $mpdf->Output($tmpPath, 'F');
            
            $content = file_get_contents($tmpPath);
            unlink($tmpPath);
            
            return new Response($content, 200);

        } catch (\Exception $ex) {
            return new View('Ex: '.$ex->getMessage().', '.$ex->getFile().':'.$ex->getLine(), Response::HTTP_NOT_FOUND);
        }
    }
}
