<?php
/**
 * Class for XML parser based on lowest level XML tags and attributes.
 *
 * @author Yaron Koren
 * @author Alexander Mashin
 */

class EDParserXML extends EDParserBase {
	/** @var array $XMLValues Stores XML values between calls. */
	private static $XMLValues = [];
	/** @var string $currentXMLTag Stores current XML tag between calls. */
	private static $currentXMLTag = null;
	/** @var string $currentValue Stores value of the current XML tag between calls. */
	private static $currentValue = null;
	/** @var string $ampersandReplacement A temporary replacement for ampersands, not likely to be met in a real XML. */
	private static $ampersandReplacement = 'THIS IS A LONG STRING USED AS A REPLACEMENT FOR AMPERSANDS 55555555';

	/**
	 * Constructor.
	 *
	 * @param array $params A named array of parameters passed from parser or Lua function.
	 *
	 * @throws EDParserException.
	 *
	 */
	public function __construct( array $params ) {
		parent::__construct( $params );
	}

	/**
	 * Parse the text as XML. Called as $parser( $text ) as syntactic sugar.
	 *
	 * @param string $text The text to be parsed.
	 * @param ?array $defaults The intial values.
	 *
	 * @return array A two-dimensional column-based array of the parsed values.
	 *
	 * @throws EDParserException
	 *
	 */
	public function __invoke( $text, $defaults = [] ) {
		self::$XMLValues = parent::__invoke( $text, $defaults );

		// Remove comments from XML - for some reason, xml_parse()
		// can't handle them.
		$xml = preg_replace( '/<!--.*?-->/s', '', $text );

		// Also, re-insert ampersands, after they were removed to
		// avoid parsing problems.
		$xml = str_replace( '&amp;', self::$ampersandReplacement, $xml );

		$xml_parser = xml_parser_create();
		xml_set_element_handler( $xml_parser, [ __CLASS__, 'startElement' ], [ __CLASS__, 'endElement' ] );
		xml_set_character_data_handler( $xml_parser, [ __CLASS__, 'getContent' ] );
		if ( !xml_parse( $xml_parser, $xml, true ) ) {
			throw new EDParserException( 'externaldata-xml-error',
				xml_error_string( xml_get_error_code( $xml_parser ) ),
				xml_get_current_line_number( $xml_parser )
			);
		}
		xml_parser_free( $xml_parser );
		return self::$XMLValues;
	}

	/**
	 * This method and endElement() below it are both based on code found at
	 * @see http://us.php.net/xml_set_element_handler
	 *
	 * @param resource $parser XML parser created by xml_parser_create();
	 * @param string $name
	 * @param array $attrs
	 *
	 */
	private static function startElement( $parser, $name, array $attrs ) {
		// Set to all lowercase to avoid casing issues.
		self::$currentXMLTag = strtolower( $name );
		self::$currentValue = '';
		foreach ( $attrs as $attr => $value ) {
			$attr = strtolower( $attr );
			$value = str_replace( self::$ampersandReplacement, '&amp;', $value );
			if ( array_key_exists( $attr, self::$XMLValues ) ) {
				self::$XMLValues[$attr][] = $value;
			} else {
				self::$XMLValues[$attr] = [ $value ];
			}
		}
	}

	/**
	 *
	 * @param resource $parser XML parser created by xml_parser_create();
	 * @param string $name
	 *
	 */
	private static function endElement( $parser, $name ) {
		if ( !array_key_exists( self::$currentXMLTag, self::$XMLValues ) ) {
			self::$XMLValues[self::$currentXMLTag] = [];
		}
		self::$XMLValues[self::$currentXMLTag][] = self::$currentValue;
		// Clear the value both here and in startElement(), in case this
		// is an embedded tag.
		self::$currentValue = '';
	}

	/**
	 * Due to the strange way xml_set_character_data_handler() runs,
	 * getContent() may get called multiple times, once for each fragment
	 * of the text, for very long XML values. Given that, we keep a static
	 * attribute self::$currentValue with the current value and append to it.
	 *
	 * @param resource $parser XML parser created by xml_parser_create();
	 * @param string $content A chunk of XML tag unner content.
	 */
	private static function getContent( $parser, $content ) {
		// Replace ampersands, to avoid the XML getting split up
		// around them.
		// Note that this is *escaped* ampersands being replaced -
		// this is unrelated to the fact that bare ampersands aren't
		// allowed in XML.
		$content = str_replace( self::$ampersandReplacement, '&amp;', $content );
		self::$currentValue .= $content;
	}
}
