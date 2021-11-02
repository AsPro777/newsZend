<?php
namespace User\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\ArrayCollection;
/**

 * Этот класс представляет собой новости для пользователей.

 * @ORM\Entity

 * @ORM\HasLifecycleCallbacks()

 * @ORM\Table(name="news")
 *
 * @ORM\Entity(repositoryClass="\User\Repository\NewsRepository")

 *
 */
class News {
    /**

     * @access protected

     * @ORM\Id

     * @ORM\GeneratedValue

     * @ORM\Column(name="id")

     */

     protected $id;


    /**

     * @access protected

     * @ORM\Column(name="head")

     */

    protected $head;

    /**

     * @access protected

     * @ORM\Column(name="text")

     */

    protected $text;

    /**

     * @access protected

     * @ORM\Column(name="date_reg")

     */

    protected $dateReg;

    /**

     * @access protected

     * @ORM\Column(name="add_files")

     */

    protected $addFiles;

    /**

     * @access public

     * @ORM\Column(name="public")

     */

    protected $public;

    /**

     * @access inside

     * @ORM\Column(name="inside")

     */

    protected $inside;

    /**

     * @access carrier

     * @ORM\Column(name="carrier")

     */

    protected $carrier;

     /**

     * @access passenger

     * @ORM\Column(name="passenger")

     */

    protected $passenger;

     /**

     * @access terminal

     * @ORM\Column(name="terminal")

     */

    protected $terminal;

    /**

     * @access personal

     * @ORM\Column(name="personal")

     */

    protected $personal;

    /**

     * @access status

     * @ORM\Column(name="status")

     */

    protected $status;


    /**

     * getId - Возвращает ID данного поста.

     * @return mixed

     */


    public function getId()
    {
        return $this->id;
    }

    /**

     * setId - Задает ID данной новости.

     * @param $id - идентификатор новости

     */

    public function setId($id)
    {
        $this->id = $id;
    }

    /**

     * getHead - Возвращает заголовок новости.

     * @return mixed

     */

    public function getHead()
    {
        return $this->head;
    }

    /**

     * setHead - установить заголовок новости.

     * @param $head - текст заголовка

     */

    public function setHead($head)
    {
        $this->head = $head;
    }

    /**

     * getText - Возвращает текст новости

     * @return mixed

     */

    public function getText()
    {
        return $this->text;
    }

    /**

     * setText - Устанавливает текст новости

     * @param $text - текст новости

     */

    public function setText($text)
    {
        $this->text = $text;
    }

    /**

     * getTitle - Возвращает дату создания записи.

     * @return mixed

     */

    public function getDateReg()
    {
        return $this->dateReg;
    }

    /**

     * setDateReg - дату создания записи.

     * @ORM\PrePersist

     */

    /*благодаря тому что стоит док блок @ORM\PrePersist в сервисе можно не использовать этот метод для вставки даты в табл. Теперь она сама вставится*/
    public function setDateReg()
    {
        $this->dateReg = date('Y-m-d H:i:s');
    }

     /**

     * getAddFiles - Возвращает в формате json прикрепленные изображения

     * @return mixed

     */

    public function getAddFiles()
    {
        return $this->addFiles;
    }

    /**

     * setAddFiles - Устанавливает в формате json прикрепленные изображения

     * @param $text - зфкодированные файлы в виде строки

     */

    public function setAddFiles($text)
    {
        $this->addFiles = $text;
    }

    /**

     * getPublic - если вернет true то данная новость должна размещаться на сайте

     * @return mixed

     */

    public function getPublic()
    {
        return $this->public;
    }

    /**

     * setPublic - Установить в true/false public

     * @param $text

     */

    public function setPublic($text)
    {
        $this->public = $text;
    }

    /**

     * getInside - если вернет true то данная новость должна быть отправлена всем зарегистрированым пользователям в админке

     * @return mixed

     */

    public function getInside()
    {
        return $this->inside;
    }

    /**

     * setInside - Установить в true/false inside

     * @param $text

     */

    public function setInside($text)
    {
        $this->inside = $text;
    }

    /**

     * getCarrier - если вернет true то данная новость должна отправиться перевозчику

     * @return mixed

     */

    public function getCarrier()
    {
        return $this->carrier;
    }

    /**

     * setCarrier - Установить в true/false carrier

     * @param $text

     */

    public function setCarrier($text)
    {
        $this->carrier = $text;
    }

    /**

     * getPassenger - если вернет true то данная новость должна отправиться пассажиру

     * @return mixed

     */

    public function getPassenger()
    {
        return $this->passenger;
    }

    /**

     * setPassenger - Установить в true/false passenger

     * @param $text

     */

    public function setPassenger($text)
    {
        $this->passenger = $text;
    }

    /**

     * getTerminal - если вернет true то данная новость должна отправиться вокзалам

     * @return mixed

     */

    public function getTerminal()
    {
        return $this->terminal;
    }

    /**

     * setTerminal - Установить в true/false terminal

     * @param $text

     */

    public function setTerminal($text)
    {
        $this->terminal = $text;
    }

    /**

     * getPersonal - вернет json-массив пользователей которым данная новость должна отправиться

     * @return mixed

     */

    public function getPersonal()
    {
        return $this->personal;
    }

    /**

     * setPersonal - Занести данные в personal

     * @param $text

     */

    public function setPersonal($text)
    {
        $this->personal = $text;
    }

     /**

     * getStatus - вернет статус новости (должна ли она публиковаться или только быть сохранена)

     * @return mixed

     */

    public function getStatus()
    {
        return $this->status;
    }

    /**

     * setStatus - Занести данные в status

     * @param $text

     */

    public function setStatus($text)
    {
        $this->status = $text;
    }

}
