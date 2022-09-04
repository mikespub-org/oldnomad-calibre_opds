<?php

declare(strict_types=1);

use OCA\Calibre2OPDS\Opds\OpdsApp;
use OCA\Calibre2OPDS\Opds\OpdsAttribute;
use OCA\Calibre2OPDS\Opds\OpdsAuthor;
use OCA\Calibre2OPDS\Opds\OpdsCategory;
use OCA\Calibre2OPDS\Opds\OpdsEntry;
use OCA\Calibre2OPDS\Opds\OpdsLink;
use OCA\Calibre2OPDS\Opds\OpdsResponse;
use PHPUnit\Framework\TestCase;

class OpdsTest extends TestCase {
	private const APP_ID = 'app-id';
	private const APP_NAME = 'app-name';
	private const APP_VERSION = '3.1416';
	private const APP_WEBSITE = 'app-website';
	private const RESPONSE_ID = 'response-id';
	private const RESPONSE_TITLE = 'response-title';
	private const RESPONSE_ICON_URL = 'response-icon';
	private const RESPONSE_UPDATED = '2001-09-09 01:46:40+00:00';
	private const LINK_REL = 'link-rel';
	private const LINK_URL = 'link-url';
	private const LINK_TYPE = 'link-type';
	private const ENTRIES = [
		[ 'id1', 'title1' ],
		[ 'id2', 'title2', '<b>summary2</b>' ],
		[ 'id3', 'title3',
			'updated' => '2009-02-13 23:31:30+00:00',
			'authors' => [
				[ 'author3-1' ],
				[ 'author3-2', null, 'author3-2-email' ],
			],
			'categories' => [
				[ 'cat1-term' ],
				[ 'cat2-term', 'cat2-schema' ],
				[ 'cat3-term', null, 'cat3-label' ],
			],
			'attributes' => [
				[ 'ext-tag1' ],
				[ 'ext-tag2', 'ext-value2' ],
				[ 'ext-tag3', 'ext-value3', 'dc' ],
				[ 'ext-tag4', '!2009-02-13 23:31:30+00:00' ],
			],
			'links' => [
				[ 'link1-rel', 'link1-url', 'link1-type' ],
			],
		],
	];
	private const RESPONSE_XML = <<<'EOT'
	<?xml version="1.0" encoding="utf-8"?>
	<feed
		xmlns="http://www.w3.org/2005/Atom"
		xmlns:dc="http://purl.org/dc/terms/"
		xmlns:opds="http://opds-spec.org/2010/catalog">
		<id>opds:response-id</id>
		<link href="link-url" rel="link-rel" type="link-type"/>
		<title>response-title</title>
		<updated>2001-09-09T01:46:40+00:00</updated>
		<author>
			<name>app-name</name>
			<uri>app-website</uri>
		</author>
		<generator uri="app-website" version="3.1416">app-name</generator>
		<icon>response-icon</icon>
			<entry>
			<id>opds:id1</id>
			<title>title1</title>
			<updated>2001-09-09T01:46:40+00:00</updated>
		</entry>
		<entry>
			<id>opds:id2</id>
			<title>title2</title>
			<updated>2001-09-09T01:46:40+00:00</updated>
			<content type="html">&lt;b&gt;summary2&lt;/b&gt;</content>
		</entry>
		<entry>
			<id>opds:id3</id>
			<title>title3</title>
			<updated>2009-02-13T23:31:30+00:00</updated>
			<author>
				<name>author3-1</name>
			</author>
			<author>
				<name>author3-2</name>
				<email>author3-2-email</email>
			</author>
			<category term="cat1-term"/>
			<category term="cat2-term" schema="cat2-schema"/>
			<category term="cat3-term" label="cat3-label"/>
			<ext-tag1/>
			<ext-tag2>ext-value2</ext-tag2>
			<dc:ext-tag3>ext-value3</dc:ext-tag3>
			<ext-tag4>2009-02-13T23:31:30+00:00</ext-tag4>
			<link href="link1-url" rel="link1-rel" type="link1-type"/>
		</entry>
	</feed>
	EOT;

	public function testOpdsResponse(): void {
		$app = new OpdsApp(self::APP_ID, self::APP_NAME, self::APP_VERSION, self::APP_WEBSITE);
		$this->assertEquals(self::APP_ID, $app->getAppId(), 'App ID');
		$opds = new OpdsResponse($app, self::RESPONSE_ID, self::RESPONSE_TITLE, self::RESPONSE_ICON_URL);
		$opds->setUpdated(new DateTimeImmutable(self::RESPONSE_UPDATED));
		$opds->addLink(new OpdsLink(self::LINK_REL, self::LINK_URL, self::LINK_TYPE));
		foreach (self::ENTRIES as $attr) {
			$entry = new OpdsEntry($attr[0], $attr[1], $attr[2] ?? null);
			if (isset($attr['updated'])) {
				$updated = new DateTimeImmutable($attr['updated']);
				$entry->setUpdated($updated);
			}
			foreach ($attr['authors'] ?? [] as $sub) {
				$author = new OpdsAuthor($sub[0], $sub[1] ?? null, $sub[2] ?? null);
				$entry->addAuthor($author);
			}
			foreach ($attr['categories'] ?? [] as $sub) {
				$category = new OpdsCategory($sub[0], $sub[1] ?? null, $sub[2] ?? null);
				$entry->addCategory($category);
			}
			foreach ($attr['attributes'] ?? [] as $sub) {
				$value = $sub[1] ?? null;
				if (!is_null($value) && $value[0] === '!') {
					$value = new DateTimeImmutable(substr($value, 1));
				}
				$attribute = new OpdsAttribute($sub[2] ?? null, $sub[0], $value);
				$entry->addAttribute($attribute);
			}
			foreach ($attr['links'] ?? [] as $sub) {
				$link = new OpdsLink($sub[0], $sub[1], $sub[2]);
				$entry->addLink($link);
			}
			$opds->addEntry($entry);
		}
		$xml = $opds->render();
		$actual = simplexml_load_string($xml, SimpleXMLElement::class, LIBXML_NOBLANKS);
		$expected = simplexml_load_string(self::RESPONSE_XML, SimpleXMLElement::class, LIBXML_NOBLANKS);
		$this->assertEquals(dom_import_simplexml($actual), dom_import_simplexml($expected), 'Full response');
	}
}
