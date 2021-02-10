<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Zehntech\HomeProducts\Setup;


use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Catalog\Model\Category;

/**
 * Upgrade Data script
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpgradeData implements UpgradeDataInterface {



    public function __construct(
   \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
    ) {
         $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context) {
        
        $setup->startSetup();
            if (version_compare($context->getVersion(), '0.0.2') < 0) {

                $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

                $attributeSetId = $eavSetup->getDefaultAttributeSetId(Category::ENTITY);
                $attributeGroupId = $eavSetup->getDefaultAttributeGroupId(Category::ENTITY);

                $eavSetup->addAttribute(Category::ENTITY, 'add_to_nav', [
                'type' => 'varchar',
                'label' => 'Add To Navigation',
                'input' => 'select',
                'required' => false,
                'visible' => true,
                'user_defined' => true,
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'sort_order' => 4,
                'default' => 0,
                'group' => 'General Information',
                'option' =>
                    array(
                        'values' =>
                            array(
                                0 => 'No',
                                1 => 'Yes',
                            ),
                    ),
            ]);

                $eavSetup->addAttribute(Category::ENTITY, 'nav_child', [
                'type' => 'varchar',
                'label' => 'Enable Child',
                'input' => 'select',
                'required' => false,
                'visible' => true,
                'user_defined' => true,
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'sort_order' => 5,
                'default' => 0,
                'group' => 'General Information',
                'option' =>
                    array(
                        'values' =>
                            array(
                                0 => 'No',
                                1 => 'Yes',
                            ),
                    ),
            ]);
        }
        $setup->endSetup();
    }

}
