<?php
namespace User\Filter;
use Zend\Filter\AbstractFilter;

/**
 * Class ZakazUrlFilter
 * @package User\Filter
 */
class ZakazUrlFilter extends AbstractFilter
{
    /**
     * filter - фильтрует переданный URL страницы заказа билетов
     * на рейс и возвращает идентификатор рейса и хэш записи
     * пример url: http://gobus.local/fraht/index/4862-XHRzMDmcQoSrcXwrdQCzaORTMP7
     * пример возврата:4862-XHRzMDmcQoSrcXwrdQCzaORTMP7
     * @param string $value - фильтруемый url
     * @return mixed
     */
    public function filter($value)
    {
        // если url преобразовать не удалось возвращаем входные данные без изменений
        $arr_url = parse_url(preg_replace("#/*$#", "", $value));
        if ( array_key_exists("path", $arr_url) ) {
            $path_url = explode("/", $arr_url["path"]);
            return $path_url[count($path_url) - 1];
        } else {
            return $value;
        }
    }
}
