<?php
namespace User\Form;

use Zend\Form\Form;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\FileInput;
use Application\Service\ImageManager;
use Application\Filter\ImgToDb;

class DrvDriverScanForm extends Form implements ObjectManagerAwareInterface 
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
            'name' => 'scan_passport',
            'attributes' => [                
                'id' => 'scan_passport',
            ],
            'options' => [
                'label' => 'Паспорт',
            ],
        ]);        
       
       $this->add([
            'type'  => 'file',
            'name' => 'scan_vu',
            'attributes' => [                
                'id' => 'scan_vu',
            ],
            'options' => [
                'label' => 'Водительское удостоверение',
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
                'name'     => 'scan_passport',
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
                            'tag'=>'passport',
                            'id_obj'=>$this->objId,
                            'table_obj'=>"driver",
                            'manager'=>$this->imageManager
                        ]
                    ]
                ],   
            ]);                    
        $inputFilter->add([
                'type'     => FileInput::class,
                'name'     => 'scan_vu',
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
                            'tag'=>'vu',
                            'id_obj'=>$this->objId,
                            'table_obj'=>"driver",
                            'manager'=>$this->imageManager
                        ]
                    ]
                ],   
            ]);                    
    }
}