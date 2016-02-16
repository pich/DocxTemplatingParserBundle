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
    private $pathTemplateInput;

    /**
     * @var string
     */
    private $pathTemplateOutput;

    /**
     * @var array
     */
    private $templateInfo;

    /**
     * @var string
     */
    private $tmpName;

    /**
     * @var string
     */
    private $pathTmpDir;

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
     * @param $pathTemplateInput
     * @param $pathTemplateOutput
     * @param array $param = array()
     * @param array $option = array()
     * @internal param string $pathTemplate
     */
    public function __construct($pathTemplateInput, $pathTemplateOutput, array $param = array(), array $option = array())
    {
        /*
         * Prepare unique value for temporary file and directory
         */
        $date=new \DateTime('now');
        $this->tmpName = $date->format('U');
        $this->pathTmpDir= sys_get_temp_dir().'/DocxTemplatingParserBundle';
        if(!is_dir($this->pathTmpDir)){
            mkdir($this->pathTmpDir);
        }

        $this->pathTemplateInput = $pathTemplateInput;
        $this->pathTemplateOutput = $pathTemplateOutput;
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
        unlink($this->pathTmpDir.'/'.$this->tmpName.'.docx');
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
        $this->xmlContent = $template->render($this->param);
        $this->addToLogs('Parse by twig');
        $this->zipDocx();

        copy($this->pathTmpDir.'/'.$this->tmpName.'.docx', $this->pathTemplateOutput);
        $this->addToLogs('docx move to '.$this->pathTemplateOutput);
        return true;
    }

    /**
     *
     */
    private function cleanXml(){
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
            //changing office word quote to readable quote for twig
            $var = str_ireplace(array('‘', '`' , "'", '’'),"'",$var); // replace ‘ ` ’ '  by '
            $var = str_ireplace(array('»', '«', '"'),'"',$var);// replace « » "  by "

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
            copy($this->templateInfo['dirname'].'/'.$this->templateInfo['basename'], $this->pathTmpDir.'/'.$this->tmpName.'.docx');
            $this->addToLogs('copy at '.$this->pathTmpDir.'/'.$this->tmpName.'.docx');

            $zip = new \ZipArchive;
            if ($zip->open($this->pathTmpDir.'/'.$this->tmpName.'.docx') === TRUE) {
                $zip->extractTo($this->pathTmpDir.'/'.$this->tmpName);
                $zip->close();

                $this->addToLogs('unzip in '.$this->pathTmpDir.'/'.$this->tmpName);
            } else {
                throw new \Exception('Fail to unzip '.$this->templateInfo['basename']);
            }
        }
        catch (\Exception $e) {
            $this->addToLogs('unzip : '.$e->getMessage());
        }
    }

    /**
     * zipDocx
     * unzip the docx in $pathTmpDir = '/tmp/DocxTemplatingParserBundle' by default
     */
    private function zipDocx(){
        try{
            $zip = new \ZipArchive;
            if ($zip->open($this->pathTmpDir.'/'.$this->tmpName.'.docx') === TRUE) {
                $zip->deleteName('word/document.xml');
                $zip->addFromString('word/document.xml', $this->xmlContent);
                $zip->close();

                $this->addToLogs('zip in '.$this->pathTmpDir.'/'.$this->tmpName.'.docx');
            } else {
                throw new \Exception('Fail to zip '.$this->templateInfo['basename']);
            }
        }
        catch (\Exception $e) {
            $this->addToLogs('zip : '.$e->getMessage());
        }
    }

    /**
     * CheckTemplateFile
     *
     * @throws \Exception
     */
    private function checkTemplateFile(){
        if(!file_exists($this->pathTemplateInput)){
            throw new \Exception(sprintf('The file "%s" does not exist', $this->pathTemplateInput));
        }
        $this->addToLogs('File exist');
        $this->templateInfo = pathinfo(realpath($this->pathTemplateInput));
        if($this->templateInfo['extension'] != 'docx'){
            throw new \Exception(sprintf('The file "%s" is not a docx', $this->templateInfo['basename']));
        }
        $this->addToLogs('File '.$this->templateInfo['basename'].' is a docx');
    }

    /**
     * AddToLogs
     * add log at the right format for debuging this object
     *
     * @param string $log
     */
    public function addToLogs($log)
    {
        $date = new \DateTime('now');
        $this->logs .= '[' . $date->format('d-m-Y h:i:s') . '][northvik\DocxTemplatingParserBundle]';
        $this->logs .= isset($this->templateInfo['basename']) ? ('[' . $this->templateInfo['basename'] . ']' . $log . "\n") : ($log . "\n");
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
