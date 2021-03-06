<?php

/*
 * This file is part of the Orient package.
 *
 * (c) Alessandro Nadalin <alessandro.nadalin@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Class Profile
 *
 * @package
 * @subpackage
 * @author      Alessandro Nadalin <alessandro.nadalin@gmail.com>
 * @author      David Funaro <ing.davidino@gmail.com>
 */

namespace Doctrine\ODM\OrientDB\Tests\Models\Standard;

/**
 * @Document(oclass="Profile")
 */
class Profile
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
     * @Property(type="long")
     */
    public $hash;

    /**
     * @LinkMap(targetDoc="Profile")
     * @var Profile[]
     */
    public $followers;
}
