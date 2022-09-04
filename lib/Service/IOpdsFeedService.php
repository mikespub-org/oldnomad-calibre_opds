<?php

declare(strict_types=1);

namespace OCA\Calibre2OPDS\Service;

use OCA\Calibre2OPDS\FeedBuilder\IOpdsFeedBuilder;

interface IOpdsFeedService {
	/**
	 * Create OPDS feed builder.
	 *
	 * @param string $selfRoute this route name.
	 * @param array $selfParams this route parameters.
	 * @param string $title title for this feed.
	 * @param string|null $upRoute optional route name for containing feed.
	 * @param array $upParams optional route parameters for containing feed.
	 *
	 * @return IOpdsFeedBuilder constructed feed builder.
	 */
	public function createBuilder(string $selfRoute, array $selfParams, string $title, ?string $upRoute = null, array $upParams = []): IOpdsFeedBuilder;
}
