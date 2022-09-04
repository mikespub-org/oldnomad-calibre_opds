<?php

declare(strict_types=1);

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
