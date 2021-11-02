<?php
namespace User\Validator;
use Zend\Validator\AbstractValidator;

/**
 * Class ExpiredReisValidator
 * @package User\Validator
 */
class ExpiredReisValidator extends AbstractValidator
{
    /**
     * @inheritDoc
     */
    public function isValid($value)
    {
        if ( !$this->isBusTimedOut($value["reis"]) ) return false;
        return true;
    }

    /**
     * isBusTimedOut - наступило ли время отправки автобуса
     * @param $trip - запись о рейсе
     * @return bool
     */
    private function isBusTimedOut($trip) {
        $params = $trip->getParams();
        if ( array_key_exists( "welcome", $params ) ) {
            if (array_key_exists("link", $params["welcome"])) {
                $start_date = $params["welcome"]["actual_to_date"] . " " .$params["welcome"]["actual_to_time"];
                $curr_date  = date('d.m.Y H:i');
                if ( $start_date > $curr_date ) {
                    return true;
                }
            }
        }
        return false;
    }
}
