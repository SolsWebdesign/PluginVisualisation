<?php
/**
 * Product : SolsWebdesign
 *
 * @copyright Copyright © 2024 SolsWebdesign. All rights reserved.
 * @author    Isolde van Oosterhout
 */
namespace SolsWebdesign\PluginVisualisation\Model\Scanner;

use Magento\Setup\Module\Di\Code\Scanner\ConfigurationScanner;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\Csv;

class Plugin
{
    protected $configurationScanner;
    protected $csvWriter;
    protected $directoryList;
    protected $devLogging = true;
    protected $devLog;

    public function __construct(
        ConfigurationScanner $configurationScanner,
        Csv $csvWriter,
        DirectoryList $directoryList
    ) {
        $this->configurationScanner = $configurationScanner;
        $this->csvWriter = $csvWriter;
        $this->directoryList = $directoryList;

        $monthNumber = date("m");
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/sols_list_plugins_' . $monthNumber . '.log');
        $this->devLog = new \Zend_Log();
        $this->devLog->addWriter($writer);
    }
    protected function getDiFiles()
    {
        $files = $this->configurationScanner->scan('di.xml');
        $results = $this->getAllTypes($files);
        return $results;
    }

    public function getAllTypes($files)
    {
        $classes = [];
        foreach ($files as $file) {
            $classes = array_merge_recursive($classes, $this->scanFile($file));
        }
        return $classes;
    }

    public function scanFile($file)
    {
        $types = [];
        $dom = new \DOMDocument();
        $dom->load($file);
        $xpath = new \DOMXPath($dom);
        $results = $xpath->query('//plugin/..');
        $i = 0;
        foreach ($results as $result) {
            //if($i == 20) {
            //    break;
            //}
            $class = $this->trimInstanceStartingBackslash($result->getAttribute('name'));
            $disabled = $result->getAttribute('disabled');

            if(isset($disabled) && $disabled=="false") {
                continue;
            }

            if (!isset($types[$class])) {
                $types[$class] = [];
            }
            foreach ($result->childNodes as $plugin) {
                if (!$this->isValidPlugin($plugin)) {
                    continue;
                }
                $methods = $this->getMethods($plugin->getAttribute('type'));
                if (!count($methods)) {
                    continue;
                }
                $types[$class][] = [
                    'plugin'     => $this->trimInstanceStartingBackslash($plugin->getAttribute('type')),
                    'sort_order' => $plugin->getAttribute('sortOrder'),
                    'methods'    => implode(', ', $methods),
                ];
            }
            $i++;
        }
        return $types;
    }

    public function trimInstanceStartingBackslash($typeName)
    {
        return ltrim($typeName, '\\');
    }

    protected function isValidPlugin($plugin)
    {
        return ($plugin instanceof \DOMElement)
            && $plugin->tagName === 'plugin'
            && $plugin->getAttribute('type')
            && class_exists($plugin->getAttribute('type'));
    }

    protected function getMethods($class)
    {
        $reflection = new \ReflectionClass($class);
        $methods = [];
        foreach ($reflection->getMethods() as $method) {
            foreach (['before', 'after', 'around'] as $prefix) {
                if (strpos($method->name, $prefix) === 0) {
                    $methods[] = $method->name;
                    break;
                }
            }
        }
        return $methods;
    }

    public function createListPluginsCsv($csvName)
    {
//        $data = [
//            ['column 1','column 2','column 3'],
//            ['row 1','row 1','row 1'],
//            ['row 2','row 2','row 2'],
//        ];

        $data = $this->getDiFiles();

        //$this->devLog->info('data :');
        //$this->devLog->info(print_r($data, true));
        $printable = [];
        $printable[] = ['origin', 'plugin number', 'plugin', 'sort order', 'methods'];
        foreach ($data as $origin => $resultArray) {
            foreach ($resultArray as $pluginNr => $subArray) {
                $key = $pluginNr+1;
                if(!isset($subArray['sort_order']) || strlen($subArray['sort_order']) == 0) {
                    $sortOrder = 'n.a.';
                } else {
                    $sortOrder = $subArray['sort_order'];
                }
                $printable[] = [$origin, $key, $subArray['plugin'], $sortOrder, $subArray['methods']];
            }
        }
        $this->devLog->info(print_r($printable, true));

        $filePath = $this->directoryList->getPath(DirectoryList::VAR_DIR)."/export/".$csvName;

        $this->csvWriter
            ->setEnclosure('"')
            ->setDelimiter(',')
            ->saveData($filePath ,$printable);

        return 'Hello there! Your CSV name is '.$csvName;
    }
}
