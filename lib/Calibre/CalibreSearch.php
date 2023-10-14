<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: 2023 Alec Kojaev <alec@kojaev.name>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Calibre2OPDS\Calibre;

use OCA\Calibre2OPDS\Calibre\Types\CalibreAuthor;
use OCA\Calibre2OPDS\Calibre\Types\CalibreBook;
use OCA\Calibre2OPDS\Calibre\Types\CalibreSeries;
use OCA\Calibre2OPDS\Calibre\Types\CalibreTag;

/**
 * Search implementation.
 */
class CalibreSearch {
	/**
	 * Pattern to look for.
	 *
	 * @var non-empty-string
	 */
	private string $pattern;

	/**
	 * Construct an instance.
	 *
	 * @param string $terms search string.
	 */
	private function __construct(string $terms) {
		/* @var string */
		$dataEsc = str_replace('/', '\/', $terms);
		$this->pattern = '/'.$dataEsc.'/inS';
	}

	/**
	 * Filter for books.
	 *
	 * @param CalibreBook $item book item to check.
	 * @return bool `true` if the book passes, `false` otherwise.
	 */
	private function filterBook(CalibreBook $item): bool {
		$haystack = [ $item->title, $item->comment ?? '' ];
		/** @var CalibreAuthor $author */
		foreach ($item->authors as $author) {
			array_push($haystack, $author->name);
		}
		/** @var CalibreSeries $series */
		foreach ($item->series as $series) {
			array_push($haystack, $series->name);
		}
		/** @var CalibreTag $tag */
		foreach ($item->tags as $tag) {
			array_push($haystack, $tag->name);
		}
		/** @psalm-suppress MixedArgumentTypeCoercion -- Psalm gets confused about type of $haystack */
		$match = preg_grep($this->pattern, $haystack);
		/** @psalm-suppress RedundantConditionGivenDocblockType -- Psalm is mistaken about return type of preg_grep() */
		return $match !== false && count($match) > 0;
	}

	/**
	 * Get a callable filter for search among books.
	 *
	 * @param string $terms search string.
	 * @return callable(CalibreBook):bool|null callable filter for books, or `null` to skip checks.
	 */
	public static function searchBooks(string $terms): ?callable {
		if ($terms === '') {
			return null;
		}
		$obj = new self($terms);
		return $obj->filterBook(...);
	}
}
