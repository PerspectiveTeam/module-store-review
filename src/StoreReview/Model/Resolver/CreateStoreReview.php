<?php

namespace Perspective\StoreReview\Model\Resolver;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Review\Model\Review;
use Magento\Store\Api\Data\StoreInterface;
use Perspective\ReviewTree\Api\Data\ReviewFieldsEx;
use Perspective\ReviewTree\Model\ConfigManager as ReviewTreeConfigManager;
use Perspective\ReviewTree\Setup\Patch\Data\VerifiedReviewerCustomerAttribute;
use Perspective\StoreReview\Api\StoreReviewRepositoryInterface;
use Perspective\StoreReview\Model\ConfigManager;

class CreateStoreReview implements ResolverInterface
{

    public function __construct(
        private readonly CustomerRepositoryInterface    $customerRepository,
        private readonly ConfigManager                  $configManager,
        private readonly ReviewTreeConfigManager        $reviewTreeConfigManager,
        private readonly StoreReviewRepositoryInterface $storeReviewRepository
    ) {
    }

    public function getInputData(array $input): array
    {
        return [
            ReviewFieldsEx::PARENT_ID => $input[ReviewFieldsEx::PARENT_ID],
            'nickname' => $input['nickname'],
            'title' => $input['summary'],
            'detail' => $input['text'],
        ];
    }

    public function getReviewData(Review $review): array
    {
        return [
            'parent_id' => $review->getData(ReviewFieldsEx::PARENT_ID),
            'summary' => $review->getSummary(),
            'text' => $review->getDetail(),
            'nickname' => $review->getNickname(),
            'created_at' => $review->getCreatedAt(),
        ];
    }

    /**
     * @throws GraphQlAuthorizationException
     * @throws GraphQlNoSuchEntityException
     * @throws LocalizedException
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (false === $this->configManager->isEnabled()) {
            throw new GraphQlAuthorizationException(__('Creating store reviews are not currently available.'));
        }

        $customer = null; /** @var null|CustomerInterface $isAuthorVerified */
        $isAuthorVerified = false;

        if (false !== $context->getExtensionAttributes()->getIsCustomer()) {
            $customer = $this->customerRepository->getById((int)$context->getUserId());
            $isAuthorVerified = $customer->getCustomAttribute(VerifiedReviewerCustomerAttribute::ATTRIBUTE_CODE)
                                         ?->getValue();
        }

        if (!$customer && !$this->configManager->isGuestAllowToWrite()) {
            throw new GraphQlAuthorizationException(__('Guest customers aren\'t allowed to add store reviews.'));
        }

        $ratings = $args['input']['ratings'];
        $data = array_merge([
            ReviewFieldsEx::IS_AUTHOR_VERIFIED => $isAuthorVerified,
        ], $this->getInputData($args['input']));

        if (!$data['nickname'] && $generator = $this->reviewTreeConfigManager->getAutogenerateNicknameStrategy()) {
            $data['nickname'] = $generator->compute($data, $customer);
        }

        if (!$data['title'] && $generator = $this->reviewTreeConfigManager->getAutogenerateTitleStrategy()) {
            $data['title'] = $generator->compute($data, $customer);
        }

        /** @var StoreInterface $store */
        $store = $context->getExtensionAttributes()->getStore();

        $review = $this->storeReviewRepository->save($data, $ratings, $store->getId(), $customer?->getId(), $store->getId());
        return ['review' => $this->getReviewData($review)];
    }
}
