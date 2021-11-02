<?php
namespace User\Controller;

use Zend\Mvc\MvcEvent;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use User\Entity\Responses;

class AccountController extends AbstractActionController
{
    private $entityManager;
//    private $userManager;
//    private $imageManager;
    private $user;

    public function __construct($entityManager /* , $userManager, $imageManager */)
    {
        $this->entityManager = $entityManager;
//        $this->userManager = $userManager;
//        $this->imageManager = $imageManager;
        $this->user = null;
    } // of __construct()

    public function onDispatch(MvcEvent $e)
    {
        // Вызываем метод базового класса onDispatch() и получаем ответ
        $response = parent::onDispatch($e);

        // Устанавливаем альтернативный лэйаут
        $this->layout()->setTemplate('layout/accaunt');

        // Возвращаем ответ
        return $response;
    } // of onDispatch()

    public function getEntityManager()
    {
        return $this->entityManager;
    }

    public function getUser()
    {
        return $this->user;
    }

    function init()
    {
        return $this->user = $this->entityManager->getRepository(\User\Entity\Usr::class)->getCurrentUser();
    } // of init()

    function deny()
    {
        return $this->redirect()->toRoute('account', ['action'=>'deny']);
    } // of deny()

    public function denyAction()
    {
        return new ViewModel([]);
    } // of denyAction()

    public function indexAction()
    {
        if(!$this->init()) return $this->deny();
        return new ViewModel([
            'user' => $this->user
        ]);
    } // of indexAction()


    /*создание тикета*/
    public function ticketsAction()
    {
        $errorMessage = $successMessage = "";
        $roles = [];
        if(!$this->init()) return $this->deny();

        $service = new \User\Service\TicketsService($this);

        if ( $this->getRequest()->isPost() )/*создание тикета или вопрос в сущ тиките*/
        {
            $post = $this->params()->fromPost();
            $result = $service->parsePost($post);
            if(is_array($result))
            {
                header('Content-type: text/javascript');
                $resp = $this->getResponse();
                return $resp->setContent(json_encode($result));
            }
            else $errorMessage=$result;
        }
        else
        {
            $id = @$this->params()->fromQuery("id");
            $name= @$this->params()->fromQuery('name');
            $resize= @$this->params()->fromQuery('resize');

            $fm = $this->flashMessenger();

            if($fm->hasMessages(\Zend\Mvc\Plugin\FlashMessenger\FlashMessenger::NAMESPACE_SUCCESS))
                $successMessage = implode("<br>", $fm->getMessages(\Zend\Mvc\Plugin\FlashMessenger\FlashMessenger::NAMESPACE_SUCCESS));

            if($fm->hasMessages(\Zend\Mvc\Plugin\FlashMessenger\FlashMessenger::NAMESPACE_ERROR))
                $errorMessage = implode("<br>", $fm->getMessages(\Zend\Mvc\Plugin\FlashMessenger\FlashMessenger::NAMESPACE_ERROR));

            if(!empty($id)&&!empty($name)){/*формирование миниатюр и открытие их*/
                if(!empty($resize)) $service->getResizeTicketImage($id,$name);
                else $service->getTicketImage($id,$name);
            }
        }

        $result = $service->getIndexPageData();

        return new ViewModel([
            'user' => $this->user,
            'heads' => $result["heads"],
            'page_size' => $result["page_size"],
            'errorMessage' => $errorMessage,
            'successMessage' => $successMessage,
            'granted' => [
              'sa' => \User\Service\AccessChecker::isSA($this->user),
            ],
            'action' => $this->params()->fromRoute("action", null),
            'post' => $this->params()->fromPost()
        ]);
    } // of ticketsAction()

    /*для автозаполнения. Получить фио, id, логин или номер тел по первым введенным символам*/
    public function getUserInfoAction()
    {
        header('Content-type: text/javascript');

        $result = [];
        if ($this->getRequest()->isPost())
        {
            $params = $this->params()->fromPost();/*получим то что ввели в инпуте*/
            $query = strip_tags(empty($params["query"])?"":$params["query"]);
            $result=$this->entityManager->getRepository(\User\Entity\Usr::class)->findUsersForAutocomplite($query);/*$result- массив одномерный*/
        }
        $resp = $this->getResponse();
        return $resp->setContent(json_encode($result));

    }

