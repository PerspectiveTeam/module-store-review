<?php

namespace Perspective\StoreReview\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

class ConfigManager
{

    public const GROUP_GENERAL = 'general';

    public const GROUP_INFINITE_SCROLL = 'infinite_scroll';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    public function getConfig(string $group, string $key): ?string
    {
        return $this->scopeConfig->getValue("perspective_storereview/$group/$key");
    }

    public function isEnabled(): bool
    {
        return (bool)$this->getConfig(self::GROUP_GENERAL, 'enable');
    }

    public function getDepthLimit(): ?int
    {
        return (int)$this->getConfig(self::GROUP_GENERAL, 'depth_limit');
    }

    public function isGuestAllowToWrite(): bool
    {
        return (bool)$this->getConfig(self::GROUP_GENERAL, 'guest_allow_to_write');
    }

    public function isInfiniteScrollEnabled(): bool
    {
        return (bool)$this->getConfig(self::GROUP_INFINITE_SCROLL, 'enable');
    }

    public function getInfiniteScrollInitialRenderSize(): ?int
    {
        return (int)$this->getConfig(self::GROUP_INFINITE_SCROLL, 'initial_render_size');
    }

    public function getInfiniteScrollItemsPerFetch(): ?int
    {
        return (int)$this->getConfig(self::GROUP_INFINITE_SCROLL, 'items_per_fetch');
    }
}
