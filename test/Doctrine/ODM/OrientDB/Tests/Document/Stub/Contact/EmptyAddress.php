<?php

namespace Doctrine\ODM\OrientDB\Tests\Document\Stub\Contact;

/**
 * @Document(class="ContactEmptyAddress")
 */
class EmptyAddress
{
    /**
     * @RID
     */
    public $rid;

    /**
     * @Version
     */
    public $version;

    /**
     * @Property(type="string", nullable=true)
     */
    public $string;

    /**
     * @Property(type="integer", nullable=true)
     */
    public $integer;
}
