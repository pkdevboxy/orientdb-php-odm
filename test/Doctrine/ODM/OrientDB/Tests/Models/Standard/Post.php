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
 * Class Post
 *
 * @package
 * @subpackage
 * @author      Alessandro Nadalin <alessandro.nadalin@gmail.com>
 * @author      David Funaro <ing.davidino@gmail.com>
 */

namespace Doctrine\ODM\OrientDB\Tests\Models\Standard;

/**
 * @Document(oclass="Post")
 */
class Post
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
     * @LinkList(targetDoc="Comment")
     */
    public $comments;

    /**
     * @Property(name="id", type="integer")
     */
    public $id;

    /**
     * @Property(type="string")
     */
    public $title;

    public function getRid() {
        return $this->rid;
    }

    public function setRid($rid) {
        $this->rid = $rid;
    }

    /**
     * @return \Doctrine\ODM\OrientDB\Collections\PersistentCollection|Comment[]
     */
    public function getComments() {
        return $this->comments;
    }

    public function setComments($city) {
        $this->comments = $city;
    }
}
