<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: 2023 Alec Kojaev <alec@kojaev.name>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Calibre2OPDS\Calibre\Types;

use OCA\Calibre2OPDS\Calibre\CalibreItem;
use OCA\Calibre2OPDS\Calibre\ICalibreDB;
use OCA\Calibre2OPDS\Util\MapAggregate;
use PDOException;
use Traversable;

/**
 * Class for Calibre author entry.
 *
 * @property int $id
 * @property string $name
 * @property ?string $uri
 * @property string $sort
 * @property int $count
 */
final class CalibreAuthor extends CalibreItem {
	public const URI = 'author';
	public const CRITERION = CalibreBookCriteria::AUTHOR;

	/**
	 * SQL statement to extract authors.
	 *
	 * This is a format, with parameter 1 containing WHERE clause.
	 *
	 * Statement parameter 1 is available in condition as `param`.
	 *
	 * @var string
	 */
	private const SQL_AUTHORS = <<<'EOT'
		select authors.id as id, authors.name as name, authors.link as uri, authors.sort as sort, count(bal.id) as count, ? as param
		from authors left join books_authors_link as bal on authors.id = bal.author
		%1$s
		group by authors.id
		order by authors.sort
	EOT;

	private function __construct(ICalibreDB $db, array $data) {
		parent::__construct($db, $data);
	}

	#[\Override]
	protected function mangle(ICalibreDB $db, array $data): array {
		if ($data['uri'] === '') {
			$data['uri'] = null;
		}
		return $data;
	}

	/**
	 * Get authors, optionally filtered by a prefix applied to sort name.
	 *
	 * @param ICalibreDB $db Calibre database.
	 * @param string $prefix optional prefix.
	 *
	 * @return Traversable<self> list of author entries.
	 * @throws PDOException on error.
	 */
	public static function getByPrefix(ICalibreDB $db, string $prefix = ''): Traversable {
		$where = '';
		if ($prefix !== '') {
			$where = 'where SUBSTR(authors.sort, 1, LENGTH(param)) = param';
		}
		return new MapAggregate(
			$db->query(sprintf(self::SQL_AUTHORS, $where), [$prefix]),
			fn (array $row) => new self($db, $row)
		);
	}

	/**
	 * Get authors for a book.
	 *
	 * @param ICalibreDB $db Calibre database.
	 * @param mixed $book_id book ID.
	 *
	 * @return Traversable<self> list of author entries.
	 * @throws PDOException on error.
	 */
	public static function getByBook(ICalibreDB $db, $book_id): Traversable {
		return new MapAggregate(
			$db->query(sprintf(self::SQL_AUTHORS, 'where bal.book = param'), [$book_id]),
			fn (array $row) => new self($db, $row)
		);
	}

	/**
	 * Get author by author ID.
	 *
	 * @param ICalibreDB $db Calibre database.
	 * @param mixed $id author ID.
	 *
	 * @return self|null author entry, or `null` if not found.
	 * @throws PDOException on error.
	 */
	public static function getById(ICalibreDB $db, $id): ?self {
		$data = $db->querySingle(sprintf(self::SQL_AUTHORS, 'where authors.id = param'), [$id]);
		return is_null($data) ? null : new self($db, $data);
	}
}
