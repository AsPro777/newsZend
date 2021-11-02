<?php
namespace User\Validator;

use Zend\Validator\AbstractValidator;
use User\Entity\Usr;
/**
 * This validator class is designed for checking if there is an existing user
 * with such an email.
 */
class Phone1ExistsValidator extends AbstractValidator
{
    /**
     * Available validator options.
     * @var array
     */
    protected $options = array(
        'entityManager' => null,
        'user' => null
    );

    // Validation failure message IDs.
    const NOT_SCALAR  = 'notScalar';
    const USER_EXISTS = 'userExists';

    /**
     * Validation failure messages.
     * @var array
     */
    protected $messageTemplates = array(
        self::NOT_SCALAR  => "Должно быть скалярное значение",
        self::USER_EXISTS  => "Этот адрес уже используется другим пользователем"
    );

    /**
     * Constructor.
     */
    public function __construct($options = null)
    {
        // Set filter options (if provided).
        if(is_array($options)) {
            if(isset($options['entityManager']))
                $this->options['entityManager'] = $options['entityManager'];
            if(isset($options['user']))
                $this->options['user'] = $options['user'];
        }

        // Call the parent class constructor
        parent::__construct($options);
    }

    /**
     * Check if user exists.
     */
    public function isValid($value)
    {
        if(!is_scalar($value)) {
            $this->error(self::NOT_SCALAR);
            return false;
        }

        $value = preg_replace ('/[^\d]+/','',$value);
        if(in_array(strlen($value), [11,10]))
        {
            $value = substr($value, -10);
            $value = doubleval($value);
        }
//        else
//            return false;

        // Get Doctrine entity manager.
        $entityManager = $this->options['entityManager'];
        $conn = $entityManager->getConnection();
        $qb = $conn->createQueryBuilder();
        $qb
         ->select('u.id')
         ->from('usr', 'u')
         ->where('coalesce(deleted, FALSE)=FALSE')
         ->andWhere(":phone1 = RIGHT(REGEXP_REPLACE(data->'profile'->'contact'->>'phone1','[^[:digit:]]','','g'),10)")->setParameter(':phone1', $value, \PDO::PARAM_STR);

        if(!empty($this->options['user']))
            $qb->andWhere(":id != id")->setParameter(':id', $this->options['user']->getId(), \PDO::PARAM_INT);

        $qb->setMaxResults(1);


        $stmt = $qb->execute();

        $isValid = empty($stmt->fetch(\PDO::FETCH_ASSOC, \PDO::FETCH_ORI_NEXT));

        if(!$isValid)
            $this->error(self::USER_EXISTS);

        return $isValid;
    }
}

