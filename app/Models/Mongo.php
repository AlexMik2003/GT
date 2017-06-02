<?php

namespace App\Models;

class Mongo
{
    /**
     * @var \MongoDB
     */
    protected $mongo;

    /**
     * Mongo constructor.
     */
    public function __construct()
    {
        try{
            $conn = new \MongoClient();
            $this->mongo = $conn->snmp;

        }
        catch (\MongoConnectionException $e) {
                die('Error connecting to MongoDB server');
        }
        catch (\MongoException $e) {
            die('Error: ' . $e->getMessage());
        }

    }

    /**
     * Insert data to db
     *
     * @param string $collect - collection
     *
     * @param array $data - data
     */
    public function insertData($collect,$data)
    {
        $collection = $this->mongo->selectCollection($collect);
        $collection->insert($data);
    }

    /**
     * Get data from db
     *
     * @param string $collect - collection
     *
     * @param mixed $criteria - search criteria
     *
     * @return \MongoCursor
     */
    public function getData($collect, $criteria)
    {
        $collection = new \MongoCollection($this->mongo,$collect);
        return $collection->find($criteria);

    }

    /**
     * Delete selected data from db
     *
     * @param string $collect - collection
     *
     * @param mixed $criteria - search criteria
     *
     * @return array|bool
     */
    public function removeData($collect, $criteria)
    {
        $collection = new \MongoCollection($this->mongo,$collect);
        return $collection->remove($criteria);

    }

}