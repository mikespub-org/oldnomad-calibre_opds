<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: 2023 Alec Kojaev <alec@kojaev.name>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Calibre2OPDS\Util;

use Iterator;

/**
 * Caching iterator.
 *
 * Unlike SPL's `CachingIterator`, this one doesn't touch the original iterator after the first pass.
 *
 * @template TKey
 * @template TValue
 * @implements Iterator<TKey,TValue>
 */
final class CachedIterator implements Iterator {
	/**
	 * Inner iterator.
	 *
	 * @var Iterator<TKey,TValue>
	 */
	private Iterator $inner;
	/**
	 * Flag set on initial pass.
	 */
	private bool $initialPass;
	/**
	 * Current element index.
	 */
	private int $index;
	/**
	 * Cached iterator data.
	 *
	 * @var array<list{TKey,TValue}>
	 */
	private array $cache;

	/**
	 * Create an instance.
	 *
	 * @param Iterator<TKey,TValue> $inner inner iterator.
	 */
	public function __construct(Iterator $inner) {
		$this->inner = $inner;
		$this->initialPass = true;
		$this->index = 0;
		$this->cache = [];
	}

	/**
	 * Get entry by index.
	 *
	 * @param int $index entry index.
	 * @return null|list{TKey,TValue} single entry, or null if doesn't exist.
	 */
	private function entry(int $index): ?array {
		if ($index < count($this->cache)) {
			return $this->cache[$index];
		}
		if (!$this->initialPass) {
			return null;
		}
		$last = null;
		for ($i = count($this->cache); $i <= $index; ++$i) {
			if (!$this->inner->valid()) {
				$this->initialPass = false;
				return null;
			}
			$this->cache[] = $last = [ $this->inner->key(), $this->inner->current() ];
			$this->inner->next();
		}
		return $last;
	}

	/**
	 * @return TValue
	 */
	#[\Override]
	public function current(): mixed {
		$entry = $this->entry($this->index);
		return is_null($entry) ? null : $entry[1];
	}

	/**
	 * @return TKey
	 */
	#[\Override]
	public function key(): mixed {
		$entry = $this->entry($this->index);
		return is_null($entry) ? null : $entry[0];
	}

	#[\Override]
	public function next(): void {
		$this->index++;
	}

	#[\Override]
	public function rewind(): void {
		$this->index = 0;
	}

	#[\Override]
	public function valid(): bool {
		return !is_null($this->entry($this->index));
	}
}
