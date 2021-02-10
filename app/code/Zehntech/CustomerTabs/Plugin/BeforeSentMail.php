<?php

namespace Zehntech\CustomerTabs\Plugin;

use Magento\Customer\Model\EmailNotification;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Customer\Helper\View as CustomerViewHelper;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Exception\LocalizedException;
use Magento\Customer\Model\EmailNotificationInterface;

class BeforeSentMail
{
    const XML_PATH_REGISTER_EMAIL_IDENTITY = 'customer/create_account/email_identity';
    const NEW_ACCOUNT_EMAIL_REGISTERED = 'customer/create_account/email_template';
    const XML_PATH_REGISTER_EMAIL_TEMPLATE = 'customer/create_account/email_template';
    const XML_PATH_REGISTER_NO_PASSWORD_EMAIL_TEMPLATE = 'customer/create_account/email_no_password_template';
    const XML_PATH_CONFIRMED_EMAIL_TEMPLATE = 'customer/create_account/email_confirmed_template';  
    const XML_PATH_CONFIRM_EMAIL_TEMPLATE = 'customer/create_account/email_confirmation_template';

    const TEMPLATE_TYPES = [
        EmailNotificationInterface::NEW_ACCOUNT_EMAIL_REGISTERED => self::XML_PATH_REGISTER_EMAIL_TEMPLATE,
        EmailNotificationInterface::NEW_ACCOUNT_EMAIL_REGISTERED_NO_PASSWORD => self::XML_PATH_REGISTER_NO_PASSWORD_EMAIL_TEMPLATE,
        EmailNotificationInterface::NEW_ACCOUNT_EMAIL_CONFIRMED => self::XML_PATH_CONFIRMED_EMAIL_TEMPLATE,
        EmailNotificationInterface::NEW_ACCOUNT_EMAIL_CONFIRMATION => self::XML_PATH_CONFIRM_EMAIL_TEMPLATE,
    ];

    public function __construct(StoreManagerInterface $storeManager, \Magento\Customer\Model\AccountManagement $accountManagement, ScopeConfigInterface $scopeConfig, CustomerRegistry $customerRegistry, TransportBuilder $transportBuilder, CustomerViewHelper $customerViewHelper,DataObjectProcessor $dataProcessor, SenderResolverInterface $senderResolver = null){
        $this->storeManager = $storeManager;
        $this->accountManagement = $accountManagement;
        $this->scopeConfig = $scopeConfig;
        $this->customerRegistry = $customerRegistry;
        $this->transportBuilder = $transportBuilder;
        $this->customerViewHelper = $customerViewHelper;
        $this->dataProcessor = $dataProcessor;
        $this->senderResolver = $senderResolver ?: ObjectManager::getInstance()->get(SenderResolverInterface::class);

    }


    public function aroundNewAccount(
        EmailNotification $emailNotification,
        \Closure $proceed,
        CustomerInterface $customer,
        $type = self::NEW_ACCOUNT_EMAIL_REGISTERED,
        $backUrl = '',
        $storeId = 0,
        $sendemailStoreId = null
    ) {
        $types = self::TEMPLATE_TYPES;
        if (!isset($types[$type])) {
            throw new LocalizedException(
                __('The transactional account email type is incorrect. Verify and try again.')
            );
        }
        if (!$storeId) {
            $storeId = $this->getWebsiteStoreId($customer, $sendemailStoreId);
        }
        $store = $this->storeManager->getStore($customer->getStoreId());
        $customerEmailData = $this->getFullCustomerObject($customer);
        $address = $customerEmailData->getAddresses();
        $address = $address[0];
        $street = $address['street'][0];
        $ean = $customerEmailData->getData('custom_attributes');
        $eanNumber = $ean[0]['value'];
        $this->sendEmailTemplate(
            $customer,
            $types[$type],
            self::XML_PATH_REGISTER_EMAIL_IDENTITY,
            ['customer' => $customerEmailData, 'back_url' => $backUrl, 'store' => $store, 'street' => $street, 'city'=> $address['city'], 'country' => $address['country_id'], 'telephone' => $address['telephone'], 'postcode' => $address['postcode'], 'company' => $address['company'], 'ean' => $eanNumber],
            $storeId
        );
    }



    public function getWebsiteStoreId($customer, $defaultStoreId = null)
    {
        if ($customer->getWebsiteId() != 0 && empty($defaultStoreId)) {
            $storeIds = $this->storeManager->getWebsite($customer->getWebsiteId())->getStoreIds();
            $defaultStoreId = reset($storeIds);
        }
        return $defaultStoreId;
    }

    public function getFullCustomerObject($customer)
    {
        // No need to flatten the custom attributes or nested objects since the only usage is for email templates and
        // object passed for events
        $mergedCustomerData = $this->customerRegistry->retrieveSecureData($customer->getId());
        $customerData = $this->dataProcessor
            ->buildOutputDataArray($customer, \Magento\Customer\Api\Data\CustomerInterface::class);
        $mergedCustomerData->addData($customerData);
        $mergedCustomerData->setData('name', $this->customerViewHelper->getCustomerName($customer));
        return $mergedCustomerData;
    }

    public function sendEmailTemplate(
        $customer,
        $template,
        $sender,
        $templateParams = [],
        $storeId = null,
        $email = null
    ) {
        $templateId = $this->scopeConfig->getValue($template, 'store', $storeId);
        if ($email === null) {
            $email = $customer->getEmail();
        }

        /** @var array $from */
        $from = $this->senderResolver->resolve(
            $this->scopeConfig->getValue($sender, 'store', $storeId),
            $storeId
        );

        $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
            ->setTemplateOptions(['area' => 'frontend', 'store' => $storeId])
            ->setTemplateVars($templateParams)
            ->setFrom($from)
            ->addTo($email, $this->customerViewHelper->getCustomerName($customer))
            ->getTransport();

        $transport->sendMessage();
    }
}