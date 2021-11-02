<?php
namespace User\Form;

use Zend\Form\Form;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Zend\InputFilter\InputFilter;


class FioForm extends Form implements ObjectManagerAwareInterface 
{    
    private $entityManager = null;
    private $user = null;

    public function __construct($entityManager = null, $user = null)
    {
        parent::__construct('fm-FioForm');
     
        $this->setAttribute('method', 'post');
        
        
        
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
            'name' => 'f',            
            'options' => [
                'label' => 'Фамилия',
            ],
        ]);

        $this->add([            
            'type'  => 'text',
            'name' => 'i',            
            'options' => [
                'label' => 'Имя',
            ],
        ]);

        $this->add([            
            'type'  => 'text',
            'name' => 'o',            
            'options' => [
                'label' => 'Отчество',
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
    }           
}