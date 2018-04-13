<?php
/**
 *
 * @filesource   MarkupTest.php
 * @created      17.12.2016
 * @package      chillerlan\QRCodeTest\Output
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2016 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Output;

use chillerlan\QRCode\Output\QRMarkup;
use chillerlan\QRCode\Output\QRMarkupOptions;
use chillerlan\QRCode\QRCode;

/**
 * Class MarkupTest
 */
class MarkupTest extends OutputTestAbstract{

	protected $outputInterfaceClass = QRMarkup::class;
	protected $outputOptionsClass   = QRMarkupOptions::class;

	public function testOptions(){
		$this->assertEquals(QRCode::OUTPUT_MARKUP_SVG, $this->options->type);
	}

	public function markupDataProvider(){
		return [
			[QRCode::OUTPUT_MARKUP_HTML, true,  'foobar', 'str1.html'],
			[QRCode::OUTPUT_MARKUP_HTML, false, 'foobar', 'str2.html'],
			[QRCode::OUTPUT_MARKUP_HTML, true,  'otpauth://totp/test?secret=B3JX4VCVJDVNXNZ5&issuer=chillerlan.net', 'str3.html'],
			[QRCode::OUTPUT_MARKUP_HTML, false, 'otpauth://totp/test?secret=B3JX4VCVJDVNXNZ5&issuer=chillerlan.net', 'str4.html'],
			[QRCode::OUTPUT_MARKUP_SVG , null,  'foobar', 'str1.svg'],
			[QRCode::OUTPUT_MARKUP_SVG , null,  'otpauth://totp/test?secret=B3JX4VCVJDVNXNZ5&issuer=chillerlan.net', 'str2.svg'],
		];
	}

	/**
	 * @dataProvider markupDataProvider
	 */
	public function testMarkupOutput($type, $omitEndTag, $data, $expected){
		$this->options->type = $type;
		$this->options->htmlOmitEndTag = $omitEndTag;
		$this->options->cssClass = 'test';
		$this->assertEquals(file_get_contents(__DIR__.'/markup/'.$expected), (new QRCode($data, new $this->outputInterfaceClass($this->options)))->output());
	}

	public function markupTestDataProvider(){
		return [
			[QRCode::OUTPUT_MARKUP_SVG],
			[QRCode::OUTPUT_MARKUP_HTML],
		];
	}

	/**
	 * @dataProvider markupTestDataProvider
	 */
	public function testSaveToFile(string $type){
		$this->options->type     = $type;
		$this->options->cssClass = 'foo';

		$data = (new QRCode('foo', new $this->outputInterfaceClass($this->options)))->output();

		$this->options->cachefile = __DIR__.'/markup/save_test.'.$type;

		$this->assertTrue((new QRCode('foo', new $this->outputInterfaceClass($this->options)))->output());

		$this->assertContains($data, file_get_contents($this->options->cachefile));
	}

	/**
	 * @dataProvider markupTestDataProvider
	 *
	 * @expectedException \chillerlan\QRCode\Output\QRCodeOutputException
	 * @expectedExceptionMessage Could not write to cache file
	 */
	public function testSaveToFileException(string $type){
		$this->options->type = $type;
		$this->options->cachefile = __DIR__.'/foo/bar';

		(new QRCode('foo', new $this->outputInterfaceClass($this->options)))->output();
	}

}
