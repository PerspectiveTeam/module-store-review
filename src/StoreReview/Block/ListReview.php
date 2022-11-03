<?php

namespace Perspective\StoreReview\Block;

use Magento\Framework\View\Element\Template;
use Magento\Review\Model\Review;
use Magento\Store\Model\StoreManagerInterface;
use Perspective\ReviewTree\Model\ResourceModel\Review\Collection as ReviewTreeCollection;
use Perspective\ReviewTree\Model\ResourceModel\Review\CollectionFactory as ReviewTreeCollectionFactory;
use Perspective\StoreReview\Api\StoreReviewRepositoryInterface;
use Perspective\StoreReview\Model\ConfigManager;

class ListReview extends Template
{

    protected ?int $storeId = null;

    protected ?ReviewTreeCollection $reviewsCollection = null;

    public function __construct(
        private readonly ConfigManager               $configManager,
        private readonly StoreManagerInterface       $storeManager,
        private readonly ReviewTreeCollectionFactory $reviewTreeCollectionFactory,
        Template\Context                             $context,
        array                                        $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function isInfiniteScrollEnabled(): bool
    {
        return $this->configManager->isInfiniteScrollEnabled();
    }

    public function getInfiniteScrollInitialRenderSize(): int
    {
        return $this->configManager->getInfiniteScrollInitialRenderSize() ?? 10;
    }

    public function getInfiniteScrollItemsPerFetch(): int
    {
        return $this->configManager->getInfiniteScrollItemsPerFetch() ?? 10;
    }

    public function getEntityId(): int
    {
        if (null === $this->storeId) {
            $this->storeId = $this->storeManager->getStore()->getId();
        }

        return $this->storeId;
    }

    public function getReviewsCollection(): ReviewTreeCollection
    {
        if (null === $this->reviewsCollection) {
            $this->reviewsCollection = $this->reviewTreeCollectionFactory->create()->addStoreFilter(
                $this->_storeManager->getStore()->getId()
            )->addStatusFilter(
                Review::STATUS_APPROVED
            )->addEntityFilter(
                StoreReviewRepositoryInterface::ENTITY_CODE,
                $this->getEntityId()
            )->prepareFrontendPlainTreeSelect();

            if ($this->isInfiniteScrollEnabled()) {
                $p = $this->getData('p') ?? 1;
                $this->reviewsCollection->setCurPage($p);
                $this->reviewsCollection->setPageSize(
                    $p > 1 ? $this->getInfiniteScrollItemsPerFetch() : $this->getInfiniteScrollInitialRenderSize()
                );
            }
        }

        return $this->reviewsCollection;
    }
}
