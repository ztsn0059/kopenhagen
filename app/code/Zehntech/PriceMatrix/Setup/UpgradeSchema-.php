<?php

namespace Zehntech\PriceMatrix\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * {@inheritdoc}
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;
        $installer->startSetup();
        if (version_compare($context->getVersion(), '0.0.1', '<')) {

        	$olstable = $installer->getTable('zt_ols_price_matrix');
        	if ($installer->getConnection()->isTableExists($olstable) != true) {
        		$installer->getConnection()
                    ->newTable($olstable)
                	->addColumn(
                	    'id',
                	    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                	    null,
                	    [
                	        'identity' => true,
                	        'nullable' => false,
                	        'primary' => true,
                	        'unsigned' => true,
                	    ],
                	    'ID'
                	)
                	->addColumn(
                	    'min_price',
                	    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                	    255,
                	    ['nullable => false'],
                	    'Min Price'
                	)
                	->addColumn(
                	    'max_price',
                	    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                	    255,
                	    ['nullable => false'],
                	    'Max Price'
                	)
                	->addColumn(
                	    'markup',
                	    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                	    255,
                	    [],
                	    'Mark Up'
                	)
                	->addColumn(
                	    'created_at',
                	    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                	    null,
                	    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                	    'Created At'
                	)->addColumn(
                	    'updated_at',
                	    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                	    null,
                	    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                	    'Updated At')
                	->setComment('Ols Price Matrix Table');
                	$installer->getConnection()->createTable($table);



        		$table = $installer->getConnection()->newTable(
        		    $installer->getTable('zt_ols_price_matrix')
        		)
        		    ->addColumn(
        		        'id',
        		        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        		        null,
        		        [
        		            'identity' => true,
        		            'nullable' => false,
        		            'primary' => true,
        		            'unsigned' => true,
        		        ],
        		        'ID'
        		    )
        		    ->addColumn(
        		        'min_price',
        		        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        		        255,
        		        ['nullable => false'],
        		        'Min Price'
        		    )
        		    ->addColumn(
        		        'max_price',
        		        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        		        255,
        		        ['nullable => false'],
        		        'Max Price'
        		    )
        		    ->addColumn(
        		        'markup',
        		        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        		        255,
        		        [],
        		        'Mark Up'
        		    )
        		    ->addColumn(
        		        'created_at',
        		        \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
        		        null,
        		        ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
        		        'Created At'
        		    )->addColumn(
        		        'updated_at',
        		        \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
        		        null,
        		        ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
        		        'Updated At')
        		    ->setComment('Ols Price Matrix Table');
        		$installer->getConnection()->createTable($table);

        	}
        }
    }
}