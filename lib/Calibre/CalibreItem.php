<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: 2023 Alec Kojaev <alec@kojaev.name>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Calibre2OPDS\Calibre;

use DateTimeImmutable;
use DateTimeInterface;
use OCA\Calibre2OPDS\Calibre\Types\CalibreBookCriteria;

/**
 * Base class for Calibre metadata items.
 *
 * @property int|string $id -- this and following properties are usually defined in subclasses
 * @property string $name
 * @property int $count
 */
abstract class CalibreItem {
	/**
	 * Identifier to use in URI for this metadata item.
	 *
	 * Subclasses __must__ override this.
	 *
	 * @var string
	 * @psalm-suppress InvalidConstantAssignmentValue -- This is deliberate
	 */
	public const URI = null;
	/**
	 * Criterion that should be applied to books.
	 *
	 * Subclasses __may__ override this.
	 *
	 * @var ?CalibreBookCriteria
	 */
	public const CRITERION = null;

	/**
	 * Value used for empty timestamps.
	 *
	 * This timestamp corresponds to "0101-01-01 00:00:00+00:00".
	 *
	 * @var int
	 */
	private const NULL_TIMESTAMP = -58979923200;

	/**
	 * Row contents.
	 */
	private array $data;

	/**
	 * Construct an instance of metadata from result row.
	 *
	 * @param ICalibreDB $db Calibre database.
	 * @param array $data result row data.
	 */
	protected function __construct(ICalibreDB $db, array $data) {
		$this->data = $this->mangle($db, $data);
	}

	/**
	 * Mangle, if necessary, result row data.
	 *
	 * @param ICalibreDB $db Calibre database.
	 * @param array $data result row data.
	 *
	 * @return array mangled data.
	 */
	protected function mangle(ICalibreDB $db, array $data): array {
		return $data;
	}

	/**
	 * Update item name.
	 *
	 * @param string $name new item name.
	 */
	public function setName(string $name): void {
		$this->data['name'] = $name;
	}

	public function __isset(string $name): bool {
		return array_key_exists($name, $this->data);
	}

	public function __get(string $name): mixed {
		if (array_key_exists($name, $this->data)) {
			return $this->data[$name];
		}
		$trace = debug_backtrace();
		$file = $trace[0]['file'] ?? '???';
		$line = $trace[0]['line'] ?? '???';
		trigger_error(sprintf('Getting unknown property %s from object of class %s in %s on line %d',
			$name, get_class($this), $file, $line), E_USER_ERROR);
		return null;
	}

	/**
	 * Parse database timestamp value.
	 *
	 * @param mixed $value column value.
	 *
	 * @return DateTimeInterface|null timestamp value, or `null` if empty.
	 */
	protected static function parseTimestamp(mixed $value): ?DateTimeInterface {
		if (!is_string($value) || $value === '') {
			return null;
		}
		$timestamp = new DateTimeImmutable($value);
		if ($timestamp->getTimestamp() == self::NULL_TIMESTAMP) {
			return null;
		}
		return $timestamp;
	}
}