    /*создание раздела новостей*/
    public function newsAction()
    {
        $errorMessage = $successMessage = "";
        $roles = [];
        if(!$this->init()) return $this->deny();

        $service = new \User\Service\AccauntNewsService($this->entityManager,$this->user);

        $post = $this->params()->fromPost();

        if ( $this->getRequest()->isPost() )
        {
            $result = $service->parsePost($post);/*if($post['action']=='setPublicNews'){var_dump($result);die;}*/

            if(is_array($result))
            {
                if( $post["action"] == "filterNews" ) $successMessage = "";
                else{
                 header('Content-type: text/javascript');
                 $resp = $this->getResponse();
                 return $resp->setContent(json_encode($result));
                }
            }
            else $errorMessage=$result;
        }
        else
        {
            $id = @$this->params()->fromQuery("id");
            $name= @$this->params()->fromQuery('name');
            $resize= @$this->params()->fromQuery('resize');
            $selectPic=@$this->params()->fromQuery('selectPic');

            $fm = $this->flashMessenger();

            if($fm->hasMessages(\Zend\Mvc\Plugin\FlashMessenger\FlashMessenger::NAMESPACE_SUCCESS))
                $successMessage = implode("<br>", $fm->getMessages(\Zend\Mvc\Plugin\FlashMessenger\FlashMessenger::NAMESPACE_SUCCESS));

            if($fm->hasMessages(\Zend\Mvc\Plugin\FlashMessenger\FlashMessenger::NAMESPACE_ERROR))
                $errorMessage = implode("<br>", $fm->getMessages(\Zend\Mvc\Plugin\FlashMessenger\FlashMessenger::NAMESPACE_ERROR));

            if(!empty($id)&&!empty($name)&&!empty($selectPic)){
                if(!empty($resize)){ $service->getResizeNewsImage($id,$name,$selectPic);}
               else {$service->getNewsImage($id,$name,$selectPic);}
            }

            $result = $service->getIndexPageData($this->user->getId(),$post);
        }


        return new ViewModel([
            'user' => $this->user,
            'heads' => $result["heads"],
            'errorMessage' => $errorMessage,
            'flag' => $result["heads"]["flag"],
            'successMessage' => $successMessage,
            'page_size' => $result['page_size'],
            'radioId' => $result['filter']['accepted'],
            'granted' => [
              'sa' => \User\Service\AccessChecker::isSA($this->user),
            ],
            'action' => $this->params()->fromRoute("action", null),
            'post' => $this->params()->fromPost()

        ]);
    } // of ticketsAction()

    /*страница для редактирования новости*/
    public function editNewsAction()
    {
        $errorMessage = $successMessage = "";

        if(!$this->init()) return $this->deny();

        $service = new \User\Service\AccauntNewsService($this->entityManager,$this->user);

        if ( $this->getRequest()->isPost() )
        {
           $post = $this->params()->fromPost();
           $result = $service->parsePost($post);
           if(is_array($result))
            {
                if( $post["action"] == "filterNews" ) $successMessage = "";
                else{
                 header('Content-type: text/javascript');
                 $resp = $this->getResponse();
                 return $resp->setContent(json_encode($result));
                }
            }
            else $errorMessage=$result;
        }

        $fm = $this->flashMessenger();

        if($fm->hasMessages(\Zend\Mvc\Plugin\FlashMessenger\FlashMessenger::NAMESPACE_SUCCESS))
           $successMessage = implode("<br>", $fm->getMessages(\Zend\Mvc\Plugin\FlashMessenger\FlashMessenger::NAMESPACE_SUCCESS));

        if($fm->hasMessages(\Zend\Mvc\Plugin\FlashMessenger\FlashMessenger::NAMESPACE_ERROR))
           $errorMessage = implode("<br>", $fm->getMessages(\Zend\Mvc\Plugin\FlashMessenger\FlashMessenger::NAMESPACE_ERROR));

        $id = @$this->params()->fromRoute("id");
        $resize= @$this->params()->fromQuery('resize');
        $selectPic=@$this->params()->fromQuery('selectPic');
        $newW=@$this->params()->fromQuery('newWidth');
        $newH=@$this->params()->fromQuery('newHeight');
        $name=@$this->params()->fromQuery('name');
        if(!empty($id)&&!empty($name)&&!empty($selectPic)){
            if(!empty($resize)&&!empty($newW)&&!empty($newH)){  $service->getResizeNewsImage($id,$name,$selectPic,$newW,$newH);}
               else {$service->getNewsImage($id,$name,$selectPic);}
        }

        $news=$this->entityManager->getRepository(\User\Entity\News::class)->find($id);

        return new ViewModel([
            'result' => $news,
            'errorMessage' => $errorMessage,
            'successMessage' => $successMessage,
            'action' => $this->params()->fromRoute("action", null),
        ]);
    }

