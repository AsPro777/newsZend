<?php
namespace User\Form;

use Zend\Form\Form;
use DoctrineModule\Form\Element\ObjectSelect;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Zend\InputFilter\InputFilter;

use Zend\Form\Element\DateSelect;
use Zend\Validator\Step;

class DrvBusForm extends Form implements ObjectManagerAwareInterface 
{
    
    private $entityManager = null;
    private $user = null;
    private $ed_bus = null;
    private $scenario = "create";

    public function __construct($scenario = 'create', $entityManager = null, $user = null, $ed_bus = null )
    {
        parent::__construct('drv-bus-form');
        $this->setAttribute('method', 'post');

        // Save parameters for internal use.
        $this->entityManager = $entityManager;
        $this->user = $user;
        $this->ed_bus = $ed_bus;
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
            'name' => 'vendor',            
            'attributes' => [
                'class'=>'form-control',
                ],
            'options' => [
                'label' => 'Марка *',
            ],
        ]);

        $this->add([            
            'type'  => 'text',
            'name' => 'model',            
            'attributes' => [
                'class'=>'form-control',
                ],
            'options' => [
                'label' => 'Модель *',
            ],
        ]);

        $this->add([            
            'type'  => 'number',
            'name' => 'size',    
            'attributes' => [
                'class'=>'form-control',
		'min' => '1',
		'max' => '60',
		'step' => '1', 
                'readonly' => true
                ],
            'options' => [
                'label' => 'Количество мест *',
            ],
        ]);
        
        $this->add([            
            'type'  => 'number',
            'name' => 'year',    
            'attributes' => [
                'class'=>'form-control',
		'min' => date("Y") - 20,
		'max' => date("Y"),
		'step' => '1'
                ],
            'options' => [
                'label' => 'Год выпуска *',
            ],
        ]);
               
        $this->add([            
            'name' => 'gn',    
            'attributes' => [
                'class'=>'form-control',
                ],
            'options' => [
                'label' => 'Госномер *',
            ],
        ]);
               
        $this->add([            
            'type'  => 'select',
            'name' => 'enabled',
            'attributes' => [
                'class'=>'form-control',
                ],
            'options' => [
                'label' => 'Допуск в систему',
                'value_options' => [
                    0 => 'Заблокирован',                    
                    1 => 'Активен',
                ]
            ],
        ]);

        $this->add([            
            'type'  => 'hidden',
            'name' => 'config',
            'attributes' => [
                'class'=>'form-control', 
            ],
            'options' => [
                'label' => ' ',
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
                'name'     => 'gn',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'StringTrim'],
                ],                
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 1,
                            'max' => 15
                        ],
                    ],
                ],
            ]);
                
        $inputFilter->add([
                'name'     => 'vendor',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'StringTrim'],
                ],                
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 1,
                            'max' => 20
                        ],
                    ],
                ],
            ]);
        
        $inputFilter->add([
                'name'     => 'model',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'StringTrim'],
                ],                
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 1,
                            'max' => 20
                        ],
                    ],
                ],
            ]);
        
        $inputFilter->add([
                'name'     => 'size',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    [
                        'name'=>'NotEmpty',
                    ],
                    [
                        'name'=>'Step',
                        'messageTemplates' => [
                                'typeInvalid' => "Invalid value given. Scalar expected",
                                'stepInvalid' => "The input is not a valid step 111"
                            ]
                        ],
                ],
            ]);
        
        $inputFilter->add([
                'name'     => 'enabled',
                'required' => true,                
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>[0, 1]]]
                ],
            ]);        
    }
}