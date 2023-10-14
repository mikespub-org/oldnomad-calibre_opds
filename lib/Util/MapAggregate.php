<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: 2023 Alec Kojaev <alec@kojaev.name>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Calibre2OPDS\Util;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

/**
 * Iterator aggregator that maps and, optionally, filters contents of another (inner) iterator.
 *
 * Retrieved values are cached.
 *
 * @template TKey
 * @template TValue
 * @template TValueInner
 * @implements IteratorAggregate<TKey,TValue>
 */
class MapAggregate implements IteratorAggregate {
	/**
	 * Value cache.
	 *
	 * @var array<TKey,TValue>
	 */
	private array $cache;

	/**
	 * Construct an instance.
	 *
	 * @param Traversable<TKey,TValueInner> $inner inner iterator.
	 * @param callable(TValueInner):TValue $mapper mapping function.
	 * @param null|callable(TValue):bool $filter optional filtering function.
	 */
	public function __construct(Traversable $inner, callable $mapper, ?callable $filter = null) {
		$this->cache = [];
		foreach ($inner as $key => $item) {
			$value = call_user_func($mapper, $item);
			if (is_null($filter) || call_user_func($filter, $value)) {
				$this->cache[$key] = $value;
			}
		}
	}

	public function getIterator(): Traversable {
		/** @psalm-suppress MixedArgumentTypeCoercion -- psalm is confused about arrays */
		return new ArrayIterator($this->cache);
	}
}
