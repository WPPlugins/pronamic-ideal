<?php

/**
 * Title: XML parser
 * Description:
 * Copyright: Copyright (c) 2005 - 2017
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.0.0
 */
interface Pronamic_WP_Pay_Gateways_Sisow_XML_Parser {
	/**
	 * Parse the specified XML element
	 *
	 * @param SimpleXMLElement $xml
	 */
	public static function parse( SimpleXMLElement $xml );
}
