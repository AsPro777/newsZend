<?php
namespace User\Form;

use Zend\Form\Form;
use DoctrineModule\Form\Element\ObjectSelect;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Zend\InputFilter\InputFilter;

use Zend\Validator\Step;

class DrvReisesForm extends Form implements ObjectManagerAwareInterface 
{
    
    private $entityManager = null;
    private $user = null;
    private $ed_reis = null;
    private $scenario = "create";
       
    public function __construct($scenario = 'create', $entityManager = null, $user = null, $ed_reis = null )
    {
        parent::__construct('drv-reises-form');
        $this->setAttribute('method', 'post');

        // Save parameters for internal use.
        $this->entityManager = $entityManager;
        $this->user = $user;
        $this->ed_reis = $ed_reis;
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
            'type'  => 'hidden',
            'name' => 'id_from',
            'options' => [
                'label' => ' ',
            ],
        ]);
        $this->add([
            'type'  => 'text',
            'name' => 'name_from',
            'attributes' => [
                'class'=>'form-control',
                'readonly' => 'readonly'
                ],
            'options' => [
                'label' => 'Пункт отправления',
            ],
        ]);

        $this->add([            
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'id_from_endpoint',
            'attributes' => [
                'class'=>'form-control',
                ],
            'options' => array(
                'label' => 'Конечная остановка',
                'object_manager' => $this->getObjectManager(),
                'target_class'   => 'User\Entity\Point',
                'property'       => 'name',
                'display_empty_item' => true,
                'empty_item_label'   => 'Укажите остановку',
                'disable_inarray_validator' => true,
                'is_method'      => true,
                'find_method'    => [
                    'name'   => 'findBy', 
                    'params' => [
                        'criteria' => [
                            'idOwner' => $this->user,
                            'idCity' => $this->ed_reis->getIdFrom(),
                        ],
                        'orderBy' => [
                            'name' => 'asc'
                        ],
                        //'limit' => '100',
                        'offset' => '0',
                    ]
                ],
            ),
        ]);

        $this->add([
            'type'  => 'hidden',
            'name' => 'id_to',
            'options' => [
                'label' => ' ',
            ],
        ]);
        $this->add([            
            'type'  => 'text',
            'name' => 'name_to',
            'attributes' => [
                'class'=>'form-control',
                'readonly' => 'readonly'
                ],
            'options' => [
                'label' => 'Пункт назначения',
            ],
        ]);

        $this->add([            
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'id_to_endpoint',
            'attributes' => [
                'class'=>'form-control',
                ],
            'options' => array(
                'label' => 'Конечная остановка',
                'object_manager' => $this->getObjectManager(),
                'target_class'   => 'User\Entity\Point',
                'property'       => 'name',
                'display_empty_item' => true,
                'empty_item_label'   => 'Укажите остановку',
                'disable_inarray_validator' => true,
                'is_method'      => true,
                'find_method'    => [
                    'name'   => 'findBy', 
                    'params' => [
                        'criteria' => [
                            'idOwner' => $this->user,
                            'idCity' => $this->ed_reis->getIdTo(),
                        ],
                        'orderBy' => [
                            'name' => 'asc'
                        ],
                        //'limit' => '100',
                        'offset' => '0',
                    ]
                ],
            ),
        ]);
        
        $this->add([            
            'type'  => 'text',
            'name' => 'points',
            'attributes' => [
                'class'=>'form-control',
                ],
            'options' => [
                'label' => 'Промежуточные пункты',
            ],
        ]);
        
        $this->add([            
            'type'  => 'text',
            'name' => 'drivers',
            'attributes' => [
                'class'=>'form-control',
                ],
            'options' => [
                'label' => 'Водители на маршруте',
            ],
        ]);

        $this->add([            
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'id_bus',            
            'attributes' => [
                'class'=>'form-control',
                ],
                    'options' => array(
                        'label' => 'Автобус',
                        'object_manager' => $this->getObjectManager(),
                        'target_class'   => 'User\Entity\Bus',
                        'property'       => 'name',
//                        'display_empty_item' => true,
//                        'empty_item_label'   => '---',
                        'is_method'      => true,
                        'find_method'    => [
                            'name'   => 'findBy', 
                            'params' => [
                                'criteria' => [
                                    'idOwner' => $this->user,
                                    'enabled' => true
                                ],
                                'orderBy' => [
                                    'name' => 'asc'
                                ],
                                'limit' => '100',
                                'offset' => '0',
                            ]
                        ],
                    ),
        ]);

        $this->add([            
            'type'  => 'text',
            'name' => 'date',
            'attributes' => [
                'class'=>'form-control',
                ],
            'options' => [
                'label' => 'Дата отправления',
                'label_attributes' => [
                    ],
            ],
        ]);
              
        $this->add([            
            'type'  => 'time',
            'name' => 'time',
            'attributes' => [
                'class'=>'form-control',
                ],
            'options' => [
                'label' => 'Время отправления',
                'format' => 'H:i'
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
                'name'     => 'id_bus',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],
                'validators' => [
                    [
                        'name'=> \Application\Validator\NotZeroValidator::class,
                    ],
                ],
            ]);
        
        $inputFilter->add([
                'name'     => 'id_from_endpoint',
                'required' => true,
                'filters'  => [                                        
                ],
                'validators' => [
                    [
                        'name'=> \Application\Validator\NotZeroValidator::class,
                    ],
                ],
            ]);
        
        $inputFilter->add([
                'name'     => 'id_to_endpoint',
                'required' => true,
                'filters'  => [                                        
                ],
                'validators' => [
                    [
                        'name'=> \Application\Validator\NotZeroValidator::class,
                    ],
                ],
            ]);
        
        $inputFilter->add([
                'name'     => 'name_from',
                'required' => true,
                'filters'  => [                    
                ],
                'validators' => [
                    [
                        'name' => \Application\Validator\CoupleValidator::class,
                        'options' => [
                            'format' => 'int',
                            'hidden' => 'id_from'
                        ],
                    ],
                ],
            ]);
        
        $inputFilter->add([
                'name'     => 'name_to',
                'required' => true,
                'filters'  => [                    
                ],
                'validators' => [
                    [
                        'name' => \Application\Validator\CoupleValidator::class,
                        'options' => [
                            'format' => 'int',
                            'hidden' => 'id_to'
                        ],
                    ],
                ],
            ]);
        
        $inputFilter->add([
                'name'     => 'drivers',
                'required' => false,
                'filters'  => [
                    [
                        'name' => \Application\Filter\FakeFilter::class
                    ]
                ],
                'validators' => [
                    [
                        'name' => \Application\Validator\CoupleValidator::class,
                        'options' => [
                            'format' => 'arr',
                            'hidden' => 'driver'
                        ],
                    ],
                ],
            ]);
        
        $inputFilter->add([
                'name'     => 'date',
                'required' => true,
                'filters'  => [                    
                ],
                'validators' => [
                   [
                        'name' => \Application\Validator\RussianDateValidator::class,
                    ],
                ],
            ]);
        
        $inputFilter->add([
                'name'     => 'time',
                'required' => true,
                'filters'  => [                    
                ],
                'validators' => [
                   [
                        'name' => \Application\Validator\RussianTimeValidator::class,
                    ],
                ],
            ]);
                
    }
}