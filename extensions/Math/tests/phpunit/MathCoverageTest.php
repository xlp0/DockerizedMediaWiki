<?php

/**
 * PHPUnit tests to test the wide range of all typical use cases for formulae at Wikipedia.
 * To generate the page https://www.mediawiki.org/wiki/Extension:Math/CoverageTest is used to
 * generate the test data.
 * The testData is generated by the maintenance script Math/maintenance/MathGenerateTests.php.
 * To update the test data locally with vagrant the following procedure is recommended:
 *
 * 1. copy the source from https://www.mediawiki.org/wiki/Extension:Math/CoverageTest
 * to a new page e.g.
 *    MathTest at your local vagrant instance
 * 2. run <code>php MathGenerateTests.php MathTest</code>
 *	  in the maitenance folder of the Math extension.
 * 3. Test local e.g. via
 *    <code>sudo php /vagrant/mediawiki/tests/phpunit/phpunit.php
 *		/vagrant/mediawiki/extensions/Math/tests/MathCoverageTest.php</code>
 *    (If you don't use sudo you might have problems with the permissions set at vagrant.)
 *
 * @covers \MathRenderer
 *
 * @group Math
 *
 * @license GPL-2.0-or-later
 */
class MathCoverageTest extends MediaWikiTestCase {

	/**
	 * Loops over all test cases provided by the provider function.
	 * Compares each the rendering result of each input with the expected output.
	 * @dataProvider provideCoverage
	 */
	public function testCoverage( $input, $options, $output ) {
		// TODO: Make rendering mode configurable
		// TODO: Provide test-ids
		// TODO: Link to the wikipage that contains the reference rendering
		$this->assertEquals(
			$this->normalize( $output ),
			$this->normalize( MathRenderer::renderMath( $input, $options, 'png' ) ),
			"Failed to render ${input}"
		);
	}

	/**
	 * Gets the test-data from the file ParserTest.json
	 * @return array where $input is the test input string
	 * and $output is the rendered html5-output string
	 */
	public function provideCoverage() {
		$testcases = json_decode( file_get_contents( __DIR__ . '/../ParserTest.json' ), true );
		// uncomment for fast testing
		// $testcases = array_slice($testcases,0,3);
		return $testcases;
	}

	private function normalize( $input ) {
		return preg_replace( '#src="(.*?)/(([a-f]|\d)*)"#', 'src="\2"', $input );
	}
}
