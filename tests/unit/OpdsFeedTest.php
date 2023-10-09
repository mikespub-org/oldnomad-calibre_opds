<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: 2023 Alec Kojaev <alec@kojaev.name>
// SPDX-License-Identifier: AGPL-3.0-or-later

use OCA\Calibre2OPDS\Calibre\ICalibreDB;
use OCA\Calibre2OPDS\Calibre\Types\CalibreAuthor;
use OCA\Calibre2OPDS\Calibre\Types\CalibreAuthorPrefix;
use OCA\Calibre2OPDS\Calibre\Types\CalibreBook;
use OCA\Calibre2OPDS\Calibre\Types\CalibreBookFormat;
use OCA\Calibre2OPDS\Calibre\Types\CalibreBookId;
use OCA\Calibre2OPDS\Calibre\Types\CalibreLanguage;
use OCA\Calibre2OPDS\Calibre\Types\CalibrePublisher;
use OCA\Calibre2OPDS\Calibre\Types\CalibreSeries;
use OCA\Calibre2OPDS\Calibre\Types\CalibreTag;
use OCA\Calibre2OPDS\FeedBuilder\OpdsFeedBuilder;
use OCA\Calibre2OPDS\Opds\OpdsResponse;
use OCA\Calibre2OPDS\Opds\OpenSearchResponse;
use PHPUnit\Framework\TestCase;
use Stubs\CalibreStub;
use Stubs\L10NStub;
use Stubs\SettingsServiceStub;

class OpdsFeedTest extends TestCase {
	private const SELF_ROUTE = 'self-route';
	private const SELF_PARAMS = [ 'selfParam' => 'selfValue' ];
	private const UP_ROUTE = 'up-route';
	private const UP_PARAMS = [];
	private const FEED_TITLE = 'feed-title';
	private const EXPECTED_LINKS = [
		[ 'start', 'app-route:index?', OpdsResponse::MIME_TYPE_ATOM ],
		[ 'search', 'app-route:search_xml?', OpenSearchResponse::MIME_TYPE_OPENSEARCH ],
		[ 'self', 'app-route:self-route?selfParam=selfValue', OpdsResponse::MIME_TYPE_ATOM ],
		[ 'up', 'app-route:up-route?', OpdsResponse::MIME_TYPE_ATOM ],
	];
	private const EXPECTED_ENTRIES = [
		[
			'sect-id1', 'sect-title1', null, null,
			null,
			null,
			[[ 'subsection', 'app-route:sect-route1?' ]]
		],
		[
			'sect-id2', 'sect-title2', 'sect-summary2', null,
			null,
			null,
			[[ 'subsection', 'app-route:sect-route2?' ]]
		],
		[
			'author-prefix:author-prefix-value', 'author-prefix-value', 'Authors: 123', null,
			null,
			null,
			[[ 'subsection', 'app-route:authors?prefix=author-prefix-value' ]]
		],
		[
			'author:author-id', 'author-name', 'Books: 123', null,
			null,
			null,
			[[ 'subsection', 'app-route:books?criterion=author&id=author-id' ]]
		],
		[
			'publisher:publisher-id', 'publisher-name', 'Books: 123', null,
			null,
			null,
			[[ 'subsection', 'app-route:books?criterion=publisher&id=publisher-id' ]]
		],
		[
			'series:series-id', 'series-name', 'Books: 123', null,
			null,
			null,
			[[ 'subsection', 'app-route:books?criterion=series&id=series-id' ]]
		],
		[
			'tag:tag-id', 'tag-name', 'Books: 123', null,
			null,
			null,
			[[ 'subsection', 'app-route:books?criterion=tag&id=tag-id' ]]
		],
		[
			'lang:lang-id', '@lang-code', 'Books: 123', null,
			null,
			null,
			[[ 'subsection', 'app-route:books?criterion=language&id=lang-id' ]]
		],
		[
			'book:book-id', 'book-title', 'book-comment', null,
			[
				[ 'author-name1', 'author-uri1' ],
				[ 'author-name2', 'author-uri2' ],
			],
			[
				[ 'tag-name1' ],
				[ 'tag-name2' ],
			],
			[
				[ 'http://opds-spec.org/image', 'app-route:book_cover?id=book-id', 'image/jpeg' ],
				[ 'http://opds-spec.org/acquisition', 'app-route:book_data?id=book-id&type=format-type1', 'application/octet-stream' ],
				[ 'http://opds-spec.org/acquisition', 'app-route:book_data?id=book-id&type=format-type2', 'application/octet-stream' ],
			],
			[
				[ 'dc', 'issued', '1980-02-01T00:00:00+00:00' ],
				[ null, 'published', '2023-10-01T01:02:00+00:00' ],
				[ 'dc', 'identifier', 'urn:uuid:book-uuid' ],
				[ 'dc', 'identifier', 'id-uri1' ],
				[ 'dc', 'identifier', 'id-urn2' ],
				[ 'dc', 'identifier', 'id-epubbud3' ],
				[ 'dc', 'identifier', 'urn:id-type4:id-value4' ],
				[ 'dc', 'publisher', 'publisher-name1' ],
				[ 'dc', 'publisher', 'publisher-name2' ],
				[ 'dc', 'language', 'lang-code1' ],
				[ 'dc', 'language', 'lang-code2' ],
				[ 'dc', 'isPartOf', 'app-route:books?criterion=series&id=series-id1' ],
				[ 'dc', 'isPartOf', 'app-route:books?criterion=series&id=series-id2' ],
			],
		],
	];

