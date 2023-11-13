<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\AuthApi\Response;

use Magento\Framework\DataObject;

class OAuthTokenResponse extends DataObject
{
    public const ACCESS_TOKEN = 'access_token';
    public const EXPIRES_IN = 'expires_in';
    public const REFRESH_EXPIRES_IN = 'refresh_expires_in';
    public const TOKEN_TYPE = 'token_type';
    public const NOT_BEFORE_POLICY = 'not-before-policy';
    public const SCOPE = 'scope';

    public function getAccessToken(): string
    {
        return (string)$this->getData(self::ACCESS_TOKEN);
    }

    public function setAccessToken(string $accessToken): void
    {
        $this->setData(self::ACCESS_TOKEN, $accessToken);
    }

    public function getExpiresIn(): int
    {
        return (int)$this->getData(self::EXPIRES_IN);
    }

    public function setExpiresIn(int $expiresIn): void
    {
        $this->setData(self::EXPIRES_IN, $expiresIn);
    }

    public function getRefreshExpiresIn(): int
    {
        return (int)$this->getData(self::REFRESH_EXPIRES_IN);
    }

    public function setRefreshExpiresIn(int $refreshExpiresIn): void
    {
        $this->setData(self::REFRESH_EXPIRES_IN, $refreshExpiresIn);
    }

    public function getTokenType(): string
    {
        return (string)$this->getData(self::TOKEN_TYPE);
    }

    public function setTokenType(string $tokenType): void
    {
        $this->setData(self::TOKEN_TYPE, $tokenType);
    }

    public function getNotBeforePolicy(): int
    {
        return (int)$this->getData(self::NOT_BEFORE_POLICY);
    }

    public function setNotBeforePolicy(int $notBeforePolicy): void
    {
        $this->setData(self::NOT_BEFORE_POLICY, $notBeforePolicy);
    }

    public function getScope(): string
    {
        return (string)$this->getData(self::SCOPE);
    }

    public function setScope(string $scope): void
    {
        $this->setData(self::SCOPE, $scope);
    }
}
