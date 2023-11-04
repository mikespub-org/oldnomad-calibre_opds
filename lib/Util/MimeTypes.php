<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: 2023 Alec Kojaev <alec@kojaev.name>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Calibre2OPDS\Util;

/**
 * Methods for handling MIME types.
 */
final class MimeTypes {
	/**
	 * Array of known MIME types, indexed by lower-case extensions.
	 *
	 * @var array<string>
	 */
	private static array $MIME_TYPES = [];

	/**
	 * Load MIME types from a file.
	 *
	 * @param string $filename file to load types from.
	 *
	 * @return bool `true` on success, `false` on error.
	 */
	public static function loadMimeTypes(string $filename = __DIR__.'/mime.types'): bool {
		$list = [];
		$lines = @file($filename, FILE_IGNORE_NEW_LINES);
		if ($lines === false) {
			return false;
		}
		foreach ($lines as $line) {
			$line = trim($line);
			if (strlen($line) == 0 || $line[0] === '#') {
				continue;
			}
			$parts = preg_split('/\s+/', $line, -1, PREG_SPLIT_NO_EMPTY);
			if ($parts === false || count($parts) < 2) {
				continue;
			}
			$type = array_shift($parts);
			foreach ($parts as $ext) {
				$list[strtolower($ext)] = $type;
			}
		}
		self::$MIME_TYPES = array_merge(self::$MIME_TYPES, $list);
		return true;
	}

	/**
	 * Get MIME type for an extension.
	 *
	 * @param string $type file extension.
	 *
	 * @return string MIME type.
	 */
	public static function getMimeType(string $type): string {
		if (count(self::$MIME_TYPES) === 0) {
			self::loadMimeTypes();
		}
		return self::$MIME_TYPES[strtolower($type)] ?? 'application/octet-stream';
	}
}
