<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: 2023 Alec Kojaev <alec@kojaev.name>
// SPDX-License-Identifier: AGPL-3.0-or-later

use OCA\Calibre2OPDS\Util\MapIterator;
use OCA\Calibre2OPDS\Util\MimeTypes;
use PHPUnit\Framework\TestCase;

final class UtilTest extends TestCase {
	public function testMimeTypes() {
		$this->assertEquals('application/x-ms-reader', MimeTypes::getMimeType('LIT'));
		$this->assertEquals('application/octet-stream', MimeTypes::getMimeType('XXX'));
	}

	public function testMapIterator() {
		$sample = SplFixedArray::fromArray([0, 1, 2, 3, 4, 5, 6, 7, 8, 9]);
		$test = iterator_to_array(new MapIterator(
			$sample, fn ($v) => $v * $v
		));
		$this->assertEquals([0, 1, 4, 9, 16, 25, 36, 49, 64, 81], $test, "Non-filtering iterator");

		$test = new MapIterator(
			$sample,
			fn ($v) => $v * $v,
			fn ($v): bool => ($v % 3) == 1
		);
		$this->assertInstanceOf(Iterator::class, $test->getInnerIterator(), "Getting inner iterator");
		$this->assertEquals(
			[1 => 1, 2 => 4, 4 => 16, 5 => 25, 7 => 49, 8 => 64],
			iterator_to_array($test), "Filtering iterator"
		);
		$test->rewind();
		$this->assertTrue($test->valid(), "Calling valid() after rewind() on filtering iterator");
		$this->assertEquals(1, $test->current(), "First value on filtering iterator");

		$test = iterator_to_array(new MapIterator(
			$sample,
			fn ($v) => $v * $v,
			fn ($v): bool => $v > 100
		));
		$this->assertEquals([], $test, "Empty filtering iterator");
	}
}
