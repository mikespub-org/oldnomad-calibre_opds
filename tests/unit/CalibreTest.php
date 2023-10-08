<?php

declare(strict_types=1);

use OCA\Calibre2OPDS\Calibre\CalibreDB;
use OCA\Calibre2OPDS\Calibre\CalibreItem;
use OCA\Calibre2OPDS\Calibre\ICalibreDB;
use OCA\Calibre2OPDS\Calibre\Types\CalibreAuthor;
use OCA\Calibre2OPDS\Calibre\Types\CalibreAuthorPrefix;
use OCA\Calibre2OPDS\Calibre\Types\CalibreBook;
use OCA\Calibre2OPDS\Calibre\Types\CalibreBookCriteria;
use OCA\Calibre2OPDS\Calibre\Types\CalibreBookFormat;
use OCA\Calibre2OPDS\Calibre\Types\CalibreLanguage;
use OCA\Calibre2OPDS\Calibre\Types\CalibrePublisher;
use OCA\Calibre2OPDS\Calibre\Types\CalibreSeries;
use OCA\Calibre2OPDS\Calibre\Types\CalibreTag;
use OCP\Files\File;
use OCP\Files\Folder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Stubs\StorageStub;

class CalibreTest extends TestCase {
	use StorageStub;

	protected ICalibreDB $db;
	protected Folder $root;

	public function setUp(): void {
		$this->initStorage(':memory:', true); // Trick to force an in-memory database
		$this->root = $this->createFolderNode('.', [
			$this->createFileNode('metadata.db'),
			$this->createFolderNode('dummies_cicero', [
				$this->createFileNode('cover.jpg'),
				$this->createFileNode('cicero_for_dummies.epub')
			])
		]);

		/** @var CalibreDB */
		$this->db = CalibreDB::fromFolder($this->root, false);
		$pdo = $this->db->getDatabase();

		$ddl = file_get_contents(__DIR__.'/../files/metadata.sql');
		$pdo->exec($ddl);
		$ddl = file_get_contents(__DIR__.'/../files/test-data.sql');
		$pdo->exec($ddl);
	}

	private function checkDataItem(?array $expected, ?CalibreItem $actual, string $message): void {
		if (is_null($expected)) {
			$this->assertNull($actual, $message.' -- null check');
			return;
		}
		$this->assertNotNull($actual, $message.' -- null check');
		foreach ($expected as $key => $expectedValue) {
			$msg = $message.' -- key '.$key;
			$actualValue = $actual->$key;
			if (is_array($expectedValue)) {
				$this->checkData($expectedValue, $actualValue, $msg);
			} else {
				if (is_string($expectedValue) && str_starts_with($expectedValue, '!!time!!')) {
					$value = substr($expectedValue, 8); // strlen('!!time!!') === 8
					$expectedValue = new DateTimeImmutable($value);
				}
				$this->assertEquals($expectedValue, $actualValue, $msg);
			}
		}
	}

	private function checkData(array $expected, Traversable $actual, string $message): void {
		reset($expected);
		foreach ($actual as $actualItem) {
			$this->assertInstanceOf(CalibreItem::class, $actualItem, $message.' -- wrong type');
			$key = key($expected);
			$expectedItem = current($expected);
			$this->assertFalse($expectedItem === false, $message.' -- result too long');
			$this->checkDataItem($expectedItem, $actualItem, $message.' -- key '.$key);
			next($expected);
		}
		$this->assertTrue(current($expected) === false, $message.' -- result too short');
	}

	public function testAuthorsAll(): void {
		$authors = CalibreAuthor::getByPrefix($this->db);
		$this->checkData([
			[ 'id' => 53, 'name' => 'Emmanuel Goldstein', 'uri' => '', 'count' => 1 ],
			[ 'id' => 54, 'name' => 'Conrad Trachtenberg', 'uri' => '', 'count' => 1 ],
			[ 'id' => 52, 'name' => 'Beth Wildgoose', 'uri' => 'http://example.com/', 'count' => 2 ],
			[ 'id' => 51, 'name' => 'Aaron Zeroth', 'uri' => '', 'count' => 1 ],
		], $authors, 'Authors (all)');
	}

