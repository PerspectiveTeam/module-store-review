<?php

namespace Perspective\StoreReview\Controller\Adminhtml\Reviews;

use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Review\Controller\Adminhtml\Product\MassDelete as ProductMassDelete;

class MassDelete implements HttpPostActionInterface
{

    public function __construct(
        private readonly ProductMassDelete $massDelete,
        private readonly ResultFactory     $resultFactory
    ) {
    }

    public function execute()
    {
        $this->massDelete->execute();

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('perspective_storereview/reviews/index');
        return $resultRedirect;
    }
}
