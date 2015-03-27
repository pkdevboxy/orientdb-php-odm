<?php

namespace Doctrine\ODM\OrientDB\Persister\SQLBatch;

/**
 * Class QueryWriter
 *
 * @package    Doctrine\ODM
 * @subpackage OrientDB
 * @author     Tamás Millián <tamas.millian@gmail.com>
 */
class QueryWriter
{
    private $queries = [];

    /**
     * @var int
     */
    private $inserts = 0;

    public function getQueries() {
        return $this->queries;
    }

    public function addBegin() {
        $this->queries [] = 'begin';
    }

    public function addCommit($retries = 10) {
        $this->queries [] = 'commit retry ' . $retries;
    }

    public function addInsertQuery($var, $class, \stdClass $fields, $cluster = null) {
        $query           = "let %s = INSERT INTO %s%s SET %s RETURN @rid";
        $cluster         = $cluster ? ' cluster ' . $cluster : '';
        $this->queries[] = sprintf($query, $var, $class, $cluster, $this->flattenFields($fields));

        // returned so we can map the rid to the document
        return $this->inserts++;
    }

    protected function flattenFields(\stdClass $fields) {
        $parts = '';
        foreach ($fields as $name => $value) {
            $parts [] = sprintf('%s=%s', $name, self::escape($value));
        }

        return implode(',', $parts);
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public static function escape($value) {
        switch (true) {
            case $value instanceof Value:
                return $value->toValue();

            case is_string($value):
                return "'$value'";

            case is_null($value):
                return 'null';

            case is_object($value):
            case is_array($value):
                // embedded document, list, map or set
                return json_encode($value);

            default:
                return $value;
        }
    }

    public function addCreateVertexQuery($var, $class, \stdClass $fields) {
        $query           = "let %s = CREATE VERTEX %s SET %s RETURN @rid";
        $this->queries[] = sprintf($query, $var, $class, $this->flattenFields($fields));

        // returned so we can map the rid to the document
        return $this->inserts++;
    }

    public function addCreateEdgeQuery($var, $class, $from, $to, \stdClass $fields) {
        $query           = "let %s = CREATE EDGE %s FROM %s TO %s SET %s";
        $this->queries[] = sprintf($query, $var, $class, $from, $to, $this->flattenFields($fields));

        // returned so we can map the rid to the document
        return $this->inserts++;
    }

    /**
     * @param string   $class
     * @param string[] $rids
     */
    public function addCreateLightEdgeQuery($class, $rids) {
        $query           = "CREATE EDGE %s FROM %s TO %s";
        $this->queries[] = sprintf($query, $class, $rids[0], $rids[1]);
    }

    /**
     * @param string    $rid
     * @param \stdClass $fields
     * @param null      $var
     * @param int|null  $version
     * @param string    $lock
     */
    public function addUpdateQuery($rid, \stdClass $fields, $var = null, $version = null, $lock = 'DEFAULT') {
        $query = "%sUPDATE %s SET %s %s %s LOCK %s";
        if ($version !== null) {
            $let    = "let $var = ";
            $where  = "WHERE @version = $version";
            $return = "RETURN AFTER @version";
        } else {
            $let = $where = $return = "";
        }
        $this->queries[] = sprintf($query, $let, $rid, $this->flattenFields($fields), $return, $where, $lock);
    }

    public function addCollectionAddQuery($rid, $fieldName, $value, $lock = 'DEFAULT') {
        $query           = "UPDATE %s ADD %s = [%s] LOCK %s";
        $this->queries[] = sprintf($query, $rid, $fieldName, $value, $lock);
    }

    public function addCollectionDelQuery($rid, $fieldName, $value, $lock = 'DEFAULT') {
        $query           = "UPDATE %s REMOVE %s = [%s] LOCK %s";
        $this->queries[] = sprintf($query, $rid, $fieldName, $value, $lock);
    }

    public function addCollectionClearQuery($rid, $fieldName, $lock = 'DEFAULT') {
        $query           = "UPDATE %s SET %s = [] LOCK %s";
        $this->queries[] = sprintf($query, $rid, $fieldName, $lock);
    }

    public function addCollectionMapPutQuery($rid, $fieldName, $key, $value, $lock = 'DEFAULT') {
        $query           = "UPDATE %s PUT %s = '%s', %s LOCK %s";
        $this->queries[] = sprintf($query, $rid, $fieldName, $key, $value, $lock);
    }

    public function addCollectionMapDelQuery($rid, $fieldName, $key, $lock = 'DEFAULT') {
        $query           = "UPDATE %s REMOVE %s = '%s' LOCK %s";
        $this->queries[] = sprintf($query, $rid, $fieldName, $key, $lock);
    }

    public function addCollectionMapClearQuery($rid, $fieldName, $lock = 'DEFAULT') {
        $query           = "UPDATE %s SET %s = {} LOCK %s";
        $this->queries[] = sprintf($query, $rid, $fieldName, $lock);
    }

    /**
     * @param string $rid
     * @param string $lock
     */
    public function addDeleteQuery($rid, $lock = 'DEFAULT') {
        $query           = "DELETE FROM %s LOCK %s";
        $this->queries[] = sprintf($query, $rid, $lock);
    }

    /**
     * @param string $rid
     */
    public function addDeleteVertexQuery($rid) {
        $query           = "DELETE VERTEX %s";
        $this->queries[] = sprintf($query, $rid);
    }

    /**
     * @param string $rid
     */
    public function addDeleteEdgeByRidQuery($rid) {
        $query           = "DELETE EDGE %s";
        $this->queries[] = sprintf($query, $rid);
    }

    /**
     * @param string   $oclass
     * @param string[] $rids
     */
    public function addDeleteEdgeQuery($oclass, $rids) {
        $query           = "DELETE EDGE FROM %s TO %s WHERE @class = '%s'";
        $this->queries[] = sprintf($query, $rids[0], $rids[1], $oclass);
    }

    /**
     * @param string $oclass
     * @param string $dir
     * @param string $rid
     */
    public function addDeleteEdgeCollectionQuery($oclass, $dir, $rid) {
        $query           = "DELETE EDGE %s WHERE %s = %s";
        $this->queries[] = sprintf($query, $oclass, $dir, $rid);
    }
}