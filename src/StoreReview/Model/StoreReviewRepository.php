<?php

namespace Perspective\StoreReview\Model;

use Magento\Review\Model\RatingFactory;
use Magento\Review\Model\ResourceModel\Rating;
use Magento\Review\Model\ResourceModel\Rating\Option\Vote\Collection as OptionVoteCollection;
use Magento\Review\Model\ResourceModel\Rating\Option\Vote\CollectionFactory as OptionVoteCollectionFactory;
use Magento\Review\Model\Review;
use Magento\Review\Model\ReviewFactory;
use Perspective\StoreReview\Api\StoreReviewRepositoryInterface;

/**
 * @see \Magento\Review\Block\Product\View
 * @see \Magento\ReviewGraphQl\Model\Review\AddReviewToProduct
 */
class StoreReviewRepository implements StoreReviewRepositoryInterface
{

    public function __construct(
        private readonly ReviewFactory               $reviewFactory,
        private readonly RatingFactory               $ratingFactory,
        private readonly OptionVoteCollectionFactory $ratingOptionCollectionFactory
    ) {
    }

    private function addRatingVotes(Review $review, array $ratings): void
    {
        foreach ($ratings as $rating) {
            $ratingModel = $this->ratingFactory->create();
            $ratingModel->setRatingId(base64_decode($rating['id']))
                        ->setReviewId($review->getId())
                        ->setCustomerId($review->getCustomerId())
                        ->addOptionVote(base64_decode($rating['value_id']), $review->getEntityPkValue());
        }
    }

    private function getReviewRatingVotes(int $reviewId, int $storeId): OptionVoteCollection
    {
        $votesCollection = $this->ratingOptionCollectionFactory->create();
        $votesCollection->setReviewFilter($reviewId)/*->setStoreFilter($storeId)*/ ->addRatingInfo($storeId);

        return $votesCollection;
    }

    public function save(array $data, array $ratings, int $entityId, ?int $customerId, int $storeId): Review
    {
        $review = $this->reviewFactory->create()->setData($data);
        $review->unsetData('review_id');

        $review->setEntityId($review->getEntityIdByCode(self::ENTITY_CODE))
               ->setEntityPkValue($entityId)
               ->setStatusId(Review::STATUS_PENDING)
               ->setCustomerId($customerId)
               ->setStoreId($storeId)
               ->setStores([$storeId])
               ->save();

        $this->addRatingVotes($review, $ratings);
        $review->aggregate();

        $votesCollection = $this->getReviewRatingVotes((int)$review->getId(), $storeId);
        $review->setData('rating_votes', $votesCollection);

        return $review;
    }
}
