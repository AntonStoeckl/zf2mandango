<?php

/*
 * This file is part of Zf2mandango.
 *
 * (c) Anton Stöckl <anton@stoeckl.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Zf2mandango\Mandango\Extension;

use Mandango\Mondator\Extension;

/**
 * DocumentArrayAccess extension.
 *
 * @author Anton Stöckl <anton@stoeckl.de>
 */
class DocumentArraySerializable extends Extension
{
    /**
     * {@inheritdoc}
     */
    protected function doClassProcess()
    {
        $this->definitions['document_base']->addInterface('\Zend\Stdlib\ArraySerializableInterface');

        $this->processTemplate(
            $this->definitions['document_base'],
            file_get_contents(__DIR__.'/templates/DocumentArraySerializable.php.twig')
        );
    }
}
