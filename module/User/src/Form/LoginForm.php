<?php
namespace User\Form;

use Zend\Form\Form;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilter;

/**
 * This form is used to collect user's login, password and 'Remember Me' flag.
 */
class LoginForm extends Form
{
    /**
     * Constructor.     
     */
    public function __construct()
    {
        // Define form name
        parent::__construct('login-form');
     
        // Set POST method for this form
        $this->setAttribute('method', 'post');
                
        $this->addElements();
        $this->addInputFilter();          
    }
    
    /**
     * This method adds elements to form (input fields and submit button).
     */
    protected function addElements() 
    {
        // Add "email" field
        $this->add([            
            'type'  => 'text',
            'name' => 'login',
            'options' => [
                'label' => 'Ваш Логин или E-mail',
            ],
        ]);
        
        // Add "password" field
        $this->add([            
            'type'  => 'password',
            'name' => 'password',
            'options' => [
                'label' => 'Пароль',
            ],
        ]);
        
        // Add "remember_me" field
        $this->add([            
            'type'  => 'checkbox',
            'name' => 'remember_me',
            'options' => [
                'label' => 'Запомнить меня',
            ],
        ]);
        
        // Add "redirect_url" field
        $this->add([            
            'type'  => 'hidden',
            'name' => 'redirect_url'
        ]);
        
        // Add the CAPTCHA field
        $this->add([
            'type' => 'captcha',
            'name' => 'captcha',
            'options' => [
                'label' => 'Ввод защитного кода:',
                'captcha' => [
                    'class' => 'ReCaptcha',
                    'pubKey' => '6Lfek3IUAAAAAKPLUGjt11mqsFcJUVK-XdshqnO0',
                    'privKey' => '6Lfek3IUAAAAABdXKMiH8Re4Twl-rXvBAgRuDY8J',
                    
//                    'class' => 'Image',
//                    'imgDir' => 'public/img/captcha',
//                    'suffix' => '.png',
//                    'imgUrl' => '/img/captcha/',
//                    'imgAlt' => 'Ввод защитного кода',
//                    'font' => './data/font/thorne_shaded.ttf',
//                    'fsize' => 18,
//                    'width' => 360,
//                    'height' => 60,
//                    'expiration' => 600,
//                    'dotNoiseLevel' => 40,
//                    'lineNoiseLevel' => 3
                    
                ],
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
                'value' => 'Вход',
                'id' => 'submit',
            ],
        ]);
    }
    
    /**
     * This method creates input filter (used for form filtering/validation).
     */
    private function addInputFilter() 
    {
        // Create main input filter
        $inputFilter = new InputFilter();        
        $this->setInputFilter($inputFilter);
                
        // Add input for "email" field
        $inputFilter->add([
                'name'     => 'login',
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim'],                    
                ],                
//                'validators' => [
//                    [
//                        'name'    => \Application\Validator\RussianStringLength::class,
//                        'options' => [
//                            'min' => 1,
//                            'max' => 100
//                        ],
//                    ],
                    
//                    [
//                        'name' => 'EmailAddress',
//                        'options' => [
//                            'allow' => \Zend\Validator\Hostname::ALLOW_DNS,
//                            'useMxCheck' => false,                            
//                        ],
//                    ],
//                ],
            ]);     
        
        // Add input for "password" field
        $inputFilter->add([
                'name'     => 'password',
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
        
        // Add input for "remember_me" field
        $inputFilter->add([
                'name'     => 'remember_me',
                'required' => false,
                'filters'  => [                    
                ],                
                'validators' => [
                    [
                        'name'    => 'InArray',
                        'options' => [
                            'haystack' => [0, 1],
                        ]
                    ],
                ],
            ]);
        
        // Add input for "redirect_url" field
        $inputFilter->add([
                'name'     => 'redirect_url',
                'required' => false,
                'filters'  => [
                    ['name'=>'StringTrim']
                ],                
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 0,
                            'max' => 2048
                        ]
                    ],
                ],
            ]);
    }                
}

