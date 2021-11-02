<?php
namespace User\Form;

use Zend\Form\Form;
use DoctrineModule\Form\Element\ObjectSelect;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Zend\InputFilter\InputFilter;
use User\Validator\UserExistsValidator;

use User\Entity\SprUsrType;
use User\Entity\UsrData;
use Zend\Form\Element\DateSelect;

class DrvPersonalForm extends Form implements ObjectManagerAwareInterface 
{
    
    private $entityManager = null;
    private $user = null;
    private $ed_user = null;
    private $scenario = "create";

    public function __construct($scenario = 'create', $entityManager = null, $user = null, $ed_user = null )
    {
        parent::__construct('drv-personal-form');
        $this->setAttribute('method', 'post');

        // Save parameters for internal use.
        $this->entityManager = $entityManager;
        $this->user = $user;
        $this->ed_user = $ed_user;
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
            'type'  => 'password',
            'name' => 'password',
            'attributes' => [
                'class'=>'form-control',
                ],
            'options' => [
                'label' => 'Пароль',
            ],
        ]);
        
        $this->add([            
            'type'  => 'select',
            'name' => 'status',
            'attributes' => [
                'class'=>'form-control',
                ],
            'options' => [
                'label' => 'Допуск в систему',
                'value_options' => [
                    2 => 'Заблокирован',                    
                    1 => 'Активен',
                ]
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
                        'name' => 'EmailAddress',
                        'options' => [
                            'allow' => \Zend\Validator\Hostname::ALLOW_DNS,
                            'useMxCheck'    => false,                            
                        ],
                    ],
                    [
                        'name' => UserExistsValidator::class,
                        'options' => [
                            'entityManager' => $this->entityManager,
                            'user' => $this->ed_user
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
        
            $inputFilter->add([
                    'name'     => 'password',
                    'required' => false,
                    'filters'  => [                        
                    ],                
                    'validators' => [
                        [
                            'name'    => 'StringLength',
                            'options' => [
                                'min' => 6,
                                'max' => 64
                            ],
                        ],
                    ],
                ]);
        
        $inputFilter->add([
                'name'     => 'status',
                'required' => true,                
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2]]]
                ],
            ]);        
    }
}