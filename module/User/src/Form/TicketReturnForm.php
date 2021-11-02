<?php
namespace User\Form;

use Zend\Form\Form;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilter;

class TicketReturnForm extends Form
{
    public function __construct()
    {
        parent::__construct('ticket-return-form');
        $this->setAttribute('method', 'post');
        $this->addElements();
        $this->addInputFilter();
    }

    protected function addElements()
    {
        $this->add([
            'type'  => 'text',
            'name' => 'id_ticket',
            'options' => [
                'label' => 'Номер билета',
            ],
        ]);

        $this->add([
            'type'  => 'text',
            'name' => 'doc_num',
            'options' => [
                'label' => 'Номер паспорта',
            ],
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
                'value' => 'Найти билет',
                'id' => 'submit',
            ],
        ]);
    }

    private function addInputFilter()
    {
        $inputFilter = new InputFilter();
        $this->setInputFilter($inputFilter);

        $inputFilter->add([
                'name'     => 'id_ticket',
                'required' => true,
                'filters'  => [
                ],
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 1,
                            'max' => 10
                        ],
                    ],
                ],
            ]);

        $inputFilter->add([
                'name'     => 'doc_num',
                'required' => true,
                'filters'  => [
                ],
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 6,
                            'max' => 20
                        ],
                    ],
                ],
            ]);
    }
}

