<?php

/*
 * This file is part of the Doctrine\OrientDB package.
 *
 * (c) Alessandro Nadalin <alessandro.nadalin@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Class used to manipulate and identity properties in an annotation.
 *
 * @package    Doctrine\ODM
 * @subpackage OrientDB
 * @author     Alessandro Nadalin <alessandro.nadalin@gmail.com>
 */

namespace Doctrine\ODM\OrientDB\Mapping\Annotations;
use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
class Property extends AbstractProperty
{
    public $type;
    public $cast;
    public $notnull;

    public function getCast()
    {
        return $this->cast;
    }
    
    /**
     * Defines whether an hydrated property can be null.
     * 
     * @return bool
     */
    public function isNullable()
    {
        return $this->notnull === "false";
    }
}
