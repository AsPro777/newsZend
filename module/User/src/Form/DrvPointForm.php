<?php
namespace User\Form;

use Zend\Form\Form;
use DoctrineModule\Form\Element\ObjectSelect;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Zend\InputFilter\InputFilter;

use Zend\Form\Element\DateSelect;
use Zend\Validator\Step;

class DrvPointForm extends Form implements ObjectManagerAwareInterface 
{
    
    private $entityManager = null;
    private $user = null;
    private $point = null;
    private $scenario = "create";
       
    public function __construct($scenario = 'create', $entityManager = null, $user = null, $point = null )
    {
        parent::__construct('drv-point-form');
        $this->setAttribute('method', 'post');

        // Save parameters for internal use.
        $this->entityManager = $entityManager;
        $this->user = $user;
        $this->point = $point; // array !!
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
    
    public function getScenario()
    {
        return $this->scenario;
    }     
    
    protected function addElements() 
    {              
        $this->add([
            'type'  => 'text',
            'name' => 'city',
            'attributes' => [
                'class'=>'form-control',
                ],
            'options' => [
                'label' => 'Город'                
            ],
        ]);
        
        $this->add([
            'type'  => 'hidden',
            'name' => 'id_city',
            'options' => [
                'label' => ' '                
            ],
        ]);
        
        $this->add([
            'type'  => 'text',
            'name' => 'name',
            'attributes' => [
                'class'=>'form-control',
                ],
            'options' => [
                'label' => 'Наименование остановки',
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
                'name'     => 'id_city',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
            ]);
        
        $inputFilter->add([
                'name'     => 'city',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'StringTrim'],
                ],                
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 3
                        ],
                    ],
                ],
            ]);
        
        $inputFilter->add([
                'name'     => 'name',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'StringTrim'],
                ],                
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 3,
                            'max' => 50
                        ],
                    ],
                ],
            ]);
        
   }
}