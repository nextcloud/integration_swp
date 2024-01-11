<?php

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
