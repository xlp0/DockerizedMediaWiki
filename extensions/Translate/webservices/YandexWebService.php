<?php
/**
 * Contains a class for querying external translation service.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

use MediaWiki\MediaWikiServices;

/**
 * Implements support for Yandex translation api v1.
 * @see https://tech.yandex.com/translate/
 * @ingroup TranslationWebService
 * @since 2013-01-01
 */
class YandexWebService extends TranslationWebService {
	public function getType() {
		return 'mt';
	}

	protected function mapCode( $code ) {
		if ( $code === 'be-tarask' ) {
			$code = 'be';
		}
		return $code;
	}

	protected function doPairs() {
		if ( !isset( $this->config['key'] ) ) {
			throw new TranslationWebServiceConfigurationException( 'API key is not set' );
		}

		$pairs = [];

		$params = [
			'key' => $this->config['key'],
		];

		$url = $this->config['pairs'] . '?' . wfArrayToCgi( $params );
		$json = MediaWikiServices::getInstance()->getHttpRequestFactory()->get(
			$url,
			[ 'timeout' => $this->config['timeout'] ],
			__METHOD__
		);
		$response = FormatJson::decode( $json );

		if ( !is_object( $response ) ) {
			$exception = 'Malformed reply from remote server: ' . (string)$json;
			throw new TranslationWebServiceException( $exception );
		}

		foreach ( $response->dirs as $pair ) {
			list( $source, $target ) = explode( '-', $pair );
			$pairs[$source][$target] = true;
		}

		return $pairs;
	}

	protected function getQuery( $text, $from, $to ) {
		if ( !isset( $this->config['key'] ) ) {
			throw new TranslationWebServiceConfigurationException( 'API key is not set' );
		}

		# https://tech.yandex.com/translate/doc/dg/reference/translate-docpage/
		if ( strlen( $text ) > 10000 ) {
			throw new TranslationWebServiceInvalidInputException( 'Source text too long' );
		}

		$text = trim( $text );
		$text = $this->wrapUntranslatable( $text );

		return TranslationQuery::factory( $this->config['url'] )
			->timeout( $this->config['timeout'] )
			->postWithData( wfArrayToCgi(
				[
					'key' => $this->config['key'],
					'text' => $text,
					'lang' => "$from-$to",
					'format' => 'html',
				]
			) );
	}

	protected function parseResponse( TranslationQueryResponse $reply ) {
		$body = $reply->getBody();
		$response = FormatJson::decode( $body );
		if ( !is_object( $response ) ) {
			throw new TranslationWebServiceException( 'Invalid json: ' . serialize( $body ) );
		} elseif ( $response->code !== 200 ) {
			throw new TranslationWebServiceException( $response->message );
		}

		$text = Sanitizer::decodeCharReferences( $response->text[0] );
		$text = $this->unwrapUntranslatable( $text );

		return trim( $text );
	}
}
