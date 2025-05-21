<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: 2023 Alec Kojaev <alec@kojaev.name>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Stubs;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

trait LoggerInterfaceStub {
	protected LoggerInterface $logger;

	protected ?array $expectMessage = null;

	private function emulateLog($level, $message, array $context) {
		$msg = $level . ': ' . $message . ' ' . json_encode($context, JSON_PRETTY_PRINT);
		if (is_null($this->expectMessage)) {
			$this->fail('Unexpected message: ' . $msg);
		}
		if (array_key_exists('level', $this->expectMessage)) {
			$this->assertEquals($this->expectMessage['level'], $level, 'Unexpected message level: ' . $msg);
		}
		if (array_key_exists('msg', $this->expectMessage)) {
			$this->assertEquals($this->expectMessage['msg'], $message, 'Unexpected message text: ' . $msg);
		}
		$this->assertTrue(true);
		$this->expectMessage = null;
	}

	protected function initLoggerInterface(): void {
		$logger = $this->createStub(LoggerInterface::class);
		$logger->method('log')->willReturnCallback($this->emulateLog(...));
		$logger->method('emergency')->willReturnCallback(
			fn ($message, array $context) => $this->emulateLog(LogLevel::EMERGENCY, $message, $context)
		);
		$logger->method('alert')->willReturnCallback(
			fn ($message, array $context) => $this->emulateLog(LogLevel::ALERT, $message, $context)
		);
		$logger->method('critical')->willReturnCallback(
			fn ($message, array $context) => $this->emulateLog(LogLevel::CRITICAL, $message, $context)
		);
		$logger->method('error')->willReturnCallback(
			fn ($message, array $context) => $this->emulateLog(LogLevel::ERROR, $message, $context)
		);
		$logger->method('warning')->willReturnCallback(
			fn ($message, array $context) => $this->emulateLog(LogLevel::WARNING, $message, $context)
		);
		$logger->method('notice')->willReturnCallback(
			fn ($message, array $context) => $this->emulateLog(LogLevel::NOTICE, $message, $context)
		);
		$logger->method('info')->willReturnCallback(
			fn ($message, array $context) => $this->emulateLog(LogLevel::INFO, $message, $context)
		);
		$logger->method('debug')->willReturnCallback(
			fn ($message, array $context) => $this->emulateLog(LogLevel::DEBUG, $message, $context)
		);
		$this->logger = $logger;
	}
}
