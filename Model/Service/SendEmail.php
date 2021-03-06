<?php

namespace Flancer32\Csp\Model\Service;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Escaper;

class SendEmail
{
    /**
     * @var TransportBuilder
     */
    private $transportBuilder;
    /**
     * @var StateInterface
     */
    private $inlineTranslation;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var Escaper
     */
    private $escaper;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        ScopeConfigInterface $scopeConfig,
        Escaper $escaper,
        Context $context)
    {
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->scopeConfig = $scopeConfig;
        $this->escaper = $escaper;
        $this->logger = $context->getLogger();
    }

    /**
     * @param $reportHtml
     * @return array
     * @throws EmailSendException
     */
    public function with($reportHtml)
    {
        $recepients = [];
        $this->inlineTranslation->suspend();
        try {
            $sender = [
                'name'  => $this->escaper->escapeHtml($this->getMailSenderClearName()),
                'email' => $this->escaper->escapeHtml($this->getMailSenderAddress()),
            ];
            $transportBuilder =
                $this->transportBuilder->setTemplateIdentifier('security_csp_report')->setTemplateOptions(
                    [
                        'area'  => \Magento\Framework\App\Area::AREA_ADMINHTML,
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                    ]
                )->setTemplateVars(
                    [
                        'body' => $reportHtml,
                    ]
                )->setFrom($sender);

            $recepients = $this->getMailRecipients();
            foreach ($recepients as $recipient) {
                $transportBuilder->addTo($recipient);
            }
            $transportBuilder->getTransport()->sendMessage();
        } finally {
            $this->inlineTranslation->resume();
            return $recepients;
        }
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function getMailRecipients(): array
    {
        $recipientsSerialized = $this->scopeConfig->getValue('fl32_csp/cron/developer_email_csv');
        if (!$recipientsSerialized) {
            throw new EmailSendException('aborted due to missing recepients');
        }
        $recipients = explode(',', $recipientsSerialized);
        return $recipients;
    }

    private function getMailSenderAddress(): string
    {
        $mailSenderAddress = $this->scopeConfig->getValue('fl32_csp/cron/report_email_from');
        if (!$mailSenderAddress) {
            throw new EmailSendException('aborted due to missing email sender address');
        }
        return $mailSenderAddress;
    }

    private function getMailSenderClearName(): string
    {
        $mailSenderClearName = $this->scopeConfig->getValue('fl32_csp/cron/report_email_from_clear_name');
        if (!$mailSenderClearName) {
            $mailSenderClearName = $this->getMailSenderAddress();
        }
        return $mailSenderClearName;
    }
}
