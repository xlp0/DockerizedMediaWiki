<?php

namespace SubPageList\Lister\UI;

use SubPageList\Lister\Page;

/**
 * @since 1.2
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
interface SubPageListRenderer {

	/**
	 * Render a representation of the page and its sub pages.
	 *
	 * This might or might not include the top level page.
	 * This might or might not include additional things
	 * such as headers and footers.
	 *
	 * The $options parameter is an array containing string
	 * keys that are option names. The values are mixed.
	 * The interface does not define which options can be,
	 * or should be, supported by the implementing class.
	 *
	 * @param Page $page
	 * @param array $options
	 *
	 * @return string
	 */
	public function render( Page $page, array $options );

}
