<?php
namespace User\Validator;

use User\Entity\Bus;
use User\Entity\Reis;
use Zend\Validator\AbstractValidator;

/**
 * Class TicketValidator
 * @package User\Validator
 */
class TicketValidator extends AbstractValidator
{
    // Сообщения об ошибках валидации.
    const INVALID_REIS_ID  = "Не задан рейс!";
    const INVALID_F        = "Не указана фамилия!";
    const INVALID_I        = "Не указано имя!";
    const INVALID_O        = "Не указано отчество!";
    const INVALID_ID_FROM  = "Не указан пункт отправления!";
    const INVALID_ID_TO    = "Не указан пункт прибытия!";
    const INVALID_PAXES    = "Не указаны сведения о пассажирах!";
    const INVALID_EMAIL    = "Не указан адрес электронной почты!";
    const INVALID_PHONE    = "Не указан номер телефона!";
    const INVALID_DOC_TYPE = "Неизвестный тип удостоверяющего документа!";
    const INVALID_DOC_NUM  = "Не указан номер документа";
    const INVALID_DR       = "Не указана дата рождения!";
    const INVALID_GRAZHD   = "Неизвестный тип гражданства!";
    const INVALID_SEX      = "Не указан пол пассажира!";
    const INVALID_PLACE    = "Не указан номер места!";

    const INCORRECT_DR     = "Некорректная дата рождения!";
    const INCORRECT_EMAIL  = "Указан некорректный адрес электронной почты!";
    const INCORRECT_PHONE  = "Неправильный номер телефона! В номере телефона должно быть 11 цифр!";
    const INCORRECT_GRAZHD_RU     = "Для указанного типа документа должно быть указано гражданство РФ!";
    const INCORRECT_GRAZHD_NOT_RU = "Для указанного типа документа должно быть указано гражданство не РФ!";

    /**
     * @inheritDoc
     */
    public function isValid($value)
    {
        // проверяем общие данные для всех пассажиров
        if(empty($value["id_reis"])) $this->error(self::INVALID_REIS_ID);
        if(empty($value["id_from"])) $this->error(self::INVALID_ID_FROM);
        if(empty($value["id_to"]))   $this->error(self::INVALID_ID_TO);
        if(empty($value["email"]))   $this->error(self::INVALID_EMAIL);
        if(empty($value["phone"]))   $this->error(self::INVALID_PHONE);
        if(empty($value["paxes"]))   $this->error(self::INVALID_PAXES);

        // проверяем корректность адреса элетронной рочты
        $emailValidator = new \Zend\Validator\EmailAddress();
        if( ! $emailValidator->isValid($value['email']) ) $this->error(self::INCORRECT_EMAIL);

        // проверяем корректность ноера телефона
        $ff = new \Application\Filter\PhoneFilter();
        $phone = $ff->filter($value["phone"]);
        $phv = new \Application\Validator\PhoneValidator();
        if( !$phv->isValidNew($phone)) $this->error($phv->getErrorMessage());

        // проверяем корректность данных о пассажирах
        $this->checkPassengers($value);

        // проверяем были ли ошибки
        $err = $this->getMessages();
        if ( count($err) > 0 ) return false;

        return true;
    }

    /**
     * checkPassengers
     * @param $value
     * @return void
     */
    private function checkPassengers($reis) {

        foreach ( $reis["paxes"] as $pax ) {
            if(empty($pax["f"]))        $this->error(self::INVALID_F);
            if(empty($pax["i"]))        $this->error(self::INVALID_I);
            if(empty($pax["o"]))        $this->error(self::INVALID_O);
            if(empty($pax["dr"]))       $this->error(self::INVALID_DR);
            if(empty($pax["doc_num"]))  $this->error(self::INVALID_DOC_NUM);

            $pax["doc_type"] = intval($pax["doc_type"]);
            $pax["grazhd"]   = intval($pax["grazhd"]);
            $pax["sex"]      = intval($pax["sex"]);

            // проверяем корректность пола пассажира
            if ( $pax["sex"] != 0 && $pax["sex"] != 1 ) $this->error(self::INVALID_SEX);

            // проверяем корректность паспортных данных
            $rus_docs = [0,2,4,5,7,8,10,11,13];
            $is_rus_grazhd = ($pax["grazhd"]==643);
            if(!$is_rus_grazhd && in_array($pax["doc_type"], $rus_docs) ) $this->error(self::INCORRECT_GRAZHD_RU);
            if($is_rus_grazhd && !in_array($pax["doc_type"], $rus_docs) ) $this->error(self::INCORRECT_GRAZHD_NOT_RU);

            // проверяем корректность даты рождения пассажира
            $f = new \Application\Validator\RussianDateValidator();
            if(!$f->isValid($pax["dr"])) $this->error(self::INCORRECT_DR);
        }
    }
}
