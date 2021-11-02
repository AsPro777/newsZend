<?php
namespace User\Validator;
use User\Filter\ZakazUrlFilter;
use Zend\Validator\AbstractValidator;

/**
 * Class ZakazValidator
 * @package User\Validator
 */
class ZakazValidator extends AbstractValidator
{
    /**
     * @access private
     * @var string $token - токен записи об рейсе
     */
    private $token = "";

    /**
     * @inheritDoc
     */
    public function isValid($value)
    {
        if ( is_null($value["reis"])  ) return false;
        if ( !$this->isSetBus($value["reis"]) ) return false;
        if ( !$this->checkToken($value["reis"], $this->token) ) return false;
        return true;
    }

    /**
     * setToken - устанавливает токен
     * @param $token
     */
    public function setToken( $token ) {
        $this->token = $token;
    }

    /**
     * checkToken - проверить совпадение токенов из url и из записи о рейсе
     * @param $trip - запись о рейсе
     * @param $token - токен полученный из url
     * @return bool
     */
    private function checkToken($trip, $token)
    {
        $zakazUrlFilter = new ZakazUrlFilter();
        $params = $trip->getParams();
        if ( array_key_exists( "welcome", $params ) ) {
            if ( array_key_exists( "link", $params["welcome"] ) ) {
                $str_id_hash = $zakazUrlFilter->filter($params["welcome"]["link"]);
                $arr_id_hash = explode("-", $str_id_hash);
                if ( array_key_exists("1", $arr_id_hash) ) {
                    if ($arr_id_hash[1] === $token) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * isSetBus - установлен ли автобус для данного рейса
     * @param $trip - запись о рейсе
     * @return bool
     */
    private function isSetBus($trip)
    {
        if (property_exists($trip, "idBus")) {
            $idBus = $trip->getIdBus();
            if($idBus !== null && $idBus !== '' && $idBus !== 0)  {
                return true;
            }
        }
        return false;
    }
}
