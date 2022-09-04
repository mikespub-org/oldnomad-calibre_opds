<?php

declare(strict_types=1);

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
