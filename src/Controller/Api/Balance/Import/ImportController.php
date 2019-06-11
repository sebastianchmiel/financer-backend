<?php

namespace App\Controller\Api\Balance\Import;

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
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Domain\Balance\Import\Import;

/**
 * @Route("/api/balance/import")
 */
class ImportController extends AbstractFOSRestController implements ClassResourceInterface {
    /**
     * Import bank statement from file
     *
     * @Route("/", methods={"POST"}, name="balance_import")
     * 
     * @SWG\Tag(name="Balance - import")
     * 
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     * )
     * @SWG\Parameter(
     *     name="file",
     *     in="body",
     *     description="import file",
     *     @SWG\Schema(
     *         type="file"
     *     )
     * )
     * 
     * @SWG\Response(
     *     response=201,
     *     description="Returned when successful"
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
    public function postAction(Import $importHandler, Request $request) {
        /* @var $file UploadedFile */
        $file = $request->files->get('file');

        if ($file) {
            // Move the file to the directory where brochures are stored
            try {
                $fileName = $this->generateUniqueFileName();
                
                $file->move(
                    $this->getParameter('temp_dir'),
                    $fileName
                );
                $filePath = $this->getParameter('temp_dir') . $fileName;
                
                // import file data
                $savedItemsCount = $importHandler->importFile($filePath);
                
                return new View($savedItemsCount, Response::HTTP_OK);
            } catch (FileException $ex) {
                return new View('Error during import data ('.$ex->getMessage().')', Response::HTTP_BAD_REQUEST);
            }
        } else {
            return new View('File is required', Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @return ContractorRepository
     */
//    private function getRepository(): ContractorRepository {
//        return $this->getDoctrine()->getManager()->getRepository(Contractor::class);
//    }

    /**
     * generate unique file name
     * 
     * @return string
     */
    private function generateUniqueFileName() {
        return 'bank-statement-'.(new \DateTime('now'))->format('Y-m-d-H-i-s').'.csv';
    }
    
}
