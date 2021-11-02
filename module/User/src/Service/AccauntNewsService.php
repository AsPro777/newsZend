<?php
namespace User\Service;

use User\Entity\News;
use User\Entity\AlarmSendNews;

class AccauntNewsService
{
    /**
     * Doctrine entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    protected $entityManager;
    protected $resultTpl = ["success" => false];
    protected $controller;
    private $user = null;

    protected $pageSize = 3;
    const ADMIN = "Михаил Обельченко";
    const PODPIS = "\r\nС Уважением, технический специалист Гоубас ".self::ADMIN;


    public function __construct($entityManager,$user)
    {
        $this->entityManager = $entityManager;
        $this->user = $user;
    }

    /*разбор post-данных*/
    public function parsePost($post)
    {
        $badResult = $this->resultTpl;
        $badResult["msg"] = "Неизвестная команда!";

        if(!is_array($post) || !isset($post["action"])) return $badResult;

        switch ($post["action"])
        {
            case "new-news" : return $this->newNews($post);/*внести новую новость*/
            case "next" : return $this->entityManager->getRepository(News::class)->getPage(@$post["start"],$this->pageSize,@$post["userTypeId"],@$post['property']);/*кнопка Следующие 5 записей*/
            case "autocompleteSelect" :  return $this->getSelectedUser(@$post['id']);/*получить информацию о пользователе выбраном в списке автозаполнения для отображения ее в табл на стр*/
            case "deleteNews": return $this->deleteNews($post['idNews']);/*удалить из Бд новость по id*/
            case "getDataNews": return $this->getDataNews($post['idNews']);  /*получить 1 запись по id*/
            case "setPublicNews": return $this->entityManager->getRepository(News::class)->setPublicNews(@$post['idNews']);/*опубликовать новость*/
            /*case "filterNews": return $this->entityManager->getRepository(News::class)->getPage(@$post["start"],$this->pageSize,$this->user->getId(),@$post["accepted"]);*/
            case "filterNews": return $this->getIndexPageData($this->user->getId(),@$post);/*если выбрали радиобатон те если хотим вывести только сохраненные или опубликованые или все записи*/
            case "updateImagesNews": return $this->entityManager->getRepository(News::class)->updateImagesNews(@$post);/*если пользователь удалил изображение из просмотра текста*/
            case "refresh-news": return $this->entityManager->getRepository(News::class)->refreshNews(@$post);/*обновить новости при изменении ее заголовка, текста или даты*/

            default : return $badResult;
        }
    }

    /*получить текст новости по id*/
    public function getDataNews($id)
    {
       $result=$this->entityManager->getRepository(News::class)->find($id);
            if(($result->getAddFiles()!=='{}')&&(isset($pic->mainPic[0]))){
                $pic=json_decode($result->getAddFiles());
                $src='/account/news?resize=yes&id='.$result->getId().'&name='.$pic->mainPic[0]->name.'&selectPic=true data-img=true';
                $mainImg='<img width="70" height="70" src='.$src.' >';
            }
            else $mainImg=0;
       /*$mainImg='<img width="70" height="70" src=>'*/
       if(!empty($result)) return ['success' => true,
                                   'text' => $result->getText(),
                                   'dataReg' => $result->getDateReg(),
                                   'mainImg' => $mainImg,
                                   'public' => $result->getPublic(),
                                   'inside' => $result->getInside(),
                                   'head' => $result->getHead(),
                                   'pic' => json_decode ($result->getAddFiles())
                                  ];
       else return ['success' => false];
    }

    /*удаление строки с новостью по ее id*/
    public function deleteNews($id)
    {/*так как в таблице alarm_send_news внешние ключи имеют каскадное удаление то при удалении строки из news сразу удалится все по id_news и в alarm_send_news*/
       $deleteRec= $this->entityManager->getRepository(News::class)->find($id);
       try{
                 $this->entityManager->remove($deleteRec);
                 $this->entityManager->flush();//применим изменения
             }
             catch (\Exception $e){
                   return ['success'=>FALSE];
             }
        return ['success'=>TRUE];
    }

