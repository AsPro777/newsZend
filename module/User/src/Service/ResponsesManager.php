<?php
namespace User\Service;

use User\Entity\Responses;
use Zend\Filter\StaticFilter;

// Сервис The PostManager, отвечающий за дополнение новых постов.
class ResponsesManager
{
    /**
     * Doctrine entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
    private $response;
    private $resultTpl = ["success" => false];
    private $pageSize = 50;

    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
        $this->response=new Responses();
    }

    // Этот метод добавляет новую строку в табл responses.
    public function addNewResponse($data)
    {
        $this->response->setReaded($data['readed']);
        $this->response->setDeleted($data['deleted']);
        $this->response->setData($data['data']);

        /*setDateReg() можно не использовать. Дата вставится сама тк в сущности используется @ORM\PrePersist*/

        try{
            // Добавляем сущность в менеджер сущностей.
            $this->entityManager->persist($this->response);
            // Применяем изменения к базе данных.
            $this->entityManager->flush();
        } catch (\Exception $e)
        {
            return false;
        }

        return true;
    }

    //этот метод удаляет строку из табл responses
    public function deleteRowTrigger($resp)
    {
        $datNow=new \DateTime();
        $datDateReg=new \DateTime($resp->getDateReg());//дата из строки ($resp) преобразовалась к формату переменной $datNow для того чтобы потом можно было их сравнить
       /*$a= \Application\Filter\DateFilter::diff($datDateReg->format("d.m.Y H:i")); /*-5м 28д ......*/

        $days=0;
        $diff=$datDateReg->diff($datNow);//в классе  DateTime есть метод diff. Теперь в переменной diff-объект. Это разница между сегодняшней датой и датой из $resp
        $month=$diff->m;//сколько месяцев прошло между сегодняшней датой и датой из $resp*/
        if($month==0) $days=$diff->d;
        if($days<90&&$month<3){//если хотим удалить строку которой более 3 месяцев-удаляем строку из таблицы иначе в столбец deleted занесем true
           $this->entityManager->getRepository(Responses::class)->updateRowDeleted($resp->getId());
           try{
                 $this->entityManager->flush();//применим изменения
           }
           catch (\Exception $e){
                   return FALSE;
           }
        }
        else try{
                 $this->entityManager->remove($resp);
                 $this->entityManager->flush();//применим изменения
             }
             catch (\Exception $e){
                   return FALSE;
             }
        return TRUE;
    }

    /*разбираем данные от пост-запроса. Например когда нажали кнопку "Следующие 50 записей"*/
    public function parsePost($post)
    {
        $badResult = $this->resultTpl;
        $badResult["msg"] = "Неизвестная команда!";

        if(!is_array($post) || !isset($post["action"])) return $badResult;

        switch ($post["action"])
        {
            case "responses" : return $this->resultResponse($post);/*данные пришли из submit формы. Когда меняем значение радиокнопок или выбираем дату в календаре*/
            case "next" : return $this->next($post);/*данные пришли из аякс-запроса. Когда нажимаем кнопку Следующие 50 записей*/
            case "read-response" : return $this->readResponse($post);
            case "delete-response" : return $this->deleteResponse($post);
            case "sms" : return $this->sms($post);
            default : return $badResult;
        }

    }

    public function sms($post)
    {
        $multi = ["79107325800" => "Hello World",];
        \Application\Filter\Sms::send($multi);
        return ["success" => 1];
    }


    /*получить данные если выбрали другое значение радиокнопки или сменили дату в календаре*/
    public function resultResponse($post)
    {
      $radioId=$post['accepted'];
      $dt=$post['dt'];
      if($dt=='') $dt=date('d').'.'.date('m').'.'.date('Y');

      $dtFromTo=$this->getFilterParams($dt);

       $responses= $this->entityManager->getRepository(Responses::class)->getRows(0,$dtFromTo['filteredFromValue'],$dtFromTo['filteredToValue'],$this->pageSize,$radioId);

       if($responses['success']==1){
          $filter["dt"]=$dt;
          $filter["accepted"]=$radioId;
          $responses=['filter'=>$filter,
                      'items'=>$responses['result'],
                      'page_size'=> $this->pageSize];
          return $responses;
       }
       else return $responses['result'];
    }

    /*получить следующие записи (кнопка "Следующие 50 записей")*/
    public function next($post)
    {
        $badResult = $this->resultTpl;

        $start = intval(@$post["start"]);
        if(empty($start)) return $badResult;

        $radioId=$post['radio'];
        $dt=$post['dt'];

        $items = $this->entityManager->getRepository(Responses::class)->getPage($start,$radioId,$dt, $this->pageSize);
        $paginData=[];
        $k=0;
            foreach ($items['result'] as $pagin) {
                   $paginData[$k++]=['id'=>$pagin['id'],
                                     'dateReg'=>$pagin['dateReg'],
                                     'readed'=>$pagin['readed'],
                                     'deleted'=>$pagin['deleted'],
                                     'data'=>json_decode($pagin['data'])];
            }
        return [
                "success" => sizeof($items)?true:false,
                "items" => $paginData,
                "page_size" => $this->pageSize,
                "flag"=>$items['flag']
        ];
    }

    /*если нажали на кнопку Прочитать строку*/
    public function readResponse($post)
    {
        return ["success" => $this->entityManager->getRepository(Responses::class)->updateRowReaded(@$post['id'])];
    }

    /*если нажали на кнопку Удалить строку*/
    public function deleteResponse($post)
    {
        return ["success" => $this->entityManager->getRepository(Responses::class)->updateRowDeleted(@$post['id'])];
    }

    /*формирует массив из даты для календаря, идентификатора нажатой радиокнопки*/
    public function getResponsesIndexPageData($post)
    {
        $filter = $post;

        $filter["accepted"] = in_array(@$post["accepted"], ["all", "nonReaded", "readed", "deleted"]) ? $post["accepted"] : "nonReaded";

        $tz = new \DateTimeZone("Europe/Moscow");
        $dt = \Application\Filter\DateFilter::filter(@$post["dt"]);
        if(empty($dt))
        {
            // если no и нет даты - выводить только активные заявки за все даты
            if($filter["accepted"] == 'nonReaded')
                $filter["dt"] = '';
            // иначе выводить за сегодня
            else
                $filter["dt"] = (new \DateTime("today", $tz))->format("d.m.Y");
        }
        else
            $filter["dt"] = $dt;

        $items=$this->entityManager->getRepository(Responses::class)->getRows(0,'','',$this->pageSize,'nonReaded');

          return [
                   "filter" => $filter,
                   "items" => $items['result'],
                   "page_size" => $this->pageSize
          ];
    }

       /*преобразует дату из вида 2020-02-02 к виду 20-02-02 23:59:59*/
    public function getFilterParams($dt)
    {
        if($dt==''){
            $res=['filteredFromValue'=>'',
                   'filteredToValue'=>''];
            return $res;
        }
        $dtFrom=$dt.' 00:00:00';
        $dtTo=$dt.' 23:59:59';

        $filter = new \Zend\Filter\DateTimeFormatter();
        $filter->setFormat('Y-m-d H:i:s');
        $filteredFromValue = $filter->filter($dtFrom);
        $filteredToValue = $filter->filter($dtTo);

        $res=['filteredFromValue'=>$filteredFromValue,
                  'filteredToValue'=>$filteredToValue];
        return $res;
    }
}
