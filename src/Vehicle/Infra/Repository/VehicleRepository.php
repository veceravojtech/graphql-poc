<?php

namespace App\Vehicle\Infra\Repository;

use App\Vehicle\App\Query\VehiclesQuery;
use App\Person\Domain\Person;
use App\Vehicle\Domain\VehicleAbstract;
use App\Vehicle\Domain\VehicleInterface;
use App\Vehicle\Domain\VehicleRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class VehicleRepository implements VehicleRepositoryInterface
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function findByPerson(Person $person): array
    {
        $qb = $this->getRepository()->createQueryBuilder('v');

        $qb
            ->innerJoin(Person::class, 'p')
            ->innerJoin('p.vehicles', 'phv', \Doctrine\ORM\Query\Expr\Join::WITH, 'phv.id = v.id')
            ->andWhere(
                $qb->expr()->eq(
                    'p.id',
                    $qb->expr()->literal($person->getId())
                )
            );

        return $qb->getQuery()->getResult();
    }

    public function findAll(VehiclesQuery $query): array
    {
        $qb = $this->getRepository()->createQueryBuilder('v');

        if ($query->hasPersonId()) {
            $qb
                ->innerJoin(Person::class, 'p')
                ->innerJoin('p.vehicles', 'phv', \Doctrine\ORM\Query\Expr\Join::WITH, 'phv.id = v.id')
                ->andWhere(
                    $qb->expr()->eq(
                        'p.id',
                        $qb->expr()->literal($query->getPersonId())
                    )
                )
            ;
        }

        if ($query->hasVehicleId()) {
            $qb->andWhere(
                $qb->expr()->eq(
                    'v.id',
                    $qb->expr()->literal($query->getVehicleId())
                )
            );
        }

        if ($query->hasAfter()) {
            $qb->andWhere(
                $qb->expr()->gt(
                    'v.id',
                    $qb->expr()->literal($query->getAfter())
                )
            );
        }

        if ($query->hasOffset()) {
            $qb->setFirstResult($query->getOffset());
        }

        if ($query->hasLimit()) {
            $qb->setMaxResults($query->getLimit());
        }

        $qb->orderBy('v.id', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function find(string $id): ?VehicleInterface
    {
        $qb = $this->getRepository()->createQueryBuilder('v');

        $qb->andWhere(
            $qb->expr()->eq(
                'v.id',
                $qb->expr()->literal($id)
            )
        );

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function save(VehicleInterface $vehicle): VehicleInterface
    {
        $this->em->persist($vehicle);
        $this->em->flush();

        return $vehicle;
    }

    public function delete(VehicleInterface $vehicle): void
    {
        $this->em->remove($vehicle);
        $this->em->flush();
    }

    protected function getRepository(): EntityRepository
    {
        return $this->em->getRepository(VehicleAbstract::class);
    }
}
