<?php
namespace User\Service;

use User\Entity\Tickets;

class TicketsService
{
    protected $entityManager;
    protected $user;
    protected $resultTpl = ["success" => false];
    protected $controller;

    private $pageSize = 10;
   

    public function __construct($controller)
    {
        $this->controller = $controller;
        $this->entityManager = $controller->getEntityManager();
        $this->user = $controller->getUser();
    }

    public function parsePost($post)
    {
        $badResult = $this->resultTpl;
        $badResult["msg"] = "Неизвестная команда!";

        if(!is_array($post) || !isset($post["action"])) return $badResult;

        switch ($post["action"])
        {
            case "new-ticket" : return $this->newTicket($post);
            case "new-answer" : return $this->newAnswer($post);
            case "next" : return $this->next($post);
            case "set-status" : return $this->setStatus($post);
            default : return $badResult;
        }
    }

    /*проверка  загруженых файлов*/
    public function checkFile($fileRec, $options = [])
    {
        if(empty($options))
            $options = [
                'size'=>['min'=>'1kb','max'=>'15mb'],
                'imageSize'=>['minWidth'=>800,'minHeight'=>800,'maxWidth'=>4096,'maxHeight'=>4096],
                /*'mimeType'=>explode(",", ',image/jpeg')*/
                'mimeType'=>['image/jpeg','image/png','image/jpg']
            ];

        $errorReport="Системная ошибка при загрузке файла";

        if(!is_array($fileRec)) return $errorReport;

        /*$errorReport='';*/
        $validator = new \Zend\Validator\File\MimeType($options['mimeType']);
        for($i=0;$i<count($fileRec["tmp_name"]);$i++){
          if(   !isset($fileRec["tmp_name"][$i]) || empty($fileRec["tmp_name"][$i])
                || !isset($fileRec["size"][$i]) || !$fileRec["size"][$i]
                || !isset($fileRec["error"][$i]) || ($fileRec["error"][$i]>0)
                || !isset($fileRec["type"][$i]) || empty($fileRec["type"][$i]) )
              return $errorReport;


          if( ! $validator->isValid($fileRec["tmp_name"][$i]) ) return "Недопустимый тип файла ".$fileRec["name"][$i]."! Допустимые типы файлов: " . implode(", ",$options['mimeType']) .".";

        }

        return true;
    }

    /*сформировать json для записи файлов в БД*/
    public function addFiles($files)
    {
        $manager = new \Application\Service\ImageManager($this->entityManager);

        $images='{"images":[';

        for($i=0;$i<count($files["name"]);$i++){
          if(empty($files["type"][$i])) return 0;

          $data = $manager->base64_encode_image ($files["tmp_name"][$i], $files["type"][$i]);/*кодируем файл*/
          if($data === false) { $images=''; return images; }

           $images=$images.'{"name":"'.($i+1).'","file":"'.$data.'"},';/*сформируем json с именем файла и его закодированным содержимым*/

        }
        $images=substr_replace($images,']}',strrpos($images, ','));

        return $images;

    }

