<?php

namespace northvik\DocxTemplatingParserBundle\Service;

use northvik\DocxTemplatingParserBundle\Model\DocxModel;
use Twig_Environment;
use Twig_Loader_Array;

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

    protected $docx;
    /**
     * DocxParserService constructor.
     *
     */
    public function __construct()
    {
    }

    /**
     * Parse
     *
     * @param $pathTemplateInput
     * @param $pathTemplateOutput
     * @param array $param
     * @param array $option
     * @return string
     * @throws \Exception
     */
    public function parse($pathTemplateInput, $pathTemplateOutput, array $param = array(), array $option = array()){
        if(!is_string($pathTemplateInput)
            || !file_exists($pathTemplateInput)
            || strtolower(pathinfo($pathTemplateInput, PATHINFO_EXTENSION)) != 'docx'){
            throw new \Exception(sprintf('The input path "%s" is not valide', $pathTemplateInput));
        }
        if(!is_string($pathTemplateOutput)
            || strtolower(pathinfo($pathTemplateOutput, PATHINFO_EXTENSION)) != 'docx'){
            throw new \Exception(sprintf('The output path "%s" is not valide', $pathTemplateOutput));
        }
        $this->docx = new DocxModel($pathTemplateInput, $pathTemplateOutput, $param , $option);
        
        $this->unzipTemplate();
        $this->extractXml();
        $this->cleanXml();

        $environment = new Twig_Environment(new Twig_Loader_Array(array()));
        $template = $environment->createTemplate($this->docx->getXmlContent());
        $this->docx->setXmlContent( $template->render($this->docx->getParam()));
        $this->docx->addToLogs('Parse by twig');
        $this->zipDocx();

        copy($this->docx->getPathTmpDir().'/'.$this->docx->getTmpName().'.docx', $this->docx->getPathTemplateOutput());
        $this->docx->addToLogs('docx move to '.$this->docx->getPathTemplateOutput());
        return $this->docx->getLogs();
    }

    /**
     * CleanXml
     */
    private function cleanXml(){
        $twigVars = array();
        $res = preg_match_all("/({{|{%|{#).*?(}}|%}|#})/", $this->docx->getXmlContent(), $matches);
        if($res!=false && !empty($matches)) {
            $matches = $matches[0];
            foreach ($matches as $match) {
                if(preg_match_all("#(</|<).*?(>|/>)#", $match, $result)!=false) {
                    $tags = implode('', $result[0]);
                    $var = $match;
                    foreach ($result[0] as $tag) {
                        $var = str_ireplace($tag, '', $var);
                    }
                    //changing office word quote to readable quote for twig
                    $quote='‘|`|&apos;|’|´|·|᾽|᾿|῀|`|´|῾|&apos;»|»|«|&quot;|῀|῍|῎|῏|῝|“|”';
                    $var = str_ireplace(explode('|',$quote), "'", $var); // replace ‘ ` ’ '  by '
                    $twigVars[] = array('match' => $match, 'twig' => $var, 'tag' => $tags);
                }else{
                    //changing office word quote to readable quote for twig
                    $quote='‘|`|&apos;|’|´|·|᾽|᾿|῀|`|´|῾|&apos;»|»|«|&quot;|῀|῍|῎|῏|῝|“|”';
                    $var = str_ireplace(explode('|',$quote), "'", $match);
                    $twigVars[] = array('match' => $match, 'twig' => $var, 'tag' => '');
                }
            }
//var_dump($twigVars);die();
            $cleanXml = $this->docx->getXmlContent();
            foreach ($twigVars as $twigVar) {
                $cleanXml = str_replace($twigVar['match'], $twigVar['tag'] . $twigVar['twig'], $cleanXml);
            }
            $this->docx->setXmlContent($cleanXml);
            $this->docx->addToLogs('xml clean');
        }
    }

    /**
     *
     */
    private function extractXml(){
        $this->docx->setXmlContent( file_get_contents($this->docx->getPathTmpDir().'/'.$this->docx->getTmpName().'/word/document.xml'));
        $this->docx->addToLogs('xml extract');
    }

    /**
     * unzipTemplate
     * unzip the docx in $pathTmpDir = '/tmp/DocxTemplatingParserBundle' by default
     */
    private function unzipTemplate(){
        try{
            $templateInfo = $this->docx->getTemplateInfo();
            copy($this->docx->getPathTemplateInput(), $this->docx->getPathTmpDir().'/'.$this->docx->getTmpName().'.docx');
            $this->docx->addToLogs('copy at '.$this->docx->getPathTmpDir().'/'.$this->docx->getTmpName().'.docx');

            $zip = new \ZipArchive;
            if ($zip->open($this->docx->getPathTmpDir().'/'.$this->docx->getTmpName().'.docx') === TRUE) {
                $zip->extractTo($this->docx->getPathTmpDir().'/'.$this->docx->getTmpName());
                $zip->close();

                $this->docx->addToLogs('unzip in '.$this->docx->getPathTmpDir().'/'.$this->docx->getTmpName());
            } else {
                throw new \Exception('Fail to unzip '.$templateInfo['basename']);
            }
        }
        catch (\Exception $e) {
            $this->docx->addToLogs('unzip : '.$e->getMessage());
        }
    }

    /**
     * zipDocx
     * unzip the docx in $pathTmpDir = '/tmp/DocxTemplatingParserBundle' by default
     */
    private function zipDocx(){
        try{
            $templateInfo = $this->docx->getTemplateInfo();
            $zip = new \ZipArchive;
            if ($zip->open($this->docx->getPathTmpDir().'/'.$this->docx->getTmpName().'.docx') === TRUE) {
                $zip->deleteName('word/document.xml');
                $zip->addFromString('word/document.xml', $this->docx->getXmlContent());
                $zip->close();

                $this->docx->addToLogs('zip in '.$this->docx->getPathTmpDir().'/'.$this->docx->getTmpName().'.docx');
            } else {
                throw new \Exception('Fail to zip '.$templateInfo['basename']);
            }
        }
        catch (\Exception $e) {
            $this->docx->addToLogs('zip : '.$e->getMessage());
        }
    }
}
