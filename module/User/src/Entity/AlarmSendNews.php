<?php


namespace User\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\ArrayCollection;

/**

 * Этот класс содержит информацию о том какая новость какому пользователю отправится

 * @ORM\Entity

 * @ORM\HasLifecycleCallbacks()

 * @ORM\Table(name="alarm_send_news")

 *
 */
class AlarmSendNews {
 /**

     * @access protected

     * @ORM\Id

     * @ORM\GeneratedValue

     * @ORM\Column(name="id")

     */

     protected $id;

        /**

     * @access protected

     * @ORM\Column(name="id_news")
     *
     */

    protected $idNews;


     /**

     * @access protected

     * @ORM\Column(name="id_user")
     *
     */

    protected $idUser;
     /**

     * @access protected

     * @ORM\Column(name="readed")
     *
     */

    protected $readed;

     /**

     * @access protected

     * @ORM\Column(name="date_reg")
     *
     */

    protected $dateReg;

     /**

     * getId - Возвращает ID записи.

     * @return mixed

     */


    public function getId()
    {
        return $this->id;
    }

    /**

     * setId - Задает ID данной записи.

     * @param $id - идентификатор новости

     */

    public function setId($id)
    {
        $this->id = $id;
    }

    /**

     * getIdNews - Возвращает id новости.

     * @return mixed

     */

    public function getIdNews()
    {
        return $this->idNews;
    }

    /**

     * setIdNews - Устанавливает id новости.

     * @param $text - id новости

     */

    public function setIdNews($text)
    {
        $this->idNews = $text;
    }
    /**

     * getReaded - Возвращает признак-прочитана ли эта новость у данного пользователя.

     * @return mixed

     */

    public function getReaded()
    {
        return $this->readed;
    }
    /**

     * setReaded - Устанавливает true/false в столбец readed.

     * @param $text

     */

    public function setReaded($text)
    {
        $this->readed = $text;
    }

    /**

     * getIdUser - Возвращает id пользователя, которому адресована новость

     * @return mixed

     */

    public function getIdUser()
    {
        return $this->idUser;
    }

    /**

     * setIdUser - Устанавливает id пользователя, которому адресована новость

     * @param $text - id пользователя

     */

    public function setIdUser($text)
    {
        $this->idUser = $text;
    }

    /**

     * getDateReg - Получить дату отправки новости

     */

    public function getDateReg()
    {
        return $this->dateReg;
    }

    /**

     * setDateReg - дату создания новости.

     * @ORM\PrePersist

     */

    /*благодаря тому что стоит док блок @ORM\PrePersist в сервисе можно не использовать этот метод для вставки даты в табл. Теперь она сама вставится*/
    public function setDateReg()
    {
        $this->dateReg = date('Y-m-d H:i:s');
    }
}