    /*формирование нового тикета*/
    public function newTicket($post)
    {
        $errorMessage = $successMessage = "";
        $last_id = 0;

        $badResult = array("success" => false, "msg" => "Ошибка параметров загрузки!");

        if(isset($_FILES["addFilesInNewTicket"])&&!empty($_FILES["addFilesInNewTicket"])){/*если пользователь прикрепил файлы*/
           $checked=$this->checkFile($_FILES["addFilesInNewTicket"]);/*проверка структуры файлов*/
           if($checked !== true) {
               $newBadResult=array("msg" => $checked);
               $badResult=array_merge($badResult, $newBadResult); return $badResult;
           }
           $images=$this->addFiles($_FILES["addFilesInNewTicket"]);        /*формирование json строки-массива из прикрепленных файлов*/
           if($images=='') {
               $newBadResult=array("msg"=>"Ошибка при записи файла/файлов!");
               $badResult=array_merge($badResult,$newBadResult); return $badResult;
               }
        }
        else $images='{}';

        if(empty($post['name'])) {
            $newBadResult=array("msg"=>"Не указан заголовок!");
            $badResult=array_merge($badResult,$newBadResult); return $badResult;
        }
        if(empty($post['text'])) {
            $newBadResult=array("msg"=>"Не указан текст обращения!");
            $badResult=array_merge($badResult,$newBadResult); return $badResult;
            }
        if(strlen($post['text'])<20) {
            $newBadResult=array("msg"=>"Указан слишком короткий текст обращения!");
            $badResult=array_merge($badResult,$newBadResult); return $badResult;
            }

        $conn = $this->entityManager->getConnection();

        $conn->beginTransaction();
        try{
            $qb = $conn->createQueryBuilder();
            $qb->insert('tickets_head')
                ->values(
                        [
                          'id_owner' => ':idOwner',
                          'name' => ':name',
                        ]
                )
                ->setParameter(':idOwner', $this->user->getId(), \PDO::PARAM_INT)
                ->setParameter(':name', $post["name"], \PDO::PARAM_STR);

            $stmt = $qb->execute();
            $last_id = $conn->lastInsertId();

            if(empty($last_id))
            {
                $conn->rollback();
                return [
                    "success" => false,
                    "err" => "Неудачно!",
                    "msg" => "Ошибка сохранения заголовка обращения!"
                ];
            }

            $qb = $conn->createQueryBuilder();
            $qb->insert('tickets')
                ->values(
                        [
                          'id_ticket_head' => ':id_ticket_head',
                          'id_owner' => ':idOwner',
                          'status' => '0', // 0 - новый, 1 - ожидание, 2 - обработка, 3 - отвечен, 4 - закрыт
                          'text' => ':text',
                          'data' => ':data'
                        ]
                )
                ->setParameter(':id_ticket_head', $last_id, \PDO::PARAM_INT)
                ->setParameter(':idOwner', $this->user->getId(), \PDO::PARAM_INT)
                ->setParameter(':text', $post["text"], \PDO::PARAM_STR)
                ->setParameter(':data', $images, \PDO::PARAM_STR);

            $stmt = $qb->execute();
            $last_id = $conn->lastInsertId();
            $conn->commit();
        } catch (Exception $e) {
            $conn->rollback();
            return [
                "success" => false,
                "err" => "Неудачно!",
                "msg" => "Ошибка сохранения обращения!"
            ];
        }

        if( ! \User\Service\AccessChecker::isSA($this->user) )
            \Application\Filter\MailBlankFilter::sendAdmNotify("Новый тикет: ", $post['name'] ."<br><br>" . $post['text']);

        return [
            "success" => $last_id>0,
        ];
    } // newTicket()

    /*формирование нового ответа в уже существующем тиките. (тикет с заголовком был но пользователь добавил новую запись)*/
    public function newAnswer($post)
    {
        $errorMessage = $successMessage = "";
        $last_id = 0;
        $keyFiles='';
        $badResult = array("success" => false, "msg" => "Ошибка параметров загрузки!");
        foreach($_FILES as $key=>$value){
            $keyFiles=$key;
        }

        if(empty($post['id']))  {
            $newBadResult=array("msg"=>"Не указан тикет!");
            $badResult=array_merge($badResult,$newBadResult); return $badResult;
        }
        if(strlen(@$post['text'])<7) {
            $newBadResult=array("msg"=>"Указан слишком короткий текст обращения!");
            $badResult=array_merge($badResult,$newBadResult); return $badResult; }

        if(isset($_FILES[$keyFiles])&&!empty($_FILES[$keyFiles])){/*если пользователь прикрепил файлы*/
           $checked=$this->checkFile($_FILES[$keyFiles]);/*проверка структуры файлов*/
           if($checked !== true) {
               $newBadResult=array("msg" => $checked);
               $badResult=array_merge($badResult, $newBadResult);
               return $badResult;
           }
           $images=$this->addFiles($_FILES[$keyFiles]);    /*формирование json строки-массива из прикрепленных файлов*/
           if($images=='') {
               $newBadResult=array("msg"=>"Не удалось загрузить файлы!");
               $badResult=array_merge($badResult,$newBadResult);
               return $badResult;
           }
        }
        else $images='{}';

        $conn = $this->entityManager->getConnection();

        $qb = $conn->createQueryBuilder();
        $qb->select('id_owner')
            ->from('tickets')
            ->where('id_ticket_head=:id_ticket_head')
            ->setParameter(':id_ticket_head', $post['id'], \PDO::PARAM_INT)
            ->orderBy('id', 'ASC')
            ->setMaxResults(1);

        $stmt = $qb->execute();
        $row = $stmt->fetch(\PDO::FETCH_ASSOC, \PDO::FETCH_ORI_NEXT);
        if(empty($row))
            return [
                "success" => false,
                "err" => "Неудачно!",
                "msg" => "Не обнаружен тикет для ответа!"
            ];

        $creator = intval($row["id_owner"]);

        $conn->beginTransaction();
        try{
            $qb = $conn->createQueryBuilder();
            $qb->insert('tickets')
                ->values(
                        [
                          'id_ticket_head' => ':id_ticket_head',
                          'id_owner' => ':idOwner',
                          'status' => ($creator == $this->user->getId()) ? '0' : '3', // 0 - новый, 1 - ожидание, 2 - обработка, 3 - отвечен, 4 - отвечен и просмотрен, 5 - закрыт
                          'text' => ':text',
                          'data' => ':data'
                        ]
                )
                ->setParameter(':id_ticket_head', $post['id'], \PDO::PARAM_INT)
                ->setParameter(':idOwner', $this->user->getId(), \PDO::PARAM_INT)
                ->setParameter(':text', $post["text"] . (\User\Service\AccessChecker::isSA($this->user) ? self::PODPIS : ""), \PDO::PARAM_STR)
                ->setParameter(':data', $images, \PDO::PARAM_STR);

            $stmt = $qb->execute();
            $last_id = $conn->lastInsertId();
            $conn->commit();
        } catch (Exception $e) {
            $conn->rollback();
            return [
                "success" => false,
                "err" => "Неудачно!",
                "msg" => "Ошибка сохранения текста обращения!"
            ];
        }

        if( ! \User\Service\AccessChecker::isSA($this->user) )
            \Application\Filter\MailBlankFilter::sendAdmNotify("Новый ответ в тикете", $post['text']);

        return [
            "success" => $last_id>0,
        ];
    } // newAnswer()

