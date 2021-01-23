<?php

namespace App\Repository;

use App\Entity\Pet;
use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CategoryRepository;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

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
        ValidatorInterface $validator
    ) {
        parent::__construct($registry, Pet::class);
        $this->em = $entityManagerInterface;
        $this->categoryRepo = $categoryRepository;
        $this->validator = $validator;
    }

    public function getPetById(int $petId)
    {

        $connection = $this->em->getConnection();
        $data = [];
        $sQ = "SELECT * FROM pet WHERE id = {$petId} ";
        $query = $connection->prepare($sQ);
        $query->execute();
        $results = $query->fetchAll();

        if (count($results) > 0) {
           $data = $results[0];
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
                    throw new Exception("Category Invalid", 1);
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
