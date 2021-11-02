<?php
namespace User\Service;

use User\Entity\Usr;
use User\Entity\Reis;
use User\Entity\Reserved;
use User\Filter\AddPassForReisPaxesFilter;

/**
 * Class TicketManager
 * @package User\Service
 */
class TicketManager
{
    /**
     * @access private
     * @var Doctrine\ORM\EntityManager $em менеджер сущностей
     */
    private $em;

    /**
     * @access private
     * @var User\Service\UserManager $userManager - сервис для работы с пользователями
     */
    private $userManager;

    /**
     * @access private
     * @var User\Service\CharterPaxManager $charterPaxManager - сервис для работы с пассажирами чартерного рейса
     */
    private $charterPaxManager;

    /**
     * @access private
     * @var User\Filter\AddPassForReisPaxesFilter $addPassForReisPaxesFilter -
     */
    private $addPassForReisPaxesFilter;

    /**
     * TicketManager constructor.
     * @param $entityManager
     * @param $userManager
     * @param $charterPaxManager
     */
    public function __construct($entityManager, $userManager, $charterPaxManager)
    {
        $this->em = $entityManager;
        $this->userManager = $userManager;
        $this->charterPaxManager = $charterPaxManager;
        $this->addPassForReisPaxesFilter = new AddPassForReisPaxesFilter();
    }

    /**
     * orderTickets - заказать билеты
     * @param $data - данные о заказанных билетах
     * @return array
     */
    public function orderTickets($data)
    {
        if( isset($data["create_account"]) && ($data["create_account"]==true) )
            $current_user = $this->createAccount($data);

        if( empty($current_user) )
            $current_user = $this->em->getRepository(Usr::class)->getCurrentUser("email",$data['email']);

        if( $data["subscribe_news"] ) {
            $subscribe = new \Application\Filter\Subscribe(["em"=>$this->em]);
            $subscribe->addMail($data["email"], $current_user);
            $subscribe->addPhone($data["phone"], $current_user);
        }

        // сохранить пассажиров заказавших билеты
        $tickets = $this->savePassengers( $data );
        if ( $tickets == false )
            return ["success" => false, "msg" => ["Рейс не обнаружен!" => null]];
        else
            return ["success" => 1, "tickets" => $tickets, "order" => null, "link" => null];
    }

    /**
     * savePassengers - сохранить пассажиров в рейс соглассно заказанным билетам
     * @param $data - общие данные о заказанных билетах
     * @return array
     */
    private function savePassengers($data)
    {
        $tickets = [];
        $reis  = $this->em->getRepository(Reis::class)->findOneById($data["id_reis"]);
        // если рейс не найден, завершаем работу метода
        if ($reis == null ) {return false;}
        $paxes = $reis->getPaxes();

        $params = $reis->getParams();
        $dvf    = new \Application\Filter\DataValueFilter();

        foreach( $data["paxes"] as $pax ) {
            $place = $pax["place"];

            // удалить пятиминутную бронь с заказанного билета
            if ( !$this->removeReservedForTicket($data, $place) ) continue;

            // сохраняем данные о пассажирах в pax_history
            $docNum = preg_replace('/\s/i', '', $pax['doc_num']);
            $fio = $pax["f"] . " " . $pax["i"] . " " . $pax["o"];
            $params = $dvf->set("pax_history." . $docNum . ".fio", $fio, $params);
            $params = $dvf->set("pax_history." . $docNum . ".email", $data["email"], $params);
            $params = $dvf->set("pax_history." . $docNum . ".phone", $data["phone"], $params);
            $params = $dvf->set("pax_history." . $docNum . ".register", date('d-m-Y H:i'), $params);

            $paxes[(int)$place] = $pax;

            $pax["start"] = $reis->getDateStart()->format("d.m.Y H:i");
            $tickets[] = $pax;
        }

        // Фильтруем данные о пассажире перед сохранением в базу
        // удаляем ненужные поля из массива
        $paxes = $this->addPassForReisPaxesFilter->filter($paxes);

        $reis->setParams($params);
        $reis->setPaxes($paxes);
        $this->em->persist($reis);
        $this->em->flush();

        // также сохраняем пассажиров в таблице пассажиров чартерных рейсов
        $this->charterPaxManager->savePassengers($data["id_reis"],$tickets);

        return $tickets;
    }

    /**
     * removeReservedForTicket - удалить резерв(бронь) для заказанного билета
     * @param $data - общие данные о заказанных билетах
     * @param $place - место в автобусе
     * @return bool
     */
    private function removeReservedForTicket($data, $place)
    {
        $reserv = $this->em->getRepository(Reserved::class)->findReservedByReisIdAndSelerIdAndPlaceNum(
            $data["id_reis"],
            crc32(session_id()),
            $place
        );

        if ( $reserv != null ) {
             $this->em->remove($reserv);
             $this->em->flush($reserv);
             return true;
        }

        return false;
    }

    /**
     * createAccount
     * @param $data
     * @return bool
     */
    private function createAccount($data)
    {
        $register_data = array_merge([], $data["paxes"][0], ["email"=>$data["email"], "idUsrType"=>2]);
        $user = $this->userManager->registerUser($register_data);

        if (empty($user)) return false;
        $this->userManager->generateRegisterConfirm($user);

        $dvf = new \Application\Filter\DataValueFilter();
        $user_data = $user->getData();

        $user_data = $dvf->set("profile.user.grazhd", $register_data["grazhd"], $user_data);
        $user_data = $dvf->set("profile.user.grazhd_txt", $register_data["grazhd_txt"], $user_data);
        $user_data = $dvf->set("profile.user.passport_type", $register_data["doc_type"], $user_data);
        $user_data = $dvf->set("profile.user.passport_type_txt", $register_data["doc_type_txt"], $user_data);
        $user_data = $dvf->set("profile.user.passport", $register_data["doc_num"], $user_data);
        $user_data = $dvf->set("profile.user.dr", $register_data["dr"], $user_data);
        $user_data = $dvf->set("profile.contact.phone2", $data["phone"], $user_data);

        $user->setIsDeletable(false);
        $user->setData($user_data);
        $this->em->flush();

        return $user;
    }
}
