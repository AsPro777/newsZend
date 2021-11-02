<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace User\Repository;

use Doctrine\ORM\EntityRepository;
use User\Entity\Responses;
use User\Service\ResponsesManager;

/**
 * Description of ResponsesRepository
 *
 * @author Анна
 */
class ResponsesRepository extends EntityRepository
{
    /**
     * updateRowDeleted - обновить строку. Поставить deleted=true если кассир в админке нажал на кнопку Удалить
     * @param $reisId - идентификатор рейса
     * @param $selerId - идентификатор пользователя
     * @param $placeNum - номер резервируемого места в автобусе
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */

/*обновить столбец deleted в таблице responses когда пользователь нажал на кнопку Удалить запись*/
    public function updateRowDeleted($rowId)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $q=$queryBuilder->update(Responses::class,'r')
                     ->set('r.deleted', 'true')
                     ->where('r.id = :param')->setParameter('param', $rowId) ->getQuery();

        return !empty($q->getResult());
    }

/*обновить столбец readed в таблице responses когда пользователь нажал на кнопку Прочитано*/
    public function updateRowReaded($rowId)
    {
       $queryBuilder = $this->getEntityManager()->createQueryBuilder();
       $q=$queryBuilder->update(Responses::class,'r')
                     ->set('r.readed', 'true')
                     ->where('r.id = :param')->setParameter('param', $rowId) ->getQuery();

       return !empty($q->getResult());
    }

    /*вывести прочитаные или непрочитаные или удаленные или все записи из табл в зависимости от выбранного пункта из комбобокса*/
    public function getRows($start=0, $filteredFromValue,$filteredToValue,$pageSize,$property)
    {
       if($start==0)$select='r';
       else $select='r.id,r.dateReg,r.readed,r.deleted,r.data';
       $qb=$this->getEntityManager()->createQueryBuilder();
       $queryBuilder=$this->getEntityManager()->createQueryBuilder();

       $qb->select($select)
          ->from(Responses::class,'r')
          ->orderBy("r.dateReg");

       if(!empty($filteredFromValue)&&!empty($filteredToValue)){
          $qb->where("r.dateReg between ?1 and ?2")
             ->setParameter('1', $filteredFromValue )
             ->setParameter('2', $filteredToValue );
       }

       $res=$qb->getQuery()->getResult();
       $count=count($res);
       $queryBuilder=$qb;
       $queryBuilder->setFirstResult($start*$pageSize)
                    ->setMaxResults($pageSize);

        switch ($property) {

               case 'readed':    { $queryBuilder->andWhere("r.readed=true")->andWhere("r.deleted=false");break; }
               case 'nonReaded': { $queryBuilder->andWhere("r.readed=false")->andWhere("r.deleted=false");break; }
               case 'deleted':   { $queryBuilder->andWhere("r.deleted=true");break; }
               case 'all':       { break; }
               default:          { return ['success'=>0,'result'=>'Ошибка ввода']; break; }
        }

        $result=$queryBuilder->getQuery()->getResult();

        if(($start*$pageSize+$pageSize)>=count($res)) $request['flag']=1;
          else $request['flag']=0;

        $request['result']=$result;
        if(!isset($result)) $request='';
        return ['success'=>1,'result'=>$request];
    }

    /*отобразит следующие 50 записей*/
    public function getPage($start=0,$radioId,$dt,$pageSize)
    {
        $responsesManager=new ResponsesManager($this);
        $dtFromTo=$responsesManager->getFilterParams($dt);

        $result= $this->getRows($start,$dtFromTo['filteredFromValue'],$dtFromTo['filteredToValue'],$pageSize,$radioId);

     if(!isset($result['result'])) $result['result']='';
     return $result['result'];
    }
}

