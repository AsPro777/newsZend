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

class DrvContactForm extends Form implements ObjectManagerAwareInterface 
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
//        $this->addInputFilter();          
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
            'name' => 'uaddress',
            'attributes' => [
                'class'=>'form-control', 
            ],
            'options' => [
                'label' => 'Юридический адрес',
            ],
        ]);

        $this->add([
            'type'  => 'text',
            'name' => 'faddress',
            'attributes' => [
                'class'=>'form-control', 
            ],
            'options' => [
                'label' => 'Фактический адрес',
            ],
        ]);
        
        $this->add([
            'type'  => 'text',
            'name' => 'phone1',
            'attributes' => [
                'class'=>'form-control', 
            ],
            'options' => [
                'label' => 'Телефон основной',
            ],
        ]);
        $this->add([
            'type'  => 'text',
            'name' => 'phone2',
            'attributes' => [
                'class'=>'form-control', 
            ],
            'options' => [
                'label' => 'Телефон резервный',
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