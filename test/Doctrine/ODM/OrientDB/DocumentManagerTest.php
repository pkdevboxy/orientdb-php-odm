<?php

/**
 * QueryTest
 *
 * @package    Doctrine\ODM\OrientDB
 * @subpackage Test
 * @author     Alessandro Nadalin <alessandro.nadalin@gmail.com>
 * @author     David Funaro <ing.davidino@gmail.com>
 * @author     Daniele Alessandri <suppakilla@gmail.com>
 * @version
 */

namespace test\Doctrine\ODM\OrientDB;

use Doctrine\ODM\OrientDB\DocumentManager;
use Doctrine\ODM\OrientDB\Mapping\ClassMetadata;
use Doctrine\OrientDB\Binding\BindingInterface;
use Doctrine\OrientDB\Binding\BindingResultInterface;
use Doctrine\OrientDB\Binding\HttpBindingInterface;
use Doctrine\OrientDB\Query\Query;
use test\PHPUnit\TestCase;

class DocumentManagerTest extends TestCase
{
    protected function createTestManager() {
        $rawResult = json_decode('[{
            "@type": "d", "@rid": "#19:0", "@version": 2, "@class": "ContactAddress",
            "name": "Luca",
            "surname": "Garulli",
            "out": ["#20:1"]
        }]');

        $result = $this->getMock(BindingResultInterface::class);
        $result->expects($this->any())
               ->method('getResult')
               ->will($this->returnValue($rawResult));

        /** @var HttpBindingInterface $binding */
        $binding = $this->getMock(HttpBindingInterface::class);
        $binding->expects($this->any())
                ->method('execute')
                ->will($this->returnValue($result));

        $data = <<<JSON
{
    "classes": [
        {"name":"ContactAddress", "clusters":[1]}
    ]
}
JSON;
        $data = json_decode($data);

        $stub = $this->getMock(BindingResultInterface::class);
        $stub->expects($this->any())
             ->method('getData')
             ->willReturn($data);


        $binding->expects($this->any())
                ->method('getDatabase')
                ->willReturn($stub);

        $configuration = $this->getConfiguration();
        $configuration->setMetadataDriverImpl($configuration->newDefaultAnnotationDriver(['test/Doctrine/ODM/OrientDB/Document/Stub']));
        $manager = new DocumentManager($binding, $configuration);

        return $manager;
    }

    public function testMethodUsedToTryTheManager() {
        $manager  = $this->createTestManager();
        $metadata = $manager->getClassMetadata('test\Doctrine\ODM\OrientDB\Document\Stub\Contact\Address');

        $this->assertInstanceOf(ClassMetadata::class, $metadata);
    }

    public function testManagerActsAsAProxyForExecutingQueries() {
        $query   = new Query(array('ContactAddress'));
        $manager = $this->createTestManager();
        $results = $manager->execute($query);

        $this->isInstanceOf(static::COLLECTION_CLASS, $results);
        $this->assertInstanceOf('test\Doctrine\ODM\OrientDB\Document\Stub\Contact\Address', $results[0]);
    }

    public function testFindingADocument() {
        $manager = $this->createTestManager();

        $this->assertInstanceOf('test\Doctrine\ODM\OrientDB\Document\Stub\Contact\Address', $manager->findByRid('1:1'));
    }
}
