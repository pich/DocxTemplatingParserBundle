<?php
/**
 * DocxParserService.php
 *
 * @author Camille Pire <camillepire@northvik.fr>
 * Date: 27/02/2016
 */

namespace northvik\DocxTemplatingParserBundle\Service;

use northvik\DocxTemplatingParserBundle\Model\DocxModel;
use northvik\DocxTemplatingParserBundle\Twig\Extension\TableExtension;
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
    /**
     * @var DocxModel $docx
     */
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
     * Main methode.
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

        $this->docx->setXmlContent( $this->getTemplate()->render($this->docx->getParam()));
        $this->docx->addToLogs('Parse by twig');
        $this->zipDocx();

        copy($this->docx->getPathTmpDir().'/'.$this->docx->getTmpName().'.docx', $this->docx->getPathTemplateOutput());
        $this->docx->addToLogs('docx move to '.$this->docx->getPathTemplateOutput());
        return $this->docx->getLogs();
    }

    /**
     * GetLogs
     *
     * @return string
     */
    public function getLogs(){
        return $this->docx->getLogs();
    }

    /**
     * GetDocx
     *
     * @return DocxModel
     */
    public function getDocx(){
        return $this->docx;
    }

    /**
     * CleanXml
     *
     * This function is use for isolate the twig of the xml and clean it
     */
    private function cleanXml(){
        $twigVars = array();
        /** First part catch all the twig balise */
        $res = preg_match_all("/({{|{%|{#).*?(}}|%}|#})/", $this->docx->getXmlContent(), $matches);
        if($res!=false && !empty($matches)) {
            $matches = $matches[0];
            foreach ($matches as $match) {
                /** Then get all the xml balise in the twig balise */
                if(preg_match_all("#(</|<).*?(>|/>)#", $match, $result)!=false) {
                    $tags = implode('', $result[0]);
                    $var = $match;
                    foreach ($result[0] as $tag) {
                        $var = str_ireplace($tag, '', $var);
                    }
                    //changing office word quote to readable quote for twig
                    $quote='‘|`|&apos;|’|´|·|᾽|᾿|῀|`|´|῾|&apos;»|»|«|&quot;|῀|῍|῎|῏|῝|“|”';
                    $var = str_ireplace(explode('|',$quote), "'", $var);
                    $twigVars[] = array('match' => $match, 'twig' => $var, 'tag' => $tags);
                }else{
                    //changing office word quote to readable quote for twig
                    $quote='‘|`|&apos;|’|´|·|᾽|᾿|῀|`|´|῾|&apos;»|»|«|&quot;|῀|῍|῎|῏|῝|“|”';
                    $var = str_ireplace(explode('|',$quote), "'", $match);
                    $twigVars[] = array('match' => $match, 'twig' => $var, 'tag' => '');
                }
            }
            $cleanXml = $this->docx->getXmlContent();
            /** Finaly replace twig balise by twig balise without xml inside and with good quote.
             * And put the xml balise find in the twig before the twig */
            foreach ($twigVars as $i => $twigVar) {
                $cleanXml = str_replace($twigVar['match'], $twigVar['tag'] . $twigVar['twig'], $cleanXml);
            }
            $this->docx->setXmlContent($cleanXml);
            $this->docx->addToLogs('xml clean');
        }
    }

    /**
     * ExtractXml
     */
    private function extractXml(){
        $this->docx->setXmlContent( file_get_contents($this->docx->getPathTmpDir().'/'.$this->docx->getTmpName().'/word/document.xml'));
        $this->docx->addToLogs('xml extract');
    }

    /**
     * UnzipTemplate
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
     * zip the docx with all the change an word/document.xml
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

    /**
     * GetTemplate
     * Prepare template for the render with all the twig extension
     *
     * @return \Twig_Template|\Twig_TemplateInterface
     */
    private function getTemplate(){
        $environment = new Twig_Environment(new Twig_Loader_Array(array()));
        $environment->addExtension(new TableExtension());

        return $environment->createTemplate($this->docx->getXmlContent());
    }
}
