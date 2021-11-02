<?php
namespace User\Form;

use Zend\Form\Form;
use DoctrineModule\Form\Element\ObjectSelect;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Zend\InputFilter\InputFilter;

class DrvReisForm extends Form implements ObjectManagerAwareInterface 
{
    
    private $entityManager = null;
    private $user = null;
    private $ed_reis = null;
    private $scenario = "create";
       
    public function __construct($scenario = 'create', $entityManager = null, $user = null, $ed_reis = null )
    {
        parent::__construct('drv-reis-form');
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
            'type'  => 'text',
            'name' => 'name',    
            'attributes' => [
                'class'=>'form-control',
                ],
            'options' => [
                'label' => 'Наименование маршрута',
            ],
        ]);
        
        $this->add([            
            'type'  => 'text',
            'name' => 'reis-num',    
            'attributes' => [
                'class'=>'form-control',
                ],
            'options' => [
                'label' => 'Регистрационный номер маршрута',
            ],
        ]);
        
        $this->add([            
            'type'  => 'text',
            'name' => 'cargo',    
            'attributes' => [
                'class'=>'form-control',
                ],
            'options' => [
                'label' => 'Тариф на багаж (% от полной стоимости)',
            ],
        ]);
        
        $this->add([            
            'type'  => 'text',
            'name' => 'from-points',
            'attributes' => [
                'class'=>'form-control',
                'placeholder' => 'Введите город'
                ],
            'options' => [
                'label' => 'Пункт отправления',
            ],
        ]);
                       
        $this->add([            
            'type'  => 'text',
            'name' => 'trace-points',
            'attributes' => [
                'class'=>'form-control',
                'placeholder' => 'Введите город'
                ],
            'options' => [
                'label' => 'Промежуточные пункты',
            ],
        ]);
                       
        $this->add([            
            'type'  => 'text',
            'name' => 'to-points',
            'attributes' => [
                'class'=>'form-control',
                'placeholder' => 'Введите город'
                ],
            'options' => [
                'label' => 'Пункт прибытия',
            ],
        ]);
                       
        $this->add([            
            'type'  => 'text',
            'name' => 'tarifs',
            'attributes' => [
                'class'=>'form-control',
                'placeholder' => 'Назначьте тарифы'
                ],
            'options' => [
                'label' => 'Тарифы',
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
                'name'     => 'name',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'StringTrim'],
                ],                
                'validators' => [
                    [
                        'name'    => \Application\Validator\RussianStringLength::class,
                        'options' => [
                            'min' => 1,
                            'max' => 100
                        ],
                    ],
                ],
            ]);
    
        $inputFilter->add([
                'name'     => 'reis-num',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'StringTrim'],
                ],                
                'validators' => [
                    [
                        'name'    => \Application\Validator\RussianStringLength::class,
                        'options' => [
                            'min' => 1,
                            'max' => 20
                        ],
                    ],
                ],
            ]);
    
        $inputFilter->add([
                'name'     => 'cargo',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'StringTrim'],
                ],                
                'validators' => [
                    [
                        'name'    => \Application\Validator\NotZeroValidator::class,
                    ],
                ],
            ]);
    
        $inputFilter->add([
                'name'     => 'from-points',
                'required' => false,
                'filters'  => [                    
                    ['name' => \Application\Filter\FakeFilter::class],
                ],                
                'validators' => [
                    [
                        'name'    => \Application\Validator\RussianAllwaysOkValidator::class,
                    ],
                ],
            ]);
        $inputFilter->add([
                'name'     => 'to-points',
                'required' => false,
                'filters'  => [                    
                    ['name' => \Application\Filter\FakeFilter::class],
                ],                
                'validators' => [
                    [
                        'name'    => \Application\Validator\RussianAllwaysOkValidator::class,
                    ],
                ],
            ]);
        $inputFilter->add([
                'name'     => 'trace-points',
                'required' => false,
                'filters'  => [                    
                    ['name' => \Application\Filter\FakeFilter::class],
                ],                
                'validators' => [
                    [
                        'name'    => \Application\Validator\RussianAllwaysOkValidator::class,
                    ],
                ],
            ]);
        $inputFilter->add([
                'name'     => 'tarifs',
                'required' => false,
                'filters'  => [                    
                    ['name' => \Application\Filter\FakeFilter::class],
                ],                
                'validators' => [
                    [
                        'name'    => \Application\Validator\RussianAllwaysOkValidator::class,
                    ],
                ],
            ]);
    }
}