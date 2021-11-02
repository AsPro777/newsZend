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
use Application\Validator\RussianDateValidator;

class DrvLicensyForm extends Form implements ObjectManagerAwareInterface 
{
    
    private $entityManager = null;
    private $user = null;

    public function __construct($entityManager = null, $user = null)
    {
        parent::__construct('licensy-form');
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
            'name' => 'lcensynum',
            'attributes' => [
                'class'=>'form-control', 
            ],
            'options' => [
                'label' => 'Номер лицензии',
            ],
        ]);
        
        $this->add([
            'type'  => 'text',
            'name' => 'lcensydtend',
            'attributes' => [
                'class'=>'form-control',
            ],
            'options' => [
                'label' => 'Действ. до',
            ],
        ]);
        
        $this->add([
            'type'  => 'checkbox',
            'name' => 'lcensypermanently',
            'attributes' => [
                'class'=>'form-control',
            ],
            'options' => [
                'label' => 'Бессрочная',
            ],
        ]);
        
        $this->add([
            'type'  => 'text',
            'name' => 'internationalnum',
            'attributes' => [
                'class'=>'form-control',
                ],
            'options' => [
                'label' => '№ удост. допуска к МП',
            ],
        ]);
        $this->add([
            'type'  => 'text',
            'name' => 'internationaldtend',
            'attributes' => [
                'class'=>'form-control',
            ],
            'options' => [
                'label' => 'Действ. до',
            ],
        ]);
        
        $this->add([
            'type'  => 'text',
            'name' => 'ugadnnum',
            'attributes' => [
                'class'=>'form-control',
                ],
            'options' => [
                'label' => '№ уведомления УГАДН',
            ],
        ]);
        $this->add([
            'type'  => 'text',
            'name' => 'ugadndtend',
            'attributes' => [
                'class'=>'form-control',
            ],
            'options' => [
                'label' => 'Действ. до',
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
                
         $inputFilter->add([
                'name'     => 'lcensydtend',
                'required' => false,
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
                'name'     => 'internationaldtend',
                'required' => false,
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
                'name'     => 'ugadndtend',
                'required' => false,
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
                'name'     => 'lcensynum',
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim'],
                ],                
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 5,
                            'max' => 150
                        ],
                    ],
                ],
            ]);                
         
        $inputFilter->add([
                'name'     => 'internationalnum',
                'required' => false,
                'filters'  => [                    
                    ['name' => 'StringTrim'],
                ],                
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 5,
                            'max' => 150
                        ],
                    ],
                ],
            ]);                
         
        $inputFilter->add([
                'name'     => 'ugadnnum',
                'required' => false,
                'filters'  => [                    
                    ['name' => 'StringTrim'],
                ],                
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 5,
                            'max' => 150
                        ],
                    ],
                ],
            ]);                
         
   }           
}