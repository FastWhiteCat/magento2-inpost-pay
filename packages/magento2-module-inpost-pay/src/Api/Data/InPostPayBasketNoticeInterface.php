<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data;

interface InPostPayBasketNoticeInterface
{
    public const TABLE_NAME = 'inpost_pay_basket_notice';
    public const ENTITY_NAME = 'inpost_pay_basket_notice';
    public const BASKET_NOTICE_ID = 'basket_notice_id';
    public const INPOST_PAY_QUOTE_ID = 'inpost_pay_quote_id';
    public const TYPE = 'type';
    public const DESCRIPTION = 'description';
    public const IS_SENT = 'is_sent';
    public const ATTENTION = 'ATTENTION';
    public const ERROR = 'ERROR';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';

    public function getBasketNoticeId(): ?int;

    public function setBasketNoticeId(int $basketNoticeId): InPostPayBasketNoticeInterface;

    public function getInPostPayQuoteId(): int;

    public function setInPostPayQuoteId(int $inPostPayQuoteId): InPostPayBasketNoticeInterface;

    public function getType(): string;

    public function setType(string $type): InPostPayBasketNoticeInterface;

    public function getDescription(): string;

    public function setDescription(string $description): InPostPayBasketNoticeInterface;

    public function getIsSent(): bool;

    public function setIsSent(bool $isSent): InPostPayBasketNoticeInterface;

    public function getCreatedAt(): string;

    public function getUpdatedAt(): string;
}
