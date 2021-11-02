<?php

namespace User\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * Class Passengers
 * @package User\View\Helper
 */
class Passengers extends AbstractHelper
{
    /**
     * @access private
     * @var array $items - массив пассажиров
     */
    protected $items = [];

    /**
     * Passengers constructor.
     */
    public function __construct()
    {
    }

    /**
     * setItems
     * @param $items - массив пассажиров
     */
    public function setItems($items)
    {
        $this->items = $items;
    }

    /**
     * render - отрисовывает верстку виджета
     * @return string
     */
    public function render()
    {
        $result = "";
        foreach ( $this->items as $placeNum => $item ) {
            $result .= "<tbody><tr style=\"border-bottom: 1px solid rgb(52, 52, 52);\">";
            $result .= $this->getPassengerRow(
                $item["f"],
                $item["i"],
                $item["o"],
                $item["dr"],
                $item["drMask"],
                $item["sex"],
                $item["sex_txt"],
                $item["grazhd"],
                $item["grazhd_txt"],
                $item["doc_type_txt"],
                $item["doc_num"],
                $placeNum
            );
            $result .= "</tr></tbody>";
        }

        return $result;
    }

    /**
     * getPassengerRow - собирает html структуру из массива пассажиров
     * @param $f - фамилия
     * @param $i - имя
     * @param $o - отчество
     * @param $dr - день рождения
     * @param $drMask - маска дня рождения
     * @param $sex - пол
     * @param $sexTxt - пол текстом
     * @param $grazhdId - код страны
     * @param $grazhdTxt - гражданство текстом
     * @param $docTypeTxt - тип документа текстом
     * @param $docNum - номер паспорта
     * @param $placeNum - номер сидения
     * @return string
     */
    private function getPassengerRow($f,$i,$o,$dr,$drMask,$sex,$sexTxt,$grazhdId,$grazhdTxt,$docTypeTxt,$docNum,$placeNum) {
        $mSelected = "";
        $wSelected = "";
        if ((int)$sex == 1) $mSelected = "selected=selected";
        else $wSelected = "selected=selected";
        return "<td class=\"center\">
                    <span class=\"mobile-only\">Пассажир
                        <span class=\"pax-counter\">1</span> &nbsp;
                        <span class=\"text-muted mobile-label\">Номер места:</span>
                    </span><br class=\"mobile-only\">
                    <div>
                        <input name=\"place-number\" 
                               class=\"form-control\" 
                               type=\"text\" 
                               value=\"$placeNum\"
                               placeholder=\"\" readonly=\"\" title=\"Нажмите на это поле и выберите места на схеме\">
                        <span class=\"mobile-only text-muted small\" style=\"width:calc( 100% - 50px )\">Нажмите на это поле и выберите места на схеме</span>
                    </div>
                </td>
                <td class=\"mobile-only\">Тариф:</td>
                <td class=\"w120\">
                    <select name=\"tarif\" class=\"form-control\">
                        <option selected=\"\" value=\"Полный:100\">Полный, 100%</option>
                    </select></td>
                <td class=\"mobile-only cargo-column\">Количество мест багажа:</td>
                <td class=\"w20 cargo-column\">
                    <select name=\"bag\" class=\"form-control\">
                        <option val=\"0\" selected=\"\">Нет</option>
                        <option val=\"1\">1 место</option>
                        <option val=\"2\">2 места</option>
                        <option val=\"3\">3 места</option>
                    </select></td>
                <td class=\"mobile-only\">Фамилия:</td>
                <td class=\"w110\">
                    <input name=\"f\" value=\"$f\" class=\"form-control icon-close error\" type=\"text\"placeholder=\"Фамилия\">
                    <span class=\"icon\"></span>
                </td>
                <td class=\"mobile-only\">Имя:</td>
                <td class=\"w110\">
                    <input name=\"i\" value=\"$i\" class=\"form-control icon-close error\" type=\"text\"placeholder=\"Имя\">
                    <span class=\"icon\"></span>
                </td>
                <td class=\"mobile-only\">Отчество:</td>
                <td class=\"w110\">
                    <input name=\"o\" value=\"$o\" class=\"form-control icon-close error\" type=\"text\"placeholder=\"Отчество\">
                    <span class=\"icon\"></span>
                </td>
                <td class=\"mobile-only\">Пол:</td>
                <td class=\"w50\">
                    <select name=\"sex\" class=\"form-control\">
                        <option $wSelected value=\"0\">Ж</option>
                        <option $mSelected value=\"1\">М</option>
                    </select>
                </td>
                <td class=\"mobile-only\">Дата рождения:</td>
                <td class=\"w100\">
                    <input name=\"dr\" value=\"$dr\" class=\"form-control icon-close error\" type=\"text\" mask=\"$drMask\" placeholder=\"дд.мм.гггг\"maxlength=\"10\">
                    <span class=\"icon\"></span>
                </td>
                <td class=\"mobile-only\">Гражданство:</td>
                <td class=\"w100\">
                    <input name=\"grazhd\"
                                        class=\"form-control icon-close ui-autocomplete-input\"
                                        type=\"text\" data-id=\"$grazhdId\" value=\"$grazhdTxt\"
                                        placeholder=\"Страна\" autocomplete=\"off\"><span
                            class=\"icon\"></span></td>
                <td class=\"mobile-only\">Удостоверение личности:</td>
                <td class=\"w100\"><input name=\"doc\"
                                        class=\"form-control icon-close ui-autocomplete-input\"
                                        type=\"text\" data-id=\"0\" value=\"$docTypeTxt\"
                                        placeholder=\"Удостоверение личности\"
                                        autocomplete=\"off\"><span class=\"icon\"></span></td>
                <td class=\"mobile-only\">Серия и номер удостоверения:</td>
                <td class=\"mobile-only\">
                    <div class=\"info docnumhelp text-muted\">Формат номера документа:
                        4+6 цифр
                        Пример: 2002 123456
                    </div>
                </td>
                <td class=\"w120\">
                    <input name=\"docnum\" value=\"$docNum\" class=\"form-control icon-close error\" type=\"text\" mask=\"0000 000000\" placeholder=\"0000 000000\" maxlength=\"11\">
                    <span class=\"icon\"></span>
                </td>
                <td class=\"buttons-container\">
                    <div class=\"button trash\" title=\"Удалить пассажира\"><span class=\"mobile-only\"> Удалить пассажира </span>
                        <i class=\"fa fa-close\"></i>
                    </div>
                    <div class=\"button info docnumhelp non-mobile-only\" title=\"Формат номера документа: 4+6 цифр Пример: 2002 123456\">
                        <i class=\"fa fa-info\"></i>
                    </div>
                    <div class=\"button add mobile-only\">
                        <span> Добавить пассажира </span> 
                        <i class=\"fa fa-plus\"></i>
                    </div>
                </td>";
    }
}
