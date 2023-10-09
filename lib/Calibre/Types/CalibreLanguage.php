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
 * Class for Calibre language entry.
 */
class CalibreLanguage extends CalibreItem {
	public const URI = 'lang';
	public const CRITERION = CalibreBookCriteria::LANGUAGE;

	/**
	 * SQL statement to extract languages.
	 *
	 * This is a format, with parameter 1 containing WHERE clause.
	 *
	 * @var string
	 */
	private const SQL_LANGUAGES = <<<'EOT'
		select languages.id as id, languages.lang_code as code, count(bll.id) as count
		from languages left join books_languages_link as bll on languages.id = bll.lang_code
		%1$s
		group by languages.id
		order by languages.lang_code
	EOT;

	private function __construct(ICalibreDB $db, array $data) {
		parent::__construct($db, $data);
	}

	protected function mangle(ICalibreDB $db, array $data): array {
		/** @var string $data['code'] */
		$data['name'] = $data['code'];
		return $data;
	}

	/**
	 * Get all available languages.
	 *
	 * @param ICalibreDB $db Calibre database.
	 *
	 * @return Traversable<self> list of language entries.
	 * @throws PDOException on error.
	 */
	public static function getAll(ICalibreDB $db): Traversable {
		return new MapIterator(
			$db->query(sprintf(self::SQL_LANGUAGES, '')),
			fn (array $row) => new self($db, $row)
		);
	}

	/**
	 * Get languages for a book.
	 *
	 * @param ICalibreDB $db Calibre database.
	 * @param mixed $book_id book ID.
	 *
	 * @return Traversable<self> list of language entries.
	 * @throws PDOException on error.
	 */
	public static function getByBook(ICalibreDB $db, $book_id): Traversable {
		return new MapIterator(
			$db->query(sprintf(self::SQL_LANGUAGES, 'where bll.book = ?'), [$book_id]),
			fn (array $row) => new self($db, $row)
		);
	}

	/**
	 * Get language by language ID.
	 *
	 * @param ICalibreDB $db Calibre database.
	 * @param mixed $id language ID.
	 *
	 * @return self|null language entry, or `null` if not found.
	 * @throws PDOException on error.
	 */
	public static function getById(ICalibreDB $db, $id): ?self {
		$data = $db->querySingle(sprintf(self::SQL_LANGUAGES, 'where languages.id = ?'), [$id]);
		return is_null($data) ? null : new self($db, $data);
	}
}