    /*вернет тип учетной записи пользователя по id*/
    public function getSelectedUser($id)
    {
        $res=array();
        $selectedUserInfo=$this->entityManager->getRepository(\User\Entity\Usr::class)->find($id);
        $idUserType=$selectedUserInfo->getIdUsrType()->getId();
        $res['login']=$selectedUserInfo->getLogin();
        $res['usrType']=$this->entityManager->getRepository(\User\Entity\SprUsrType::class)->find($idUserType)->getName();
        return $res;
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

        $validator = new \Zend\Validator\File\MimeType($options['mimeType']);

        if( !isset($fileRec["tmp_name"]) || empty($fileRec["tmp_name"])
                || !isset($fileRec["size"]) || !$fileRec["size"]
                || !isset($fileRec["error"]) || ($fileRec["error"]>0)
                || !isset($fileRec["type"]) || empty($fileRec["type"]) )
              return $errorReport;
          if( ! $validator->isValid($fileRec["tmp_name"]) ) return "Недопустимый тип файла ".$fileRec["name"]."! Допустимые типы файлов: " . implode(", ",$options['mimeType']) .".";


        return true;
    }

    /*добавление записи/записей в таблицу alarmSendNews*/
    public function addAlarmSendNews($post,$isAllOrGroup)
    {
       $account=array();
       $users=array();
       $idGroup=array();
       $idPerson=array();

       /*получим id последней записи из таблицы news*/
       $getLastId=$this->entityManager->getRepository(\User\Entity\News::class)->findBy([],['id'=>'DESC'],1);
       $lastId=$getLastId[0]->getId();

       /*0-послать всем зарегистрированным пользователям
         1-послать послать группе пользователей или конкретному пользователю*/
       if($isAllOrGroup==1){

         if(!empty($post['idPerson'])) {
             $idPerson[]=$post['idPerson'];
             $users[]=$idPerson;
         }
         else{
             if(!empty($post['carrier'])) $idGroup[]=$post['carrier'];
             if(!empty($post['passenger'])) $idGroup[]=$post['passemger'];
             if(!empty($post['terminal'])) $idGroup[]=$post['terminal'];
             /*получим массив id пользователей которые входят в данную учетную запись/записи*/
             $users=$this->entityManager->getRepository(\User\Entity\Usr::class)->findUsersGroupByAccount($idGroup);
         }
         if(empty($users))return false;

         foreach ($users as $accountVal) {
            foreach ($accountVal as $value) {
              $sn=new AlarmSendNews();
              $sn->setIdNews($lastId);
              $sn->setIdUser($value['id']);
              $sn->setReaded(0);
              $this->entityManager->persist($sn);
           }
         }
       }
       else{
           $users=$this->entityManager->getRepository(\User\Entity\Usr::class)->findAll();
            if(empty($users))return false;

            foreach ($users as $value) {
              $sn=new AlarmSendNews();
              $sn->setIdNews($lastId);
              $sn->setIdUser($value->getId());
              $sn->setReaded(0);
              $this->entityManager->persist($sn);
         }
       }
       try{
              $this->entityManager->flush();
       }
       catch (\Exception $e){
              return false;
       }

       return true;
    }

    /*сформировать json для записи файлов в БД*/
    public function addFiles($files)
    {
        $manager = new \Application\Service\ImageManager($this->entityManager);
        $i=0;
        /*$badResult = array("success" => false, "msg" => "Ошибка параметров загрузки!");*/
        $mainPic='"mainPic":[]';
        $images='"images":[]';
        $imagesAndMain='';
        $ifMainPic=0;
        $ifImg=0;
        foreach($files as $key=>$value){
            $keyFiles=$key;

            if(isset($files[$keyFiles])&&!empty($files[$keyFiles])){/*если пользователь прикрепил файлы*/
                $checked=$this->checkFile($files[$keyFiles]);/*проверка структуры файлов*/
                if($checked !== true) {
               /*$newBadResult=array("msg" => $checked);*/
               /*$badResult=$badResult+$newBadResult;*/ return array("msg" => $checked);
                }

                $loadFilePos= strpos($keyFiles,'LoadFile');
                if($loadFilePos !== false) {/*если массив-изображения в самой новости-то в БД занести в json массив images*/
                  $i++;
                  if($ifImg==0)$images=substr($images,0,-1);
                  $ifImg=1;
                  if(empty($files[$keyFiles]["type"])) return 0;
                  $data = $manager->base64_encode_image ($files[$keyFiles]["tmp_name"], $files[$keyFiles]["type"]);
                  $images=$images.'{"name":"'.$i.'","file":"'.$data.'"},';
                }
                if($keyFiles=='inputLoadMainImg'){
                    if(empty($files[$keyFiles]["type"])) return 0;
                    if($ifMainPic==0)$mainPic=substr($mainPic,0,-1);
                    $ifMainPic=1;
                    $data = $manager->base64_encode_image ($files[$keyFiles]["tmp_name"], $files[$keyFiles]["type"]);
                    if($ifImg==0) {
                        $mainPic=$mainPic.'{"name":"1","file":"'.$data.'"}]';

                       $images='"images":[],';
                    }
                    else
                    $mainPic=$mainPic.'{"name":"1","file":"'.$data.'"}]';
                }
            }
        }
        if($ifMainPic==1){
           if($ifImg==1) $images=substr_replace($images,']',strrpos($images, ',')).',';
        }
        else {
            $imagesAndMain=substr_replace($images,']',strrpos($images, ','));
            $imagesAndMain='{'.$imagesAndMain.','.$mainPic.'}';
            return $imagesAndMain;
        }
        $imagesAndMain='{'.$images.$mainPic.'}'; 
        return $imagesAndMain;
    }

