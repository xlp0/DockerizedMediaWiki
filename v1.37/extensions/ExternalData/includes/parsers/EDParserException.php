<?php
/**
 * Class for exceptions thrown by EDParser* methods and intercepted by EDConnectors* constructors.
 *
 * @author Alexander Mashin
 *
 */

class EDParserException extends Exception {
	/** @var string MW message code. */
	private $msg_code;
	/** @var array Parameters to that message. */
	private $params;

	/**
	 * Constructor.
	 *
	 * @param string $code MW message code.
	 * @param array $params,... Message parameters.
	 */
	public function __construct( $code, ...$params ) {
		parent::__construct( wfMessage( $code, $params )->inContentLanguage()->text() );
		$this->msg_code = $code;
		$this->params = $params;
	}

	/**
	 * Return MW message code.
	 *
	 * @return string Message code.
	 */
	public function code() {
		return $this->msg_code;
	}

	/**
	 * Return MW message params.
	 *
	 * @return array Message params.
	 */
	public function params() {
		return $this->params;
	}
}
