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
        $accessToken = $this->getData(self::ACCESS_TOKEN);

        return (is_scalar($accessToken)) ? (string)$accessToken : '';
    }

    public function setAccessToken(string $accessToken): void
    {
        $this->setData(self::ACCESS_TOKEN, $accessToken);
    }

    public function getExpiresIn(): int
    {
        $expiresIn = $this->getData(self::EXPIRES_IN);

        return (is_scalar($expiresIn)) ? (int)$expiresIn : 0;
    }

    public function setExpiresIn(int $expiresIn): void
    {
        $this->setData(self::EXPIRES_IN, $expiresIn);
    }

    public function getRefreshExpiresIn(): int
    {
        $refreshExpiresIn = $this->getData(self::REFRESH_EXPIRES_IN);

        return (is_scalar($refreshExpiresIn)) ? (int)$refreshExpiresIn : 0;
    }

    public function setRefreshExpiresIn(int $refreshExpiresIn): void
    {
        $this->setData(self::REFRESH_EXPIRES_IN, $refreshExpiresIn);
    }

    public function getTokenType(): string
    {
        $tokenType = $this->getData(self::TOKEN_TYPE);

        return (is_scalar($tokenType)) ? (string)$tokenType : '';
    }

    public function setTokenType(string $tokenType): void
    {
        $this->setData(self::TOKEN_TYPE, $tokenType);
    }

    public function getNotBeforePolicy(): int
    {
        $notBeforePolicy = $this->getData(self::NOT_BEFORE_POLICY);

        return (is_scalar($notBeforePolicy)) ? (int)$notBeforePolicy : 0;
    }

    public function setNotBeforePolicy(int $notBeforePolicy): void
    {
        $this->setData(self::NOT_BEFORE_POLICY, $notBeforePolicy);
    }

    public function getScope(): string
    {
        $scope = $this->getData(self::SCOPE);

        return (is_scalar($scope)) ? (string)$scope : '';
    }

    public function setScope(string $scope): void
    {
        $this->setData(self::SCOPE, $scope);
    }
}
