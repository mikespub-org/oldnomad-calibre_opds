<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: 2023 Alec Kojaev <alec@kojaev.name>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Calibre2OPDS\Calibre\Types;

use OCA\Calibre2OPDS\Calibre\CalibreItem;
use OCA\Calibre2OPDS\Calibre\CalibreSearch;
use OCA\Calibre2OPDS\Calibre\ICalibreDB;
use OCA\Calibre2OPDS\Util\MapIterator;
use OCP\Files\File;
use OCP\Files\FileInfo;
use OCP\Files\Folder;
use PDOException;
use Traversable;

/**
 * Class for Calibre book entry.
 */
class CalibreBook extends CalibreItem {
	public const URI = 'book';

	/**
	 * SQL statement to extract books.
	 *
	 * This is a format, with parameter 1 containing JOIN clauses, parameter 2 containing WHERE clause,
	 * and parameter 3 containing optional SORT columns.
	 *
	 * @var string
	 */
	private const SQL_BOOKS = <<<'EOT'
		select books.id as id, books.title as title, books.timestamp as timestamp, books.pubdate as pubdate,
			books.series_index as series_index, books.uuid as uuid, books.has_cover as has_cover,
			books.last_modified as last_modified, books.path as path,
			comments.text as comment
		from books left join comments on books.id = comments.book %1$s
		%2$s
		order by %3$s books.sort
	EOT;

	private function __construct(ICalibreDB $db, array $data) {
		parent::__construct($db, $data);
	}

	protected function mangle(ICalibreDB $db, array $data): array {
		/** @var int */
		$book_id = $data['id'];
		$data['has_cover'] = boolval($data['has_cover']);
		$data['timestamp'] = self::parseTimestamp($data['timestamp']);
		$data['pubdate'] = self::parseTimestamp($data['pubdate']);
		$data['last_modified'] = self::parseTimestamp($data['last_modified']);
		$data['series_index'] = floatval($data['series_index']);
		$data['authors'] = CalibreAuthor::getByBook($db, $book_id);
		$data['publishers'] = CalibrePublisher::getByBook($db, $book_id);
		$data['languages'] = CalibreLanguage::getByBook($db, $book_id);
		$data['series'] = CalibreSeries::getByBook($db, $book_id);
		$data['tags'] = CalibreTag::getByBook($db, $book_id);
		$data['formats'] = CalibreBookFormat::getByBook($db, $book_id);
		$data['identifiers'] = CalibreBookId::getByBook($db, $book_id);
		return $data;
	}

	/**
	 * Get book cover file.
	 *
	 * @param Folder $root root library folder.
	 *
	 * @return File|null cover file, or `null` if doesn't exist.
	 */
	public function getCoverFile(Folder $root): ?File {
		if (!$this->has_cover) {
			return null;
		}
		/** @var string $this->path */
		$filename = $this->path.'/cover.jpg';
		$data = $root->get($filename);
		if (!$data->isReadable() || $data->getType() !== FileInfo::TYPE_FILE || !($data instanceof File)) {
			return null;
		}
		return $data;
	}

	/**
	 * Get books, optionally filtered by a criterion.
	 *
	 * @param ICalibreDB $db Calibre database.
	 * @param CalibreBookCriteria|null $criterion optional criterion type.
	 * @param string $data optional criterion data.
	 *
	 * @return Traversable<self> list of book entries.
	 * @throws PDOException on error.
	 */
	public static function getByCriterion(ICalibreDB $db, ?CalibreBookCriteria $criterion = null, string $data = ''): Traversable {
		$join = '';
		$where = '';
		$sort = '';
		$terms = null;
		$params = [$data];
		$filter = null;
		switch ($criterion) {
			case CalibreBookCriteria::SEARCH:
				$filter = CalibreSearch::searchBooks($data);
				$params = [];
				break;
			case CalibreBookCriteria::AUTHOR:
				$where = 'where bal.author = ?';
				$join = 'inner join books_authors_link as bal on books.id = bal.book';
				break;
			case CalibreBookCriteria::PUBLISHER:
				$where = 'where bpl.publisher = ?';
				$join = 'inner join books_publishers_link as bpl on books.id = bpl.book';
				break;
			case CalibreBookCriteria::LANGUAGE:
				$where = 'where bll.lang_code = ?';
				$join = 'inner join books_languages_link as bll on books.id = bll.book';
				break;
			case CalibreBookCriteria::SERIES:
				$where = 'where bsl.series = ?';
				$join = 'inner join books_series_link as bsl on books.id = bsl.book';
				$sort = 'books.series_index,';
				break;
			case CalibreBookCriteria::TAG:
				$where = 'where btl.tag = ?';
				$join = 'inner join books_tags_link as btl on books.id = btl.book';
				break;
			default:
				$params = [];
				break;
		}
		return new MapIterator(
			$db->query(sprintf(self::SQL_BOOKS, $join, $where, $sort), $params),
			fn (array $row) => new self($db, $row),
			$filter
		);
	}

	/**
	 * Get book by book ID.
	 *
	 * @param ICalibreDB $db Calibre database.
	 * @param mixed $id book ID.
	 *
	 * @return self|null book entry, or `null` if not found.
	 * @throws PDOException on error.
	 */
	public static function getById(ICalibreDB $db, $id): ?self {
		$data = $db->querySingle(sprintf(self::SQL_BOOKS, '', 'where books.id = ?', ''), [$id]);
		return is_null($data) ? null : new self($db, $data);
	}
}
