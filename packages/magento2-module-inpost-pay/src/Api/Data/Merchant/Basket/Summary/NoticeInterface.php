<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data\Merchant\Basket\Summary;

interface NoticeInterface
{
    public const TYPE = 'type';
    public const DESCRIPTION = 'description';
    public const ATTENTION = 'ATTENTION';
    public const ERROR = 'ERROR';

    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @param string $type
     * @return void
     */
    public function setType(string $type): void;

    /**
     * @return string
     */
    public function getDescription(): string;

    /**
     * @param string $description
     * @return void
     */
    public function setDescription(string $description): void;
}
