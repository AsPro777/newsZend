<?php
namespace User\Form;

use Zend\Form\Form;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Zend\InputFilter\InputFilter;
use Application\Validator\RussianDateValidator;
use Application\Validator\PhoneValidator;
use Application\Validator\RussianStringLength;
use Zend\Validator\StringLength;

class DrvDriverForm extends Form implements ObjectManagerAwareInterface 
{
    
    private $entityManager = null;
    private $user = null;
    private $ed = null;
    private $scenario = "create";

    public function __construct($scenario = 'create', $entityManager = null, $user = null, $ed = null )
    {
        parent::__construct('drv-driver-form');
        $this->setAttribute('method', 'post');

        // Save parameters for internal use.
        $this->entityManager = $entityManager;
        $this->user = $user;
        $this->ed = $ed;
        $this->scenario = $scenario;
        
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
            'name' => 'f',
            'attributes' => [
                'class'=>'form-control',
                ],
            'options' => [
                'label' => 'Фамилия *',
            ],
        ]);

        $this->add([
            'type'  => 'text',
            'name' => 'i',
            'attributes' => [
                'class'=>'form-control',
                ],
            'options' => [
                'label' => 'Имя *',
            ],
        ]);

        $this->add([
            'type'  => 'text',
            'name' => 'o',
            'attributes' => [
                'class'=>'form-control',
                ],
            'options' => [
                'label' => 'Отчество *',
            ],
        ]);

        $this->add([            
            'type'  => 'select',
            'name' => 'sex',
            'attributes' => [
                'class'=>'form-control',
                ],
            'options' => [
                'label' => 'Пол *',
                'value_options' => [
                    1 => 'Мужской',                    
                    2 => 'Женский',
                ]
            ],
        ]);
        
        $this->add([            
            'type'  => 'text',
            'name' => 'birthday',            
            'attributes' => [
                'class'=>'form-control',
                ],
            'options' => [
                'label' => 'Дата рождения *',
            ],
        ]);

        $this->add([            
            'type'  => 'text',
            'name' => 'address',
            'attributes' => [
                'class'=>'form-control',
                ],
            'options' => [
                'label' => 'Адрес *',
            ],
        ]);

        $this->add([            
            'type'  => 'select',
            'name' => 'passport_type',
            'attributes' => [
                'class'=>'form-control',
                ],
            'options' => [
                'label' => 'Удостоверение личности *',
                'value_options' => $this->entityManager->getRepository(\User\Entity\SprDocType::class)->getCodeItems()
            ],
        ]);
        
        $this->add([            
            'type'  => 'text',
            'name' => 'passport',
            'attributes' => [
                'class'=>'form-control',
                ],
            'options' => [
                'label' => 'Серия и номер удостоверяющего документа*',
            ],
        ]);

        $this->add([            
            'type'  => 'text',
            'name' => 'vu',
            'attributes' => [
                'class'=>'form-control',
                ],
            'options' => [
                'label' => 'Серия и номер ВУ *',
            ],
        ]);
        
        $this->add([            
            'type'  => 'text',
            'name' => 'phone1',
            'attributes' => [
                'class'=>'form-control',
                ],
            'options' => [
                'label' => 'Основной телефон *',
            ],
        ]);
        
        $this->add([            
            'type'  => 'text',
            'name' => 'phone2',
            'attributes' => [
                'class'=>'form-control',
                ],
            'options' => [
                'label' => 'Первый резервный телефон',
            ],
        ]);
               
        $this->add([            
            'type'  => 'text',
            'name' => 'phone3',
            'attributes' => [
                'class'=>'form-control',
                ],
            'options' => [
                'label' => 'Второй резервный телефон',
            ],
        ]);
                              
        $this->add([
            'type' => 'csrf',
            'name' => 'csrf',
            'attributes' => [
                'class'=>'form-control', 
            ],
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
                'class'=>'btn btn-primary',
                'value' => 'Сохранить'
            ],
        ]);
    }
    
     private function addInputFilter() 
    {
        $inputFilter = new InputFilter();        
        $this->setInputFilter($inputFilter);

        $inputFilter->add([
                'name'     => 'f',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'StringTrim'],
                ],                
                'validators' => [
                    [
                        'name'    => RussianStringLength::class,
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
                        'name'    => RussianStringLength::class,
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
                        'name'    => RussianStringLength::class,
                        'options' => [
                            'min' => 1,
                            'max' => 100
                        ],
                    ],
                ],
            ]);                
        
        $inputFilter->add([
                'name'     => 'birthday',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'StringTrim'],
                ],                
                'validators' => [
                    [
                        'name' => RussianDateValidator::class
                    ],
                ],
            ]);                
        
        $inputFilter->add([
                'name'     => 'passport',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'StringTrim'],
                ],                
                'validators' => [
                    [
                        'name'    => RussianStringLength::class,
                        'options' => [
                            'min' => 1,
                            'max' => 50
                        ],
                    ],
                ],
            ]);                
        
        $inputFilter->add([
                'name'     => 'address',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'StringTrim'],
                ],                
                'validators' => [
                    [
                        'name'    => RussianStringLength::class,
                        'options' => [
                            'min' => 10,
                            'max' => 250
                        ],
                    ],
                ],
            ]);                
        
        $inputFilter->add([
                'name'     => 'vu',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'StringTrim'],
                ],                
                'validators' => [
                    [
                        'name'    => RussianStringLength::class,
                        'options' => [
                            'min' => 5,
                            'max' => 50
                        ],
                    ],
                ],
            ]);                
        
        $inputFilter->add([
                'name'     => 'phone1',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'StringTrim'],
                ],                
                'validators' => [
                    [
                        'name'    => PhoneValidator::class,
                        'options' => [
                            'format' => "intl"
                        ],
                    ],
                ],
            ]);                
        $inputFilter->add([
                'name'     => 'phone2',
                'required' => false,
                'filters'  => [                    
                    ['name' => 'StringTrim'],
                ],                
                'validators' => [
                    [
                        'name'    => PhoneValidator::class,
                        'options' => [
                            'format' => "intl"
                        ],
                    ],
                ],
            ]);                
        $inputFilter->add([
                'name'     => 'phone3',
                'required' => false,
                'filters'  => [                    
                    ['name' => 'StringTrim'],
                ],                
                'validators' => [
                    [
                        'name'    => PhoneValidator::class,
                        'options' => [
                            'format' => "intl"
                        ],
                    ],
                ],
            ]);                
        
    }
}