	public function testAuthorsByPrefix(): void {
		$authors = CalibreAuthor::getByPrefix($this->db, 'W');
		$this->checkData([
			[ 'id' => 52 ],
		], $authors, 'Authors by prefix');
	}

	public function testAuthorsByBook(): void {
		$authors = CalibreAuthor::getByBook($this->db, 12);
		$this->checkData([
			[ 'id' => 54 ],
			[ 'id' => 52 ],
		], $authors, 'Authors by book');
	}

	public function testAuthorById(): void {
		$author = CalibreAuthor::getById($this->db, 53);
		$this->checkDataItem([ 'id' => 53 ], $author, 'Author by id');
	}

	public function testAuthorPrefixes(): void {
		$prefixes = CalibreAuthorPrefix::getAll($this->db);
		$this->checkData([
			[ 'prefix' => 'G', 'id' => 'G', 'name' => 'G', 'count' => 1 ],
			[ 'prefix' => 'T', 'id' => 'T', 'name' => 'T', 'count' => 1 ],
			[ 'prefix' => 'W', 'id' => 'W', 'name' => 'W', 'count' => 1 ],
			[ 'prefix' => 'Z', 'id' => 'Z', 'name' => 'Z', 'count' => 1 ],
		], $prefixes, 'Author prefixes');
	}

	public function testLanguagesAll(): void {
		$languages = CalibreLanguage::getAll($this->db);
		$this->checkData([
			[ 'id' => 71, 'code' => 'en', 'count' => 2 ],
			[ 'id' => 74, 'code' => 'enm', 'count' => 0 ],
			[ 'id' => 75, 'code' => 'la', 'count' => 1 ],
			[ 'id' => 72, 'code' => 'ru', 'count' => 0 ],
			[ 'id' => 73, 'code' => 'uk', 'count' => 0 ],
		], $languages, 'Languages (all)');
	}

	public function testLanguagesByBook(): void {
		$languages = CalibreLanguage::getByBook($this->db, 12);
		$this->checkData([
			[ 'code' => 'en' ],
			[ 'code' => 'la' ],
		], $languages, 'Languages by book');
	}

	public function testLanguageById(): void {
		$language = CalibreLanguage::getById($this->db, 75);
		$this->checkDataItem([ 'code' => 'la' ], $language, 'Language by id');
	}

	public function testPublishersAll(): void {
		$publishers = CalibrePublisher::getAll($this->db);
		$this->checkData([
			[ 'id' => 92, 'name' => 'Big Brother Books', 'count' => 1 ],
			[ 'id' => 91, 'name' => 'Megadodo Publications', 'count' => 1 ],
		], $publishers, 'Publishers (all)');
	}

	public function testPublishersByBook(): void {
		$publishers = CalibrePublisher::getByBook($this->db, 13);
		$this->checkData([
			[ 'name' => 'Megadodo Publications' ]
		], $publishers, 'Publishers by book');
	}

	public function testPublisherById(): void {
		$publisher = CalibrePublisher::getById($this->db, 92);
		$this->checkDataItem([ 'name' => 'Big Brother Books' ], $publisher, 'Publisher by id');
	}

	public function testSeriesAll(): void {
		$series = CalibreSeries::getAll($this->db);
		$this->checkData([
			[ 'id' => 111, 'name' => 'Philosophy For Dummies', 'count' => 2 ],
		], $series, 'Series (all)');
	}

	public function testSeriesByBook(): void {
		$series = CalibreSeries::getByBook($this->db, 12);
		$this->checkData([
			[ 'name' => 'Philosophy For Dummies' ],
		], $series, 'Series by book');
	}

