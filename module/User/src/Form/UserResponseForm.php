<?php
namespace User\Form;

use Zend\Form\Form;
use Zend\Form\Fieldset;

class UserResponseForm extends Form
{
    public function __construct()
    {
        parent::__construct('user-response-form');
        $this->setAttribute('method', 'post');
        $this->addElements();
        $this->addInputFilter();
    }

    protected function addElements()
    {
         $this->add([
            'name' => 'device',
            'type' => 'radio',
            'options' => [
                'value_options' => [
                    [
                        'value' => 'Компьютер',
                        'label' => 'Компьютер',
                        'selected' => true,
                    ],
                    [
                        'value' => 'Ноутбук',
                        'label' => 'Ноутбук',
                        'selected' => false,
                    ],
                    [
                        'value' => 'Смартфон',
                        'label' => 'Смартфон',
                        'selected' => false,
                    ],
                    [
                        'value' => 'Касса',
                        'label' => 'Касса',
                        'selected' => false,
                    ]
                ],
            ],
        ]);

         $this->add([
            'name' => 'mark',
            'type' => 'radio',
            'options' => [
                'value_options' => [
                    [
                        'value' => '1',
                        'label' => '1',
                        'selected' => true,
                    ],
                    [
                        'value' => '2',
                        'label' => '2',
                        'selected' => false,
                    ],
                    [
                        'value' => '3',
                        'label' => '3',
                        'selected' => false,
                    ],
                    [
                        'value' => '4',
                        'label' => '4',
                        'selected' => false,
                    ],
                    [
                        'value' => '5',
                        'label' => '5',
                        'selected' => false,
                    ]
                ],
            ],
        ]);

        $this->add([
            'type'  => 'textarea',
            'name' => 'remark',
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
                ],
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
                'value' => 'Готово',
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
        $inputFilter = $this->getInputFilter();
        $inputFilter->add([
            'name'     => 'remark',
            'required' => false,
            'filters'  => [
                [
                    'name' => 'StringTrim',
                    'name' => 'StripNewlines'
                ],

            ],
        ]);
    }

}