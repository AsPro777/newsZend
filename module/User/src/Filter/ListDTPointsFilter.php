<?php
namespace User\Filter;

use DateTime;
use Zend\Filter\AbstractFilter;

/**
 * Class ListDTPointsFilter
 * @package User\Filter
 */
class ListDTPointsFilter extends AbstractFilter
{
    /**
     * @access private
     * @var Doctrine\ORM\EntityManager $em менеджер сущностей
     */
    private $dateM = array(
        'январь', 'февраль', 'март', 'апрель', 'май', 'июнь', 'июль',
        'август', 'сентябрь', 'октябрь', 'ноябрь', 'декабрь'
    );

    /**
     * @param mixed $value - объект рейса
     * @return mixed|void
     */
    public function filter($value)
    {
        $arr["dates"]  = self::prepareDateTimePoints($value);
        $arr["points"] = self::prepareListPoints(
            $value->getFromPoints(),
            $value->getTracePoints(),
            $value->getToPoints()
        );

        return self::prepareListPointsWithPointStops($arr,$value);
    }

    /**
     * prepareDateTimePoints - подготавливает время и дату остановок
     * @param $reis - объект рейса
     * @return array|bool|mixed
     */
    private function prepareDateTimePoints($reis)
    {
        $date_start = self::getDateStartReis($reis);

        $ttt = new \Application\Filter\TimeToTargetFilter();
        return $ttt->allPath(
            $date_start,
            [$reis->getFromPoints(),
            $reis->getTracePoints(),
            $reis->getToPoints()]
        );
    }

    /**
     * getDateStartReis - получить время отправки рейса
     * @param $reis - рейс
     * @return DateTime|false
     */
    private function getDateStartReis($reis) {

        $timeZone  = new \DateTimeZone('Europe/Moscow');
        $dateStart = $reis->getDateStart()->format("Y.m.d H:i");
        $dateStart = \DateTime::createFromFormat("Y.m.d H:i", $dateStart, $timeZone);

        return $dateStart;
    }

    /**
     * prepareListPoints - подготовить список всех остановок в рейсе
     * @param $fromPoints - остановки по городу отправления
     * @param $tracePoints - остановки промежуточные между городами
     * @param $toPoints - остановки по городу прибытия
     * @return array
     */
    private function prepareListPoints($fromPoints, $tracePoints, $toPoints)
    {
        $points = [];
        foreach ( $fromPoints as $point )
            array_push($points, $point);
        foreach ( $tracePoints as $point )
            array_push($points, $point);
        foreach ( $toPoints as $point )
            array_push($points, $point);

        return $points;
    }

    private function prepareListPointsWithPointStops($arr, $reis)
    {
        $lastKey = count($arr["points"]) - 1;
        foreach ( $arr["points"] as $key => $point ) {
            $dateStart = self::getDateStartReis($reis);
            $dayStart  = (int)$dateStart->format("d");

            // обрабатывается начальная отстановка
            if ( $key == 0 ) {
                $m = $this->dateM[((int)$arr["dates"][0]->format("m") - 1)];
                $arr["points"][$key]["data"] .= ":0";
                $arr["points"][$key]["date_point"] = $arr["dates"][0]->format("d") . " " . $m;
                $arr["points"][$key]["time_point"] = $arr["dates"][0]->format("H:i");
            }
            // обрабатывается конечная остановка
            if ( $key == $lastKey ) {
                $dateStart->modify("+$point[time] minutes");
                $dayEnd = (int)$dateStart->format("d");

                $arr["points"][$key]["date_point"] = "";
                if ( $dayStart != $dayEnd ) {
                    $m = $this->dateM[((int)$arr["dates"][1]->format("m") - 1)];
                    $arr["points"][$key]["date_point"] = $dateStart->format("d") . " " . $m;
                }

                $arr["points"][$key]["data"] .= ":" . $lastKey;
                $arr["points"][$key]["time_point"] = $dateStart->format("H:i");
            }
            // обрабатываются промежуточнын остановки
            if ( $key != 0 && $key != $lastKey ) {
                $dateStart->modify("+$point[time] minutes");

                $arr["points"][$key]["data"] .= ":" . $key;
                $arr["points"][$key]["date_point"] = $dateStart->format("d");
                $arr["points"][$key]["time_point"] = $dateStart->format("H:i");
            }
        }

        return $arr["points"];
    }
}
