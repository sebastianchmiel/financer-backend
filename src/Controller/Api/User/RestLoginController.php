<?php

namespace App\Controller\Api\User;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;


class RestLoginController extends AbstractController
{
    /**
     * @SWG\Response(
     *     response=200,
     *     description="Returns token"
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Bad credentials (wrong structure or uncorrect data)"
     * )
     * @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         description="list of unit measure objects",
     *         required=true,
     *         @SWG\Schema(
     *             @SWG\Property(property="username", type="string"),
     *             @SWG\Property(property="password", type="string")
     *         )
     *     ),
     * @SWG\Tag(name="authorization")
     * 
     * @throws \DomainException
     * 
     * @Route("/api/login", methods={"POST"})
    */
    public function postAction() {
        throw new \DomainException('You should never see this');
    }
}
