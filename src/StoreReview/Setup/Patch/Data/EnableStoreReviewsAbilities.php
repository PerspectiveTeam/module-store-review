<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Perspective\StoreReview\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Perspective\StoreReview\Api\StoreReviewRepositoryInterface;

class EnableStoreReviewsAbilities implements DataPatchInterface
{

    /**
     * PatchInitial constructor.
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        //Fill table review/review_entity
        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('review_entity'),
            ['entity_code' => StoreReviewRepositoryInterface::ENTITY_CODE]
        );

        //Fill table rating/rating_entity
        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('rating_entity'),
            ['entity_code' => StoreReviewRepositoryInterface::ENTITY_CODE]
        );
        $entityId = $this->moduleDataSetup->getConnection()->lastInsertId(
            $this->moduleDataSetup->getTable('rating_entity')
        );

        //Fill table rating/rating
        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('rating'),
            ['entity_id' => $entityId, 'rating_code' => StoreReviewRepositoryInterface::RATING_CODE, 'position' => 0]
        );

        //Fill table rating/rating_option
        $ratingId = $this->moduleDataSetup->getConnection()->lastInsertId(
            $this->moduleDataSetup->getTable('rating')
        );

        $optionData = [];
        for ($i = 1; $i <= 5; $i++) {
            $optionData[] = ['rating_id' => $ratingId, 'code' => (string)$i, 'value' => $i, 'position' => $i];
        }

        $this->moduleDataSetup->getConnection()->insertMultiple(
            $this->moduleDataSetup->getTable('rating_option'),
            $optionData
        );

        // rating store
        // rating title
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
