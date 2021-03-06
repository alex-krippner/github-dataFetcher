<?php

namespace mon\oversight\inc;

use PDO;
use PDOException;

// TODO: Write migration logic
// https://www.sqlite.org/lang_altertable.html

/**
 * PDO wrapper class
 *
 * Class DB
 * @package oversight\inc
 */
class DB
{


    protected $pdo;

    /**
     * Creates connection to database.
     */
    public function connect()
    {
        try {
            $this->pdo = new PDO("sqlite:" . __DIR__ . "/../data/oversight.sqlite3");
        } catch (PDOException $e) {
            print "Error!: " . $e->getMessage() . "<br/>";
            die();
        }
    }

    public function closeConnection()
    {
        $this->pdo = null;
    }


    /**
     * Creates tables using up to date schemas
     */
    public function createTable()
    {
        $schemasArray = $this->getSchemas();
        foreach ($schemasArray as $schema) {
            if (isset($schema) && $schema !== "") {
                $this->pdo->exec($schema);
            }
        }
    }

    /**
     * @param string $query sqlite query string with ? placeholders
     * @param array $data array of data strings
     * @return void
     */
    public function insertData($query, $data)
    {
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if (isset($data)) {
            $this->pdo->beginTransaction();

            try {
                $stmt = $this->pdo->prepare($query);
                $stmt->execute($data);
                $this->pdo->commit();
            } catch (\Exception $e) {
                $this->pdo->rollBack();
                echo "Failed: " . $e->getMessage();
            }
        }
    }

    public function countRows($tableName)
    {
        $data = $this->pdo->query('SELECT * FROM ' . $tableName)->fetchAll(PDO::FETCH_ASSOC);
        if (isset($data)) {
            echo 'There are ' . count($data) . ' ' . $tableName . ' in the table ' . $tableName;
        }
    }


    /**
     *
     * Fetches the latest schema
     * @return string[] An array of the latest sql commands
     */
    private function getSchemas()
    {
        $schemasVersion = file_get_contents(__DIR__ . '/../data/latest.version');
        $schemas = file_get_contents(__DIR__ . '/../data/update000' . $schemasVersion . '.sql');
        return explode(';', $schemas);
    }
}
