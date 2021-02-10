<?php

namespace Zehntech\FmePostEvent\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class ApproveEventMail extends AbstractHelper
{

    const XML_PATH_EMAIL_TEMPLATE_FIELD = 'zehntech_fmepostevent_approve_event_template';

    /**
     * Sender email config path - from default CONTACT extension
     */
    const XML_PATH_EMAIL_SENDER = 'contact/email/sender_email_identity';

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    private $inlineTranslation;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    private $transportBuilder;

    /**
     * Demo constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation, \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
    )
    {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
    }

    /**
     * Return store configuration value of your template field that which id you set for template
     *
     * @param string $path
     * @param int $storeId
     * @return mixed
     */
    private function getConfigValue($path, $storeId)
    {
        return $this->scopeConfig->getValue(
            $path, ScopeInterface::SCOPE_STORE, $storeId
        );
    }

    /**
     * Return store
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStore()
    {
        return $this->storeManager->getStore();
    }

    /**
     * @param $variable
     * @param $receiverInfo
     * @param $templateId
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function generateTemplate($variable, $receiverInfo, $templateId)
    {
        $this->transportBuilder->setTemplateIdentifier($templateId)
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->storeManager->getStore()->getId(),
                ]
            )
            ->setTemplateVars($variable)
            ->setFrom($this->emailSender())
            ->addTo($receiverInfo['email'], $receiverInfo['name']);

        return $this;
    }

    /**
     * Return email for sender header
     * @return mixed
     */
    public function emailSender()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EMAIL_SENDER, ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param $name
     * @param $email
     * @return $this
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function notify($name, $email, $eventTitle)
    {

        /* Receiver Detail */
        $receiverInfo = [
            'name' => $name,
            'email' => $email
        ];

        /* Assign values for your email template variables  */

        $variable = [
            'admin' => 'Pave Tech',
            'subject' => 'Event Status',
            'name' => $name,
            'email' => $email,
            'title' => $eventTitle
        ];

        //$templateId = $this->getConfigValue(self::XML_PATH_EMAIL_TEMPLATE_FIELD, $this->getStore()->getStoreId());
        $this->inlineTranslation->suspend();
        $this->generateTemplate($variable, $receiverInfo, 'zehntech_fmepostevent_approve_event_template');
        $transport = $this->transportBuilder->getTransport();
        $transport->sendMessage();
        $this->inlineTranslation->resume();

        return $this;
    }

}

?>