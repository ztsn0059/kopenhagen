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

class InstallData implements InstallDataInterface
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


    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {

        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);


        $attributeSetId = $eavSetup->getDefaultAttributeSetId(Customer::ENTITY);
        $attributeGroupId = $eavSetup->getDefaultAttributeGroupId(Customer::ENTITY);


        $eavSetup->addAttribute(Customer::ENTITY, 'pay_from_invoice', [

            'type' => 'varchar',
            'label' => 'Pay From Invoice',
            'input' => 'select',
            'required' => false,
            'visible' => true,
            'user_defined' => true,
            'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
            'sort_order' => 996,
            'position' => 996,
            'default' => 0,
            'system' => 0,
            'option' =>
                array(
                    'values' =>
                        array(
                            0 => 'No',
                            1 => 'Yes',
                        ),
                ),
        ]);


        $attribute = $this->eavConfig->getAttribute(Customer::ENTITY, 'pay_from_invoice');
        $attribute->setdata('attribute_set_id', $attributeSetId);
        $attribute->setData('attribute_group_id', $attributeGroupId);

        $eavSetup->addAttributeToSet(
            CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
            null,
            'pay_from_invoice');

        $attribute->setData('used_in_forms', [
            'adminhtml_customer',
            'customer_account_create',
            'customer_account_edit'
        ]);
        $this->attributeResource->save($attribute);

    }
}

?>