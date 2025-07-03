<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: 2023 Alec Kojaev <alec@kojaev.name>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Stubs;

use OCA\Calibre2OPDS\Calibre\CalibreDB;
use OCA\Calibre2OPDS\Calibre\ICalibreDB;
use OCP\Files\Folder;

trait TestDataStub {
	use StorageStub;

	protected ICalibreDB $dataDb;
	protected Folder $dataRoot;

	public function initTestData(): void {
		$this->initStorage(':memory:', true); // Trick to force an in-memory database
		$this->dataRoot = $this->createFolderNode('.', [
			$this->createFileNode('metadata.db'),
			$this->createFolderNode('dummies_cicero', [
				$this->createFileNode('cover.jpg'),
				$this->createFileNode('cicero_for_dummies.epub'),
				$this->createFileNode('cicero_for_dummies.fb2', false),
			]),
			$this->createFolderNode('whores_eroticon6', [
				$this->createFileNode('cover.jpg', false),
			]),
		]);

		/** @var CalibreDB */
		$this->dataDb = CalibreDB::fromFolder($this->dataRoot, false);
		$pdo = $this->dataDb->getDatabase();

		$ddl = file_get_contents(__DIR__ . '/../files/metadata.sql');
		$pdo->exec($ddl);
		$ddl = file_get_contents(__DIR__ . '/../files/test-data.sql');
		$pdo->exec($ddl);
	}
}
