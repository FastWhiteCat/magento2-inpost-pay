<?php

declare(strict_types=1);

namespace InPost\InPostPay\Traits;

trait AnonymizerTrait
{
    private array $keysToAnonymize = [
        'request_body',
        'name',
        'surname',
        'mail',
        'digital_delivery_email',
        'city',
        'address',
        'street',
        'building',
        'flat',
        'postal_code',
        'phone',
        'tax_id',
        'company_name',
        'additional_information',
        'client_secret',
        'merchant_secret',
    ];

    private array $keysToShorten = [
        'access_token',
        'Authorization',
        'qr_code',
    ];

    /**
     * @param string $value
     * @return string
     */
    public function anonymizeString(string $value): string
    {
        $length = mb_strlen($value);

        if ($length <= 2) {
            return $value;
        }

        $firstChar = mb_substr($value, 0, 1);
        $lastChar = mb_substr($value, $length - 1, 1);

        return $firstChar . str_repeat('*', $length - 2) . $lastChar;
    }

    /**
     * @param string $secret
     * @return string
     */
    public function anonymizeSecret(string $secret): string
    {
        $length = mb_strlen($secret);

        if ($length <= 8) {
            return $secret;
        }

        $firstPart = mb_substr($secret, 0, 4);
        $lastPart = mb_substr($secret, $length - 4, 4);

        return $firstPart . str_repeat('*', $length - 8) . $lastPart;
    }

    /**
     * @param string $email
     * @return string
     */
    public function anonymizeEmail(string $email): string
    {
        $parts = explode('@', $email);

        if (count($parts) !== 2) {
            return $email;
        }

        [$localPart, $domainPart] = $parts;

        $length = strlen($localPart);

        if ($length <= 2) {
            $anonymizedLocalPart = $localPart;
        } else {
            $anonymizedLocalPart = $localPart[0] . str_repeat('*', $length - 2) . $localPart[$length - 1];
        }

        return $anonymizedLocalPart . '@' . $domainPart;
    }

    /**
     * @param string $value
     * @param string $key
     * @return string|array
     */
    public function anonymizeValueByKey(string $value, string $key): string|array
    {
        switch ($key) {
            case 'merchant_secret':
            case 'client_secret':
                $anonymisedValue = $this->anonymizeSecret($value);

                break;
            case 'digital_delivery_email':
            case 'mail':
                $anonymisedValue = $this->anonymizeEmail($value);

                break;
            case 'request_body':
                $value = json_decode($value, true);
                $value = is_array($value) ? $value : [];

                if (!empty($value)) {
                    $anonymisedValue = $this->anonymizeArray($value);
                } else {
                    $anonymisedValue = '';
                }

                break;
            default:
                $anonymisedValue = $this->anonymizeString($value);
        }

        return $anonymisedValue;
    }

    public function shortenValue(string $value): string|array
    {
        return substr($value, 0, 15) . '...' . substr($value, -3);
    }

    /**
     * @param array $data
     * @param int $depth
     * @return array
     */
    public function anonymizeArray(array $data, int $depth = 0): array
    {
        if ($depth > 10) {
            return $data;
        }

        foreach ($data as $key => $value) {
            if (in_array($key, $this->keysToAnonymize, true) && is_string($value)) {
                $data[$key] = $this->anonymizeValueByKey($value, $key);
            } elseif (in_array($key, $this->keysToShorten, true) && is_string($value)) {
                $data[$key] = $this->shortenValue($value);
            } elseif (is_array($value)) {
                $data[$key] = $this->anonymizeArray($value, $depth + 1);
            }
        }

        return $data;
    }
}
