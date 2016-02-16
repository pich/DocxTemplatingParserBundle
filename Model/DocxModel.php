<?php

namespace northvik\DocxTemplatingParserBundle\Model;


/**
 * Class DocxModel
 * @package northvik\DocxTemplatingParserBundle\Model
 * @author Camille Pire <camillepire@northvik.fr>
 *
 * it use to stock the docx template
 *
 */
class DocxModel
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
    private $param;

    /**
     * @var array
     */
    private $option;

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
     * @var string
     */
    private $logs;

    /**
     * DocxModel constructor.
     * @param $pathTemplateInput
     * @param $pathTemplateOutput
     * @param array $param = array()
     * @param array $option = array()
     * @throws \Exception
     * @internal param string $pathTemplate
     */
    public function __construct($pathTemplateInput, $pathTemplateOutput, array $param = array(), array $option = array())
    {
        if(!is_string($pathTemplateInput)
            || !file_exists($pathTemplateInput)
            || strtolower(pathinfo($pathTemplateInput, PATHINFO_EXTENSION)) != 'docx'){
            throw new \Exception(sprintf('The input path "%s" is not valide', $pathTemplateInput));
        }
        if(!is_string($pathTemplateOutput) || strtolower(pathinfo($pathTemplateOutput, PATHINFO_EXTENSION)) != 'docx'){
            throw new \Exception(sprintf('The output path "%s" is not valide', $pathTemplateOutput));
        }

        /*
         * Prepare unique value for temporary file and directory
         */
        $date = new \DateTime('now');
        $this->tmpName = $date->format('U');
        $this->pathTmpDir = sys_get_temp_dir() . '/DocxTemplatingParserBundle';
        if (!is_dir($this->pathTmpDir)) {
            mkdir($this->pathTmpDir);
        }
        $this->logs = '';
        $this->addToLogs('Construction Start');

        $this->pathTemplateInput = $pathTemplateInput;
        $this->pathTemplateOutput = $pathTemplateOutput;
        $this->param = $param;
        $this->option = $option;
    }

    /**
     * DocxModel destructor.
     */
    function __destruct()
    {
        $this->delTree($this->pathTmpDir . '/' . $this->tmpName);
        unlink($this->pathTmpDir . '/' . $this->tmpName . '.docx');
    }

    /**
     * GetLogs
     * get the log vars for this object
     * use to debug
     *
     * @return string
     */
    public function getLogs()
    {
        return $this->logs;
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
    private function delTree($dir)
    {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->delTree("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }

    /**
     * @return string
     */
    public function getPathTemplateInput()
    {
        return $this->pathTemplateInput;
    }

    /**
     * @param string $pathTemplateInput
     */
    public function setPathTemplateInput($pathTemplateInput)
    {
        $this->pathTemplateInput = $pathTemplateInput;
    }

    /**
     * @return string
     */
    public function getPathTemplateOutput()
    {
        return $this->pathTemplateOutput;
    }

    /**
     * @param string $pathTemplateOutput
     */
    public function setPathTemplateOutput($pathTemplateOutput)
    {
        $this->pathTemplateOutput = $pathTemplateOutput;
    }

    /**
     * @return array
     */
    public function getParam()
    {
        return $this->param;
    }

    /**
     * @param array $param
     */
    public function setParam($param)
    {
        $this->param = $param;
    }

    /**
     * @return array
     */
    public function getOption()
    {
        return $this->option;
    }

    /**
     * @param array $option
     */
    public function setOption($option)
    {
        $this->option = $option;
    }

    /**
     * @return string
     */
    public function getTmpName()
    {
        return $this->tmpName;
    }

    /**
     * @return string
     */
    public function getPathTmpDir()
    {
        return $this->pathTmpDir;
    }

    /**
     * @return string
     */
    public function getXmlContent()
    {
        return $this->xmlContent;
    }

    /**
     * @param string $xmlContent
     */
    public function setXmlContent($xmlContent)
    {
        $this->xmlContent = $xmlContent;
    }

    /**
     * @return array
     */
    public function getTemplateInfo()
    {
        return $this->templateInfo;
    }

    /**
     * @param array $templateInfo
     */
    public function setTemplateInfo($templateInfo)
    {
        $this->templateInfo = $templateInfo;
    }
}
