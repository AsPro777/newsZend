<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace User\Repository;

use Doctrine\ORM\EntityRepository;
use User\Entity\News;
use User\Service\AccauntNewsService;
use User\Entity\AlarmSendNews;

/**
 * Description of NewsRepository
 *
 * @author Анна
 */
class NewsRepository extends EntityRepository {
   /**
     * getPage - получить новую строку
     * @param $start-номер страницы
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */

    /**
     * getAllNewsPublic - найти все новости для незарегистрированных пользователей
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */

    public function getAllNewsPublic()
    {
        $query = $this->getEntityManager()
        ->createQueryBuilder()
        ->select('a')
        ->from(News::class, 'a')
        ->orderBy('a.dateReg', 'DESC')
        ->where('a.id >= 1 and a.public = true')
        ->getQuery()
        ->execute();

        return $query;
    }

    /**
     * getItemNewsPublic - найти определенное количество последних новостей
     * @param $limitMin
     * @param $limitMax
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */

    public function getItemNewsPublic($limitMin, $limitMax)
    {
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('a')
            ->from(News::class, 'a')
            ->orderBy('a.dateReg', 'DESC')
            ->where('a.id >= 1 and a.public = true')
            ->setFirstResult($limitMin)
            ->setMaxResults($limitMax)
            ->getQuery()
            ->execute();

        return $query;
    }

     /**
     * getAllNewsUser - найти все новости для зарегистрированных пользователей
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */

    public function getAllNewsUser()
    {
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('a')
            ->from(News::class, 'a')
            ->orderBy('a.dateReg', 'DESC')
            ->where('a.id >= 1 and a.public = false')
            ->getQuery()
            ->execute();

        return $query;
    }

    /**
     * filterNextPrevious - найти следующую запись и предыдущую новости
     * @param $id
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */

    public function filterNextPrevious($id)
    {
        $expr = $this->_em->getExpressionBuilder();
        $qbNext = $this->createQueryBuilder('a')
            ->select(['MIN(a.id)'])
            ->where($expr->gt('a.id', ':id'))
            ->andwhere('a.public = true');
        $qbPrevious = $this->createQueryBuilder('b')
            ->select(['MAX(b.id)'])
            ->where($expr->lt('b.id', ':id'))
            ->andwhere('b.public = true');
        $query = $this->createQueryBuilder('m')
            ->select(['m.id, m.head'])
            ->where($expr->orX(
                $expr->eq('m.id', '(' . $qbNext->getDQL() . ')'),
                $expr->eq('m.id', '(' . $qbPrevious->getDQL() . ')')
            ))
            ->setParameter('id', $id)
            ->addOrderBy('m.id', 'ASC')
            ->getQuery();

        return $query->getScalarResult();
    }

    /*опубликуем новость (поменяем поле status c draft на public)*/
    public function setPublicNews($id)
    {
        $text='public';
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $q=$queryBuilder->update(News::class,'n')
                        ->set('n.status', "'$text'")
                        ->where('n.id = :param')->setParameter('param', $id) ->getQuery();
        if(!empty($q->getResult())) $result=['success' => true];
        else $result=['success' => false];

        return $result;
    }

