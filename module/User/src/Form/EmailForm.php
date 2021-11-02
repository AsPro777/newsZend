<?php
namespace User\Form;

use Zend\Form\Form;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Zend\InputFilter\InputFilter;
use User\Validator\UserExistsValidator;

class EmailForm extends Form implements ObjectManagerAwareInterface 
{    
    private $entityManager = null;
    private $user = null;

    public function __construct($entityManager = null, $user = null)
    {
        parent::__construct('fm-EmailForm');
     
        $this->setAttribute('method', 'post');
        $this->setAttribute('action', '../users/change-email');
        
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
    
    /**
     * This method adds elements to form (input fields and submit button).
     */
    protected function addElements() 
    {
        $this->add([            
            'type'  => 'text',
            'name' => 'email',
            'attributes' => [
                'class'=>'form-control', 
                'placeholder'=>'name@example.com'
            ],
            'options' => [
                'label' => 'E-mail',
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
                            'user' => $this->user
                        ],
                    ],                    
                ],
            ]);             
    }           
}