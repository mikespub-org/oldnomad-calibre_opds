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
 * Class for Calibre series entry.
 *
 * @property int $id
 * @property string $name
 * @property int $count
 */
final class CalibreSeries extends CalibreItem {
	public const URI = 'series';
	public const CRITERION = CalibreBookCriteria::SERIES;

	/**
	 * SQL statement to extract series.
	 *
	 * This is a format, with parameter 1 containing WHERE clause.
	 *
	 * @var string
	 */
	private const SQL_SERIES = <<<'EOT'
		select series.id as id, series.name as name, count(bsl.id) as count
		from series left join books_series_link as bsl on series.id = bsl.series
		%1$s
		group by series.id
		order by series.sort
	EOT;

	private function __construct(ICalibreDB $db, array $data) {
		parent::__construct($db, $data);
	}

	/**
	 * Get all known series.
	 *
	 * @param ICalibreDB $db Calibre database.
	 *
	 * @return Traversable<self> list of series entries.
	 * @throws PDOException on error.
	 */
	public static function getAll(ICalibreDB $db): Traversable {
		return new MapAggregate(
			$db->query(sprintf(self::SQL_SERIES, '')),
			fn (array $row) => new self($db, $row)
		);
	}

	/**
	 * Get series for a book.
	 *
	 * @param ICalibreDB $db Calibre database.
	 * @param mixed $book_id book ID.
	 *
	 * @return Traversable<self> list of series entries.
	 * @throws PDOException on error.
	 */
	public static function getByBook(ICalibreDB $db, $book_id): Traversable {
		return new MapAggregate(
			$db->query(sprintf(self::SQL_SERIES, 'where bsl.book = ?'), [$book_id]),
			fn (array $row) => new self($db, $row)
		);
	}

	/**
	 * Get series by series ID.
	 *
	 * @param ICalibreDB $db Calibre database.
	 * @param mixed $id series ID.
	 *
	 * @return self|null series entry, or `null` if not found.
	 * @throws PDOException on error.
	 */
	public static function getById(ICalibreDB $db, $id): ?self {
		$data = $db->querySingle(sprintf(self::SQL_SERIES, 'where series.id = ?'), [$id]);
		return is_null($data) ? null : new self($db, $data);
	}
}