    /*загрузка первой и последующих страниц*/
    public function getPage($start=0,$pageSize,$userId,$property='')
    {
        $badResult = array("success" => false, "msg" => "Ошибка загрузки следующей страницы!");

        $tickets = [];
        $ticketsQB=[];

        if($start==0)$select='n';
        else $select='n.id,n.head,n.text,n.dateReg,n.addFiles,n.public,n.inside,n.carrier,n.passenger,n.terminal,n.personal,n.status';

        $qb = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder=$this->getEntityManager()->createQueryBuilder();

        if($userId=='1'){
            $qb
              ->select($select)
              ->from(News::class,'n')
              ->setFirstResult($start*$pageSize)
              ->setMaxResults($pageSize);

            $queryBuilder
                ->select($select)
                ->from(News::class,'n');

            /*если администратор выбрал одну из радиокнопок -Все Опубликованые Сохраненные*/
            switch ($property) {

               case 'publiced':  { $qb->where("n.status='public'"); $queryBuilder->where("n.status='public'");break; }
               case 'saved':     { $qb->where("n.status='draft'"); $queryBuilder->where("n.status='draft'"); break; }
               case 'all':       { break; }
               default:          { return ['success'=>0,'result'=>'Ошибка ввода']; break; }
          }

            $qb->orderBy('n.dateReg',\Doctrine\Common\Collections\Criteria::DESC);
            $res=$qb->getQuery()->getResult();
            $resQB=$queryBuilder->getQuery()->getResult();
            $count=count($resQB);

            $tickets = [];
            $ticketsQB=[];

            if(!isset($res)) return $badResult;
        }
        else{
            $queryB=$this->getEntityManager()->createQueryBuilder();
            $res=$queryB->select('n.idNews') /*id всех новостей, доступных для данного пользователя*/
                        ->from(AlarmSendNews::class,'n')
                        ->where("n.idUser = :param")->setParameter('param', $userId) ->getQuery()->getResult();
            $arr=[];
            foreach($res as $val){
                $arr[]=$val['idNews'];
            }

            $res=$qb
              ->select($select)
              ->from(News::class,'n')
              ->setFirstResult($start*$pageSize)
              ->setMaxResults($pageSize)
              ->where("n.status='public'")
              ->andWhere("n.id in (:param1)")->setParameter('param1', $arr)
              ->orderBy('n.dateReg',\Doctrine\Common\Collections\Criteria::DESC)->getQuery()->getResult();

            $queryBuilder
                ->select($select)
                ->from(News::class,'n')
                ->where("n.status='public'")
                ->andWhere("n.id in (:param1)")->setParameter('param1', $arr);


           /* $res=$qb->getQuery()->getResult();*/
            $resQB=$queryBuilder->getQuery()->getResult();
            $count=count($resQB);

            $tickets = [];
            $ticketsQB=[];

            if(!isset($res)) return $badResult;
        }

        if(($start*$pageSize+$pageSize)>=$count) $flag=1;
          else $flag=0;/*если количество оставшихся записей в таблице меньше PageSize то flag=1 и кнопку Следующие 5 не отображать*/

         $request=['flag'=>$flag,
                   'heads'=>$res,
                   'page_size'=> $pageSize,
                   'success'=> TRUE];/*if($start*$pageSize==3){ var_dump($request);die;}*/
        return $request;
    }

    /*обновить текст в новости (столбец text в бд)*/
    public function updateTextNews($text,$id)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $q=$queryBuilder->update(News::class,'n')
                        ->set('n.text', "'$text'")
                        ->where('n.id = :param')->setParameter('param', $id) ->getQuery();


