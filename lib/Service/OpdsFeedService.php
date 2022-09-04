<?php

declare(strict_types=1);

namespace OCA\Calibre2OPDS\Service;

use OCA\Calibre2OPDS\FeedBuilder\IOpdsFeedBuilder;
use OCA\Calibre2OPDS\FeedBuilder\OpdsFeedBuilder;
use OCP\IL10N;

class OpdsFeedService implements IOpdsFeedService {
	public function __construct(private ISettingsService $settings, private IL10N $l) {
	}

	public function createBuilder(string $selfRoute, array $selfParams, string $title, ?string $upRoute = null, array $upParams = []): IOpdsFeedBuilder {
		return new OpdsFeedBuilder($this->settings, $this->l, $selfRoute, $selfParams, $title, $upRoute, $upParams);
	}
}
