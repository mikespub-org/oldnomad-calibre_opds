<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: 2023 Alec Kojaev <alec@kojaev.name>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Calibre2OPDS\Calibre\Types;

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
}
