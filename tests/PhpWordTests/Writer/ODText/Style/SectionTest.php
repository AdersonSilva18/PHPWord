<?php

/**
 * This file is part of PHPWord - A pure PHP library for reading and writing
 * word processing documents.
 *
 * PHPWord is free software distributed under the terms of the GNU Lesser
 * General Public License version 3 as published by the Free Software Foundation.
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code. For the full list of
 * contributors, visit https://github.com/PHPOffice/PHPWord/contributors.
 *
 * @see         https://github.com/PHPOffice/PHPWord
 *
 * @license     http://www.gnu.org/licenses/lgpl.txt LGPL version 3
 */

namespace PhpOffice\PhpWordTests\Writer\ODText\Style;

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWordTests\TestHelperDOCX;

/**
 * Test class for Headers, Footers, Tabs in ODT.
 */
class SectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Executed after each method of the class.
     */
    protected function tearDown(): void
    {
        TestHelperDOCX::clear();
    }

    /**
     * Test various section styles, including header, footer, and tabs.
     */
    public function testHeaderFooterTabs(): void
    {
        $phpWord = new PhpWord();
        $margins = \PhpOffice\PhpWord\Shared\Converter::INCH_TO_TWIP;
        $phpWord->addFontStyle('hdrstyle1', ['name' => 'Courier New', 'size' => 8]);
        $section = $phpWord->addSection(['paperSize' => 'Letter', 'marginTop' => $margins, 'marginBottom' => $margins]);
        $header = $section->addHeader();
        $phpWord->addParagraphStyle('centerheader', ['align' => 'center']);
        $header->addText('Centered Header', 'hdrstyle1', 'centerheader');
        $footer = $section->addFooter();
        $sizew = $section->getStyle()->getPageSizeW();
        $sizel = $section->getStyle()->getMarginLeft();
        $sizer = $section->getStyle()->getMarginRight();
        $footerwidth = $sizew - $sizel - $sizer;
        $phpWord->addParagraphStyle(
            'footerTab',
            [
                'tabs' => [
                    new \PhpOffice\PhpWord\Style\Tab('center', (int) ($footerwidth / 2)),
                    new \PhpOffice\PhpWord\Style\Tab('right', (int) $footerwidth),
                ],
            ]
        );
        $textrun = $footer->addTextRun('footerTab');
        $textrun->addText('Left footer', 'hdrstyle1');
        $textrun->addText("\t", 'hdrstyle1');
        $fld = $textrun->addField('DATE');
        $fld->setFontStyle('hdrstyle1');
        $textrun->addText("\t", 'hdrstyle1');
        $textrun->addText('Page ', 'hdrstyle1');
        $fld = $textrun->addField('PAGE');
        $fld->setFontStyle('hdrstyle1');
        $textrun->addText(' of ', 'hdrstyle1');
        $fld = $textrun->addField('NUMPAGES');
        $fld->setFontStyle('hdrstyle1');
        $section->addText('First page');
        $section->addPageBreak();
        $section->addText('Second page');
        $section->addPageBreak();
        $section->addText('Third page');

        $doc = TestHelperDOCX::getDocument($phpWord, 'ODText');
        $doc->setDefaultFile('styles.xml');
        $s2a = '/office:document-styles/office:automatic-styles';
        $element = "$s2a/style:page-layout/style:page-layout-properties";
        self::assertTrue($doc->elementExists($element));
        self::assertEquals('8.5in', $doc->getElementAttribute($element, 'fo:page-width'));
        self::assertEquals('11in', $doc->getElementAttribute($element, 'fo:page-height'));
        self::assertEquals('0.5in', $doc->getElementAttribute($element, 'fo:margin-top'));
        self::assertEquals('0.5in', $doc->getElementAttribute($element, 'fo:margin-bottom'));

        $s2s = '/office:document-styles/office:styles';
        $element = "$s2s/style:style[1]";
        self::assertTrue($doc->elementExists($element));
        self::assertEquals('hdrstyle1', $doc->getElementAttribute($element, 'style:name'));
        $tprop = "$element/style:text-properties";
        self::assertTrue($doc->elementExists($tprop));
        self::assertEquals('Courier New', $doc->getElementAttribute($tprop, 'style:font-name'));

        $element = "$s2s/style:style[2]";
        self::assertTrue($doc->elementExists($element));
        self::assertEquals('centerheader', $doc->getElementAttribute($element, 'style:name'));
        $tprop = "$element/style:paragraph-properties";
        self::assertTrue($doc->elementExists($tprop));
        self::assertEquals('center', $doc->getElementAttribute($tprop, 'fo:text-align'));

        $element = "$s2s/style:style[3]";
        self::assertTrue($doc->elementExists($element));
        self::assertEquals('footerTab', $doc->getElementAttribute($element, 'style:name'));
        $tprop = "$element/style:paragraph-properties/style:tab-stops";
        self::assertTrue($doc->elementExists($tprop));
        $tstop = "$tprop/style:tab-stop[1]";
        self::assertTrue($doc->elementExists($tstop));
        self::assertEquals('center', $doc->getElementAttribute($tstop, 'style:type'));
        self::assertEquals('3.25in', $doc->getElementAttribute($tstop, 'style:position'));
        $tstop = "$tprop/style:tab-stop[2]";
        self::assertTrue($doc->elementExists($tstop));
        self::assertEquals('right', $doc->getElementAttribute($tstop, 'style:type'));
        self::assertEquals('6.5in', $doc->getElementAttribute($tstop, 'style:position'));

        $s2s = '/office:document-styles/office:master-styles/style:master-page/style:footer/text:p';
        self::assertTrue($doc->elementExists($s2s));
        $element = "$s2s/text:span[1]";
        self::assertTrue($doc->elementExists($element));
        self::assertEquals('hdrstyle1', $doc->getElementAttribute($element, 'text:style-name'));
        self::assertEquals('Left footer', $doc->getElement($element)->nodeValue);
        $element = "$s2s/text:span[2]/text:tab";
        self::assertTrue($doc->elementExists($element));
        $element = "$s2s/text:span[3]/text:date";
        self::assertTrue($doc->elementExists($element));
        $element = "$s2s/text:span[4]/text:tab";
        self::assertTrue($doc->elementExists($element));
        $element = "$s2s/text:span[5]";
        self::assertTrue($doc->elementExists($element));
        self::assertEquals('Page', $doc->getElement($element)->nodeValue);
        self::assertTrue($doc->elementExists("$element/text:s"));
        $element = "$s2s/text:span[6]/text:page-number";
        self::assertTrue($doc->elementExists($element));
        $element = "$s2s/text:span[7]";
        self::assertTrue($doc->elementExists($element));
        self::assertEquals('of', $doc->getElement($element)->nodeValue);
        self::assertTrue($doc->elementExists("$element/text:s"));
        self::assertTrue($doc->elementExists("$element/text:s[2]"));
        $element = "$s2s/text:span[8]/text:page-count";
        self::assertTrue($doc->elementExists($element));
    }

    /**
     * Test HideErrors.
     */
    public function testHideErrors(): void
    {
        $phpWord = new PhpWord();
        $phpWord->getSettings()->setHideGrammaticalErrors(true);
        $phpWord->getSettings()->setHideSpellingErrors(true);
        $phpWord->getSettings()->setThemeFontLang(new \PhpOffice\PhpWord\Style\Language('en-US'));
        $phpWord->getSettings()->getThemeFontLang()->setLangId(\PhpOffice\PhpWord\Style\Language::EN_US_ID);
        $section = $phpWord->addSection();
        $section->addText('Here is a paragraph with some speling errorz');

        $doc = TestHelperDOCX::getDocument($phpWord, 'ODText');
        $doc->setDefaultFile('styles.xml');
        $element = '/office:document-styles/office:styles/style:default-style/style:text-properties';
        self::assertTrue($doc->elementExists($element));
        self::assertEquals('zxx', $doc->getElementAttribute($element, 'fo:language'));
        self::assertEquals('zxx', $doc->getElementAttribute($element, 'style:language-asian'));
        self::assertEquals('zxx', $doc->getElementAttribute($element, 'style:language-complex'));
        self::assertEquals('none', $doc->getElementAttribute($element, 'fo:country'));
        self::assertEquals('none', $doc->getElementAttribute($element, 'style:country-asian'));
        self::assertEquals('none', $doc->getElementAttribute($element, 'style:country-complex'));
    }

    /**
     * Test SpaceBeforeAfter.
     */
    public function testMultipleSections(): void
    {
        $phpWord = new PhpWord();
        $section = $phpWord->addSection(['paperSize' => 'Letter', 'Orientation' => 'portrait']);
        $section->addText('This section uses Letter paper in portrait orientation.');
        $section = $phpWord->addSection(['paperSize' => 'A4', 'Orientation' => 'landscape', 'pageNumberingStart' => '9']);
        $header = $section->addHeader();
        $header->addField('PAGE');
        $section->addText('This section uses A4 paper in landscape orientation. It should have a page break beforehand. It artificially starts on page 9.');

        $doc = TestHelperDOCX::getDocument($phpWord, 'ODText');
        $s2a = '/office:document-content/office:automatic-styles';
        $s2t = '/office:document-content/office:body/office:text';
        self::assertTrue($doc->elementExists($s2a));
        self::assertTrue($doc->elementExists($s2t));

        $element = "$s2a/style:style[2]";
        self::assertTrue($doc->elementExists($element));
        self::assertEquals('SB1', $doc->getElementAttribute($element, 'style:name'));
        self::assertEquals('Standard1', $doc->getElementAttribute($element, 'style:master-page-name'));
        $element .= '/style:text-properties';
        self::assertTrue($doc->elementExists($element));
        self::assertEquals('none', $doc->getElementAttribute($element, 'text:display'));
        $element = "$s2a/style:style[3]";
        self::assertTrue($doc->elementExists($element));
        self::assertEquals('SB2', $doc->getElementAttribute($element, 'style:name'));
        self::assertEquals('Standard2', $doc->getElementAttribute($element, 'style:master-page-name'));
        $elemen2 = "$element/style:paragraph-properties";
        self::assertEquals('9', $doc->getElementAttribute($elemen2, 'style:page-number'));
        $element .= '/style:text-properties';
        self::assertTrue($doc->elementExists($element));
        self::assertEquals('none', $doc->getElementAttribute($element, 'text:display'));

        $element = "$s2t/text:section[1]";
        self::assertTrue($doc->elementExists($element));
        $element .= '/text:p[1]';
        self::assertEquals('SB1', $doc->getElementAttribute($element, 'text:style-name'));
        $element = "$s2t/text:section[2]";
        self::assertTrue($doc->elementExists($element));
        $element .= '/text:p[1]';
        self::assertEquals('SB2', $doc->getElementAttribute($element, 'text:style-name'));

        $doc->setDefaultFile('styles.xml');
        $s2a = '/office:document-styles/office:automatic-styles';
        self::assertTrue($doc->elementExists($s2a));

        $element = "$s2a/style:page-layout[1]";
        self::assertTrue($doc->elementExists($element));
        self::assertEquals('Mpm1', $doc->getElementAttribute($element, 'style:name'));
        $element .= '/style:page-layout-properties';
        self::assertTrue($doc->elementExists($element));
        self::assertEquals('8.5in', $doc->getElementAttribute($element, 'fo:page-width'));
        self::assertEquals('11in', $doc->getElementAttribute($element, 'fo:page-height'));
        self::assertEquals('portrait', $doc->getElementAttribute($element, 'style:print-orientation'));

        $element = "$s2a/style:page-layout[2]";
        self::assertTrue($doc->elementExists($element));
        self::assertEquals('Mpm2', $doc->getElementAttribute($element, 'style:name'));
        $element .= '/style:page-layout-properties';
        self::assertTrue($doc->elementExists($element));
        self::assertEquals('29.7cm', $doc->getElementAttribute($element, 'fo:page-width'));
        self::assertEquals('21cm', $doc->getElementAttribute($element, 'fo:page-height'));
        self::assertEquals('landscape', $doc->getElementAttribute($element, 'style:print-orientation'));

        $s2a = '/office:document-styles/office:master-styles';
        self::assertTrue($doc->elementExists($s2a));
        $element = "$s2a/style:master-page[1]";
        self::assertTrue($doc->elementExists($element));
        self::assertEquals('Standard1', $doc->getElementAttribute($element, 'style:name'));
        self::assertEquals('Mpm1', $doc->getElementAttribute($element, 'style:page-layout-name'));
        $element = "$s2a/style:master-page[2]";
        self::assertTrue($doc->elementExists($element));
        self::assertEquals('Standard2', $doc->getElementAttribute($element, 'style:name'));
        self::assertEquals('Mpm2', $doc->getElementAttribute($element, 'style:page-layout-name'));
    }
}