    /*страница с отдельно выбраной новостью*/
    public function singleNewsAction()
    {
        $errorMessage = $successMessage = "";

        if(!$this->init()) return $this->deny();

        $service = new \User\Service\AccauntNewsService($this->entityManager,$this->user);

        $fm = $this->flashMessenger();

        if($fm->hasMessages(\Zend\Mvc\Plugin\FlashMessenger\FlashMessenger::NAMESPACE_SUCCESS))
           $successMessage = implode("<br>", $fm->getMessages(\Zend\Mvc\Plugin\FlashMessenger\FlashMessenger::NAMESPACE_SUCCESS));

        if($fm->hasMessages(\Zend\Mvc\Plugin\FlashMessenger\FlashMessenger::NAMESPACE_ERROR))
           $errorMessage = implode("<br>", $fm->getMessages(\Zend\Mvc\Plugin\FlashMessenger\FlashMessenger::NAMESPACE_ERROR));

        $id = @$this->params()->fromRoute("id");
        $resize= @$this->params()->fromQuery('resize');
        $selectPic=@$this->params()->fromQuery('selectPic');
        $newW=@$this->params()->fromQuery('newWidth');
        $newH=@$this->params()->fromQuery('newHeight');
        $name=@$this->params()->fromQuery('name');

        $usrId = $this->user->getId();

        if(!empty($id)) {
            $queryBuilder = $this->getEntityManager()->createQueryBuilder();

            $q=$queryBuilder->update(\User\Entity\AlarmSendNews::class,'n')
                            ->set('n.readed', "TRUE")
                            ->where('n.idUser = :param1')->setParameter('param1', $usrId)
                            ->andWhere('n.idNews = :param2')->setParameter('param2', $id)
                            ->getQuery();
            $res=$q->getResult();

            if(empty($res)) $errorMessage = implode("<br>", $fm->getMessages(\Zend\Mvc\Plugin\FlashMessenger\FlashMessenger::NAMESPACE_ERROR));

        }

        if(!empty($id)&&!empty($name)&&!empty($selectPic)){
            if(!empty($resize)&&!empty($newW)&&!empty($newH)){  $service->getResizeNewsImage($id,$name,$selectPic,$newW,$newH);}
               else {$service->getNewsImage($id,$name,$selectPic);}
        }
            $result= $this->entityManager->getRepository(\User\Entity\News::class)->getFilterNews($id,$usrId);
            $news=$this->entityManager->getRepository(\User\Entity\News::class)->find($id);
           /*var_dump($result);die;*/
            $prev='';
            $next='';

        if(!empty($result)){
          if (isset($result[1])) {
              $prev = $result[0]['id'];
              $next = $result[1]['id'];
          } elseif (!isset($result[1]) && $id < $result[0]['id']) {
              $next = $result[0]['id'];
          } elseif (!isset($result[1]) && $id > $result[0]['id']) {
              $prev = $result[0]['id'];
          }

        }

        return new ViewModel([
            'prev' => $prev,
            'next' =>$next,
            'result' => $news,
            'errorMessage' => $errorMessage,
            'successMessage' => $successMessage,
        ]);

    }

   

     public function responsesAction()
    {
        $errorMessage = $successMessage = "";
        $roles = [];
        if(!$this->init()) return $this->deny();

        // только админы могут видеть
        if( ! \User\Service\AccessChecker::isAdmin($this->user) )
                return $this->deny();

        $service = new \User\Service\ResponsesManager($this->entityManager);
        $post = $this->params()->fromPost();

        if ( $this->getRequest()->isPost() ){
               $result = $service->parsePost($post);

               if(is_array($result))
               {
                if( $post["action"] == "responses" )
                {
                    $successMessage = "";
                }
                else
                {
                    header('Content-type: text/javascript');
                    $resp = $this->getResponse();
                    return $resp->setContent(json_encode($result));
                }
               }
               else {
                      $errorMessage = $result;
               }
        }
        else
        {
            $fm = $this->flashMessenger();

            if($fm->hasMessages(\Zend\Mvc\Plugin\FlashMessenger\FlashMessenger::NAMESPACE_SUCCESS))
                $successMessage = implode("<br>", $fm->getMessages(\Zend\Mvc\Plugin\FlashMessenger\FlashMessenger::NAMESPACE_SUCCESS));

            if($fm->hasMessages(\Zend\Mvc\Plugin\FlashMessenger\FlashMessenger::NAMESPACE_ERROR))
                $errorMessage = implode("<br>", $fm->getMessages(\Zend\Mvc\Plugin\FlashMessenger\FlashMessenger::NAMESPACE_ERROR));

             $result=$service->getResponsesIndexPageData($post);/*для отображения даты в календаре*/
        }

        return new ViewModel([ 'filter' => $result["filter"],
                               'errorMessage'=>$errorMessage,
                               'responses'=>$result['items']['result'],
                               'flag'=>$result['items']['flag'],
                               'radioId' => $result['filter']['accepted'],
                               'page_size' => $result["page_size"],
                               'action' => $this->params()->fromRoute("action", null)/*чтобы задать переменную action в частичном представлении script-ana-style*/
                             ]);

    } // of onlineReturnsAction()


}
