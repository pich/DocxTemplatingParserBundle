<?php
/**
 * DocxModel.php
 *
 * @author Camille Pire <camillepire@northvik.fr>
 * Date: 27/02/2016
 */

namespace northvik\DocxTemplatingParserBundle\Model;


/**
 * Class DocxModel
 *
 * This class is a model who stock all the data of the docx.
 * It's contain all the differente path of the file and temporary file.
 * There is also inside some variable as:
 *  - The logs var, who are use for debug
 *  - The twig params to give in argument
 *  - The xml of the docx
 *
 * @package northvik\DocxTemplatingParserBundle\Model
 * @author Camille Pire <camillepire@northvik.fr>
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
     *
     * - In this constructor we prepare the temporary and unique name.
     * - We also initialise the log vars.
     * - And set the vars give in the object attributes.
     *
     * @param $pathTemplateInput
     * @param $pathTemplateOutput
     * @param array $param = array()
     * @param array $option = array()
     * @throws \Exception
     */
    public function __construct($pathTemplateInput, $pathTemplateOutput, array $param = array(), array $option = array())
    {
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
     *
     * - The destructor clean all the file in the php tmp directory
     */
    function __destruct()
    {
        $this->delTree($this->pathTmpDir . '/' . $this->tmpName);
        unlink($this->pathTmpDir . '/' . $this->tmpName . '.docx');
    }

    /**
     * GetLogs
     *
     * - Get the log vars for this object
     * - Use to debug
     *
     * @return string
     */
    public function getLogs()
    {
        return $this->logs;
    }

    /**
     * AddToLogs
     *
     * - Add log at the right format for debuging this object
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
     * DelTree
     *
     * - Use to delete recursively files from a dir
     * - Use in the destructor
     *
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
