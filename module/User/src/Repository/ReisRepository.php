<?php

namespace User\Repository;
use User\Entity\Reis;
use Doctrine\ORM\EntityRepository;

/**
 * Class ReisRepository
 * @package User\Repository
 */
class ReisRepository extends EntityRepository
{
    /**
     * findRiesById - ищет рейс по идентификатору
     * @param $id - идентификатор рейса
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findRiesById($id)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select("r")
            ->from(Reis::class, "r")
            ->where("r.id = ?1")
            ->andWhere("r.chartered = ?2")
            ->andWhere("r.idReisSchedule = ?3")
            ->andWhere("r.status = ?4")
            ->setParameter("1", $id)
            ->setParameter("2", true)
            ->setParameter("3", 0)
            ->setParameter("4", 1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * findRiesPassengersById - ищет рейс по идентификатору
     * и возвращает только пассажиров данного рейса и идентификатор автобуса
     * @param $id - идентификатор рейса
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findRiesPassengersById($id)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select("r.idBus,r.paxes")
            ->from(Reis::class, "r")
            ->where("r.id = ?1")
            ->andWhere("r.chartered = ?2")
            ->andWhere("r.idReisSchedule = ?3")
            ->andWhere("r.status = ?4")
            ->setParameter("1", $id)
            ->setParameter("2", true)
            ->setParameter("3", 0)
            ->setParameter("4", 1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * ищет рейс по идентификатору рейса провайдера
     * @param String $uid - идентификатор рейса,
     * @param DateTime $date_start - время отправления, DateTime
     * @param String $provider - провайдер
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function checkReisExists($uid='', $date_start=false, $provider="")
    {
        if(empty($date_start)) $date_start = new \DateTime ("now", new \DateTimeZone ("Europe/Moscow"));
        if(empty($uid)) $uid = "0:00:0"; // не имеет значения
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $conn = $this->getEntityManager()->getConnection();
        $qb = $conn->createQueryBuilder();
        $qb->select("r.*")
            ->from("reis", "r")
            ->where("to_char(r.date_start, 'DD.MM.YYYY HH24:MI') = :date_start")->setParameter(":date_start", $date_start->format("d.m.Y H:i"), \PDO::PARAM_STR)
            ->andWhere("r.provider = :provider")->setParameter(":provider", $provider, \PDO::PARAM_STR)
            ->andWhere("(r.params->>'uid')::text = :uid")->setParameter(":uid", $uid, \PDO::PARAM_STR)
            ->setMaxResults(1)
                ;
        $stmt = $qb->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC, \PDO::FETCH_ORI_NEXT);
    }
}

