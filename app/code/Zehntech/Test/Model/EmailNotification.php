<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Zehntech\Test\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Customer\Helper\View as CustomerViewHelper;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Exception\LocalizedException;
use Magento\Customner\Model\CustomerRegistry;
/**
 * Customer email notification
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EmailNotification extends \Magento\Customer\Model\EmailNotification
{
        const XML_PATH_FORGOT_EMAIL_IDENTITY = 'customer/password/forgot_email_identity';

    const XML_PATH_RESET_PASSWORD_TEMPLATE = 'customer/password/reset_password_template';

    const XML_PATH_CHANGE_EMAIL_TEMPLATE = 'customer/account_information/change_email_template';

    const XML_PATH_CHANGE_EMAIL_AND_PASSWORD_TEMPLATE =
        'customer/account_information/change_email_and_password_template';

    const XML_PATH_FORGOT_EMAIL_TEMPLATE = 'customer/password/forgot_email_template';

    const XML_PATH_REMIND_EMAIL_TEMPLATE = 'customer/password/remind_email_template';

    const XML_PATH_REGISTER_EMAIL_IDENTITY = 'customer/create_account/email_identity';

    const XML_PATH_REGISTER_EMAIL_TEMPLATE = 'customer/create_account/email_template';

    const XML_PATH_REGISTER_NO_PASSWORD_EMAIL_TEMPLATE = 'customer/create_account/email_no_password_template';

    const XML_PATH_CONFIRM_EMAIL_TEMPLATE = 'customer/create_account/email_confirmation_template';

    const XML_PATH_CONFIRMED_EMAIL_TEMPLATE = 'customer/create_account/email_confirmed_template';

    /**
     * self::NEW_ACCOUNT_EMAIL_REGISTERED               welcome email, when confirmation is disabled
     *                                                  and password is set
     * self::NEW_ACCOUNT_EMAIL_REGISTERED_NO_PASSWORD   welcome email, when confirmation is disabled
     *                                                  and password is not set
     * self::NEW_ACCOUNT_EMAIL_CONFIRMED                welcome email, when confirmation is enabled
     *                                                  and password is set
     * self::NEW_ACCOUNT_EMAIL_CONFIRMATION             email with confirmation link
     */
    const TEMPLATE_TYPES = [
        self::NEW_ACCOUNT_EMAIL_REGISTERED => self::XML_PATH_REGISTER_EMAIL_TEMPLATE,
        self::NEW_ACCOUNT_EMAIL_REGISTERED_NO_PASSWORD => self::XML_PATH_REGISTER_NO_PASSWORD_EMAIL_TEMPLATE,
        self::NEW_ACCOUNT_EMAIL_CONFIRMED => self::XML_PATH_CONFIRMED_EMAIL_TEMPLATE,
        self::NEW_ACCOUNT_EMAIL_CONFIRMATION => self::XML_PATH_CONFIRM_EMAIL_TEMPLATE,
    ];
    public $customerRegistry;

    /**
     * @var StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var TransportBuilder
     */
    public $transportBuilder;

    /**
     * @var CustomerViewHelper
     */
    public $customerViewHelper;

    /**
     * @var DataObjectProcessor
     */
    public $dataProcessor;

    /**
     * @var ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * @var SenderResolverInterface
     */
    public $senderResolver;
    public function __construct(
        CustomerRegistry $customerRegistry,
        StoreManagerInterface $storeManager,
        TransportBuilder $transportBuilder,
        CustomerViewHelper $customerViewHelper,
        DataObjectProcessor $dataProcessor,
        ScopeConfigInterface $scopeConfig,
        SenderResolverInterface $senderResolver = null
    ) {
        $this->customerRegistry = $customerRegistry;
        $this->storeManager = $storeManager;
        $this->transportBuilder = $transportBuilder;
        $this->customerViewHelper = $customerViewHelper;
        $this->dataProcessor = $dataProcessor;
        $this->scopeConfig = $scopeConfig;
        $this->senderResolver = $senderResolver ?: ObjectManager::getInstance()->get(SenderResolverInterface::class);
        parent::__construct($customerRegistry,$storeManager,$transportBuilder,$customerViewHelper,$dataProcessor,$scopeConfig,$senderResolver=null);
    }

   
   

   
    /**
     * Send corresponding email template
     *
     * @param CustomerInterface $customer
     * @param string $template configuration path of email template
     * @param string $sender configuration path of email identity
     * @param array $templateParams
     * @param int|null $storeId
     * @param string $email
     * @return void
     * @throws \Magento\Framework\Exception\MailException
     */
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

    /**
     * Create an object with data merged from Customer and CustomerSecure
     *
     * @param CustomerInterface $customer
     * @return \Magento\Customer\Model\Data\CustomerSecure
     */
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

    /**
     * Get either first store ID from a set website or the provided as default
     *
     * @param CustomerInterface $customer
     * @param int|string|null $defaultStoreId
     * @return int
     */
    public function getWebsiteStoreId($customer, $defaultStoreId = null)
    {
        if ($customer->getWebsiteId() != 0 && empty($defaultStoreId)) {
            $storeIds = $this->storeManager->getWebsite($customer->getWebsiteId())->getStoreIds();
            $defaultStoreId = reset($storeIds);
        }
        return $defaultStoreId;
    }

    /**
     * Send email with new customer password
     *
     * @param CustomerInterface $customer
     * @return void
     */
    public function passwordReminder(CustomerInterface $customer)
    {
        $storeId = $customer->getStoreId();
        if (!$storeId) {
            $storeId = $this->getWebsiteStoreId($customer);
        }

        $customerEmailData = $this->getFullCustomerObject($customer);

        $this->sendEmailTemplate(
            $customer,
            self::XML_PATH_REMIND_EMAIL_TEMPLATE,
            self::XML_PATH_FORGOT_EMAIL_IDENTITY,
            ['customer' => $customerEmailData, 'store' => $this->storeManager->getStore($storeId)],
            $storeId
        );
    }

    /**
     * Send email with reset password confirmation link
     *
     * @param CustomerInterface $customer
     * @return void
     */
    public function passwordResetConfirmation(CustomerInterface $customer)
    {
        $storeId = $this->storeManager->getStore()->getId();
        if (!$storeId) {
            $storeId = $this->getWebsiteStoreId($customer);
        }

        $customerEmailData = $this->getFullCustomerObject($customer);

        $this->sendEmailTemplate(
            $customer,
            self::XML_PATH_FORGOT_EMAIL_TEMPLATE,
            self::XML_PATH_FORGOT_EMAIL_IDENTITY,
            ['customer' => $customerEmailData, 'store' => $this->storeManager->getStore($storeId)],
            $storeId
        );
    }

    /**
     * Send email with new account related information
     *
     * @param CustomerInterface $customer
     * @param string $type
     * @param string $backUrl
     * @param string $storeId
     * @param string $sendemailStoreId
     * @return void
     * @throws LocalizedException
     */
    public function newAccount(
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
        $street = $address['street'];
        $eanNumber = $customerEmailData->getCustomAttribute('customer_ean');
        $eanNumber = $eanNumber['value'];
        $this->sendEmailTemplate(
            $customer,
            $types[$type],
            self::XML_PATH_REGISTER_EMAIL_IDENTITY,
            ['customer' => $customerEmailData, 'back_url' => $backUrl, 'store' => $store, 'street' => $street[0], 'city'=> $address['city'], 'country' => $address['country_id'], 'telephone' => $address['telephone'],' company' => $address['company'], 'ean' => $eanNumber],
            $storeId
        );
    }
}
