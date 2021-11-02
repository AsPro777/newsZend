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

class DrvDataForm extends Form implements ObjectManagerAwareInterface 
{
    
    private $entityManager = null;
    private $user = null;

    public function __construct($entityManager = null, $user = null)
    {
        parent::__construct('drv-form');
        $this->setAttribute('method', 'post');

        // Save parameters for internal use.
        $this->entityManager = $entityManager;
        $this->user = $user;
        
        $this->addElements();
       // $this->addInputFilter();          
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
            'type'  => 'select',
            'name' => 'opf',
            'attributes' => [
                'class'=>'form-control',
                ],
            'options' => [
                'label' => 'Организационно правовая форма',
                'value_options' => [
                    1 => 'Юридическое лицо',
                    2 => 'Индивидуальный предприниматель',
                ]
            ],
        ]);

        $this->add([
            'type'  => 'text',
            'name' => 'title',
            'attributes' => [
                'class'=>'form-control', 
            ],
            'options' => [
                'label' => 'Наименование',
            ],
        ]);
        
        $this->add([
            'type'  => 'text',
            'name' => 'director',
            'attributes' => [
                'class'=>'form-control view-only-firm', 
            ],
            'options' => [
                'label' => 'Должность руководителя',
            ],
        ]);
        $this->add([
            'type'  => 'text',
            'name' => 'fio',
            'attributes' => [
                'class'=>'form-control', 
            ],
            'options' => [
                'label' => 'ФИО руководителя',
            ],
        ]);
        
        $this->add([
            'type'  => 'text',
            'name' => 'osnova',
            'attributes' => [
                'class'=>'form-control view-only-firm', 
            ],
            'options' => [
                'label' => 'Действует на основании',
            ],
        ]);
        
        $this->add([
            'type'  => 'text',
            'name' => 'inn',
            'attributes' => [
                'class'=>'form-control', 
            ],
            'options' => [
                'label' => 'ИНН',
            ],
        ]);
        
        $this->add([
            'type'  => 'text',
            'name' => 'ogrn',
            'attributes' => [
                'class'=>'form-control', 
            ],
            'options' => [
                'label' => 'ОГРН(ИП)',
            ],
        ]);
        
        $this->add([
            'type'  => 'text',
            'name' => 'kpp',
            'attributes' => [
                'class'=>'form-control view-only-firm', 
            ],
            'options' => [
                'label' => 'КПП',
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
            ]);     
    }           
}