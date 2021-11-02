<?php

namespace User\Repository;

use User\Entity\Reserved;
use Doctrine\ORM\EntityRepository;

class ReservedRepository extends EntityRepository
{
    /**
     * findReservedByReisIdAndSelerIdAndPlaceNum - найти запись о резерве места для текущего пользователя
     * @param $reisId - идентификатор рейса
     * @param $selerId - идентификатор пользователя
     * @param $placeNum - номер резервируемого места в автобусе
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findReservedByReisIdAndSelerIdAndPlaceNum($reisId, $selerId, $placeNum)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select("r")
            ->from(Reserved::class, "r")
            ->where("r.idReis = ?1")
            ->andWhere("r.idSeller = ?2")
            ->andWhere("r.place = ?3")
            ->setParameter("1", $reisId)
            ->setParameter("2", $selerId)
            ->setParameter("3", $placeNum);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * findReservedByReisIdAndPlaceNum - найти запись о резерве места
     * @param $reisId - идентификатор рейса
     * @param $placeNum - номер резервируемого места в автобусе
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findReservedByReisIdAndPlaceNum($reisId, $placeNum)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select("r")
            ->from(Reserved::class, "r")
            ->where("r.idReis = ?1")
            ->andWhere("r.place = ?2")
            ->setParameter("1", $reisId)
            ->setParameter("2", $placeNum);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * findAllReservedPlacesByReisId - найти все зарезервированные места на рейс по его идентификатору
     * @param $reisId - идентификатор рейса
     * @return array
     */
    public function findAllReservedPlacesByReisId($reisId)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select("r")
            ->from(Reserved::class, "r")
            ->where("r.idReis = ?1")
            ->setParameter("1", $reisId);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * findReservedByIdSeller
     * @param $idSeller
     * @return array
     */
    public function findReservedByIdSeller($idSeller)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select("r")
            ->from(Reserved::class, "r")
            ->where("r.idSeller = ?1")
            ->setParameter("1", $idSeller);

        return $queryBuilder->getQuery()->getArrayResult();
    }
}
