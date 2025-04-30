<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: 2023 Alec Kojaev <alec@kojaev.name>
// SPDX-License-Identifier: AGPL-3.0-or-later

use OCA\Calibre2OPDS\Opds\OpenSearchResponse;
use OCP\AppFramework\Http;
use PHPUnit\Framework\TestCase;

class OpdsSearchTest extends TestCase {
	private const SEARCH_TEMPLATE_SHORT_NAME = 'short-name';
	private const SEARCH_TEMPLATE_LONG_NAME = 'long-name';
	private const SEARCH_TEMPLATE_DESCRIPTION = 'short-name';
	private const SEARCH_TEMPLATE_ICON_URL = 'icon-url';
	private const SEARCH_TEMPLATE_TEMPLATE = 'url-template?q={searchTerms}';
	private const SEARCH_TEMPLATE_XML = <<<'EOT'
	<?xml version="1.0" encoding="utf-8"?>
	<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/">
		<ShortName>short-name</ShortName>
		<LongName>long-name</LongName>
		<Description>short-name</Description>
		<Image>icon-url</Image>
		<Url template="url-template?q={searchTerms}" type="application/atom+xml;profile=opds-catalog"/>
	</OpenSearchDescription>
	EOT;
	private const SEARCH_TEMPLATE_XML_MINIMAL = <<<'EOT'
	<?xml version="1.0" encoding="utf-8"?>
	<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/">
		<ShortName>short-name</ShortName>
		<Description>short-name</Description>
		<Url template="url-template?q={searchTerms}" type="application/atom+xml;profile=opds-catalog"/>
	</OpenSearchDescription>
	EOT;

	public function testSearchXmlFull(): void {
		$search = new OpenSearchResponse(
			self::SEARCH_TEMPLATE_SHORT_NAME,
			self::SEARCH_TEMPLATE_LONG_NAME,
			self::SEARCH_TEMPLATE_DESCRIPTION,
			self::SEARCH_TEMPLATE_ICON_URL,
			self::SEARCH_TEMPLATE_TEMPLATE
		);
		$this->assertEquals(Http::STATUS_OK, $search->getStatus(), 'Missing status');
		$xml = $search->render();
		$actual = simplexml_load_string($xml, SimpleXMLElement::class, LIBXML_NOBLANKS);
		$expected = simplexml_load_string(self::SEARCH_TEMPLATE_XML, SimpleXMLElement::class, LIBXML_NOBLANKS);
		$this->assertEquals(dom_import_simplexml($actual), dom_import_simplexml($expected), 'Full search template');
	}

	public function testSearchXmlMinimal(): void {
		$search = new OpenSearchResponse(
			self::SEARCH_TEMPLATE_SHORT_NAME,
			null,
			self::SEARCH_TEMPLATE_DESCRIPTION,
			null,
			self::SEARCH_TEMPLATE_TEMPLATE
		);
		$this->assertEquals(Http::STATUS_OK, $search->getStatus(), 'Missing status');
		$xml = $search->render();
		$actual = simplexml_load_string($xml, SimpleXMLElement::class, LIBXML_NOBLANKS);
		$expected = simplexml_load_string(self::SEARCH_TEMPLATE_XML_MINIMAL, SimpleXMLElement::class, LIBXML_NOBLANKS);
		$this->assertEquals(dom_import_simplexml($actual), dom_import_simplexml($expected), 'Minimal search template');
	}
}
