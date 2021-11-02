<?php

namespace User\Repository;

use User\Entity\Bus;
use Doctrine\ORM\EntityRepository;

class BusRepository extends EntityRepository
{
    /**
     * findBusById - найти автобус по идентификатору
     * @param $id
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findBusById($id)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder  = $entityManager->createQueryBuilder();

        $queryBuilder->select("b")
            ->from(Bus::class, "b")
            ->where("b.id = ?1")
            ->setParameter("1", $id);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * findBusConfigByBusId - ищет конфигурацию автобуса по его идентификатору
     * @param $id - идентификатор автобуса
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findBusConfigByBusId($id)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder->select("b.config")
            ->from(Bus::class, "b")
            ->where("b.id = ?1")
            ->setParameter("1", $id);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}

