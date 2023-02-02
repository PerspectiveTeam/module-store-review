<?php

namespace Perspective\StoreReview\Block;

use Magento\Customer\Model\Context;
use Magento\Framework\View\Element\Template;
use Magento\Review\Model\ResourceModel\Rating\Collection as RatingCollection;
use Magento\Review\Model\ResourceModel\Rating\CollectionFactory as RatingCollectionFactory;
use Magento\Store\Api\Data\StoreInterface;
use Perspective\StoreReview\Api\StoreReviewRepositoryInterface;
use Perspective\StoreReview\Model\ConfigManager;

class Form extends Template
{

    public function __construct(
        private readonly ConfigManager $configManager,
        private readonly \Magento\Framework\App\Http\Context $httpContext,
        private readonly RatingCollectionFactory $ratingCollectionFactory,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getAllowWriteReviewFlag(): bool
    {
        if (!$this->hasData('allow_write_review_flag')) {
            $this->setData('allow_write_review_flag',
                $this->httpContext->getValue(Context::CONTEXT_AUTH)
                    || $this->configManager->isGuestAllowToWrite()
            );
        }

        return (bool)$this->getData('allow_write_review_flag');
    }

    public function getStore(): StoreInterface
    {
        if (!$this->hasData('store')) {
            $this->setData('store', $this->_storeManager->getStore());
        }

        return $this->getData('store');
    }

    public function getRatings(): RatingCollection
    {
        return $this->ratingCollectionFactory->create()->addEntityFilter(
            StoreReviewRepositoryInterface::ENTITY_CODE
        )->setPositionOrder()->addRatingPerStoreName(
            $this->getStore()->getId()
        )->setStoreFilter(
            $this->getStore()->getId()
        )->setActiveFilter(
            true
        )->load()->addOptionToItems();
    }
}
