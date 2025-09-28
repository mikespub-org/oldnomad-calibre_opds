<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: 2023 Alec Kojaev <alec@kojaev.name>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Calibre2OPDS\Service;

use OCA\Calibre2OPDS\Calibre\CalibreDB;
use OCA\Calibre2OPDS\Calibre\ICalibreDB;
use OCP\Files\Folder;

final class CalibreService implements ICalibreService {
	#[\Override]
	public function getDatabase(Folder $root): ICalibreDB {
		return CalibreDB::fromFolder($root);
	}
}
