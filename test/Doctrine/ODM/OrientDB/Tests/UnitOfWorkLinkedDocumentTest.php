<?php

namespace Doctrine\ODM\OrientDB\Tests;

use Doctrine\ODM\OrientDB\DocumentManager;
use Doctrine\ODM\OrientDB\Mapping\ClassMetadataFactory;
use Doctrine\ODM\OrientDB\Tests\Document\Stub\Linked\Contact;
use Doctrine\ODM\OrientDB\Tests\Document\Stub\Linked\EmailAddress;
use PHPUnit\TestCase;

/**
 * @group functional
 */
class UnitOfWorkLinkedDocumentTest extends TestCase
{
    /**
     * @var DocumentManager
     */
    private $manager;
    /**
     * @var ClassMetadataFactory
     */
    private $metadataFactory;

    /**
     * @before
     */
    public function before() {
        $this->manager         = $this->createDocumentManager([], ['Doctrine/ODM/OrientDB/Tests/Document/Stub/Linked']);
        $this->metadataFactory = $this->manager->getMetadataFactory();
    }

    /**
     * @test
     */
    public function computeChangeSet_generates_for_all_owning_associations() {
        $c       = new Contact();
        $c->name = "Sydney";

        $em        = new EmailAddress();
        $em->type  = "work";
        $em->email = "syd@gmail.com";
        $c->setEmail($em);

        $this->manager->persist($c);

        $uow = $this->manager->getUnitOfWork();
        $this->assertTrue($uow->isScheduledForInsert($c));
        $this->assertTrue($uow->isScheduledForInsert($em));

        $uow->computeChangeSets();
        $cs = $uow->getDocumentChangeSet($c);
        $this->assertEquals(['name', 'email', 'phones'], array_keys($cs));

        $cs = $uow->getDocumentChangeSet($em);
    }

    /**
     * @test
     */
    public function getDocumentChangeSet_update_value_in_linked() {
        $c       = new Contact();
        $c->name = "Sydney";
        $c->rid  = "#1:1";

        $e        = new EmailAddress();
        $e->rid   = "#2:1";
        $e->type  = "work";
        $e->email = "syd@gmail.com";
        $c->setEmail($e);
        $e->contact = $c;

        $uow = $this->manager->getUnitOfWork();
        $uow->registerManaged($c, $c->rid, ['rid' => $c->rid, 'name' => 'Sydney', 'email' => $e]);
        $uow->registerManaged($e, $e->rid, ['rid' => $e->rid, 'type' => 'home', 'email' => 'syd@gmail.com', 'contact' => $c]);
        $uow->computeChangeSets();

        $cs = $uow->getDocumentChangeSet($c);
        $this->assertEmpty($cs);

        $cs = $uow->getDocumentChangeSet($c->getEmail());
        $this->assertEquals(['type'], array_keys($cs));
        $this->assertEquals(['home', 'work'], $cs['type']);
    }

    /**
     * @test
     */
    public function getDocumentChangeSet_update_creates_linked() {
        $c       = new Contact();
        $c->name = "Sydney";
        $c->rid  = "#1:1";

        $e        = new EmailAddress();
        $e->type  = "home";
        $e->email = "syd@gmail.com";
        $c->setEmail($e);
        $e->contact = $c;

        $uow = $this->manager->getUnitOfWork();
        $uow->registerManaged($c, $c->rid, ['rid' => $c->rid, 'name' => 'Sydney', 'email' => null]);
        $uow->computeChangeSets();

        $cs = $uow->getDocumentChangeSet($c);
        $this->assertEquals(['email'], array_keys($cs));
        $this->assertEquals([null, $e], $cs['email']);

        $cs = $uow->getDocumentChangeSet($c->getEmail());
        $this->assertEquals(['type', 'email', 'contact'], array_keys($cs));
    }

    /**
     * @test
     */
    public function getDocumentChangeSet_update_nulls_linked() {
        $c       = new Contact();
        $c->name = "Sydney";
        $c->rid  = "#1:1";

        $e        = new EmailAddress();
        $e->rid   = "#2:1";
        $e->type  = "work";
        $e->email = "syd@gmail.com";
        $c->setEmail($e);
        $e->contact = $c;

        $uow = $this->manager->getUnitOfWork();
        $uow->registerManaged($c, $c->rid, ['rid' => $c->rid, 'name' => 'Sydney', 'email' => $e]);
        $uow->registerManaged($e, $e->rid, ['rid' => $e->rid, 'type' => 'home', 'email' => 'syd@gmail.com', 'contact' => $c]);

        $c->setEmail(null);
        $uow->computeChangeSets();

        $cs = $uow->getDocumentChangeSet($c);
        $this->assertEquals(['email'], array_keys($cs));
        $this->assertEquals([$e, null], $cs['email']);
    }
}
