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
     * @var string
     */
    private $pathZip;

    /**
     * @var string
     */
    private $pathParsed;

    /**
     * @var array
     */
    private $param;

    /**
     * @var array
     */
    private $option;

    /**
     * DocxController constructor.
     * @param string $pathTemplate
     * @param array $param = array()
     * @param array $option = array()
     */
    public function __construct($pathTemplate, array $param = array(), array $option = array())
    {
        $this->pathTemplate = $pathTemplate;
        $this->param = $param;
        $this->option = $option;
    }


}
