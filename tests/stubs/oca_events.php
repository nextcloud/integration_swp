<?php

/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Files_Sharing\Event {

	use OCP\EventDispatcher\Event;

	class BeforeTemplateRenderedEvent extends Event {
		public function getShare(): IShare {
		}
		public function getScope(): ?string {
		}
	}
}

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
}
