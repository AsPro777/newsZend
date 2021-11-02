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

class PassportForm extends Form implements ObjectManagerAwareInterface 
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
            'name' => 'p_seria',
            'options' => [
                'label' => 'Серия',
            ],
        ]);
               
        $this->add([            
            'type'  => 'text',
            'name' => 'p_number',
            'options' => [
                'label' => 'Номер',
            ],
        ]);

        $this->add([            
            'type'  => 'text',
            'name' => 'p_issued_by',
            'options' => [
                'label' => 'Кем выдан',
            ],
        ]);

        $this->add([            
            'type' => 'DateSelect',
            'name' => 'p_date',
            'options' => [
                'label' => 'Дата выдачи',
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
                'value' => 'Сохранить'
            ],
        ]);
    }
    
    private function addInputFilter() 
    {
        // Create main input filter
        $inputFilter = new InputFilter();        
        $this->setInputFilter($inputFilter);
                
        $inputFilter->add([
                'name'     => 'p_seria',
                'required' => true,
                'filters'  => [
                    [
                        'name' => 'StringTrim', 
                    ],
                ],                
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 4,
                            'max' => 4,
                            'messages' => [
                                'stringLengthInvalid' => "Неправильный тип аргумента. Должна быть строка!",
                                'stringLengthTooShort' => "Должно быть %min% символа",
                                'stringLengthTooLong' => "Должно быть %max% символа",
                            ] 
                        ],
                    ],
                    [
                        'name'    => 'Digits',
                        'options' => [
                            'messages' => [
                                'notDigits' => "Должны быть только цифры!",
                                'digitsStringEmpty' => "Должно быть заполнено!",
                                'digitsInvalid' => "Должно быть числовое значение!",
                            ] 
                        ],
                    ],
                ],
            ]);     

        $inputFilter->add([
                'name'     => 'p_number',
                'required' => true,
                'filters'  => [
                    [
                        'name' => 'StringTrim', 
                    ],
                ],                
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 6,
                            'max' => 6,
                            'messages' => [
                                'stringLengthInvalid' => "Неправильный тип аргумента. Должна быть строка!",
                                'stringLengthTooShort' => "Должно быть %min% символов",
                                'stringLengthTooLong' => "Должно быть %max% символов",
                            ] 
                        ],
                    ],
                    [
                        'name'    => 'Digits',
                        'options' => [
                            'messages' => [
                                'notDigits' => "Должны быть только цифры!",
                                'digitsStringEmpty' => "Должно быть заполнено!",
                                'digitsInvalid' => "Должно быть числовое значение!",
                            ] 
                        ],
                    ],
                ],
            ]);     

        $inputFilter->add([
                'name'     => 'p_issued_by',
                'required' => true,
                'filters'  => [
                    [
                        'name' => 'StringTrim', 
                    ],
                ],                
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 20,
                            'max' => 150,
                            'messages' => [
                                'stringLengthInvalid' => "Неправильный тип аргумента. Должна быть строка!",
                                'stringLengthTooShort' => "Должно быть не менее %min% символов",
                                'stringLengthTooLong' => "Должно быть не более %max% символов",
                            ] 
                        ],
                    ],
                ],
            ]);     


    }           
}