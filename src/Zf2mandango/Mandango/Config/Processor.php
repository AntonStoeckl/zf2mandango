<?php

namespace Zf2mandango\Config;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use FilesystemIterator;

class Processor
{
    /** @var string */
    protected $configDir;

    /** @var array */
    protected $config = array();

    /** @var \RecursiveIteratorIterator */
    protected $fileObjects;

    /** @var array */
    protected $parsers = array();

    /**
     * The constructor
     *
     * @param string $configDir
     */
    public function __construct($configDir)
    {
        $this->configDir = $configDir;
        $this->addDefaultParsers();
    }

    /**
     * Add the default parser(s)
     */
    protected function addDefaultParsers()
    {
        $arrayParser = function ($file) {
            return include $file;
        };

        $this->addParser('php', $arrayParser);

        $xmlParser = function ($file) {
            $reader = new \Zend\Config\Reader\Xml();
            $parsedData = $reader->fromFile($file);
            $docName = $parsedData['document']['name'];
            unset($parsedData['document']['name']);
            $parsedData[$docName] = $parsedData['document'];
            unset($parsedData['document']);
            return $parsedData;
        };

        $this->addParser('xml', $xmlParser);
    }

    /**
     * Getter for the fileObjects
     *
     * @return \RecursiveIteratorIterator
     */
    public function getFileObjects()
    {
        return $this->fileObjects;
    }

    /**
     * Adds a parser, must be a callable
     *
     * @param string   $type
     * @param callable $parser
     * @return Processor Fluent interface
     */
    public function addParser($type, $parser)
    {
        if (!is_scalar($type)) {
            //todo: exception
        }

        if (!is_callable($parser)) {
            //todo: exception
        }

        if (!array_key_exists($type, $this->parsers)) {
            $this->parsers[$type] = $parser;
        }

        return $this;
    }

    /**
     * Removes a parser by it's type
     *
     * @param string $type
     * @return Processor Fluent interface
     */
    public function removeParser($type)
    {
        if (array_key_exists($type, $this->parsers)) {
            unset($this->parsers[$type]);
        }

        return $this;
    }

    /**
     * Reads the config files recousively from the supplied path
     * and stores them in member var as a RecursiveIteratorIterator object for later use.
     *
     * @return Processor Fluent interface
     */
    protected function readConfigFilesFromPath()
    {
        $this->fileObjects = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $this->configDir,
                FilesystemIterator::SKIP_DOTS
            ),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        return $this;
    }

    /**
     * Parses the config files from $fileObjects with the supplied parsers.
     * Gracefully ignores all files that:
     *   - are not readable
     *   - have no parser set that matches their extension
     * Read the config files first if not yet done.
     *
     * @return Processor Fluent interface
     */
    public function parse()
    {
        if ($this->fileObjects === null) {
            $this->readConfigFilesFromPath();
        }

        foreach ($this->fileObjects as $name => $fileObject) {
            /** @var $fileObject \SplFileInfo */
            if (!$fileObject->isReadable()) {
                continue;
            }

            $ext = $fileObject->getExtension();
            if (array_key_exists($ext, $this->parsers)) {
                $filename = $fileObject->__toString();
                $configItem = $this->parsers[$ext]($filename);
                $this->config = array_merge($this->config, $configItem);
            }
        }

        return $this;
    }

    /**
     * Outputs the config array.
     * Parses the config files first if not yet done.
     *
     * @return array
     */
    public function output()
    {
        if (count($this->config) == 0) {
            $this->parse();
        }

        return $this->config;
    }
}

