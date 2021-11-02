<?php
namespace User\Form;

use Zend\Form\Form;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\FileInput;
use Application\Service\ImageManager;
use Application\Filter\ImgToDb;
use Application\Validator\RussianDateValidator;

class DrvOsagoScanForm extends Form implements ObjectManagerAwareInterface 
{
    
    private $entityManager = null;
    private $imageManager = null;
    private $objId = 0;
    private $scenario = "create";

    public function __construct($scenario = 'create', $entityManager = null, $imageManager = null, $objId = 0)
    {
        parent::__construct('drv-driver-scan-form');
        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype', 'multipart/form-data');

        // Save parameters for internal use.
        $this->entityManager = $entityManager;
        $this->imageManager = $imageManager;
        $this->objId = empty($objId)?0:intval($objId);
        $this->scenario = $scenario;

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
            'type'  => 'file',
            'name' => 'scan_osago',
            'attributes' => [                
                'id' => 'scan_osago',
            ],
            'options' => [
                'label' => 'Страховка ОСАГО',
            ],
        ]);        
       
       $this->add([
            'type'  => 'text',
            'name' => 'title',
            'attributes' => [                
                'id' => 'title',
                'class'=>'form-control', 
            ],
            'options' => [
                'label' => 'Наименование документа',
            ],
        ]);        
       $this->add([
            'type'  => 'text',
            'name' => 'valid_to',
            'attributes' => [                
                'id' => 'valid_to',
                'class'=>'form-control', 
            ],
            'options' => [
                'label' => 'Действительно до',
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
                'value' => 'Загрузить',
            ],
        ]);
    }
    
     private function addInputFilter() 
    {
        $inputFilter = new InputFilter();        
        $this->setInputFilter($inputFilter);

        $inputFilter->add([
                'type'     => FileInput::class,
                'name'     => 'scan_osago',
                'required' => false,   
                'validators' => [
                    ['name'    => 'FileUploadFile'],
                    [
                        'name'    => 'FileMimeType',                        
                        'options' => [                            
                            'mimeType'  => ['image/jpeg', 'image/png']
                        ]
                    ],
                    ['name'    => 'FileIsImage'],
                    [
                        'name'    => 'FileImageSize',
                        'options' => [
                            'minWidth'  => 800,
                            'minHeight' => 800,
                            'maxWidth'  => 4096,
                            'maxHeight' => 4096
                        ]
                    ],
                ],
                'filters'  => [                    
                    [
                        'name' => ImgToDb::class,
                        'options' => [  
                            'tag'=>'osago',
                            'id_obj'=>$this->objId,
                            'table_obj'=>"bus",
                            'manager'=>$this->imageManager
                        ]
                    ]
                ],   
            ]);
        
        $inputFilter->add([
                'name'     => 'title',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'StringTrim'],
                ],                
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 5,
                            'max' => 255
                        ],
                    ],
                ],
            ]);                
        $inputFilter->add([
                'name'     => 'valid_to',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'StringTrim'],
                ],                
                'validators' => [
                    [
                        'name' => RussianDateValidator::class
                    ],
                ],
            ]);                
        
    }
}