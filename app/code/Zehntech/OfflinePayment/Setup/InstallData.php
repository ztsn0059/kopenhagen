<?php
/*
 * Zehntech_offlinepayment
 * author @zehntech
 */

namespace Zehntech\OfflinePayment\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Customer\Model\Customer;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;

class InstallData implements InstallDataInterface
{

    private $eavConfig;

    public function __construct(
        Config $eavConfig,
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory
    )
    {
        $this->eavConfig = $eavConfig;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
    }


    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {

        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        /** @var $attributeSet AttributeSet */
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $customerSetup->addAttribute(Customer::ENTITY, 'pay_from_invoice', [
            'type' => 'int',
            'label' => 'Pay From Invoice',
            'input' => 'boolean',
            'required' => false,
            'visible' => true,
            'source' => '',
            'backend' => '',
            'user_defined' => false,
            'is_user_defined' => false,
            'sort_order' => 1000,
            'is_used_in_grid' => false,
            'is_visible_in_grid' => false,
            'is_filterable_in_grid' => false,
            'is_searchable_in_grid' => false,
            'position' => 1000,
            'default' => 0,
            'system' => 0,
         ]);

         $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'pay_from_invoice')
            ->addData([
                 'attribute_set_id' => $attributeSetId,
                 'attribute_group_id' => $attributeGroupId,
                 'used_in_forms' => ['adminhtml_customer'],
          ]);

         $attribute->save();
         $setup->endSetup();

    }
}

?>