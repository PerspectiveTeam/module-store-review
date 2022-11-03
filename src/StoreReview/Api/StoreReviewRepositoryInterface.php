<?php

namespace Perspective\StoreReview\Api;

use Magento\Review\Model\Review;

interface StoreReviewRepositoryInterface
{

    public const ENTITY_CODE = 'store';

    public const RATING_CODE = 'Store';

    public function save(array $data, array $ratings, int $entityId, ?int $customerId, int $storeId): Review;
}
