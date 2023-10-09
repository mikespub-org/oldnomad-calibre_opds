<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: 2023 Alec Kojaev <alec@kojaev.name>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Calibre2OPDS\Calibre;

use Traversable;

/**
 * Interface for Calibre database.
 */
interface ICalibreDB {
	/**
	 * Query the database for a set of resulting rows.
	 *
	 * @param string $sql SQL statement to execute.
	 * @param array $parameters parameters for SQL statement.
	 * @return Traversable<array> iterator over results, represented as associative arrays.
	 */
	public function query(string $sql, array $parameters = []): Traversable;

	/**
	 * Query the database for a single (first) resulting rows.
	 *
	 * @param string $sql SQL statement to execute.
	 * @param array $parameters parameters for SQL statement.
	 * @return ?array resulting row, represented as an associative array.
	 */
	public function querySingle(string $sql, array $parameters = []): ?array;
}
