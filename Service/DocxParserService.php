<?php

namespace northvik\DocxTemplatingParserBundle\Service;


/**
 * Class DocxParserService
 * @package northvik\DocxTemplatingParserBundle\Service
 * @author Camille Pire <camillepire@northvik.fr>
 *
 * Main class:
 * this class is the entry point of DocxTemplatingParserBundle
 *
 */
class DocxParserService
{

    /**
     * DocxParserService constructor.
     *
     */
    public function __construct()
    {

    }

    /**
     * Execute
     *
     * @return string
     * @throws \Exception
     */
    public function execute(){
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
}
