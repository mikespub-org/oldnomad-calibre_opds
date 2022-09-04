<?php

declare(strict_types=1);

namespace OCA\Calibre2OPDS\Service;

use OCA\Calibre2OPDS\Calibre\CalibreDB;
use OCA\Calibre2OPDS\Calibre\ICalibreDB;
use OCP\Files\Folder;

class CalibreService implements ICalibreService {
	public function getDatabase(Folder $root): ICalibreDB {
		return CalibreDB::fromFolder($root);
	}
}
