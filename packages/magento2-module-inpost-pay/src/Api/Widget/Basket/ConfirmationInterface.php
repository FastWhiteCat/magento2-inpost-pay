<?php

namespace InPost\InPostPay\Api\Widget\Basket;

/**
 * @api
 */
interface ConfirmationInterface
{
    public const MESSAGE = 'message';

    /**
     * @return string
     */
    public function getMessage(): string;

    /**
     * @return string|null
     */
    public function getStatus(): ?string;

    /**
     * @return string|null
     */
    public function getBrowserId(): ?string;

    /**
     * @return bool|null
     */
    public function getBrowserTrusted(): ?bool;

    /**
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * @return string|null
     */
    public function getSurname(): ?string;

    /**
     * @return string|null
     */
    public function getMaskedPhoneNumber(): ?string;
}
