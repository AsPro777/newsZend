<?php
namespace User\Form;

use Zend\Form\Form;
use DoctrineModule\Form\Element\ObjectSelect;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Zend\InputFilter\InputFilter;
use User\Validator\UserExistsValidator;
use Application\Validator\EmailValidator;

use User\Entity\SprUsrType;

/**
 * This form is used to collect user's email, full name, password and status. The form
 * can work in two scenarios - 'create' and 'update'. In 'create' scenario, user
 * enters password, in 'update' scenario he/she doesn't enter password.
 */
class UserForm extends Form implements ObjectManagerAwareInterface
{

    private $scenario;
    /**
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager = null;
    /**
     * @var User\Entity\Usr
     */
    private $user = null;

    public function __construct($scenario = 'create', $entityManager = null, $user = null)
    {
        // Define form name
        parent::__construct('user-form');

        // Set POST method for this form
        $this->setAttribute('method', 'post');

        // Save parameters for internal use.
        $this->scenario = $scenario;
        $this->entityManager = $entityManager;
        $this->user = $user;

        $this->addElements();
        $this->addInputFilter();
    }

    public function setObjectManager(ObjectManager $objectManager)
    {
        $this->entityManager = $objectManager;
    }

    public function getObjectManager()
    {
        return $this->entityManager;
    }

    /**
     * This method adds elements to form (input fields and submit button).
     */
    protected function addElements()
    {
        $this->add([
            'type'  => 'select',
            'name' => 'idUsrType',
            'attributes' => [
                'class'=>'form-control',
                ],
            'options' => [
                'label' => 'Зарегистрировать как',
                'value_options' => [
                    2 => 'Пассажир',
                    3 => 'Перевозчик',
                    //5 => 'Агент',
                ]
            ],
        ]);

        $this->add([
            'type'  => 'text',
            'name' => 'email',
            'attributes' => [
                'class'=>'form-control',
                ],
            'options' => [
                'label' => 'E-mail',
            ],
        ]);


        $this->add([
            'type'  => 'text',
            'name' => 'f',
            'attributes' => [
                'class'=>'form-control',
                ],
            'options' => [
                'label' => 'Фамилия',
            ],
        ]);

        $this->add([
            'type'  => 'text',
            'name' => 'i',
            'attributes' => [
                'class'=>'form-control',
                ],
            'options' => [
                'label' => 'Имя',
            ],
        ]);

        $this->add([
            'type'  => 'text',
            'name' => 'o',
            'attributes' => [
                'class'=>'form-control',
                ],
            'options' => [
                'label' => 'Отчество',
            ],
        ]);

        $this->add([
            'type'  => 'select',
            'name' => 'sex',
            'attributes' => [
                'class'=>'form-control',
                ],
            'options' => [
                'label' => 'Пол',
                'value_options' => [
                    1 => 'Мужской',
                    0 => 'Женский'
                ]
            ],
        ]);

        // Add the CAPTCHA field
        $this->add([
            'type' => 'captcha',
            'name' => 'captcha',
            'options' => [
                'label' => 'Ввод защитного кода:',
                'captcha' => [
                    'class' => 'ReCaptcha',
                    'pubKey' => '6Lfek3IUAAAAAKPLUGjt11mqsFcJUVK-XdshqnO0',
                    'privKey' => '6Lfek3IUAAAAABdXKMiH8Re4Twl-rXvBAgRuDY8J',
//                    'class' => 'Image',
//                    'imgDir' => 'public/img/captcha',
//                    'suffix' => '.png',
//                    'imgUrl' => '/img/captcha/',
//                    'imgAlt' => 'Ввод защитного кода',
//                    'font' => './data/font/thorne_shaded.ttf',
//                    'fsize' => 18,
//                    'width' => 360,
//                    'height' => 60,
//                    'expiration' => 600,
//                    'dotNoiseLevel' => 40,
//                    'lineNoiseLevel' => 3
                ],
            ],
        ]);
        
        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [
                'value' => 'Регистрация',
                'disabled' => 'disabled'
            ],
        ]);
    }

    /**
     * This method creates input filter (used for form filtering/validation).
     */
    private function addInputFilter()
    {
        // Create main input filter
        $inputFilter = new InputFilter();
        $this->setInputFilter($inputFilter);

        // Add input for "email" field
        $inputFilter->add([
                'name'     => 'email',
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 1,
                            'max' => 128
                        ],
                    ],
                    [
                        'name' => EmailValidator::class,
                        'options' => [
                            'allow' => \Zend\Validator\Hostname::ALLOW_DNS,
                            'useMxCheck'    => true,
                        ],
                    ],
                    [
                        'name' => UserExistsValidator::class,
                        'options' => [
                            'entityManager' => $this->entityManager,
                            'user' => $this->user
                        ],
                    ],
                ],
            ]);

            $inputFilter->add([
                    'name'     => 'f',
                    'required' => true,
                    'filters'  => [
                        ['name' => 'StringTrim'],
                    ],
                    'validators' => [
                        [
                            'name'    => 'StringLength',
                            'options' => [
                                'min' => 1,
                                'max' => 100
                            ],
                        ],
                    ],
                ]);

            $inputFilter->add([
                    'name'     => 'i',
                    'required' => true,
                    'filters'  => [
                        ['name' => 'StringTrim'],
                    ],
                    'validators' => [
                        [
                            'name'    => 'StringLength',
                            'options' => [
                                'min' => 1,
                                'max' => 100
                            ],
                        ],
                    ],
                ]);

            $inputFilter->add([
                    'name'     => 'o',
                    'required' => true,
                    'filters'  => [
                        ['name' => 'StringTrim'],
                    ],
                    'validators' => [
                        [
                            'name'    => 'StringLength',
                            'options' => [
                                'min' => 1,
                                'max' => 100
                            ],
                        ],
                    ],
                ]);

    }  // addInputFilter()
}


