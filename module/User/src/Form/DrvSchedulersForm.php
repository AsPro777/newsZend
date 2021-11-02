<?php
namespace User\Form;

use Zend\Form\Form;
use DoctrineModule\Form\Element\ObjectSelect;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Zend\InputFilter\InputFilter;

class DrvSchedulersForm extends Form implements ObjectManagerAwareInterface 
{
    
    private $entityManager = null;
    private $user = null;
    private $ed_reis = null;
    private $scenario = "create";
       
    public function __construct($entityManager = null, $user = null )
    {
        parent::__construct('drv-schedulers-form');
        $this->setAttribute('method', 'post');

        // Save parameters for internal use.
        $this->entityManager = $entityManager;
        $this->user = $user;
        
        $this->addElements();
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
            'name' => 'interval-days',
            'attributes' => [
                'class'=>'form-control',
                'style'=>'width:70px;',
                'placeholder' => ''
                ],
            'options' => [
                'label' => 'За сколько дней вперед:',
            ],
        ]);
        $this->add([            
            'type'  => 'checkbox',
            'name' => 'manual',
            'attributes' => [
                'class'=>'form-control',
                'style'=>'height:auto;width:auto;',
                'placeholder' => ''
                ],
            'options' => [
                'label' => 'Только вручную:',
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
    
}