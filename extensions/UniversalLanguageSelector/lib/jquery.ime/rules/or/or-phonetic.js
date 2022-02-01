( function ( $ ) {
	'use strict';

	var orPhonetic = {
		id: 'or-phonetic',
		name: 'ଫୋନେଟିକ',
		description: 'Phonetic keyboard for Odia script',
		date: '2013-02-09',
		URL: 'http://github.com/wikimedia/jquery.ime',
		author: 'Parag Nemade',
		license: 'GPLv3',
		version: '1.0',
		patterns: [
			[ '\\~', 'ଐ' ],
			[ '\\`', ' ୈ' ],
			[ '\\!', 'ଏ' ],
			[ '1', '୧' ],
			[ '\\@', '@' ],
			[ '2', '୨' ],
			[ '\\#', 'ତ୍ର' ],
			[ '3', '୩' ],
			[ '\\$', '$' ],
			[ '4', '୪' ],
			[ '\\%', 'ଞ' ],
			[ '5', '୫' ],
			[ '6', '୬' ],
			[ '7', '୭' ],
			[ '8', '୮' ],
			[ '\\(', '(' ],
			[ '9', '୯' ],
			[ '\\)', ')' ],
			[ '0', '୦' ],
			[ '\\_', '_' ],
			[ '\\-', '-' ],
			[ '\\+', '+' ],
			[ '\\=', '=' ],
			[ 'Q', 'ଔ' ],
			[ 'q', 'ଓ' ],
			[ 'W', 'ଠ' ],
			[ 'w', 'ଟ' ],
			[ 'E', 'ୈ' ],
			[ 'e', 'େ' ],
			[ 'R', 'ୃ ' ],
			[ 'r', 'ର' ],
			[ 'T', 'ଥ' ],
			[ 't', 'ତ' ],
			[ 'Y', 'ୟ' ],
			[ 'y', 'ଯ' ],
			[ 'U', 'ୂ' ],
			[ 'u', 'ୁ' ],
			[ 'I', 'ୀ' ],
			[ 'i', 'ି' ],
			[ 'O', 'ୌ' ],
			[ 'o', 'ୋ' ],
			[ 'P', 'ଫ' ],
			[ 'p', 'ପ' ],
			[ '\\{', 'ଢ' ],
			[ '\\[', 'ଡ' ],
			[ '\\}', 'ର୍' ],
			[ '\\]', 'ଋ' ],
			[ 'A', 'ଆ' ],
			[ 'a', 'ା' ],
			[ 'S', 'ଶ' ],
			[ 's', 'ସ' ],
			[ 'D', 'ଧ' ],
			[ 'd', 'ଦ' ],
			[ 'F', 'ଅ' ],
			[ 'f', '୍' ],
			[ 'G', 'ଘ' ],
			[ 'g', 'ଗ' ],
			[ 'H', 'ଃ' ],
			[ 'h', 'ହ' ],
			[ 'J', 'ଝ' ],
			[ 'j', 'ଜ' ],
			[ 'K', 'ଖ' ],
			[ 'k', 'କ' ],
			[ 'L', 'ଳ' ],
			[ 'l', 'ଲ' ],
			[ ':', 'ଈ' ],
			[ ';', 'ଇ' ],
			[ '"', 'ଊ' ],
			[ '\'', 'ଉ' ],
			[ '\\|', '|' ],
			[ '\\\\', '\\' ],
			[ 'Z', 'ଁ' ],
			[ 'z', 'ଙ' ],
			[ 'x', 'ଷ' ],
			[ 'C', 'ଛ' ],
			[ 'c', 'ଚ' ],
			[ 'V', 'ଵ' ],
			[ 'v', 'ୱ' ],
			[ 'B', 'ଭ' ],
			[ 'b', 'ବ' ],
			[ 'N', 'ଣ' ],
			[ 'n', 'ନ' ],
			[ 'M', 'ଂ' ],
			[ 'm', 'ମ' ],
			[ '\\<', '<' ],
			[ ',', ',' ],
			[ '\\>', '>' ],
			[ '\\.', '।' ],
			[ '\\?', 'ଐ' ],
			[ '/', 'ଏ' ],
			[ '\\^', 'ଜ୍ଞ' ],
			[ 'X', 'କ୍ଷ' ],
			[ '\\*', 'ଶ୍ର' ] ]
	};

	$.ime.register( orPhonetic );
}( jQuery ) );
