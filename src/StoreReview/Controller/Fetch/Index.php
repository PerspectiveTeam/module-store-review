<?php

namespace Perspective\StoreReview\Controller\Fetch;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

class Index implements HttpPostActionInterface
{

    public function __construct(
        private readonly RequestInterface $request,
        private readonly JsonFactory      $resultJsonFactory,
        private readonly PageFactory      $pageFactory,
    ) {
    }

    private function renderItems(int $p): array
    {
        $listReviewsPage = $this->pageFactory->create();
        $listReviewsPage->addHandle('perspective_storereview_index_index');

        $reviews = $listReviewsPage->getLayout()
                                   ->getBlock('review_list')
                                   ->setData('p', $p)
                                   ->getReviewsCollection()
                                   ->load()
                                   ->addRateVotes();

        $itemBlock = $listReviewsPage->getLayout()->getBlock('review_list.item');

        return [
            'html' => implode("\n", array_map(
                    fn($review) => $itemBlock->setData('review', $review)->toHtml(),
                    $reviews->getItems())
            ),
            'can_next' => $reviews->getLastPageNumber() > $p,
        ];
    }

    public function execute(): ResultInterface
    {
        $p = $this->request->getParam('p') ?? 0;

        $result = $this->resultJsonFactory->create();
        $result->setData($this->renderItems($p));

        return $result;
    }
}
