<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\PetRepository;
use App\Repository\ApiResponseRepository;

class PetController extends AbstractController
{
    /**
     * @var PetRepository
     */
    private $petRepository;
    /**
     * @var ApiResponseRepository
     */
    private $ApiResponseRepository;

    public function __construct(PetRepository $petRepository, ApiResponseRepository $apiResponseRepository)
    {
        $this->petRepository = $petRepository;
        $this->apiResponseRepository = $apiResponseRepository;
    }

    /**
     * @Route("/pet/{petId}", methods={"GET"}, name="get_pet_byId")
     */
    public function getPetById(int $petId): JsonResponse
    {
        $response = [];
        $pet = $this->petRepository->getPetById($petId);
        if (empty($pet)) {
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
                "data" => $pet
            ];
        }
        return new JsonResponse($response);
    }

    /**
     * @Route("/pet", methods={"POST"}, name="addPet")
     */
    public function addPet(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['name']) && !isset($data['photoUrls'])) {
            $codeStatus = $this->apiResponseRepository->findOneBy(['code' => 405]);
        } else {
            
            $petSaved = $this->petRepository->saveRowPet($data);
            if ($petSaved["success"] == "false") {
                $codeStatus = $this->apiResponseRepository->findOneBy(['code' => 405]);
            } else {
                $codeStatus = $this->apiResponseRepository->findOneBy(['code' => 200]);
            }
            
        }
        return new JsonResponse(['message' => $codeStatus->getMessage(), "status" => $codeStatus->getCode()]);
    }
}
