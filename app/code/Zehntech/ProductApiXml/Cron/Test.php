<?php

namespace Zehntech\ProductApiXml\Cron;

class Test
{
	public function __construct(\Zehntech\ProductApiXml\Logger\Logger $logger)
	{
		$this->_logger = $logger;
	}

	public function execute()
	{

		// $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/cron.log');
		// $logger = new \Zend\Log\Logger();
		// $logger->addWriter($writer);
		// $logger->info(__METHOD__);
		$message = "  new writer  ";
		$this->_logger->info("   written continue/n  ");

	  $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/entrylog1.log');
      $logger = new \Zend\Log\Logger();
      $logger->addWriter($writer);
      $logger->info($message);

		return $this;

	}
}