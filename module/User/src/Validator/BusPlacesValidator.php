<?php
namespace User\Validator;

use User\Entity\Bus;
use User\Entity\Reis;
use Zend\Validator\AbstractValidator;

/**
 * Class BusPlacesValidator
 * @package User\Validator
 */
class BusPlacesValidator extends AbstractValidator
{
    // Сообщения об ошибках валидации.
    const INVALID_PLACE  = "Некорректный номер места!";

    /**
     * @inheritDoc
     */
    public function isValid($value)
    {
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
        $bus = $reis["bus"];
        foreach ( $reis["post"]["paxes"] as $pax ) {
            // проверяем чтобы номерста не превышал количество мест в автобусе
            if( intval($pax["place"]) > $bus->getSize()) {
                $this->error( "В автобусе максимальный номер места {$bus->getSize()}!");
            }
            if( intval($pax["place"] ) <= 0) {
                $this->error( self::INVALID_PLACE);
            }
        }
    }
}
