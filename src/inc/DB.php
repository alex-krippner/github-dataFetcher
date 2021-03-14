<?php

namespace Mon\Oversight\inc;

use PDO;
use PDOException;

// TODO: Write migration logic
// TODO: Consider moving the connection logic to the constructor
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

    // FIXME: use PDO transaction

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

    public function queryDB($query, $data = [])
    {
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if (isset($data)) {
            $this->pdo->beginTransaction();

            try {
                $stmt = $this->pdo->prepare($query);
                $stmt->execute($data);
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (\Exception $e) {
                $this->pdo->rollBack();
                echo "Failed: " . $e->getMessage();
            }
        }
    }


    public function countRows($tableName)
    {
        return count($this->pdo->query('SELECT * FROM ' . $tableName)->fetchAll(PDO::FETCH_ASSOC));
    }

    public function getReportData($report_subject)
    {
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $this->pdo->prepare("
            SELECT p.plugin_name AS 'Plugin', owner_login AS 'Owner', repo_link AS 'URL', description 'Description',
                   forks_count as 'Forks',
                   stars_count as 'Stars',
                   watchers_count as 'Watchers',
                   ROUND((commits_count / CAST((DATE('now') - date_created) AS FLOAT)), 2) as 'Commits/Year',
				   (SELECT SUM (CASE WHEN pulls.state = 'open' THEN 1 ELSE 0 END) FROM pulls WHERE pulls.plugin_name = :plugin_name )AS 'Open Pull Requests',
				   (SELECT SUM (CASE WHEN pulls.state = 'closed' THEN 1 ELSE 0 END) FROM pulls WHERE pulls.plugin_name = :plugin_name )AS 'Closed Pull Requests',
                   (SELECT SUM (CASE WHEN issues.state = 'open' THEN 1 ELSE 0 END) FROM issues WHERE plugin_name = :plugin_name)  as 'open issues',
                   (SELECT SUM (CASE WHEN issues.state = 'closed' THEN 1 ELSE 0 END) FROM issues WHERE plugin_name = :plugin_name)  as 'closed issues',
                   (SELECT COUNT(*) from contributors WHERE contributors.plugin_name = :plugin_name)AS 'Contributors',
                   (SELECT SUM(CASE WHEN issues.created_at >= DATE('now', '-2 year') THEN 1 ELSE 0 END) FROM issues WHERE plugin_name = :plugin_name) AS 'Issues opened in the last 2 years',
                   (SELECT SUM(CASE WHEN issues.closed_at >= DATE('now', '-2 year') THEN 1 ELSE 0 END) FROM issues WHERE plugin_name = :plugin_name) AS 'Issues closed in the last 2 years'
				   FROM plugins p 
                   WHERE p.plugin_name =  :plugin_name;
            ");
        $clean = array();

        if (isset($report_subject)) {
            $this->pdo->beginTransaction();

            try {
                if (ctype_alnum($report_subject)) {
                    $clean['report_subject'] = $report_subject;
                }

                $stmt->bindParam(":plugin_name", $clean['report_subject'], PDO::PARAM_STR, 32);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (\Exception $e) {
                $this->pdo->rollBack();
                echo "Failed: " . $e->getMessage();
            }
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
