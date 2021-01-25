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
     * @Route("/pet/findByStatus", methods={"GET"}, name="getPetByStatus")
     */
    public function getPetByStatus(Request $request): JsonResponse
    {
        $response = [];
        $status = $request->query->get('status');
        if ($status) {
            if ($status != "available" && $status != "pending" && $status != "sold") {
                $codeStatus = $this->apiResponseRepository->findOneBy(['code' => 400]);
                $response = [
                    "message" => "Invalid status value",
                    "status" => $codeStatus->getCode()
                ];
            } else {
                $pet = $this->petRepository->getPetByStatus($status);
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
            }
        } else {
            $codeStatus = $this->apiResponseRepository->findOneBy(['code' => 400]);
            $response = [
                "message" => "Invalid status value",
                "status" => $codeStatus->getCode()
            ];
        }
        return new JsonResponse($response);
    }

    /**
     * @Route("/pet/{petId}", methods={"GET"}, name="get_pet_byId")
     */
    public function getPetById($petId): JsonResponse
    {
        $response = [];
        if (is_numeric($petId)) {
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
        } else {
            $codeStatus = $this->apiResponseRepository->findOneBy(['code' => 400]);
            $response = [
                "message" => $codeStatus->getMessage(),
                "status" => $codeStatus->getCode()
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

        $petSaved = $this->petRepository->saveRowPet($data);
        if ($petSaved["success"] == "false") {
            $codeStatus = $this->apiResponseRepository->findOneBy(['code' => 405]);
        } else {
            $codeStatus = $this->apiResponseRepository->findOneBy(['code' => 200]);
        }

        return new JsonResponse(['message' => $codeStatus->getMessage(), "status" => $codeStatus->getCode()]);
    }

    /**
     * @Route("/pet", methods={"PUT"}, name="updatePet")
     */
    public function updatePet(Request $request): JsonResponse
    {
        $response = [];
        $data = json_decode($request->getContent(), true);
        if (!empty($data)) {
            if (is_numeric($data['id'])) {
                $pet = $this->petRepository->findOneBy(['id' => $data['id']]);
                if (!isset($pet)) {
                    $codeStatus = $this->apiResponseRepository->findOneBy(['code' => 404]);
                    $response = [
                        "message" => $codeStatus->getMessage(),
                        "status" => $codeStatus->getCode()
                    ];
                } else {
                    $petUpdate = $this->petRepository->updateRowPet($data);
                    // print_r($petUpdate);exit;
                    if ($petUpdate["success"] == "false") {
                        if (isset($petUpdate["not found pet"])) {
                            $codeStatus = $this->apiResponseRepository->findOneBy(['code' => 404]);
                        } else {
                            $codeStatus = $this->apiResponseRepository->findOneBy(['code' => 405]);
                        }
                    } else {
                        $codeStatus = $this->apiResponseRepository->findOneBy(['code' => 200]);
                    }

                    $response = [
                        "message" => $codeStatus->getMessage(),
                        "status" => $codeStatus->getCode()
                    ];
                }
            } else {
                $codeStatus = $this->apiResponseRepository->findOneBy(['code' => 400]);
                $response = [
                    "message" => $codeStatus->getMessage(),
                    "status" => $codeStatus->getCode()
                ];
            }
        } else {
            $codeStatus = $this->apiResponseRepository->findOneBy(['code' => 400]);
            $response = [
                "message" => $codeStatus->getMessage(),
                "status" => $codeStatus->getCode()
            ];
        }

        // print_r($response);
        // exit;
        return new JsonResponse($response);
    }

    /**
     * @Route("/pet/{petId}", methods={"DELETE"}, name="deletePet")
     */
    public function removePet(Request $request, $petId): JsonResponse
    {
        $response = [];
        $apiKey = $request->headers->get('api_key');

        if ($apiKey == $_SERVER['APP_SECRET']) {
            if (is_numeric($petId)) {
                $pet = $this->petRepository->findOneBy(['id' => $petId]);
                if (empty($pet)) {
                    $codeStatus = $this->apiResponseRepository->findOneBy(['code' => 404]);
                    $response = [
                        "message" => $codeStatus->getMessage(),
                        "status" => $codeStatus->getCode()
                    ];
                } else {
                    $petRemove = $this->petRepository->removePetById($pet);
                    if ($petRemove["success"] == "false") {
                        $codeStatus = $this->apiResponseRepository->findOneBy(['code' => 400]);
                        $response = [
                            "message" => $codeStatus->getMessage(),
                            "status" => $codeStatus->getCode()
                        ];
                    } else {
                        $codeStatus = $this->apiResponseRepository->findOneBy(['code' => 200]);
                        $response = [
                            "message" => $codeStatus->getMessage(),
                            "status" => $codeStatus->getCode(),
                        ];
                    }
                }
            } else {
                $codeStatus = $this->apiResponseRepository->findOneBy(['code' => 400]);
                $response = [
                    "message" => $codeStatus->getMessage(),
                    "status" => $codeStatus->getCode()
                ];
            }
        }
        return new JsonResponse($response);
    }

    /**
     * @Route("/pet/findAllPets", methods={"GET"}, name="deletePet")
     */
    public function getAllPets(): JsonResponse
    {
        $response = [];
            $pet = $this->petRepository->findAllPets();
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
}
