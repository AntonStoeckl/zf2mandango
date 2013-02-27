<?php

namespace Zf2mandango\Config;

class ParserFactory
{
    /**
     * The type of the parser to return
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

