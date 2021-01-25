<?php

namespace App\Repository;

use App\Entity\Pet;
use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CategoryRepository;
use App\Repository\TagRepository;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @method Pet|null find($id, $lockMode = null, $lockVersion = null)
 * @method Pet|null findOneBy(array $criteria, array $orderBy = null)
 * @method Pet[]    findAll()
 * @method Pet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PetRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        EntityManagerInterface $entityManagerInterface,
        CategoryRepository $categoryRepository,
        TagRepository $tagRepository,
        ValidatorInterface $validator
    ) {
        parent::__construct($registry, Pet::class);
        $this->em = $entityManagerInterface;
        $this->categoryRepo = $categoryRepository;
        $this->tagRepo = $tagRepository;
        $this->validator = $validator;
    }

    public function getPetById(int $petId)
    {
        $data = [];
        $query = $this->createQueryBuilder('p')
            ->select('p, c, t')
            ->leftJoin('p.category', 'c')
            ->leftJoin('p.tags', 't')
            ->where('p.id = :id')
            ->setParameter('id', $petId);
        $sql = $query->getQuery();
        $query = $sql->getArrayResult();

        if (count($query) > 0) {
            $data = $query[0];
        }
        return $data;
    }

    public function getPetByStatus(string $status)
    {
        $data = [];
        $query = $this->createQueryBuilder('p')
            ->select('p, c, t')
            ->leftJoin('p.category', 'c')
            ->leftJoin('p.tags', 't')
            ->where('p.status = :status')
            ->setParameter('status', $status);
        $sql = $query->getQuery();
        $query = $sql->getArrayResult();

        if (count($query) > 0) {
            $data = $query[0];
        }
        return $data;
    }

    public function saveRowPet($request)
    {
        $response = [];
        $pet = new Pet();
        $pet
            ->setName($request["name"])
            ->setStatus($request["status"])
            ->setPhotoUrls($request["photoUrls"]);

        $errors = $this->validateData($pet);

        if (count($errors) > 0) {
            $response = [
                "success" => "false",
            ];
        } else {
            $this->em->persist($pet);

            if (isset($request["category"])) {
                $category = $this->categoryRepo->findOneBy(["id" => $request["category"]["id"]]);
                if (!$category) {
                    throw new \Exception("Category Invalid", 1);
                }
                $pet->setCategory($category);
            }
            if (isset($request["tags"])) {
                foreach ($request["tags"] as $key => $tag) {
                    $tagNew = new Tag();
                    $tagNew->setName($tag["name"]);
                    $this->em->persist($tagNew);

                    $pet->addTag($tagNew);
                }
            }

            $response = [
                "success" => "true",
            ];
            $this->em->persist($pet);
            $this->em->flush();
        }
        return $response;
    }

    public function updateRowPet($request)
    {
        $response = [];
        $query = $this->createQueryBuilder('p')
            ->select('p, c, t')
            ->leftJoin('p.category', 'c')
            ->leftJoin('p.tags', 't')
            ->where('p.id = :id')
            ->setParameter('id', $request['id']);
        $sql = $query->getQuery();
        $pet = $sql->getResult();

        if (count($pet) > 0) {
            foreach ($pet as $result) {
                $result->setName($request['name']);
                $result->setPhotoUrls($request['photoUrls']);
                $result->setStatus($request['status']);

                $errors = $this->validateData($result);
                if (count($errors) > 0) {
                    $response = [
                        "success" => "false",
                    ];
                } else {
                    if (isset($request["category"])) {
                        $category = $this->categoryRepo->findOneBy(["id" => $request["category"]["id"]]);
                        if (!$category) {
                            throw new \Exception("Category Invalid", 1);
                        }
                        $result->setCategory($category);
                    }
                    if (isset($request["tags"])) {
                        if(count($result->getTags()) > 0){
                            foreach ($result->getTags() as $tag) {
                                $result->removeTag($tag);
                                $this->em->flush();
                            }
                        }
                        foreach ($request["tags"] as $key => $t) {
                            $tag = $this->tagRepo->findOneBy(["id" => $t["id"]]);
                            if (!$tag) {
                                $tag = new Tag();
                                $tag->setName($t["name"]);
                                $this->em->persist($tag);
                                $this->em->flush();
                            }
                            $result->addTag($tag);
                        }
                    }
                    $this->em->flush();
                    $response = [
                        "success" => "true",
                    ];
                }
            }
        } else {
            $response = [
                "success" => "false",
                "not found pet" => "404"
            ];
        }
        return $response;
    }

    public function removePetById($petObject)
    {
        $response = [];
        try {
            $this->em->remove($petObject);
            $this->em->flush();
            $response = [
                "success" => "true",
            ];
        } catch (\Throwable $th) {
            $response = [
                "success" => "false",
            ];
        }
        return $response;
    }

    public function validateData($entityObject)
    {
        $errorMessage = [];
        $errors = $this->validator->validate($entityObject);
        if (count($errors) > 0) {
            foreach ($errors as $violation) {
                array_push($errorMessage, $messages[$violation->getPropertyPath()][] = $violation->getMessage());
            }
        }

        return $errorMessage;
    }
    // /**
    //  * @return Pet[] Returns an array of Pet objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Pet
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
