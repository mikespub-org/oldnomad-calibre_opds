<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: 2023 Alec Kojaev <alec@kojaev.name>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Calibre2OPDS\Util;

use Generator;
use Iterator;
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
final class MapAggregate implements IteratorAggregate {
	/**
	 * Inner iterator.
	 *
	 * @var Traversable<TKey,TValueInner>
	 */
	private Traversable $inner;
	/**
	 * Mapping function.
	 *
	 * @var callable(TValueInner):TValue
	 */
	private $mapper;
	/**
	 * Filtering function (optional).
	 *
	 * @var null|callable(TValue):bool
	 */
	private $filter;
	/**
	 * Value cache.
	 *
	 * @var Iterator<TKey,TValue>
	 */
	private Iterator $cache;

	/**
	 * Construct an instance.
	 *
	 * @param Traversable<TKey,TValueInner> $inner inner iterator.
	 * @param callable(TValueInner):TValue $mapper mapping function.
	 * @param null|callable(TValue):bool $filter optional filtering function.
	 */
	public function __construct(Traversable $inner, callable $mapper, ?callable $filter = null) {
		$this->inner = $inner;
		$this->mapper = $mapper;
		$this->filter = $filter;
		$this->cache = new CachedIterator($this->valueGenerator());
	}

	/**
	 * Generator for iterator values.
	 *
	 * @return Generator<TKey,TValue>
	 */
	private function valueGenerator(): Generator {
		/**
		 * Psalm gets royally confused here
		 * @var TKey $key
		 * @var TValueInner $item
		 */
		foreach ($this->inner as $key => $item) {
			/** @var TValue $value */
			$value = call_user_func($this->mapper, $item);
			if (is_null($this->filter) || call_user_func($this->filter, $value)) {
				yield $key => $value;
			}
		}
	}

	#[\Override]
	public function getIterator(): Traversable {
		return $this->cache;
	}
}
