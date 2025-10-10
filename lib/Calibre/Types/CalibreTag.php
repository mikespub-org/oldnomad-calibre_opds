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
 * Class for Calibre tag entry.
 *
 * @property int $id
 * @property string $name
 * @property int $count
 */
final class CalibreTag extends CalibreItem {
	public const URI = 'tag';
	public const CRITERION = CalibreBookCriteria::TAG;

	/**
	 * SQL statement to extract tags.
	 *
	 * This is a format, with parameter 1 containing WHERE clause.
	 *
	 * @var string
	 */
	private const SQL_TAGS = <<<'EOT'
		select tags.id as id, tags.name as name, count(btl.id) as count
		from tags left join books_tags_link as btl on tags.id = btl.tag
		%1$s
		group by tags.id
		order by tags.name
	EOT;

	private function __construct(ICalibreDB $db, array $data) {
		parent::__construct($db, $data);
	}

	/**
	 * Get all known tags.
	 *
	 * @param ICalibreDB $db Calibre database.
	 *
	 * @return Traversable<self> list of tag entries.
	 * @throws PDOException on error.
	 */
	public static function getAll(ICalibreDB $db): Traversable {
		return new MapAggregate(
			$db->query(sprintf(self::SQL_TAGS, '')),
			fn (array $row) => new self($db, $row)
		);
	}

	/**
	 * Get tags for a book.
	 *
	 * @param ICalibreDB $db Calibre database.
	 * @param mixed $book_id book ID.
	 *
	 * @return Traversable<self> list of tag entries.
	 * @throws PDOException on error.
	 */
	public static function getByBook(ICalibreDB $db, $book_id): Traversable {
		return new MapAggregate(
			$db->query(sprintf(self::SQL_TAGS, 'where btl.book = ?'), [$book_id]),
			fn (array $row) => new self($db, $row)
		);
	}

	/**
	 * Get tag by tag ID.
	 *
	 * @param ICalibreDB $db Calibre database.
	 * @param mixed $id tag ID.
	 *
	 * @return self|null tag entry, or `null` if not found.
	 * @throws PDOException on error.
	 */
	public static function getById(ICalibreDB $db, $id): ?self {
		$data = $db->querySingle(sprintf(self::SQL_TAGS, 'where tags.id = ?'), [$id]);
		return is_null($data) ? null : new self($db, $data);
	}
}