        return !empty($q->getResult());
    }

    /*обновить таблицу alarm_send_news столбец readed поставить в true для пользователя текущего (отметить что он новость просмотрел уже)*/
    public function updateAlarmSendNews($id)
    {
        $cUsr = $this->getCurrentUser();
        if(empty($cUsr)) return 0;

       $conn = $this->getEntityManager()->getConnection();

       $q = "update alarm_send_news set readed=true where id_user=".$cUsr->getId()." and id_news=".$id."";

       $stmt = $conn->executeQuery($q);
    }

    /*обновить текст и изображения в тексте новости после того как пользователь удалил одно из них*/
    public function updateImagesNews($param)
    {
       $nameNews=$param['dataSend']['nameNews'];
       $id=$param['dataSend']['idNews'];

       $queryBuilder = $this->getEntityManager()->createQueryBuilder();
       $image= $this->getAddFilesField($id);

       $queryBuilder=$this->getEntityManager()->createQueryBuilder();

       $imgMas= json_decode($image[0]['addFiles'],true);/*получим в виде массива json- add_files*/

       if($nameNews=='deleteMainPic'){/*если надо удалить картинку с главной темой*/
         foreach ($imgMas['mainPic'] as $val){
            unset($imgMas['mainPic'][0]);
            break;
         }
       }
       else{/*если надо удалить картинку из текста новости*/
        $text=$param['dataSend']['textNews'];
        $i=0;
        foreach ($imgMas['images'] as $val){
           if($val['name']==$nameNews){
               unset($imgMas['images'][$i]);
               sort($imgMas['images']);
               break;
           }
           $i++;
        }
        $queryBuilder->set('n.text', "'$text'");
       }
       $dataFile=json_encode($imgMas);

       $q=$queryBuilder->update(News::class,'n')
                       ->set('n.addFiles', ':data')->setParameter('data', $dataFile)
                       ->where('n.id = ?1')->setParameter('1', $id) ->getQuery();

        if(!empty($q->getResult())) $result=['success' => true];
        else $result=['success' => false];

        return $result;
    }

    /*обновить новость при изменении даты создания, заголовка, текста новости*/
    public function refreshNews($param)
    {
       $manager = new \Application\Service\ImageManager($this->getEntityManager());
       $service= new \User\Service\AccauntNewsService($this->getEntityManager(), []);
       $queryBuilder=$this->getEntityManager()->createQueryBuilder();

       $newHead=$param['editHeadNews'];
       $newText=$param['editTextNews'];
       $newDate=$param['editDateNews'];
       $id=$param['id'];

       $result=['success' => false];

       $queryBuilder->update(News::class,'n');
       if(count($_FILES)!==0){
           $images=$this->getAddFilesField($id);
           $imgMas= json_decode($images[0]['addFiles'],true);
           if(!empty($imgMas['images'])){
               $lastName=@$imgMas['images'][count($imgMas['images'])-1]['name'];
               $i=count($imgMas['images']);
           }
           else {
                 $lastName=0;
                 $i=0;
           }

           $count=1;

           foreach($_FILES as $key=>$value){
            $keyFiles=$key;

            if(isset($_FILES[$keyFiles])&&!empty($_FILES[$keyFiles])){/*если пользователь прикрепил файлы*/
                $checked=$service->checkFile($_FILES[$keyFiles]);/*проверка структуры файлов*/
                if($checked !== true) {
                    return array("msg" => $checked);
                }
                $loadFilePos= strpos($keyFiles,'LoadFile');
                if($loadFilePos !== false) {/*если массив-изображения в самой новости-то в БД занести в json массив images*/
                  if(empty($_FILES[$keyFiles]["type"])) return 0;
                  $data = $manager->base64_encode_image ($_FILES[$keyFiles]["tmp_name"], $_FILES[$keyFiles]["type"]);
                  $imgMas['images'][$i]['name']=(string)($lastName+$count);
                  $count++;
                  $imgMas['images'][$i]['file']=$data;
                  $i++;
                }
                if($keyFiles=='inputLoadMainImg'){
                    if(empty($_FILES[$keyFiles]["type"])) return 0;
                    $data = $manager->base64_encode_image ($_FILES[$keyFiles]["tmp_name"], $_FILES[$keyFiles]["type"]);
                    $imgMas['mainPic'][0]['name']='1';
                    $imgMas['mainPic'][0]['file']=$data;
                }
            }
           }
         $images= json_encode($imgMas);
         $queryBuilder->set('n.addFiles', ':data')->setParameter('data', $images);

       }

       if($newHead!=='') $queryBuilder->set('n.head',"'$newHead'");
       if($newDate!=='') $queryBuilder->set('n.dateReg',"'$newDate'");
       if($newText!=='') $queryBuilder->set('n.text',"'$newText'");

       $queryBuilder->where('n.id = ?1')->setParameter('1', $id);

       $res=$queryBuilder->getQuery()->getResult();
       if($res!==0) $result=['success' => true];
        else $result=['success' => false];

       return $result;
    }

    /*получить поле add_files*/
    public function getAddFilesField($id)
    {
       $queryBuilder = $this->getEntityManager()->createQueryBuilder();
       $img=$queryBuilder->select("n.addFiles")
                         ->from(News::class, "n")
                         ->where("n.id=?1")
                         ->setParameter("1", $id);
       $image=$img->getQuery()->getResult();
       return $image;
    }

    /*получить массив из новостей (предыдущая если есть и следующая если есть) при открытии страницы отдельной новости*/
    public function getFilterNews($id,$userId)
    {
        /*так как доктрина не поддерживает */
        $con= $this->getEntityManager()->getConnection();
        if($userId=='1'){
            $a=$con->executeQuery("select * from news n where n.id = "
                                . "(select max(q.id) from news q where q.id < ".$id.")"
                                . " or n.id=(select min(q.id) from news q where q.id > ".$id.")"
                                . "order by n.id");
        }
        else{
            $a=$con->executeQuery( "select * from news x where x.id="
                                  ." ( select MAX(c.id) FROM( select id from news where id in("
                                  ." select q.id_news from alarm_send_news q"
                                  ." where id_user=".$userId.") and status='public')c where c.id < ".$id.")"
                                  ." or x.id=( select min(c.id) FROM("
                                  ." select id from news where id in("
                                  ." select q.id_news from alarm_send_news q"
                                  ." where id_user=".$userId.") and status='public')c where c.id > ".$id.")");
        }
        $result=[];

       while( $row = $a->fetch(\PDO::FETCH_ASSOC, \PDO::FETCH_ORI_NEXT) )
        {
          $result[]=$row;
        }
       return $result;
    }
}
