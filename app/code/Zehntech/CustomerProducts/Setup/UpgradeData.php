<?php
/*
 * Zehntech_offlinepayment
 * author @zehntech
 */

namespace Zehntech\CustomerProducts\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Customer\Model\Customer;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Customer\Api\CustomerMetadataInterface;

class UpgradeData implements UpgradeDataInterface 
{

    private $eavSetupFactory;
    private $eavConfig;
    private $attributeResource;

    public function __construct(
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Customer\Model\ResourceModel\Attribute $attributeResource
    )
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $eavConfig;
        $this->attributeResource = $attributeResource;
    }


    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '0.0.2') < 0) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);


            $attributeSetId = $eavSetup->getDefaultAttributeSetId(Customer::ENTITY);
            $attributeGroupId = $eavSetup->getDefaultAttributeGroupId(Customer::ENTITY);


            $eavSetup->addAttribute(Customer::ENTITY, 'customer_ean', [

                'type' => 'varchar',
                'label' => 'EAN Number',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => true,
                'position' => 999,
                'system' => 0,
              ]);


            $attribute = $this->eavConfig->getAttribute(Customer::ENTITY, 'customer_ean');
            $attribute->setdata('attribute_set_id', $attributeSetId);
            $attribute->setData('attribute_group_id', $attributeGroupId);

            $eavSetup->addAttributeToSet(
                CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                null,
                'customer_ean');

            $attribute->setData('used_in_forms', [
                'adminhtml_customer',
                'customer_account_create',
                'customer_account_edit'
            ]);
            $this->attributeResource->save($attribute);
        }
        $setup->endSetup();
    }
}

?>