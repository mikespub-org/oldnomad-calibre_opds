<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: 2023 Alec Kojaev <alec@kojaev.name>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Calibre2OPDS\Calibre\Types;

use OCA\Calibre2OPDS\Calibre\CalibreItem;

/**
 * Criteria that may be applied to books.
 */
enum CalibreBookCriteria: string {
	case SEARCH = 'search';
	case AUTHOR = 'author';
	case PUBLISHER = 'publisher';
	case LANGUAGE = 'language';
	case SERIES = 'series';
	case TAG = 'tag';

	/**
	 * Reference implementing classes.
	 *
	 * @var array<string,class-string<CalibreItem>>
	 */
	private const REF_CLASSES = [
		self::AUTHOR->value => CalibreAuthor::class,
		self::PUBLISHER->value => CalibrePublisher::class,
		self::LANGUAGE->value => CalibreLanguage::class,
		self::SERIES->value => CalibreSeries::class,
		self::TAG->value => CalibreTag::class,
	];

	/**
	 * Get implementing class for this criterion reference.
	 *
	 * @return class-string<CalibreItem>|null reference implementing class, or `null` if not known.
	 */
	public function getDataClass(): ?string {
		return self::REF_CLASSES[$this->value] ?? null;
	}
}
