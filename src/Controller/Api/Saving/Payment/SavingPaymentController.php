<?php

namespace App\Controller\Api\Saving\Payment;

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
use App\Entity\Saving\Item\SavingItem;
use App\Repository\Saving\Item\SavingItemRepository;
use App\Form\Saving\Item\SavingItemType;
use App\Form\Saving\Payment\SavingPaymentAdd;
use App\Service\Saving\Payment\SavingPaymentService;

/**
 * @Route("/api/saving/payment")
 */
class SavingPaymentController extends AbstractFOSRestController implements ClassResourceInterface {

    /**
     * Add a single saving payment
     *
     * @Route("/add", methods={"POST"})
     * 
     * @SWG\Tag(name="Saving - payment")
     * 
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     * )
     * @SWG\Parameter(
     *     name="savingItem",
     *     in="body",
     *     required=true,
     *     description="saving item data",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="amount", type="integer"),
     *         @SWG\Property(property="name", type="string"),
     *         @SWG\Property(property="date", type="date"),
     *         @SWG\Property(property="items", type="object",
     *              @SWG\Property(property="id", type="integer"),
     *              @SWG\Property(property="amount", type="integer")
     *         )
     *     )
     * )
     * 
     * @SWG\Response(
     *     response=201,
     *     description="Returned when successful",
     *     @SWG\Schema(
     *         ref=@Model(type=SavingItem::class)
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
    public function addAction(SavingPaymentService $savingPaymentService, Request $request) {
        $parameters = $request->request->all();

        $item = [];
        $form = $this->createForm(SavingPaymentAdd::class, $item, ['csrf_protection' => false, 'method' => Request::METHOD_POST]);
        $form->submit($parameters);
        if (!$form->isValid(true)) {          
            return $form;
        }

        try {
            $savingPaymentService->addPayment($form->getData());
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $ex) {
            return new View('Duplicate name. Saving item name should by unique', Response::HTTP_BAD_REQUEST);
        }

        return new View($item, Response::HTTP_CREATED);
    }
}
