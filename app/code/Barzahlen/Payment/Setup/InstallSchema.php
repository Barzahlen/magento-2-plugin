<?php
namespace Barzahlen\Payment\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Sales\Model\Order;

class InstallSchema  implements InstallSchemaInterface
{
    /**
     * Upgrades DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(SchemaSetupInterface  $setup, ModuleContextInterface $context)
    {
        file_put_contents('var/log/barzahlen.log', date('Y-m-d H:i:s'). ' - start barzahlen install script: ' . "\n", FILE_APPEND);

        $setup->startSetup();

        $quote = 'quote';
        $orderTable = 'sales_order';

        $setup->getConnection()
            ->addColumn(
                $setup->getTable($quote),
                'barzahlen_slip_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'comment' =>'Barzahlen Slip Id'
                ]
            );
        //Order Grid table
        $setup->getConnection()
            ->addColumn(
                $setup->getTable($orderTable),
                'barzahlen_slip_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'comment' =>'Barzahlen Slip Id'
                ]
            );

        file_put_contents('var/log/barzahlen.log', date('Y-m-d H:i:s'). ' - end barzahlen install script: ' . "\n", FILE_APPEND);
        $setup->endSetup();

    }
}