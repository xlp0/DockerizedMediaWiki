<?php
/**
 * Class for text parser based on PERL-compatible regular expressions.
 *
 * @author Alexander Mashin
 */

class EDParserRegex extends EDParserBase {
	/** @var string The regular expression. */
	private $regex;

	/**
	 * Constructor.
	 *
	 * @param array $params A named array of parameters passed from parser or Lua function.
	 *
	 * @throws EDParserException
	 *
	 */
	public function __construct( array $params ) {
		parent::__construct( $params );

		// self::claim() has made sure that this parameter is set.
		$regex = $params['regex'];

		// Validate regex.
		if ( method_exists( \Wikimedia\AtEase\AtEase::class, 'suppressWarnings' ) ) {
			// MW >= 1.33
			\Wikimedia\AtEase\AtEase::suppressWarnings();
		} else {
			\MediaWiki\suppressWarnings();
		}
		// Run regular expression against null and compare results with false.
		// @see https://stackoverflow.com/a/12941133.
		if ( preg_match( $regex, null ) !== false ) {
			// A valid regular expression.
			$this->regex = $regex;
		} else {
			// A broken regular expression.
			throw new EDParserException( 'externaldata-invalid-regex', $regex );
		}
		// Restore warnings.
		if ( method_exists( \Wikimedia\AtEase\AtEase::class, 'restoreWarnings' ) ) {
			// MW >= 1.33
			\Wikimedia\AtEase\AtEase::restoreWarnings();
		} else {
			\MediaWiki\restoreWarnings();
		}
	}

	/**
	 * Parse the text. Called as $parser( $text ) as syntactic sugar.
	 *
	 * @param string $text The text to be parsed.
	 * @param ?array $defaults Default values.
	 *
	 * @return array A two-dimensional column-based array of the parsed values.
	 *
	 */
	public function __invoke( $text, $defaults = [] ) {
		$matches = [];
		// The regular expression has been validated in the constructor.
		preg_match_all( $this->regex, $text, $matches, PREG_PATTERN_ORDER );
		return array_merge( $defaults, $matches );
	}
}
