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
     * @var string
     */
    private $xmlContent;

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
        $this->delTree($this->pathTmpDir.'/'.$this->tmpName);
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
        $this->extractXml();
        $this->cleanXml();


        $environment = new \Twig_Environment(new \Twig_Loader_Array(array()));
        $template = $environment->createTemplate($this->xmlContent);


//        $twig = new \Twig_Environment();
//        $template = $twig->createTemplate($this->xmlContent);
        $this->param=array('test'=>'hell yeh');
        $this->xmlContent = $template->render($this->param);

echo $this->xmlContent;
        echo nl2br($this->logs);
        return '';
    }


    private function cleanXml(){
        $cleanXml = '';
        $twigVars = array();

        $res = preg_match_all("/({{|{%|{#).*?(}}|%}|#})/", $this->xmlContent, $matches);
        $matches = $matches[0];
        foreach($matches as $match){
            preg_match_all("#(</|<).*?(>|/>)#", $match, $result);
            $tags = implode('',$result[0]);
            $var = $match;
            foreach($result[0] as $tag){
                $var = str_ireplace($tag,'',$var);
            }
            $twigVars[] = array('match'=>$match,'twig'=>$var, 'tag'=>$tags);
        }
        $cleanXml=$this->xmlContent;
        foreach($twigVars as $twigVar ){
            $cleanXml = str_replace($twigVar['match'],$twigVar['tag'].$twigVar['twig'],$cleanXml);
        }
        $this->xmlContent=$cleanXml;
        $this->addToLogs('xml clean');
    }

    /**
     *
     */
    private function extractXml(){
        $this->xmlContent = file_get_contents($this->pathTmpDir.'/'.$this->tmpName.'/word/document.xml');
        $this->addToLogs('xml extract');
    }

    /**
     * unzipTemplate
     * unzip the docx in $pathTmpDir = '/tmp/DocxTemplatingParserBundle' by default
     */
    private function unzipTemplate(){
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
     * @param string $dir
     * @return bool
     */
    private function delTree($dir) {
        $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->delTree("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
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
