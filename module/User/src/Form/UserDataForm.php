<?php
namespace User\Form;

use Zend\Form\Form;
use DoctrineModule\Form\Element\ObjectSelect;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Zend\InputFilter\InputFilter;

use User\Entity\SprUsrType;
use User\Entity\UsrData;
use Zend\Form\Element\DateSelect;

class UserDataForm extends Form implements ObjectManagerAwareInterface 
{
    
    private $entityManager = null;
    private $user = null;

    public function __construct($entityManager = null, $user = null)
    {
        parent::__construct('user-form');
        $this->setAttribute('method', 'post');

        // Save parameters for internal use.
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
    
    protected function addElements() 
    {
        $this->add([            
            'type'  => 'text',
            'name' => 'phone',
            'options' => [
                'label' => 'Телефон',
            ],
        ]);
               
        $this->add([            
            'type'  => 'select',
            'name' => 'sex',
            'options' => [
                'label' => 'Пол',
                'value_options' => [
                    1 => 'Мужской',                    
                    2 => 'Женский',
                ]
            ],
        ]);

        $this->add([            
            'type'  => 'DateSelect',
            'name' => 'birthday',
            'options' => [
                'label' => 'Дата рождения',
            ],
        ]);

        $this->add([            
            'type' => 'text',
            'name' => 'birthplace',
            'options' => [
                'label' => 'Место рождения',
            ],
        ]);
        
        $this->add([
            'type' => 'csrf',
            'name' => 'csrf',
            'options' => [
                'csrf_options' => [
                'timeout' => 600
                ]
            ],
        ]);
        
        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [                
                'value' => 'Регистрация'
            ],
        ]);
    }
    
    private function addInputFilter() 
    {
        // Create main input filter
        $inputFilter = new InputFilter();        
        $this->setInputFilter($inputFilter);
                
        $inputFilter->add([
                'name'     => 'phone',
                'required' => true,
                'filters'  => [
                    [
                        'name' => 'PregReplace', 
                        'options' => [
                            'pattern' => '/\D/',
                            'replacement' => ''
                            ]
                    ],
                ],                
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 11,
                            'max' => 11,
                            'messages' => [
                                'stringLengthInvalid' => "Неправильный тип аргумента. Должна быть строка!",
                                'stringLengthTooShort' => "Номер телефона состоит из %min% символов",
                                'stringLengthTooLong' => "Номер телефона состоит из %max% символов",
                            ] 
                        ],
                    ],
                ],
            ]);     

        $inputFilter->add([
                'name'     => 'birthplace',
                'required' => true,
                'filters'  => [
                    [
                        'name' => 'StringTrim', 
                    ],
                ],                
                'validators' => [
                    [
                        'name'    => 'NotEmpty',
                        'options' => [
                            'messages' => [
                                'notEmptyInvalid' => "Неправильный тип аргумента. Должны быть символы или цифры!!",
                                'isEmpty' => "Необходимо ввести эти данные!",
                            ] 
                        ],
                    ],
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 2,
                            'max' => 100,
                            'messages' => [
                                'stringLengthInvalid' => "Неправильный тип аргумента. Должна быть строка!",
                                'stringLengthTooShort' => "Место рождения не может быть короче %min% символов",
                                'stringLengthTooLong' => "Место рождения ограничено длиной %max% символов",
                            ] 
                        ],
                    ],
                ],
            ]);     

    }           
}