    /*получим объект типа Image из БД по id и имени файла*/
     private function getImageObject($id,$name)
     {
        try{
             $ticket = $this->entityManager->getRepository(Tickets::class)->find($id);
        } catch (Exception $e) {
             return false;
        }/*получим запись по id*/

        $data=json_decode($ticket->getData());
        $dataImage=$data->images;
        $fileData=',';
        foreach($dataImage as $val){
            if($name==$val->name) $fileData=$val->file;
            $fileBin=explode(',',$fileData);/*получим бинарный код изображения без заголовков*/
        }

        $decodeData = base64_decode($fileBin[1]);
        $type=explode(';',$fileBin[0]);
        $type=explode(':',$type[0]);
        return [$type[1],imagecreatefromstring($decodeData)];/*создали новое изображение-объект типа Image*/
     }

    /*изменяет размер изображения (картинка становится меньше по горизонтали и вертикали и меньше весит)*/
     public function getResizeTicketImage($id,$name)
     {
       $newImg=$this->getImageObject($id, $name);
       $width=imagesx($newImg[1]); $height=imagesy($newImg[1]);
       $newImageColour=imagecreatetruecolor(70, 70); /*Создание нового полноцветного изображения 70x70*/
       imagecopyresampled($newImageColour, $newImg[1], 0, 0, 0, 0, 70, 70, $width, $height);/*Копирование и изменение размера изображения с ресемплированием */

       switch($newImg[0]){
           case 'image/image/jpeg':

           case 'image/image/jpg': {
                                     header('Content-Type: image/jpeg');
                                     return imagejpeg($newImageColour);  }
           case 'image/image/png': {
                                     header('Content-Type: image/png');
                                     return imagepng($newImageColour);  }
       }

     }

     /*получить изображение в нормальном размере*/
     public function getTicketImage($id,$name)
     {
        $newImg=$this->getImageObject($id, $name);
        switch($newImg[0]){
           case 'image/image/jpeg':

           case 'image/image/jpg': {
                                      header('Content-Type: image/jpeg');
                                      return imagejpeg($newImg[1]);}
           case 'image/image/png': {
                                      header('Content-Type: image/png');
                                      return imagepng($newImg[1]);  }
       }
     }

