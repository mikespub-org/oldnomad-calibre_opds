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
 * Class for Calibre publisher entry.
 */
class CalibrePublisher extends CalibreItem {
	public const URI = 'publisher';
	public const CRITERION = CalibreBookCriteria::PUBLISHER;

	/**
	 * SQL statement to extract authors.
	 *
	 * This is a format, with parameter 1 containing WHERE clause.
	 *
	 * @var string
	 */
	private const SQL_PUBLISHERS = <<<'EOT'
		select publishers.id as id, publishers.name as name, count(bpl.id) as count
		from publishers left join books_publishers_link as bpl on publishers.id = bpl.publisher
		%1$s
		group by publishers.id
		order by publishers.name
	EOT;

	private function __construct(ICalibreDB $db, array $data) {
		parent::__construct($db, $data);
	}

	/**
	 * Get all publishers.
	 *
	 * @param ICalibreDB $db Calibre database.
	 *
	 * @return Traversable<self> list of publisher entries.
	 * @throws PDOException on error.
	 */
	public static function getAll(ICalibreDB $db): Traversable {
		return new MapAggregate(
			$db->query(sprintf(self::SQL_PUBLISHERS, '')),
			fn (array $row) => new self($db, $row)
		);
	}

	/**
	 * Get publishers for a book.
	 *
	 * @param ICalibreDB $db Calibre database.
	 * @param mixed $book_id book ID.
	 *
	 * @return Traversable<self> list of publisher entries.
	 * @throws PDOException on error.
	 */
	public static function getByBook(ICalibreDB $db, $book_id): Traversable {
		return new MapAggregate(
			$db->query(sprintf(self::SQL_PUBLISHERS, 'where bpl.book = ?'), [$book_id]),
			fn (array $row) => new self($db, $row)
		);
	}

	/**
	 * Get publisher by publisher ID.
	 *
	 * @param ICalibreDB $db Calibre database.
	 * @param mixed $id publisher ID.
	 *
	 * @return self|null publisher entry, or `null` if not found.
	 * @throws PDOException on error.
	 */
	public static function getById(ICalibreDB $db, $id): ?self {
		$data = $db->querySingle(sprintf(self::SQL_PUBLISHERS, 'where publishers.id = ?'), [$id]);
		return is_null($data) ? null : new self($db, $data);
	}
}
