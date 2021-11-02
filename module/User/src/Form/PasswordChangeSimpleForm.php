<?php
namespace User\Form;

use Zend\Form\Form;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilter;

class PasswordChangeSimpleForm extends Form
{   
    private $scenario;
    public function __construct($scenario)
    {
        parent::__construct('password-change-simple-form');
     
        $this->scenario = $scenario;        
        $this->setAttribute('method', 'post');        
        $this->addElements();
        $this->addInputFilter();          
    }
    
    protected function addElements() 
    {        
        $this->add([            
            'type'  => 'password',
            'name' => 'new_password',
            'options' => [
                'label' => 'Новый пароль',
            ],
        ]);
        
        $this->add([            
            'type'  => 'password',
            'name' => 'confirm_new_password',
            'options' => [
                'label' => 'Повторите новый пароль',
            ],
        ]);
        
        // Add the CSRF field
        $this->add([
            'type' => 'csrf',
            'name' => 'csrf',
            'options' => [
                'csrf_options' => [
                'timeout' => 600
                ]
            ],
        ]);
        
        // Add the Submit button
        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [                
                'value' => 'Изменить пароль'
            ],
        ]);
    }
    
    private function addInputFilter() 
    {
        $inputFilter = new InputFilter();        
        $this->setInputFilter($inputFilter);
                
        $inputFilter->add([
                'name'     => 'new_password',
                'required' => true,
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
                'name'     => 'confirm_new_password',
                'required' => true,
                'filters'  => [                    
                ],                
                'validators' => [
                    [
                        'name'    => 'Identical',
                        'options' => [
                            'token' => 'new_password',                            
                        ],
                    ],
                ],
            ]);
    }
}

