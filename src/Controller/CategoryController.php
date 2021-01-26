<?php

namespace App\Controller;

use App\Repository\ApiResponseRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    /**
     * @var CategoryRepository
     */
    private $categoryRepository;
    /**
     * @var ApiResponseRepository
     */
    private $ApiResponseRepository;

    public function __construct(CategoryRepository $categoryRepository, ApiResponseRepository $apiResponseRepository)
    {
        $this->categoryRepository = $categoryRepository;
        $this->apiResponseRepository = $apiResponseRepository;
    }

    /**
     * @Route("/category/findAllCategory", methods={"GET"}, name="findAllCategory")
     */
    public function getAllPets(): JsonResponse
    {
        $response = [];
            $categorys = $this->categoryRepository->findAllCategory();
            if (empty($categorys)) {
                $codeStatus = $this->apiResponseRepository->findOneBy(['code' => 404]);
                $response = [
                    "message" => $codeStatus->getMessage(),
                    "status" => $codeStatus->getCode()
                ];
            } else {
                $codeStatus = $this->apiResponseRepository->findOneBy(['code' => 200]);
                $response = [
                    "message" => $codeStatus->getMessage(),
                    "status" => $codeStatus->getCode(),
                    "data" => $categorys
                ];
            }
        return new JsonResponse($response);
    }
}
