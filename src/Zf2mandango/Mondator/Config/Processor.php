<?php
/**
 * ZF2 Mandango
 *
 * @link      hhttps://github.com/AntonStoeckl/zf2mandango for the canonical source repository
 * @copyright Copyright (c) 2013 Anton Stöckl
 * @license   MIT License - see bundled LICENSE file
 */

namespace Zf2mandango\Mondator\Config;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use FilesystemIterator;

/**
 * Processor
 *
 * Processes config files for Mondator to create the document and repository resource classes.
 * Configs can be in any formats as long as parsers for those formats are injected to the processor,
 * the parsers must be callables that accept the filename as single parameter.
 * The mapping from a file to a parser is done via the file extension.
 * The bundled ParserFactory supports this formats:
 *   - php (plain array)
 *   - xml
 * Any different ParserFactory can also be injected as long as it implements the ParserFactoryInterface.
 */
class Processor
{
    /**
     * @var string
     */
    protected $configDir;

    /**
     * @var array
     */
    protected $config = array();

    /**
     * @var \RecursiveIteratorIterator
     */
    protected $fileObjects;

    /**
     * @var array
     */
    protected $parsers = array();

    /**
     * @var \Zf2mandango\Mondator\Config\ParserFactoryInterface
     */
    protected $parserFactory;

    /**
     * The constructor
     *
     * @param string $configDir
     */
    public function __construct($configDir)
    {
        $this->configDir = $configDir;
    }

    /**
     * @param \Zf2mandango\Mondator\Config\ParserFactoryInterface $parserFactory
     *
     * @return Processor Fluent interface
     */
    public function setParserFactory($parserFactory)
    {
        $this->parserFactory = $parserFactory;

        return $this;
    }

    /**
     * @return \Zf2mandango\Mondator\Config\ParserFactoryInterface
     */
    public function getParserFactory()
    {
        if ($this->parserFactory === null) {
            $this->parserFactory = new ParserFactory();
        }

        return $this->parserFactory;
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
     * @throws \InvalidArgumentException
     */
    public function addParser($type, $parser)
    {
        if (!is_scalar($type)) {
            throw new \InvalidArgumentException('Argument "type" is not a scalar');
        }

        if (!is_callable($parser)) {
            throw new \InvalidArgumentException('Argument "parser" is not a callable');
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
     * @param string $type
     * @return callable|boolean|null
     */
    public function getParser($type)
    {
        if (!array_key_exists($type, $this->parsers)) {
            $this->parsers[$type] = $this->getParserFactory()->getParser($type);
        }

        return $this->parsers[$type];
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

            $parser = $this->getParser($ext);

            if (is_callable($this->parsers[$ext])) {
                $filename = $fileObject->__toString();
                $configItem = $parser($filename);
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