	public function testSeriesById(): void {
		$series = CalibreSeries::getById($this->db, 111);
		$this->checkDataItem([ 'name' => 'Philosophy For Dummies' ], $series, 'Series by id');
	}

	public function testTagsAll(): void {
		$tags = CalibreTag::getAll($this->db);
		$this->checkData([
			[ 'id' => 131, 'name' => 'Political theory', 'count' => 2 ],
			[ 'id' => 132, 'name' => 'Translations', 'count' => 1 ],
		], $tags, 'Tags (all)');
	}

	public function testTagsByBook(): void {
		$tags = CalibreTag::getByBook($this->db, 12);
		$this->checkData([
			[ 'id' => 131 ],
			[ 'id' => 132 ],
		], $tags, 'Tags by book');
	}

	public function testTagById(): void {
		$tag = CalibreTag::getById($this->db, 131);
		$this->checkDataItem([ 'name' => 'Political theory' ], $tag, 'Tag by id');
	}

	public function testBookById(): void {
		$book = CalibreBook::getById($this->db, 12);
		$this->checkDataItem([
			'id' => 12,
			'title' => 'Cicero for Dummies',
			'pubdate' => '!!time!!2012-12-12',
			'timestamp' => '!!time!!2022-02-24 04:00',
			'last_modified' => '!!time!!2023-09-30 17:18',
			'path' => 'dummies_cicero',
			'has_cover' => true,
			'comment' => 'Simple explanation of Cicero for imbeciles.',
			'authors' => [
				[ 'name' => 'Conrad Trachtenberg' ],
				[ 'name' => 'Beth Wildgoose' ],
			],
			'publishers' => [],
			'languages' => [
				[ 'code' => 'en' ],
				[ 'code' => 'la' ],
			],
			'series' => [
				[ 'name' => 'Philosophy For Dummies' ],
			],
			'series_index' => 100500.0,
			'tags' => [
				[ 'name' => 'Political theory' ],
				[ 'name' => 'Translations' ],
			],
			'formats' => [
				[ 'format' => 'EPUB', 'name' => 'cicero_for_dummies', 'path' => 'dummies_cicero' ],
			],
			'identifiers' => [
				[ 'type' => 'isbn', 'value' => '978-0140440997' ],
			],
		], $book, 'Book by id');
		$coverFile = $book->getCoverFile($this->root);
		$this->assertInstanceOf(File::class, $coverFile, 'Book cover file -- class');
		$this->assertEquals('/./dummies_cicero/cover.jpg', $coverFile->getInternalPath(), 'Book cover file -- path');
	}

	public function testBookData(): void {
		$format = CalibreBookFormat::getByBookAndType($this->db, 12, 'epub');
		$this->checkDataItem([
			'format' => 'EPUB', 'name' => 'cicero_for_dummies', 'path' => 'dummies_cicero'
		], $format, 'Book data by book and format');
		$dataFile = $format->getDataFile($this->root);
		$this->assertInstanceOf(File::class, $dataFile, 'Book data file -- class');
		$this->assertEquals('/./dummies_cicero/cicero_for_dummies.epub', $dataFile->getInternalPath(), 'Book data file -- path');
	}

