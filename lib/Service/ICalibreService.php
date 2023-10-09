<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: 2023 Alec Kojaev <alec@kojaev.name>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Calibre2OPDS\Service;

use OCA\Calibre2OPDS\Calibre\ICalibreDB;
use OCP\Files\Folder;

interface ICalibreService {
	/**
	 * Get Calibre metadata database.
	 *
	 * @param Folder $root Calibre library root.
	 *
	 * @return ICalibreDB constructed Calibre metadata database object.
	 */
	public function getDatabase(Folder $root): ICalibreDB;
}
