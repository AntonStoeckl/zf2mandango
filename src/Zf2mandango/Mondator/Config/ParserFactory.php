<?php
/**
 * ZF2 Mandango
 *
 * @link      hhttps://github.com/AntonStoeckl/zf2mandango for the canonical source repository
 * @copyright Copyright (c) 2013 Anton Stöckl
 * @license   MIT License - see bundled LICENSE file
 */

namespace Zf2mandango\Mondator\Config;

/**
 * ParserFactory
 *
 * A factory for Mondator config file parsers, which must be callables.
 */
class ParserFactory implements ParserFactoryInterface
{
    /**
     * Returns a callable config parser or false if no parser for this type is defined
     *
     * @param string $type
     * @return callable|boolean
     */
    public function getParser($type)
    {
        $parser = false;

        switch ($type) {
            case 'php':
                $parser = $this->getPhpParser();
                break;
            case 'xml':
                $parser = $this->getXmlParser();
                break;
        }

        return $parser;
    }

    /**
     * The parser for simple php array configs
     *
     * @return callable
     */
    protected function getPhpParser()
    {
        $arrayParser = function ($file) {
            return include $file;
        };

        return $arrayParser;
    }

    /**
     * The parser for xml configs
     *
     * @return callable
     */
    protected function getXmlParser()
    {
        $boolNormalizer = function ($config) {
            $boolFields = array('isEmbedded', 'isFile', 'useBatchInsert');
            foreach ($config as $class => $classConfig) {
                foreach ($boolFields as $field) {
                    if (!array_key_exists($field, $classConfig)) {
                        continue;
                    }
                    $config[$class][$field] =
                        (strtolower($classConfig[$field]) === 'true' || $classConfig[$field] == 1)
                            ? true
                            : false
                    ;
                }
            }
            return $config;
        };

        $xmlParser = function ($file) use ($boolNormalizer) {
            $reader = new \Zend\Config\Reader\Xml();
            $parsedData = $reader->fromFile($file);
            $docName = $parsedData['document']['name'];
            unset($parsedData['document']['name']);
            $parsedData[$docName] = $parsedData['document'];
            unset($parsedData['document']);
            $parsedData = $boolNormalizer($parsedData);
            return $parsedData;
        };

        return $xmlParser;
    }
}

