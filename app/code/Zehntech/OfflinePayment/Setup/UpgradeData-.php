<?php
/*
 * Zehntech_offlinepayment
 * author @zehntech
 */

namespace Zehntech\OfflinePayment\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Customer\Model\Customer;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;

class UpgradeData implements UpgradeDataInterface 
{

    private $eavSetupFactory;
    private $eavConfig;
    private $attributeResource;

    public function __construct(
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Customer\Model\ResourceModel\Attribute $attributeResource,
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory
    )
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $eavConfig;
        $this->attributeResource = $attributeResource;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
    }


    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '0.0.2') < 0) {
            $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

            $customerSetup->updateAttribute(
                \Magento\Customer\Model\Customer::ENTITY,
                'pay_from_invoice',                    
                'required',
                true
            );
            $Attribute = $customerSetup->getEavConfig()->getAttribute( \Magento\Customer\Model\Customer::ENTITY, 'pay_from_invoice');
            $Attribute->setData(
                'used_in_forms',
                [
                    'adminhtml_customer',
                    'customer_account_create',
                    'customer_account_edit'
                ]);
            $Attribute->save();
            $setup->endSetup();
        }
        if (version_compare($context->getVersion(), '0.0.3') < 0) {
            $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

            $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
            $attributeSetId = $customerEntity->getDefaultAttributeSetId();

            /** @var $attributeSet AttributeSet */
            $attributeSet = $this->attributeSetFactory->create();
            $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

            $customerSetup->addAttribute(Customer::ENTITY, 'invoice_pay', [
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

             $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'invoice_pay')
                ->addData([
                     'attribute_set_id' => $attributeSetId,
                     'attribute_group_id' => $attributeGroupId,
                     'used_in_forms' => ['adminhtml_customer'],
              ]);

             $attribute->save();
             $setup->endSetup();
        }
        
    }
}