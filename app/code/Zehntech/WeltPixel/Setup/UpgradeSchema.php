<?php

/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Zehntech\WeltPixel\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Upgrade the OwlCarouselSlider module DB scheme
 * Add new column to weltpixel_owlcarouselslider_banners table
 */
class UpgradeSchema implements UpgradeSchemaInterface {

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context) {

        $setup->startSetup();
        if (version_compare($context->getVersion(), '1.0.5', '<')) {

            if ($setup->getConnection()->isTableExists('weltpixel_owlcarouselslider_banners') == true) {

                /* $setup->getConnection()->addColumn(
                  $setup->getTable('weltpixel_owlcarouselslider_banners'), 'sub_content', [
                  'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                  'nullable' => true,
                  'default' => '',
                  'afters' => 'description',
                  'comment' => 'Sub Content']
                  ); */

                $setup->getConnection()->addColumn(
                        $setup->getTable('weltpixel_owlcarouselslider_banners'), 'background_image', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'default' => '',
                    'afters' => 'background_image',
                    'comment' => 'Background Image']
                );
            }
        }
        if (version_compare($context->getVersion(), '1.0.7','<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('weltpixel_owlcarouselslider_sliders'),
                'image',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length'   => '255',
                    'nullable' => true,
                    'comment'  => 'Background IMage'
                ]
            );
        }

        $setup->endSetup();
    }

}
