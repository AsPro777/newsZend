<?php
namespace User\Filter;
use Zend\Filter\AbstractFilter;

/**
 * Class AddPassForReisPaxesFilter
 * @package User\Filter
 */
class AddPassForReisPaxesFilter extends AbstractFilter
{
    /**
     * filter - фильтрует ненужные поля в массиве пассажиров
     * @param string $value - список пассажиров
     * @return mixed
     */
    public function filter($value)
    {
        $arr = [];
        $fields = [
            "id_ticket",    "id_reis",        "id_from", "id_to",
            "from_txt",     "to_txt",         "from_city_txt",
            "to_city_txt",  "from_point_txt", "place",
            "to_point_txt", "email",          "phone"
        ];

        foreach ( $value as $key => $passenger) {
            foreach ($passenger as $keyField => $field) {
                if ( !in_array($keyField, $fields) ) {
                    $arr[$key][$keyField] = $field;
                }
            }
        }

        return $arr;
    }
}
