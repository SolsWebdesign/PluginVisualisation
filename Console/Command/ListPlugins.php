<?php
/**
 * Product : SolsWebdesign
 *
 * @copyright Copyright © 2024 SolsWebdesign. All rights reserved.
 * @author    Isolde van Oosterhout
 */
namespace SolsWebdesign\PluginVisualisation\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use SolsWebdesign\PluginVisualisation\Model\Scanner\Plugin as ScannerPlugin;

class ListPlugins extends Command
{
    const INPUT_KEY_LIST_PLUGINS_CSV = 'list_plugins_csv';

    protected $scannerPlugin;

    public function __construct(
        ScannerPlugin $scannerPlugin,
        $name = 'sols_list_plugins' //solsListPlugins
    ) {
        $this->scannerPlugin = $scannerPlugin;
        parent::__construct($name);
    }

    // here we define our cli call (sols:list:plugins) and the required and optional arguments
    protected function configure()
    {
        $this->setName('sols:list:plugins');
        $this->setDescription('Lists all plugins found, please add CSV name, e.g. listPlugins.csv, CSV will be created in var/exports.');
        $this->addOption(
            self::INPUT_KEY_LIST_PLUGINS_CSV,
            'list_plugins_csv',
            InputOption::VALUE_REQUIRED,
            'listPluginsCsv'
        );
        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $listPluginsCsv = (string)$input->getOption(self::INPUT_KEY_LIST_PLUGINS_CSV);
            if(isset($listPluginsCsv) && strlen($listPluginsCsv) > 0) {
                $message = $this->scannerPlugin->createListPluginsCsv($listPluginsCsv);
            } else {
                $message = 'Please provide CSV name, like "plugins.csv" make sure it has the .csv extension.';
            }
            $message = '<info>'.$message.'</info>';
            $output->writeln($message);
            return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            $output->write($e->getTraceAsString());
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln($e->getTraceAsString());
            }
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
        // still here?
        return \Magento\Framework\Console\Cli::RETURN_FAILURE;
    }
}
