<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Api\Command\UserIntent;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SetTextValue implements ValueUserIntent
{
    public function __construct(
        private string $attributeCode,
        private ?string $localeCode,
        private ?string $channelCode,
        private string $value
    ) {
    }

    public function attributeCode(): string
    {
        return $this->attributeCode;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function localeCode(): ?string
    {
        return $this->localeCode;
    }

    public function channelCode(): ?string
    {
        return $this->channelCode;
    }
}
