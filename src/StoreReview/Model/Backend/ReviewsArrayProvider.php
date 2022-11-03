<?php

namespace Perspective\StoreReview\Model\Backend;

use Hyva\Admin\Api\HyvaGridArrayProviderInterface;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory;
use Perspective\StoreReview\Api\StoreReviewRepositoryInterface;

class ReviewsArrayProvider implements HyvaGridArrayProviderInterface
{

    public function __construct(
        private readonly CollectionFactory $collectionFactory,
    ) {
    }

    public function getHyvaGridData(): array
    {
        $collection = $this->collectionFactory->create();

        $reviewEntityTable = $collection->getTable('review_entity');
        $collection->join(
            $reviewEntityTable,
            'main_table.entity_id=' . $reviewEntityTable . '.entity_id',
            ['entity_code']
        );

        $collection->addFilter(
            'entity',
            $collection->getConnection()->quoteInto(
                $reviewEntityTable . '.entity_code=?',
                StoreReviewRepositoryInterface::ENTITY_CODE
            ),
            'string'
        );

        return array_map(static fn($item) => [
            'review_id' => $item->getReviewId(),
            'created_at' => $item->getCreatedAt(),
            'status' => $item->getStatusId(),
            'title' => $item->getTitle(),
            'detail' => $item->getDetail(),
            'nickname' => $item->getNickname(),
            'customer_id' => $item->getCustomerId(),
        ], $collection->getItems());
    }
}
