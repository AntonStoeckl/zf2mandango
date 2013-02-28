<?php
/**
 * ZF2 Mandango
 *
 * @link      hhttps://github.com/AntonStoeckl/zf2mandango for the canonical source repository
 * @copyright Copyright (c) 2013 Anton Stöckl
 * @license   MIT License - see bundled LICENSE file
 */

namespace Zf2mandango\Mondator\Init;

use \Zend\ServiceManager\ServiceManager;
use \Zend\Mvc\Service\ServiceManagerConfig;

/**
 * Factory
 *
 * Factory for getting a configured instance of Mondator.
 */
class Factory
{
    /**
     * Get an return a configured instance of Mondator from service manager.
     *
     * @param string $serviceName  The service name as used in the service manager config
     * @param array $configuration The application config
     * @return \Mandango\Mondator\Mondator
     * @throws \UnexpectedValueException
     */
    public function getInstance($serviceName, array $configuration)
    {
        $smConfig = isset($configuration['service_manager']) ? $configuration['service_manager'] : array();
        $serviceManager = new \Zend\ServiceManager\ServiceManager(
            new \Zend\Mvc\Service\ServiceManagerConfig($smConfig)
        );
        $serviceManager->setService('ApplicationConfig', $configuration);
        $serviceManager->get('ModuleManager')->loadModules();

        $mondatorInstance = $serviceManager->get($serviceName);

        if (! $mondatorInstance instanceof \Mandango\Mondator\Mondator) {
            throw new \UnexpectedValueException(
                'Got invalid object back from service manager, should be an instance of Mondator'
            );
        }

        return $mondatorInstance;
    }
}
