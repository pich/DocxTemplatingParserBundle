<?php

namespace northvik\DocxTemplatingParserBundle\Twig\Extension;

use Symfony\Component\HttpKernel\KernelInterface;

class TableExtension extends \Twig_Extension {
    /**
     * {@inheritdoc}
     */
    public function getFunctions() {
        return array(
            'docxTable' => new \Twig_Function_Method($this, 'docxTable')
        );
    }

    /**
     * @param array $array
     * @return string
     */
    public function docxTable ($array) {

        return "docxTable";
    }

    /**
     * {@inheritdoc}
     */
    public function getName() {
        return 'northvik_docx_templating_parser';
    }
}