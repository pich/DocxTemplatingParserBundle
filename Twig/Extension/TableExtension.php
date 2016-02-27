<?php
/**
 * TableExtension.php
 *
 * @author Camille Pire <camillepire@northvik.fr>
 * Date: 27/02/2016
 */

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
     * @param $array
     * @return int
     */
    public function docxTable ($array) {
        $table = '</w:t></w:r></w:p><w:tbl><w:tblPr><w:tblStyle w:val="DocxParserTable"/><w:tblW w:w="0" w:type="auto"/><w:tblLook w:val="04A0" w:firstRow="1" w:lastRow="0" w:firstColumn="1" w:lastColumn="0" w:noHBand="0" w:noVBand="1"/></w:tblPr><w:tblGrid><w:gridCol w:w="3132"/><w:gridCol w:w="3132"/><w:gridCol w:w="3132"/></w:tblGrid><w:tr w:rsidR="001672F5" w14:paraId="72861A3B" w14:textId="77777777" w:rsidTr="001672F5"><w:tc><w:tcPr><w:tcW w:w="3132" w:type="dxa"/></w:tcPr><w:p w14:paraId="3140D40F" w14:textId="77777777" w:rsidR="001672F5" w:rsidRDefault="001672F5"><w:r><w:t>Col1</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="3132" w:type="dxa"/></w:tcPr><w:p w14:paraId="1F1410C7" w14:textId="77777777" w:rsidR="001672F5" w:rsidRDefault="001672F5"><w:r><w:t>Col2</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="3132" w:type="dxa"/></w:tcPr><w:p w14:paraId="5FD46727" w14:textId="77777777" w:rsidR="001672F5" w:rsidRDefault="001672F5"><w:r><w:t>Col3</w:t></w:r></w:p></w:tc></w:tr><w:tr w:rsidR="001672F5" w14:paraId="5FC51492" w14:textId="77777777" w:rsidTr="001672F5"><w:tc><w:tcPr><w:tcW w:w="3132" w:type="dxa"/></w:tcPr><w:p w14:paraId="1DA94C30" w14:textId="77777777" w:rsidR="001672F5" w:rsidRDefault="001672F5"><w:r><w:t>L1c1</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="3132" w:type="dxa"/></w:tcPr><w:p w14:paraId="02DC7F92" w14:textId="77777777" w:rsidR="001672F5" w:rsidRDefault="001672F5"><w:r><w:t>L1c2</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="3132" w:type="dxa"/></w:tcPr><w:p w14:paraId="6016A502" w14:textId="77777777" w:rsidR="001672F5" w:rsidRDefault="001672F5"><w:r><w:t>L1c3</w:t></w:r></w:p></w:tc></w:tr><w:tr w:rsidR="001672F5" w14:paraId="5ABA6A91" w14:textId="77777777" w:rsidTr="001672F5"><w:trPr><w:trHeight w:val="256"/></w:trPr><w:tc><w:tcPr><w:tcW w:w="3132" w:type="dxa"/></w:tcPr><w:p w14:paraId="26B30387" w14:textId="77777777" w:rsidR="001672F5" w:rsidRDefault="001672F5"><w:r><w:t>L2c1</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="3132" w:type="dxa"/></w:tcPr><w:p w14:paraId="3C9B0764" w14:textId="77777777" w:rsidR="001672F5" w:rsidRDefault="001672F5"><w:r><w:t>L2c2</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="3132" w:type="dxa"/></w:tcPr><w:p w14:paraId="22776DC2" w14:textId="77777777" w:rsidR="001672F5" w:rsidRDefault="001672F5"><w:r><w:t>L2c3</w:t></w:r></w:p></w:tc></w:tr></w:tbl><w:p w14:paraId="173AA971" w14:textId="77777777" w:rsidR="00215D95" w:rsidRDefault="003679C4"><w:pPr><w:rPr><w:lang w:val="en-US"/></w:rPr></w:pPr><w:r><w:rPr><w:lang w:val="en-US"/></w:rPr><w:t>';

        return $table;
    }

    /**
     * {@inheritdoc}
     */
    public function getName() {
        return 'northvik_docx_templating_parser';
    }
}