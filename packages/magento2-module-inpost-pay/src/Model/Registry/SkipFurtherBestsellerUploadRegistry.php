<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Registry;

class SkipFurtherBestsellerUploadRegistry
{
    private bool $skipFurtherBestsellerUpload = false;

    public function setSkipFurtherBestsellerUploadFlag(): void
    {
        $this->skipFurtherBestsellerUpload = true;
    }

    public function resetSkipFurtherBestsellerUploadFlag(): void
    {
        $this->skipFurtherBestsellerUpload = false;
    }

    public function canSkipFurtherBestsellerUploadFlag(): bool
    {
        return $this->skipFurtherBestsellerUpload;
    }
}
