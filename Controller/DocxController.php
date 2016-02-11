<?php

namespace northvik\DocxTemplatingParserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class DocxController
 * @package northvik\DocxTemplatingParserBundle\Controller
 * @author Camille Pire <camillepire@northvik.fr>
 *
 * Main class:
 * this class is the entry point of DocxTemplatingParserBundle it use to stock the docx template
 *
 */
class DocxController extends Controller
{

    /**
     * @var string
     */
    private $pathTemplate;

    /**
     * @var array
     */
    private $template;

    /**
     * @var string
     */
    private $tmpName;

    /**
     * @var string
     */
    private $pathTmpDir = '/tmp/DocxTemplatingParserBundle';

    /**
     * @var array
     */
    private $param;

    /**
     * @var array
     */
    private $option;

    /**
     * @var string
     */
    private $logs;

    /**
     * DocxController constructor.
     * @param string $pathTemplate
     * @param array $param = array()
     * @param array $option = array()
     */
    public function __construct($pathTemplate, array $param = array(), array $option = array())
    {
        /*
         * prepare unique value for temporary file and directory
         */
        $date=new \DateTime('now');
        $this->tmpName = $date->format('U');
        if(!is_dir($this->pathTmpDir)){
            mkdir($this->pathTmpDir);
            chown($this->pathTmpDir,'www-data');
        }

        $this->pathTemplate = $pathTemplate;
        $this->param = $param;
        $this->option = $option;
        $this->logs = '';
        $this->addToLogs('Construct');
    }

    /**
     * DocxController destructor.
     */
    function __destruct() {
        rmdir($this->pathTmpDir.'/'.$this->tmpName);
    }

    /**
     * Execute
     *
     * @return string
     * @throws \Exception
     */
    public function execute(){
        $this->checkTemplateFile();
        $this->unzipTemplate();

        echo nl2br($this->logs);
        return '';
    }

    public function unzipTemplate(){
        try{
            copy($this->template['dirname'].'/'.$this->template['basename'], $this->pathTmpDir.'/'.$this->tmpName.'.docx');
            $this->addToLogs('copy at '.$this->pathTmpDir.'/'.$this->tmpName.'.docx');

            $zip = new \ZipArchive;
            if ($zip->open($this->pathTmpDir.'/'.$this->tmpName.'.docx') === TRUE) {
                $zip->extractTo($this->pathTmpDir.'/'.$this->tmpName);
                $zip->close();
                $this->addToLogs('unzip in '.$this->pathTmpDir.'/'.$this->tmpName);
            } else {
                throw new \Exception('Fail to unzip '.$this->template['basename']);
            }
        }
        catch (\Exception $e) {
            $this->addToLogs('unzip : '.$e->getMessage());
        }
    }

    /**
     * CheckTemplateFile
     *
     * @throws \Exception
     */
    private function checkTemplateFile(){
        if(!file_exists($this->pathTemplate)){
            throw new \Exception(sprintf('The file "%s" does not exist', $this->pathTemplate));
        }
        $this->addToLogs('File exist');
        $this->template = pathinfo(realpath($this->pathTemplate));
        if($this->template['extension'] != 'docx'){
            throw new \Exception(sprintf('The file "%s" is not a docx', $this->template['basename']));
        }
        $this->addToLogs('File '.$this->template['basename'].' is a docx');
    }

    /**
     * AddToLogs
     * add log at the right format for debuging this object
     *
     * @param string $log
     */
    private function addToLogs($log){
        $date=new \DateTime('now');
        if(isset($this->template['basename'])){
            $this->logs .= '['.$date->format('d-m-Y h:i:s').'][northvik\DocxTemplatingParserBundle]['.$this->template['basename'].']'.$log."\n";

        }else{
            $this->logs .= '['.$date->format('d-m-Y h:i:s').'][northvik\DocxTemplatingParserBundle]'.$log."\n";
        }
    }

    /**
     * GetLogs
     * get the log vars for this object
     * use to debug
     *
     * @return string
     */
    public function getLogs(){
        return $this->logs;
    }
}
