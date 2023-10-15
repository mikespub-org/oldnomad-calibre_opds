<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: 2023 Alec Kojaev <alec@kojaev.name>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Calibre2OPDS\Util;

use ArrayIterator;
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
class CachedIterator implements Iterator {
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
	 * Cached iterator data.
	 *
	 * @var array<array<TKey,TValue>>
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
		$this->cache = [];
		if ($this->inner->valid()) {
			$this->cache[] = [ $this->key() => $this->current() ];
		}
	}

	public function isInitialPass(): bool {
		return $this->initialPass;
	}

	/**
	 * @return TValue
	 */
	public function current(): mixed {
		return $this->inner->current();
	}

	/**
	 * @return TKey
	 */
	public function key(): mixed {
		return $this->inner->key();
	}

	public function next(): void {
		$this->inner->next();
		if ($this->initialPass && $this->inner->valid()) {
			$this->cache[] = [ $this->key() => $this->current() ];
		}
	}

	public function rewind(): void {
		if ($this->initialPass) {
			while ($this->valid()) {
				$this->next();
			}
			$this->initialPass = false;
			/**
			 * Psalm gets confused with this.
			 * @psalm-suppress MissingTemplateParam
			 * @psalm-suppress MixedAssignment
			 * @psalm-suppress MixedArrayAccess
			 * @psalm-suppress MixedArgument
			 */
			$this->inner = new class($this->cache) extends ArrayIterator {
				public function current(): mixed {
					$val = parent::current();
					return $val[$this->key()];
				}

				public function key(): string|int|null {
					$val = parent::current();
					return array_key_first($val);
				}
			};
		}
		$this->inner->rewind();
	}

	public function valid(): bool {
		return $this->inner->valid();
	}
}
