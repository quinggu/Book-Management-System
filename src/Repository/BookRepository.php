<?php

namespace App\Repository;

use App\Entity\Book;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    public function findBySearchTerm(string $term): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.title LIKE :term OR b.author LIKE :term')
            ->setParameter('term', '%'.$term.'%')
            ->OrderBy('b.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findAll(): array
    {
        return $this->createQueryBuilder('b')
            ->OrderBy('b.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
