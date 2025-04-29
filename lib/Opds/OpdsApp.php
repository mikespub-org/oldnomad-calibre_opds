<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: 2023 Alec Kojaev <alec@kojaev.name>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Calibre2OPDS\Opds;

/**
 * Container for application attributes used by OPDS feed.
 */
class OpdsApp {
	/**
	 * Construct an instance.
	 *
	 * @param string $appId application ID.
	 * @param string $appName application name.
	 * @param string $appVersion application version.
	 * @param string $appWebsite application home page URL.
	 */
	public function __construct(
		private string $appId,
		private string $appName,
		private string $appVersion,
		private string $appWebsite,
	) {
	}

	/**
	 * Get application ID.
	 *
	 * @return string application ID.
	 */
	public function getAppId(): string {
		return $this->appId;
	}

	/**
	 * Get application name.
	 *
	 * @return string application name.
	 */
	public function getAppName(): string {
		return $this->appName;
	}

	/**
	 * Get application version.
	 *
	 * @return string application version.
	 */
	public function getAppVersion(): string {
		return $this->appVersion;
	}

	/**
	 * Get application home page URL.
	 *
	 * @return string application home page URL.
	 */
	public function getAppWebsite(): string {
		return $this->appWebsite;
	}
}