	public function testBooksAll(): void {
		$books = CalibreBook::getByCriterion($this->db);
		$this->checkData([
			[
				'id' => 12, 'title' => 'Cicero for Dummies',
				'pubdate' => '!!time!!2012-12-12', 'timestamp' => '!!time!!2022-02-24 04:00', 'last_modified' => '!!time!!2023-09-30 17:18',
				'path' => 'dummies_cicero', 'has_cover' => 1,
				'series_index' => 100500.0,
			],
			[
				'id' => 14, 'title' => 'Plato for Dummies',
				'pubdate' => '!!time!!2011-11-11', 'timestamp' => null, 'last_modified' => '!!time!!2023-09-30 17:18',
				'path' => 'dummies_plato', 'has_cover' => 1,
				'series_index' => 100499.0,
			],
			[
				'id' => 11, 'title' => 'The Theory and Practice of Oligarchical Collectivism',
				'pubdate' => '!!time!!1984-11-07', 'timestamp' => null, 'last_modified' => '!!time!!1949-06-08',
				'path' => 'oligarchical_collectivism', 'has_cover' => 0,
				'series_index' => 1.0,
			],
			[
				'id' => 13, 'title' => 'Whores of Eroticon 6',
				'pubdate' => '!!time!!1978-03-08', 'timestamp' => null, 'last_modified' => '!!time!!2001-05-11 00:00',
				'path' => 'whores_eroticon6', 'has_cover' => 1,
			],
		], $books, 'Books (all)');
	}

	public static function selectDataProvider(): array {
		return [
			[ CalibreBookCriteria::AUTHOR, '51', [
				[ 'id' => 13, 'title' => 'Whores of Eroticon 6' ],
			], 'author' ],
			[ CalibreBookCriteria::PUBLISHER, '92', [
				[ 'id' => 11, 'title' => 'The Theory and Practice of Oligarchical Collectivism' ],
			], 'publisher' ],
			[ CalibreBookCriteria::LANGUAGE, '75', [
				[ 'id' => 12, 'title' => 'Cicero for Dummies' ],
			], 'language' ],
			[ CalibreBookCriteria::SERIES, '111', [
				[ 'id' => 14, 'title' => 'Plato for Dummies' ],
				[ 'id' => 12, 'title' => 'Cicero for Dummies' ],
			], 'series' ],
			[ CalibreBookCriteria::TAG, '131', [
				[ 'id' => 12, 'title' => 'Cicero for Dummies' ],
				[ 'id' => 11, 'title' => 'The Theory and Practice of Oligarchical Collectivism' ],
			], 'tags' ],
		];
	}

	#[DataProvider('selectDataProvider')]
	public function testBooksSelect(CalibreBookCriteria $criterion, string $id, array $expected, string $type): void {
		$books = CalibreBook::getByCriterion($this->db, $criterion, $id);
		$this->checkData($expected, $books, 'Books by '.$type);
	}

	public static function searchDataProvider(): array {
		return [
			[ CalibreBookCriteria::SEARCH, 'whore', [
				[ 'id' => 13 ],
			], 'title' ],
			[ CalibreBookCriteria::SEARCH, 'imbec', [
				[ 'id' => 12 ],
			], 'comment' ],
			[ CalibreBookCriteria::SEARCH, 'zero', [
				[ 'id' => 13 ],
			], 'author' ],
			[ CalibreBookCriteria::SEARCH, 'philosophy', [
				[ 'id' => 12 ],
				[ 'id' => 14 ],
			], 'series' ],
			[ CalibreBookCriteria::SEARCH, 'polit', [
				[ 'id' => 12 ],
				[ 'id' => 11 ],
			], 'tags' ],
		];
	}

	#[DataProvider('searchDataProvider')]
	public function testBooksSearch(CalibreBookCriteria $criterion, string $term, array $expected, string $type): void {
		$books = CalibreBook::getByCriterion($this->db, $criterion, $term);
		$this->checkData($expected, $books, 'Books search ('.$type.')');
	}

	public function testUnknownProperty(): void {
		$author = CalibreAuthor::getById($this->db, 53);
		// NOTE: PHPUnit 10 no longer supports expecting errors.
		set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline): bool {
			restore_error_handler();
			throw new ErrorException($errstr, $errno, E_USER_ERROR, $errfile, $errline);
		}, E_USER_ERROR);
		$this->expectException(ErrorException::class);
		$this->expectExceptionMessageMatches('/Getting unknown property nonexistent_field from object of class .*/');
		$test = $author->nonexistent_field;
	}
}