	use L10NStub;
	use SettingsServiceStub;
	use CalibreStub;

	private ICalibreDB $db;

	public function setUp(): void {
		$this->initL10N();
		$this->initSettingsService();
		$this->db = $this->createStub(ICalibreDB::class);
	}

	public function testOpdsFeed(): void {
		$builder = new OpdsFeedBuilder($this->settings, $this->l,
			self::SELF_ROUTE, self::SELF_PARAMS, self::FEED_TITLE,
			self::UP_ROUTE, self::UP_PARAMS
		);
		$builder->addSubsectionItem('sect-id1', 'sect-route1', 'sect-title1', null);
		$builder->addSubsectionItem('sect-id2', 'sect-route2', 'sect-title2', 'sect-summary2');
		$builder->addNavigationEntry($this->createCalibreItem(CalibreAuthorPrefix::class, $this->db, [
			'prefix' => 'author-prefix-value', 'count' => 123
		]));
		$builder->addNavigationEntry($this->createCalibreItem(CalibreAuthor::class, $this->db, [
			'id' => 'author-id', 'name' => 'author-name', 'uri' => 'author-uri', 'count' => 123
		]));
		$builder->addNavigationEntry($this->createCalibreItem(CalibrePublisher::class, $this->db, [
			'id' => 'publisher-id', 'name' => 'publisher-name', 'count' => 123
		]));
		$builder->addNavigationEntry($this->createCalibreItem(CalibreSeries::class, $this->db, [
			'id' => 'series-id', 'name' => 'series-name', 'count' => 123
		]));
		$builder->addNavigationEntry($this->createCalibreItem(CalibreTag::class, $this->db, [
			'id' => 'tag-id', 'name' => 'tag-name', 'count' => 123
		]));
		$builder->addNavigationEntry($this->createCalibreItem(CalibreLanguage::class, $this->db, [
			'id' => 'lang-id', 'code' => 'lang-code', 'count' => 123
		]));
		$builder->addBookEntry($this->createCalibreItem(CalibreBook::class, $this->db, [
			'id' => 'book-id', 'title' => 'book-title',
			'timestamp' => '2023-10-01 01:02', 'pubdate' => '1980-02-01', 'last_modified' => null,
			'series_index' => 1.0, 'uuid' => 'book-uuid',
			'has_cover' => 1, 'path' => 'book-path',
			'comment' => 'book-comment'
		], [
			'authors' => [
				$this->createCalibreItem(CalibreAuthor::class, $this->db, [
					'id' => 'author-id1', 'name' => 'author-name1', 'uri' => 'author-uri1'
				]),
				$this->createCalibreItem(CalibreAuthor::class, $this->db, [
					'id' => 'author-id2', 'name' => 'author-name2', 'uri' => 'author-uri2'
				]),
			],
			'publishers' => [
				$this->createCalibreItem(CalibrePublisher::class, $this->db, [
					'id' => 'publisher-id1', 'name' => 'publisher-name1'
				]),
				$this->createCalibreItem(CalibrePublisher::class, $this->db, [
					'id' => 'publisher-id2', 'name' => 'publisher-name2'
				]),
			],
			'languages' => [
				$this->createCalibreItem(CalibreLanguage::class, $this->db, [
					'id' => 'lang-id1', 'code' => 'lang-code1'
				]),
				$this->createCalibreItem(CalibreLanguage::class, $this->db, [
					'id' => 'lang-id2', 'code' => 'lang-code2'
				]),
			],
			'series' => [
				$this->createCalibreItem(CalibreSeries::class, $this->db, [
					'id' => 'series-id1', 'name' => 'series-name1'
				]),
				$this->createCalibreItem(CalibreSeries::class, $this->db, [
					'id' => 'series-id2', 'name' => 'series-name2'
				]),
			],
			'tags' => [
				$this->createCalibreItem(CalibreTag::class, $this->db, [
					'id' => 'tag-id1', 'name' => 'tag-name1'
				]),
				$this->createCalibreItem(CalibreTag::class, $this->db, [
					'id' => 'tag-id2', 'name' => 'tag-name2'
				]),
			],
			'formats' => [
				$this->createCalibreItem(CalibreBookFormat::class, $this->db, [
					'path' => 'book-path', 'name' => 'format-name1', 'format' => 'format-type1'
				]),
				$this->createCalibreItem(CalibreBookFormat::class, $this->db, [
					'path' => 'book-path', 'name' => 'format-name2', 'format' => 'format-type2'
				]),
			],
			'identifiers' => [
				$this->createCalibreItem(CalibreBookId::class, $this->db, [
					'type' => 'uri', 'value' => 'id-uri1'
				]),
				$this->createCalibreItem(CalibreBookId::class, $this->db, [
					'type' => 'urn', 'value' => 'id-urn2'
				]),
				$this->createCalibreItem(CalibreBookId::class, $this->db, [
					'type' => 'epubbud', 'value' => 'id-epubbud3'
				]),
				$this->createCalibreItem(CalibreBookId::class, $this->db, [
					'type' => 'id-type4', 'value' => 'id-value4'
				]),
			],
		]));
		$resp = $builder->getResponse();

		$app = $resp->getOpdsApp();
		$this->assertEquals(self::SETTINGS_APP_ID, $app->getAppId(), 'Feed -- app ID');
		$this->assertEquals(self::SETTINGS_APP_NAME, $app->getAppName(), 'Feed -- app name');
		$this->assertEquals(self::SETTINGS_APP_VERSION, $app->getAppVersion(), 'Feed -- app version');
		$this->assertEquals(self::SETTINGS_APP_WEBSITE, $app->getAppWebsite(), 'Feed -- app website');
		$this->assertEquals('self-route:selfParam=selfValue', $resp->getId(), 'Feed -- feed ID');
		$this->assertEquals(self::FEED_TITLE, $resp->getTitle(), 'Feed -- feed title');
		$this->assertEquals('app-img:icon.ico', $resp->getIconURL(), 'Feed -- feed icon');

		$expectedLinks = self::EXPECTED_LINKS;
		foreach ($resp->getLinks() as $key => $link) {
			$this->assertNotEmpty($expectedLinks, 'Feed -- too many links');
			$expected = array_shift($expectedLinks);
			$this->assertEquals($expected[0], $link->getRel(), 'Feed -- link '.$key.' -- rel');
			$this->assertEquals($expected[1], $link->getURL(), 'Feed -- link '.$key.' -- URL');
			$this->assertEquals($expected[2], $link->getMimeType(), 'Feed -- link '.$key.' -- MIME type');
		}
		$this->assertEmpty($expectedLinks, 'Feed -- not enough links');

		$expectedEntries = self::EXPECTED_ENTRIES;
		foreach ($resp->getEntries() as $key => $entry) {
			$this->assertNotEmpty($expectedEntries, 'Feed -- too many entries');
			$expected = array_shift($expectedEntries);
			$this->assertEquals($expected[0], $entry->getId(), 'Feed -- entry '.$key.' -- ID');
			$this->assertEquals($expected[1], $entry->getTitle(), 'Feed -- entry '.$key.' -- title');
			$this->assertEquals($expected[2], $entry->getSummary(), 'Feed -- entry '.$key.' -- summary');

			$expectedUpdated = $expected[3] ?? null;
			if (!is_null($expectedUpdated)) {
				$expectedUpdated = new DateTimeImmutable($expectedUpdated);
			}
			$this->assertEquals($expectedUpdated, $entry->getUpdated(), 'Feed -- entry '.$key.' -- updated');

			$expectedAuthors = $expected[4] ?? [];
			foreach ($entry->getAuthors() as $authorKey => $author) {
				$this->assertNotEmpty($expectedAuthors, 'Feed -- entry '.$key.' -- too many authors');
				$expectedAuthor = array_shift($expectedAuthors);
				$this->assertEquals($expectedAuthor[0], $author->getName(), 'Feed -- entry '.$key.' -- author '.$authorKey.' -- name');
				$this->assertEquals($expectedAuthor[1] ?? null, $author->getURI(), 'Feed -- entry '.$key.' -- author '.$authorKey.' -- URI');
				$this->assertEquals($expectedAuthor[2] ?? null, $author->getEMail(), 'Feed -- entry '.$key.' -- author '.$authorKey.' -- e-mail');
			}
			$this->assertEmpty($expectedAuthors, 'Feed -- entry '.$key.' -- not enough authors');

			$expectedCategories = $expected[5] ?? [];
			foreach ($entry->getCategories() as $catKey => $cat) {
				$this->assertNotEmpty($expectedCategories, 'Feed -- entry '.$key.' -- too many categories');
				$expectedCat = array_shift($expectedCategories);
				$this->assertEquals($expectedCat[0], $cat->getTerm(), 'Feed -- entry '.$key.' -- category '.$catKey.' -- term');
				$this->assertEquals($expectedCat[1] ?? null, $cat->getSchema(), 'Feed -- entry '.$key.' -- category '.$catKey.' -- schema');
				$this->assertEquals($expectedCat[2] ?? null, $cat->getLabel(), 'Feed -- entry '.$key.' -- category '.$catKey.' -- label');
			}
			$this->assertEmpty($expectedCategories, 'Feed -- entry '.$key.' -- not enough categories');

			$expectedLinks = $expected[6] ?? [];
			foreach ($entry->getLinks() as $linkKey => $link) {
				$this->assertNotEmpty($expectedLinks, 'Feed -- entry '.$key.' -- too many links');
				$expectedLink = array_shift($expectedLinks);
				$this->assertEquals($expectedLink[0], $link->getRel(), 'Feed -- entry '.$key.' -- link '.$linkKey.' -- rel');
				$this->assertEquals($expectedLink[1], $link->getURL(), 'Feed -- entry '.$key.' -- link '.$linkKey.' -- URL');
				$this->assertEquals($expectedLink[2] ?? OpdsResponse::MIME_TYPE_ATOM, $link->getMimeType(), 'Feed -- entry '.$key.' -- link '.$linkKey.' -- MIME type');
			}
			$this->assertEmpty($expectedLinks, 'Feed -- entry '.$key.' -- not enough links');

			$expectedAttrs = $expected[7] ?? [];
			foreach ($entry->getAttributes() as $attrKey => $attr) {
				$this->assertNotEmpty($expectedAttrs, 'Feed -- entry '.$key.' -- too many attributes');
				$expectedAttr = array_shift($expectedAttrs);
				$this->assertEquals($expectedAttr[0], $attr->getNs(), 'Feed -- entry '.$key.' -- attr '.$attrKey.' -- NS');
				$this->assertEquals($expectedAttr[1], $attr->getTag(), 'Feed -- entry '.$key.' -- attr '.$attrKey.' -- tag');
				$this->assertEquals($expectedAttr[2] ?? null, $attr->getValueText(), 'Feed -- entry '.$key.' -- attr '.$attrKey.' -- value');
			}
			$this->assertEmpty($expectedAttrs, 'Feed -- entry '.$key.' -- not enough attributes');
		}
		$this->assertEmpty($expectedEntries, 'Feed -- not enough entries');
	}
}
