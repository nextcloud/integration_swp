<?php

/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\UserOIDC\Event {

	use OCP\EventDispatcher\Event;

	class TokenObtainedEvent extends Event {
		public function getToken(): array {
		}

		public function getProvider(): \OCA\UserOIDC\Db\Provider {
		}

		public function getDiscovery(): array {
		}
	}

	class ExternalTokenRequestedEvent extends Event {
		public function getToken(): ?\OCA\UserOIDC\Model\Token {
		}
	}
}
namespace OCA\UserOIDC\Model {
	class Token {
		public function getAccessToken(): string {
		}

		public function getIdToken(): ?string {
		}

		public function getExpiresIn(): int {
		}

		public function getExpiresInFromNow(): int {
		}

		public function getRefreshExpiresIn(): ?int {
		}

		public function getRefreshExpiresInFromNow(): int {
		}

		public function getRefreshToken(): ?string {
		}

		public function getProviderId(): ?int {
		}

		public function isExpired(): bool {
		}

		public function isExpiring(): bool {
		}

		public function refreshIsExpired(): bool {
		}

		public function refreshIsExpiring(): bool {
		}

		public function getCreatedAt() {
		}

		public function jsonSerialize(): array {
		}
	}
}
