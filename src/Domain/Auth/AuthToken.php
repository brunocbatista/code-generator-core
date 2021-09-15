<?php
declare(strict_types=1);

namespace App\Domain\Auth;

use JsonSerializable;

class AuthToken implements JsonSerializable
{
    /**
     * @var int|null
     */
    private ?int $id;

    /**
     * @var int
     */
    private int $userId;

    /**
     * @var string
     */
    private string $token;

    /**
     * @var string
     */
    private string $expiredAt;

    /**
     * @param int|null $id
     * @param int $userId
     * @param string $token
     * @param string $expiredAt
     */
    public function __construct(?int $id, int $userId, string $token, string $expiredAt)
    {
        $this->id = $id;
        $this->userId = $userId;
        $this->token = $token;
        $this->expiredAt = $expiredAt;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @param int $name
     * @void
     */
    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param string $token
     * @void
     */
    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    /**
     * @return string
     */
    public function getExpiredAt(): string
    {
        return $this->expiredAt;
    }

    /**
     * @param string $expiredAt
     * @void
     */
    public function setExpiredAt(string $expiredAt): void
    {
        $this->expiredAt = $expiredAt;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'userId' => $this->userId,
            'token' => $this->token,
            'expiredAt' => $this->expiredAt
        ];
    }
}
