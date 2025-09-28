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
 * Class for Calibre author prefix.
 *
 * @property string $id
 * @property string $name
 * @property string $prefix
 * @property int $count
 */
final class CalibreAuthorPrefix extends CalibreItem {
	public const URI = 'author-prefix';

	/**
	 * SQL statement to extract author prefixes.
	 *
	 * Statement parameter 1 is prefix length.
	 *
	 * @var string
	 */
	private const SQL_AUTHOR_PREFIXES = <<<'EOT'
		select SUBSTR(sort, 1, ?) as prefix, count(*) as count
		from authors
		group by prefix
		order by prefix
	EOT;

	private function __construct(ICalibreDB $db, array $data) {
		parent::__construct($db, $data);
	}

	#[\Override]
	protected function mangle(ICalibreDB $db, array $data): array {
		/** @var string $data['prefix'] */
		$data['id'] = $data['prefix'];
		$data['name'] = $data['prefix'];
		return $data;
	}

	/**
	 * Get all author prefixes of specified length.
	 *
	 * @param ICalibreDB $db Calibre database.
	 * @param int $length optional prefix length, default is 1.
	 *
	 * @return Traversable<self> list of author prefix entries.
	 * @throws PDOException on error.
	 */
	public static function getAll(ICalibreDB $db, int $length = 1): Traversable {
		return new MapAggregate(
			$db->query(self::SQL_AUTHOR_PREFIXES, [$length]),
			fn (array $row) => new self($db, $row)
		);
	}
}