     /*формирование новой новости*/
    public function newNews($post)
    {
        $errorMessage = $successMessage = "";
        $keyFiles='';

        $news=new News();

        $badResult = array("success" => false, "msg" => "Ошибка параметров загрузки!");

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

        if(empty($post['onUserAccount'])&&empty($post['onSite'])){
            $newBadResult=array("msg"=>"Не указано кому отправить!");
            $badResult=array_merge($badResult,$newBadResult); return $badResult;
        }

        if(empty($post['onSite'])==FALSE) $news->setPublic(TRUE);/*если был чекбокс выбран "На сайт" то в таблице News в столбец Public внести true*/
        if(empty($post['onUserAccount'])==FALSE){/*если был выбран "В учетную запись"*/
          if($post['whoSend']=='all') $news->setInside(TRUE);/*если в комбобоксе выбран "Всем"....*/
          if($post['whoSend']=='account') {
             if(empty($post['carrier'])==FALSE)$news->setCarrier (TRUE);/*...то в таблице NEws в столбце carrier будет true*/
             if(empty($post['passenger'])==FALSE)$news->setPassenger (TRUE);/*...то в таблице NEws в столбце passenger будет true*/
             if(empty($post['terminal'])==FALSE)$news->setTerminal (TRUE);/*...то в таблице NEws в столбце terminal будет true*/
           }
        if($post['whoSend']=='personal')$news->setPersonal (TRUE);

        }

        /*если в комбобоксе выбран "Персонально" то в таблице News в столбец Personal внести json-массив из id пользователей которым эту новость надо показать*/
     /*   if($post['whoSend']=='personal'){*/
              //$this->news->setPersonal(TRUE);/*если в комбобокс выбран "Персонально" то в таблице News в столбец Personal внести true
        /*}*/
        if((is_array($_FILES))&&(count($_FILES)!==0))
        {
          $images=$this->addFiles($_FILES);     /*формирование json строки-массива из прикрепленных файлов*/
          if($images=='') {
               $newBadResult=array("msg"=>"Не удалось загрузить файлы!");
               $badResult=array_merge($badResult,$newBadResult);
               return $badResult;
          }
          if(is_array($images)){
              $newBadResult=$images;
              $badResult=array_merge($badResult,$newBadResult);
              return $badResult;
          }
        }
        else $images='{}';

        $news->setHead($post['name']);
        $news->setText($post["text"]);
        $news->setAddFiles($images);
        $news->setStatus('draft');

        try{
            // Добавляем сущность в менеджер сущностей.
            $this->entityManager->persist($news);
            // Применяем изменения к базе данных.
            $this->entityManager->flush();
        } catch (\Exception $e)
        {
            return [
                    "success" => false,
                    "err" => "Неудачно!",
                    "msg" => "Ошибка сохранения новости!"
                ];
        }
        /*all-послать всем зарегистрированным пользователям
         account-послать послать группе пользователей или конкретному пользователю*/
        if((isset($post['whoSend'])==true) && ($post['whoSend']=='account'))$this->addAlarmSendNews($post,1);
        if((isset($post['whoSend'])==true) && ($post['whoSend']=='personal'))$this->addAlarmSendNews($post,1);
        if((isset($post['whoSend'])==true) && ($post['whoSend']=='all')) $this->addAlarmSendNews($post,0);
       /* if((isset($post['whoSend'])==true) && ($post['whoSend']=='personal')) $this->addAlarmSendNews($post,2);*/

        /*сперва запишем в БД текст новости не сформированый , чтоб в Бд сделала для каждой новости id*/
        /*а потом считаем тут сделанную запись и поработаем с текстом новости, чтобы сформировать img*/
        /*а потом перепишем поле text*/

        /*получим id последней записи и ее текст из таблицы news а так же картинку*/
        $getLastId=$this->entityManager->getRepository(\User\Entity\News::class)->findBy([],['id'=>'DESC'],1);
        $text=$getLastId[0]->getText();
        $pic=json_decode($getLastId[0]->getAddFiles());
        $pos=strpos($text,'/account/news?');
        $textNews='';
        $lastId=$getLastId[0]->getId();

        /*если в тексте была хоть одна картинка*/
        if($pos!==false){
           $mas=explode('/account/news?',$text);
           /*сформируем новый текст для новости в котором тег img будет полностью оформлен*/
           for($i=0;$i<count($mas)-1;$i++)
           {
             $img='/account/news?id='.$getLastId[0]->getId().'&name='.$pic->images[$i]->name.'&selectPic=false';
             $textNews=$textNews.$mas[$i].$img;
           }
           $textNews=$textNews.$mas[count($mas)-1];
           /*обновим текст новости*/
           $this->entityManager->getRepository(News::class)->updateTextNews($textNews,$lastId);
           try{
               $this->entityManager->flush();//применим изменения
           }
           catch (\Exception $e){
               return FALSE;
           }
        }
        return [  "success" => true  ];

    } // newNews()

