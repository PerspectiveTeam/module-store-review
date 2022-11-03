<?php

namespace Perspective\StoreReview\Block;

use Magento\Framework\View\Element\Template;
use Magento\Review\Model\ResourceModel\Rating\Collection as RatingCollection;
use Magento\Review\Model\ResourceModel\Rating\CollectionFactory as RatingCollectionFactory;
use Magento\Store\Api\Data\StoreInterface;
use Perspective\StoreReview\Api\StoreReviewRepositoryInterface;

class Form extends Template
{

    private ?StoreInterface $store = null;

    public function __construct(
        private readonly RatingCollectionFactory $ratingCollectionFactory,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getAllowWriteReviewFlag(): bool
    {
        return true;
    }

    public function getStore(): StoreInterface
    {
        if (null === $this->store) {
            $this->store = $this->_storeManager->getStore();
        }

        return $this->store;
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
