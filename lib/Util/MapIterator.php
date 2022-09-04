<?php

declare(strict_types=1);

namespace OCA\Calibre2OPDS\Util;

use Exception;
use Iterator;
use IteratorAggregate;
use OuterIterator;
use Traversable;

/**
 * Iterator wrapper that maps and, optionally, filters contents of another (inner) iterator.
 *
 * @template TKey
 * @template TValue
 * @template TValueInner
 * @implements OuterIterator<TKey,TValue>
 */
class MapIterator implements OuterIterator {
	/**
	 * Inner iterator.
	 *
	 * @var Iterator<TKey,TValueInner>
	 */
	private Iterator $inner;
	/**
	 * Mapping function.
	 *
	 * @var callable(TValueInner):TValue
	 */
	private $mapper;
	/**
	 * Optional filtering function.
	 *
	 * @var null|callable(TValue):bool
	 */
	private $filter;
	/**
	 * Current value.
	 *
	 * @var TValue|null
	 */
	private $value;
	/**
	 * Flag to show whether current value is already computed.
	 */
	private bool $hasValue;

	/**
	 * Construct a mapping iterator.
	 *
	 * @param Traversable<TKey,TValueInner> $inner inner iterator.
	 * @param callable(TValueInner):TValue $mapper mapping function.
	 * @param null|callable(TValue):bool $filter optional filtering function.
	 */
	public function __construct(Traversable $inner, callable $mapper, ?callable $filter = null) {
		while ($inner instanceof IteratorAggregate) {
			$inner = $inner->getIterator();
		}
		if (!($inner instanceof Iterator)) {
			throw new Exception('Traversable is neither an Iterator, nor an IteratorAggregate');
		}
		$this->inner = $inner;
		$this->mapper = $mapper;
		$this->filter = $filter;
		$this->value = null;
		$this->hasValue = false;
	}

	private function checkValue(): bool {
		if (is_null($this->filter)) {
			return true;
		}
		if (!$this->valid()) {
			return true;
		}
		return call_user_func($this->filter, $this->current());
	}

	/**
	 * @return null|Iterator<TKey,TValueInner>
	 * @psalm-suppress ImplementedReturnTypeMismatch -- Psalm bug, see <https://github.com/phpstan/phpstan/issues/6829>
	 */
	public function getInnerIterator(): ?Iterator {
		return $this->inner;
	}

	/**
	 * @return TValue
	 */
	public function current(): mixed {
		if (!$this->hasValue) {
			$this->value = call_user_func($this->mapper, $this->inner->current());
			$this->hasValue = true;
		}
		return $this->value;
	}

	/**
	 * @return TKey
	 */
	public function key(): mixed {
		return $this->inner->key();
	}

	public function next(): void {
		do {
			$this->inner->next();
			$this->hasValue = false;
		} while (!$this->checkValue());
	}

	public function rewind(): void {
		$this->inner->rewind();
		$this->hasValue = false;
		if (!$this->checkValue()) {
			$this->next();
		}
	}

	public function valid(): bool {
		return $this->inner->valid();
	}
}
