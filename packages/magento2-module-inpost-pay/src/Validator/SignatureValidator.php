<?php

declare(strict_types=1);

namespace InPost\InPostPay\Validator;

use DateTime;
use DateTimeZone;
use InPost\InPostPay\Api\Validator\SignatureValidatorInterface;
use InPost\InPostPay\Provider\PublicKeyProvider;
use Laminas\Http\Response;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\LocalizedException;
use OpenSSLAsymmetricKey;
use Psr\Log\LoggerInterface;

class SignatureValidator implements SignatureValidatorInterface
{
    private const SIGNATURE_CORRECT = 1;
    private const SIGNATURE_INCORRECT = 0;
    private const SIGNATURE_VALIDATION_ERROR = -1;
    private const REQUEST_TTL_IN_SECONDS = 240;
    private const PUBLIC_KEY_HEADER = '-----BEGIN PUBLIC KEY-----';
    private const PUBLIC_KEY_ENDING = '-----END PUBLIC KEY-----';

    /**
     * @param PublicKeyProvider $publicKeyProvider
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly PublicKeyProvider $publicKeyProvider,
        private readonly LoggerInterface $logger
    ) {
    }

    public function validate(
        string $requestSignature,
        string $requestSignatureTimestamp,
        string $requestPublicKeyVersion,
        string $requestPublicKeyHash,
        string $requestBody
    ): bool {
        try {
            $this->validateRequestPublicKeyBase64Hash($requestPublicKeyVersion, $requestPublicKeyHash);
            $this->validateSignature($requestSignature, $requestSignatureTimestamp, $requestPublicKeyVersion, $requestBody);
            $this->validateSignatureLifetime($requestSignatureTimestamp);
        } catch (AuthorizationException $e) {
            $this->logger->error(sprintf('Signature validation process failed. Reason: %s', $e->getMessage()));

            throw $e;
        }

        return true;
    }

    /**
     * @param string $requestPublicKeyVersion
     * @param string $requestPublicKeyHash
     * @return void
     * @throws AuthorizationException
     */
    private function validateRequestPublicKeyBase64Hash(
        string $requestPublicKeyVersion,
        string $requestPublicKeyHash
    ): void {
        $publicKeyBase64Hash = hash('sha256', $this->getPublicKeyBase64ByVersion($requestPublicKeyVersion));
        if ($publicKeyBase64Hash !== $requestPublicKeyHash) {
            $validationErrorMsg = __('Incorrect public key hash');

            throw new AuthorizationException($validationErrorMsg, null, Response::STATUS_CODE_401);
        }
    }

    /**
     * @param string $requestSignature
     * @param string $requestSignatureTimestamp
     * @param string $requestPublicKeyVersion
     * @param string $requestBody
     * @return void
     * @throws AuthorizationException
     */
    private function validateSignature(
        string $requestSignature,
        string $requestSignatureTimestamp,
        string $requestPublicKeyVersion,
        string $requestBody
    ): void {
        $decodedRequestSignature = base64_decode($requestSignature);
        $expectedSignature = $this->calculateSignature(
            $requestSignatureTimestamp,
            $requestPublicKeyVersion,
            $requestBody
        );

        $validationResult = (int)openssl_verify(
            $expectedSignature,
            $decodedRequestSignature,
            $this->getOpenSSLAsymmetricKey($this->getPublicKeyBase64ByVersion($requestPublicKeyVersion)),
            OPENSSL_ALGO_SHA256
        );

        switch ($validationResult) {
            case self::SIGNATURE_CORRECT:
                $validationErrorMsg = null;
                break;
            case self::SIGNATURE_INCORRECT:
                $validationErrorMsg = __('Invalid signature');
                break;
            case self::SIGNATURE_VALIDATION_ERROR:
                $validationErrorMsg = __(
                    'There has been an error during signature validation: "%1"',
                    (string) openssl_error_string()
                );
                break;
            default:
                $validationErrorMsg = __('Signature validation failed for unknown reasons');
        }

        if ($validationErrorMsg) {
            throw new AuthorizationException($validationErrorMsg, null, Response::STATUS_CODE_401);
        }
    }

    /**
     * @param string $requestSignatureTimestamp
     * @return void
     * @throws AuthorizationException
     */
    private function validateSignatureLifetime(string $requestSignatureTimestamp): void
    {
        $currentDateTime = new DateTime('now', new DateTimeZone("UTC"));
        $currentTimestamp = strtotime($currentDateTime->format(DateTime::ATOM));

        if (strtotime($requestSignatureTimestamp) + self::REQUEST_TTL_IN_SECONDS < $currentTimestamp) {
            $validationErrorMsg = __('Signature is no longer valid');

            throw new AuthorizationException($validationErrorMsg, null, Response::STATUS_CODE_401);
        }
    }

    private function calculateSignature(
        string $requestSignatureTimestamp,
        string $requestPublicKeyVersion,
        string $requestBody
    ): string {
        $digest = base64_encode(hash('sha256', $requestBody));
        try {
            $externalMerchantId = $this->publicKeyProvider->getMerchantExternalId($requestPublicKeyVersion);
        } catch (LocalizedException $e) {
            $externalMerchantId = '';
        }

        $dataToHash = [
            $digest,
            $externalMerchantId,
            $requestPublicKeyVersion,
            $requestSignatureTimestamp
        ];

        return base64_encode(implode(',', $dataToHash));
    }

    /**
     * @param string $publicKeyBase64
     * @return OpenSSLAsymmetricKey
     * @throws AuthorizationException
     */
    private function getOpenSSLAsymmetricKey(string $publicKeyBase64): OpenSSLAsymmetricKey
    {
        $key = openssl_get_publickey(
            implode(PHP_EOL, [self::PUBLIC_KEY_HEADER, $publicKeyBase64, self::PUBLIC_KEY_ENDING])
        );

        if ($key === false) {
            throw new AuthorizationException(__('Could not obtain public key.'));
        }

        return $key;
    }

    private function getPublicKeyBase64ByVersion(string $version): string
    {
        try {
            return $this->publicKeyProvider->getPublicKeyBase64($version);
        } catch (LocalizedException $e) {
            return '';
        }
    }
}