    /*получить изображение в нормальном размере*/
     public function getNewsImage($id,$name,$selectPic)
     {
        $newImg=$this->getImageObject($id, $name,$selectPic);
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

     /*изменяет размер изображения (картинка становится меньше по горизонтали и вертикали и меньше весит)*/
     public function getResizeNewsImage($id,$name,$selectPic,$newW=70,$newH=70)
     {
       $newImg=$this->getImageObject($id, $name,$selectPic);
       $width=imagesx($newImg[1]); $height=imagesy($newImg[1]);
       $newImageColour=imagecreatetruecolor($newW, $newH); /*Создание нового полноцветного изображения 70x70*/
       imagecopyresampled($newImageColour, $newImg[1], 0, 0, 0, 0, $newW, $newH, $width, $height);/*Копирование и изменение размера изображения с ресемплированием */

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

     /*получим объект типа Image из БД по id и имени файла*/
     private function getImageObject($id,$name,$selectPic)
     {
        try{
             $newsImg = $this->entityManager->getRepository(News::class)->find($id);
        } catch (Exception $e) {
             return false;
        }/*получим запись по id*/

        $data=json_decode($newsImg->getAddFiles());
        if($selectPic=='true') { $dataImage=$data->mainPic; }
        if($selectPic=='false') {$dataImage=$data->images; }

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

     /*пробуем сделать пагинацию отдельной функцией*/

    public function getPagination($page,$pageSize,$newsCount)
    {
        $count = count($newsCount);
        $pagesCount = ceil($count / $pageSize);
        $firstPage = 1;
        $lastPage = $pagesCount;
        $pagination = [];

        if ($pagesCount > 1) {
            if ($page > 1) {
                array_push($pagination, "<li><a href=\"/news\" aria-label=\"Previous\"><span aria-hidden=\"true\"><i class=\"fa fa-angle-double-left\"></i></span></a></li>");
            }

            for ($i = 1; $i <= $pagesCount; $i++) {
                if ($page == $i) {
                    array_push($pagination, "<li class=\"active\"><a>$i</a></li>");
                } else {
                    array_push($pagination, "<li><a href=\"/news/page/$i\">$i</a></li>");
                }
            }

            if ($page < $pagesCount) {
                array_push($pagination, "<li><a href=\"/news/page/$pagesCount\" aria-label=\"Next\"><span aria-hidden=\"true\"><i class=\"fa fa-angle-double-right\"></i></span></a></li>");
            }
        }

        $result['pagesCount'] = $pagesCount;
        $result['firstPage']  = $firstPage;
        $result['lastPage']   = $lastPage;
        $result['pagination'] = $pagination;

        return $result;

    }

    public function getIndexPageData($userId,$post)
    {
        $filter = $post;

        $filter["accepted"] = in_array(@$post["accepted"], ["all", "publiced", "saved"]) ? $post["accepted"] : "all";

        return [
            "heads" => $this->entityManager->getRepository(News::class)->getPage(0,$this->pageSize,$userId,$filter['accepted']),
            "successMsg" => "",
            "errorMsg" => "",
            "page_size" => $this->pageSize,
            "filter" => $filter
        ];
    } // getIndexPageData

}

