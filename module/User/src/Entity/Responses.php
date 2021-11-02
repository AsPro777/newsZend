<?php

namespace User\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\ArrayCollection;

/**

 * Этот класс представляет собой информацию об отзывах пользователей.

 * @ORM\Entity

 * @ORM\HasLifecycleCallbacks()

 * @ORM\Table(name="responses")

 * @ORM\Entity(repositoryClass="\User\Repository\ResponsesRepository")

 *
 */

class Responses
{

    /**

     * @access protected

     * @ORM\Id

     * @ORM\GeneratedValue

     * @ORM\Column(name="id")

     */

    protected $id;


    /**

     * @access protected

     * @ORM\Column(name="date_reg")

     */

    protected $dateReg;

    /**

     * @access protected

     * @ORM\Column(name="readed")

     */

    protected $readed;

    /**

     * @access protected

     * @ORM\Column(name="deleted")

     */

    protected $deleted;

    /**

     * @access protected

     * @ORM\Column(name="data")

     */

    protected $data;

    /**

     * getId - Возвращает ID данного поста.

     * @return mixed

     */

    public function getId()
    {
        return $this->id;
    }

    /**

     * setId - Задает ID данного поста.

     * @param $id - идентификатор поста

     */

    public function setId($id)
    {
        $this->id = $id;
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

     * getReaded - Возвращает статус прочитан ли отзыв.

     * @return mixed

     */

    public function getReaded()
    {
        return $this->readed;
    }

    /**

     * setStatus - Устанавливает статус прочитан ли отзыв.

     * @param $readed - статус отзыва

     */

    public function setReaded($readed)
    {
        $this->readed = $readed;
    }


    /**

     * getDeleted -  Возвращает мнформацию о том был ли удален отзыв пользователя.

     * @return mixed

     */

    public function getDeleted()
    {
        return $this->deleted;
    }

    /**

     * setDeleted - Задает признак что отзыв пользователя был удален.

     * @param $deleted - признак удаления отзыва

     */

    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
    }

    /**

     * getData - Возвращает отзыв пользователя.

     * @return mixed

     */


    public function getData()
    {
        return $this->data;
    }

    /**

     * setData - Задает отзыв пользователя.

     * @param $data- отзыв пользователя

     */

    public function setData($data)
    {
        $this->data = $data;
    }

}
