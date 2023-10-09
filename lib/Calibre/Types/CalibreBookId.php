<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: 2023 Alec Kojaev <alec@kojaev.name>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Calibre2OPDS\Calibre\Types;

use OCA\Calibre2OPDS\Calibre\CalibreItem;
use OCA\Calibre2OPDS\Calibre\ICalibreDB;
use OCA\Calibre2OPDS\Util\MapIterator;
use PDOException;
use Traversable;

/**
 * Class for Calibre book identifier entry.
 */
class CalibreBookId extends CalibreItem {
	public const URI = 'book-id';

	/**
	 * SQL statement to extract book identifiers.
	 *
	 * Statement parameter 1 is book id.
	 *
	 * @var string
	 */
	private const SQL_IDENTIFIERS = <<<'EOT'
		select identifiers.type as type, identifiers.val as value
		from identifiers
		where identifiers.book = ?
		order by identifiers.type
	EOT;

	private function __construct(ICalibreDB $db, array $data) {
		parent::__construct($db, $data);
	}

	/**
	 * Get all identifiers for a book.
	 *
	 * @param ICalibreDB $db Calibre database.
	 * @param mixed $book_id book ID.
	 *
	 * @return Traversable<self> list of book identifier entries.
	 * @throws PDOException on error.
	 */
	public static function getByBook(ICalibreDB $db, $book_id): Traversable {
		return new MapIterator(
			$db->query(self::SQL_IDENTIFIERS, [$book_id]),
			fn (array $row) => new self($db, $row)
		);
	}
}
