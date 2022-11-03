<?php

namespace Perspective\StoreReview\Controller\Adminhtml\Reviews;

use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Review\Controller\Adminhtml\Product\MassUpdateStatus as ProductMassUpdateStatus;

class MassUpdateStatus implements HttpPostActionInterface
{

    public function __construct(
        private readonly RequestInterface        $request,
        private readonly ProductMassUpdateStatus $massUpdateStatus,
        private readonly ResultFactory           $resultFactory
    ) {
    }

    public function execute()
    {
        $this->request->setParams([
            ...$this->request->getParams(),
            'status' => 1,
        ]);

        $this->massUpdateStatus->execute();

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('perspective_storereview/reviews/index');
        return $resultRedirect;
    }
}
