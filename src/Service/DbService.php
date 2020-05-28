<?php

namespace App\Service;

use Exception;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class DbService
 */
class DbService
{
    private $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }
    
    /**
     * Get general update datetime of a database table
     * @param string $table
     * @return mixed
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getLastUpdate($table)
    {
        try {
            $connection = $this->manager->getConnection();

            $sql = 'SHOW TABLE STATUS LIKE :table';
            $query = $connection->prepare($sql);
            $query->execute(['table' => $table]);

            $data = $query->fetchAll();

            return $data['0']['Update_time'];
            
        } catch (DBALException $e) {
            return;
        } catch (Exception $e) {
            return;
        }
    }
}
