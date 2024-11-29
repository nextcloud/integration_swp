<?php

/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace OCA\Swp\Model;

use JsonSerializable;

class Token implements JsonSerializable {

	private string $idToken;
	private string $accessToken;
	private int $expiresIn;
	private string $refreshToken;
	private int $createdAt;
	private ?int $providerId;

	public function __construct(array $tokenData) {
		$this->idToken = $tokenData['id_token'];
		$this->accessToken = $tokenData['access_token'];
		$this->expiresIn = $tokenData['expires_in'];
		$this->refreshToken = $tokenData['refresh_token'];
		$this->createdAt = $tokenData['created_at'] ?? time();
		$this->providerId = $tokenData['provider_id'] ?? null;
	}

	public function getAccessToken(): string {
		return $this->accessToken;
	}

	public function getIdToken(): string {
		return $this->idToken;
	}

	public function getExpiresIn(): int {
		return $this->expiresIn;
	}

	public function getRefreshToken(): string {
		return $this->refreshToken;
	}

	public function getProviderId(): ?int {
		return $this->providerId;
	}

	public function isExpired(): bool {
		return time() > ($this->createdAt + $this->expiresIn);
	}

	public function isExpiring(): bool {
		return time() > ($this->createdAt + (int)($this->expiresIn / 2));
	}

	public function getCreatedAt() {
		return $this->createdAt;
	}

	public function jsonSerialize(): array {
		return [
			'id_token' => $this->idToken,
			'access_token' => $this->accessToken,
			'expires_in' => $this->expiresIn,
			'refresh_token' => $this->refreshToken,
			'created_at' => $this->createdAt,
			'provider_id' => $this->providerId,
		];
	}
}