    private function parseStatus($status)
    {
        // 0 - новый, 1 - ожидание, 2 - обработка, 3 - отвечен, 4 - отвечен и просмотрен, 5 - закрыт
        switch(intval($status))
        {
            case 0:  return '<span class="label label-danger label-bordered">Новый</span>';
            case 1:  return '<span class="label label-warning label-bordered">Ожидание</span>';
            case 2:  return '<span class="label label-warning label-bordered">В работе</span>';
            case 3:  return '<span class="label label-success label-bordered">Есть ответ</span>';
            case 4:  return '<span class="label label-success label-bordered">Отвечен</span>';
            case 5:  return '<span class="label label-black label-bordered">Закрыт</span>';
            default: return "";
        }
    }

    private function getPage($start=0)
    {
        $conn = $this->entityManager->getConnection();
        $qb = $conn->createQueryBuilder();
        $qb
         ->select('id, status, to_char(date_reg, \'DD.MM.YYYY\') as date_reg, to_char(date_modify, \'HH24:MI\') as time_modify, to_char(date_modify, \'DD.MM.YYYY\') as date_modify, name')
         ->from('tickets_head')
         ->where('id_owner='.$this->user->getId())
         ->addOrderBy("tickets_head.status",\Doctrine\Common\Collections\Criteria::ASC)
         ->addOrderBy("tickets_head.date_modify",\Doctrine\Common\Collections\Criteria::DESC)
         ->setFirstResult($start*$this->pageSize)
         ->setMaxResults($this->pageSize);

        if( \User\Service\AccessChecker::isSA($this->user) )
            $qb->where("true");

        $stmt = $qb->execute();
//var_dump($stmt);
        $items = [];
        while( $row = $stmt->fetch(\PDO::FETCH_ASSOC, \PDO::FETCH_ORI_NEXT) )
        {
            $row["status_text"] = $this->parseStatus($row["status"]);

            $qb1 = $conn->createQueryBuilder();
            $qb1
             ->select('t.id, t.status, to_char(t.date_reg, \'HH24:MI\') as time_reg, to_char(t.date_reg, \'DD.MM.YYYY\') as date_reg, t.text, u.id as uid, u.login as user, ut.name as user_type, ut.id as utid, u.f, u.i, u.o, u.data, t.data as jsonData')
             ->from('tickets', 't')
             ->leftJoin('t', 'usr', 'u', 't.id_owner=u.id')
             ->leftJoin('u', 'spr_usr_type', 'ut', 'u.id_usr_type=ut.id')
             ->where('id_ticket_head='.intval("0".$row["id"]) )
             ->orderBy("t.date_reg", "DESC");

            $stmt1 = $qb1->execute();
            $tickets = [];
            while( $row1 = $stmt1->fetch(\PDO::FETCH_ASSOC, \PDO::FETCH_ORI_NEXT) )
            {
                $row1["status_text"] = $this->parseStatus($row["status"]);
                if($row1["utid"] == 1)
                    $row1["user"] = self::ADMIN;
                else {
                    $u = $row1["f"]." ".$row1["i"]." ".$row1["o"];
                    if( !empty($u) )
                    {
                        if( \User\Service\AccessChecker::isSA($this->user) ) $row1["user"] .= ", ".$u;
                        else $row1["user"] = $u;
                    }
                }

                $tickets[] = $row1;
                //var_dump($row1);
            }

            $row["tickets"] = $tickets;
            $items[$row["id"]] = $row;
        }
        return $items;
    }

    public function setStatus($post)
    {
        $badResult = $this->resultTpl;

        $conn = $this->entityManager->getConnection();
        $qb = $conn->createQueryBuilder();

        $qb->update('tickets_head');
        $qb->set('status', ':status')->setParameter(':status', intval($post["status"]), \PDO::PARAM_INT);
        $qb->where('id=:id')->setParameter(':id', intval($post["id"]), \PDO::PARAM_INT);

        $affectedRows = $qb->execute();

        return [
            "success" => !empty($affectedRows),
            "status" => intval($post["status"]),
            "text" => $this->parseStatus($post["status"])
        ];
    }

    public function next($post)
    {
        $badResult = $this->resultTpl;

        $start = intval(@$post["start"]);
        if(empty($start)) return $badResult;

        $items = $this->getPage($start);

        return [
            "success" => true,
            "items" => $items,
            "page_size" => $this->pageSize
        ];
    }

    public function getIndexPageData()
    {

        return [
            "heads" => $this->getPage(),
            "successMsg" => "",
            "errorMsg" => "",
            "page_size" => $this->pageSize,
        ];
    } // getIndexPageData

}

