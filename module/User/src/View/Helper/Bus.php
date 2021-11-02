<?php

namespace User\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * Class Bus
 * @package User\View\Helper
 */
class Bus extends AbstractHelper
{
    /**
     * @@access protected
     * @var array $items -Массив автобуса
     */
    protected $items = [
        [["plc" => -1, "act" => 0], ["plc" => 19, "act" => 0], ["plc" => 20, "act" => 0]],
        [["plc" => 1,  "act" => 0], ["plc" => 2,  "act" => 0], ["plc" => 0,  "act" => 0], ["plc" => 0,  "act" => 0]],
        [["plc" => 3,  "act" => 0], ["plc" => 4,  "act" => 0], ["plc" => 0,  "act" => 0], ["plc" => 5,  "act" => 0]],
        [["plc" => 6,  "act" => 0], ["plc" => 7,  "act" => 0], ["plc" => 0,  "act" => 0], ["plc" => 8,  "act" => 0]],
        [["plc" => 9,  "act" => 0], ["plc" => 10, "act" => 0], ["plc" => 0,  "act" => 0], ["plc" => 11, "act" => 0]],
        [["plc" => 9,  "act" => 0], ["plc" => 12, "act" => 0], ["plc" => 13, "act" => 0], ["plc" => 14, "act" => 0]],
        [["plc" => 15, "act" => 0], ["plc" => 16, "act" => 0], ["plc" => 17, "act" => 0], ["plc" => 18, "act" => 0]]
    ];

    /**
     * Bus constructor.
     */
    public function __construct()
    {
    }

    /**
     * setItems - установить занятные места
     * @param $passengers - массив зарегистрированных пассажиров
     */
    public function setItems($passengers)
    {
        foreach ( $this->items as $rowKey => $row ) {
            foreach ( $row as $colKey => $col ) {
                if ( array_key_exists($col["plc"], $passengers) ) {
                    $this->items[$rowKey][$colKey]["act"] = 1;
                }
            }
        }
    }

    /**
     * render - Визуализация автобуса
     * @return string
     */
    public function render()
    {
        $result = "<div id=\"bus\" class=\"col-xs-12 col-md-4\">
                        <table style=\"border-collapse: inherit;\">
                            <tbody>
                            <tr>
                                <td name=\"bus-container\">
                                    <div id=\"the_bus_salon\" 
                                         style=\"border: 1px solid rgb(242, 231, 231); 
                                         padding: 2px; 
                                         display: inline-block; 
                                         min-width: 100px; border-radius: 10px;\">
                                        ".$this->renderItems()."
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>";
        return $result;
    }

    /**
     * renderItems - отрисовывает места в автобусе
     * @return string
     */
    protected function renderItems()
    {
        $result = "";
        foreach ( $this->items as $rows ) {
            $result .= "<div style=\"display: table-row; white-space: nowrap;\">";
            $isDrvRow = false;
            foreach ( $rows as $col ) {
                if ( $col["plc"] === -1 ) {
                    $isDrvRow = true;
                    $result .= $this->getDriverPlace();
                } else if ( $col["plc"] === 0 ) {
                    $result .= $this->getMissPlace();
                } else {
                    if ( $isDrvRow ) {
                        $result .= $this->getNextToDriverPlace($col["plc"],$col["act"]);
                        $isDrvRow = false;
                    } else {
                        $result .= $this->getPassengerPlace($col["plc"],$col["act"]);
                    }
                }
            }
            $result .= "</div>";
        }
        return $result;
    }

    /**
     * getDriverPlace - отрисовать место водителя
     * @return string
     */
    private function getDriverPlace() {
        return "<div class=\"bus_place\" 
                     style=\"
                        width: 25px; height: 25px; 
                        cursor: default; display: inline-block;
                        margin-top: 10px; text-align: center;                          
                        margin-left: 20px; border-radius: 7px;
                        vertical-align: middle;\">B</div>";
    }

    /**
     * getNextToDriverPlace - отрисовать место рядом с водителем(слева)
     * @param $placeNum - номер места в автобусе
     * @param $placeActive - занято ли место
     * @return string
     */
    private function getNextToDriverPlace($placeNum,$placeActive) {
        ($placeActive) ? $vacant = "my-blocked" : $vacant = "vacant";
        return "<div class=\"bus_place clickable place_active use2x raw-place ".$vacant."\" 
                     style=\"width: 25px; 
                         height: 25px; display: inline-block; 
                         vertical-align: middle; text-align: center; 
                         border-radius: 7px; margin-left: 13px; 
                         margin-top: 10px; cursor: pointer;\" 
                         name=\"1\" data-name=\"$placeNum\" id=\"\">$placeNum</div>";
    }

    /**
     * getPassengerPlace - отрисовать пассажирское место
     * @param $placeNum - номер места в автобусе
     * @param $placeActive - занято ли место
     * @return string
     */
    private function getPassengerPlace($placeNum,$placeActive) {
        ($placeActive) ? $vacant = "my-blocked" : $vacant = "vacant";
        return "<div class=\"bus_place clickable place_active raw-place ".$vacant."\" 
                     style=\"width: 25px; height: 25px; 
                         display: inline-block; vertical-align: middle; 
                         text-align: center; border-radius: 7px; 
                         margin-left: 1px; margin-top: 10px; 
                         cursor: pointer;\" name=\"1\" 
                         data-name=\"$placeNum\" id=\"\">$placeNum</div>";
    }

    /**
     * getMissPlace - орисовать проходное место
     * @return string
     */
    private function getMissPlace() {
        return "<div class=\"bus_place prohod\" 
                     style=\"
                        width: 25px; height: 25px; display: inline-block; 
                        vertical-align: middle; text-align: center; 
                        border-radius: 7px; margin-left: 1px; 
                        margin-top: 10px; cursor: default;\" name=\"3\"></div>";
    }
}
