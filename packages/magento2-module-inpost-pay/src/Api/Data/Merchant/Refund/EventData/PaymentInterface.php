<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data\Merchant\Refund\EventData;

interface PaymentInterface
{
    public const ID = 'id';
    public const METHOD = 'method';
    public const REFERENCE = 'reference';

    /**
     * @return string
     */
    public function getId(): string;

    /**
     * @param string $id
     * @return void
     */
    public function setId(string $id): void;

    /**
     * @return string
     */
    public function getMethod(): string;

    /**
     * @param string $method
     * @return void
     */
    public function setMethod(string $method): void;

    /**
     * @return string|null
     */
    public function getReference(): ?string;

    /**
     * @param string|null $reference
     * @return void
     */
    public function setReference(?string $reference): void;
}
