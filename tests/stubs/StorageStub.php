<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: 2023 Alec Kojaev <alec@kojaev.name>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Stubs;

use OCP\Files\File;
use OCP\Files\FileInfo;
use OCP\Files\Folder;
use OCP\Files\Node;
use OCP\Files\NotFoundException;
use OCP\Files\Storage\IStorage;

trait StorageStub {
	protected IStorage $storage;

	protected function initStorage(string $path, bool $fake = false): void {
		$storage = $this->createStub(IStorage::class);
		$storage->method('getLocalFile')->willReturnCallback(
			fn (string $filePath): string => $fake ? $path : ($path.'/'.$filePath)
		);
		$this->storage = $storage;
	}

	private function createStorageNode(string $cls, string $type, string $name, bool $readable): Node {
		$node = $this->createStub($cls);
		$node->parent = null;
		$node->name = $name;
		$node->content = null;
		$node->method('isReadable')->willReturn($readable);
		$node->method('getType')->willReturn($type);
		$node->method('getInternalPath')->willReturnCallback(function () use ($node) {
			return (is_null($node->parent) ? '' : $node->parent->getInternalPath()).'/'.$node->name;
		});
		$node->method('getStorage')->willReturn($this->storage);
		return $node;
	}

	protected function createFileNode(string $filename, bool $readable = true): File {
		$file = $this->createStorageNode(File::class, FileInfo::TYPE_FILE, $filename, $readable);
		return $file;
	}

	protected function createFolderNode(string $dirname, array $content, bool $readable = true): Folder {
		$dir = $this->createStorageNode(Folder::class, FileInfo::TYPE_FOLDER, $dirname, $readable);
		$dir->content = $content;
		foreach ($dir->content as $sub) {
			$sub->parent = $dir;
		}
		$dir->method('get')->willReturnCallback(function (string $path) use ($dir): Node {
			if ($path === '') {
				throw new NotFoundException('cannot find file with empty name');
			}
			$parts = explode('/', $path, 2);
			foreach ($dir->content as $sub) {
				if ($sub->name === $parts[0]) {
					if (isset($parts[1])) {
						if ($sub->getType() !== FileInfo::TYPE_FOLDER) {
							throw new NotFoundException('found file, expecting directory');
						}
						try {
							return $sub->get($parts[1]);
						} catch (NotFoundException $e) {
							throw new NotFoundException('not found: '.$path, 0, $e);
						}
					}
					return $sub;
				}
			}
			throw new NotFoundException('not found: '.$path);
		});
		return $dir;
	}
}
