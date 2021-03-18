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
        $resp = $this->pdo->query("SELECT COUNT(DISTINCT plugin_name) as 'count' FROM " . $tableName)->fetchAll(PDO::FETCH_ASSOC);
        return $resp[0]['count'];
    }

    // FIXME: ALLOW plugin name such as 'plugin-name' with hyphens

    public function getReportData($report_subject)
    {
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $query = "
            SELECT p.plugin_name, 
                   owner_login, 
                   repo_link, 
                   description,
                   forks_count,
                   stars_count,
                   watchers_count,
                   ROUND((commits_count / CAST((DATE('now') - date_created) AS FLOAT)), 2) as 'commits/year',
				   (SELECT SUM (CASE WHEN pulls.state = 'open' THEN 1 ELSE 0 END) FROM pulls WHERE pulls.plugin_name = :plugin_name )AS 'open pull requests',
				   (SELECT SUM (CASE WHEN pulls.state = 'closed' THEN 1 ELSE 0 END) FROM pulls WHERE pulls.plugin_name = :plugin_name )AS 'closed pull requests',
                   (SELECT SUM (CASE WHEN issues.state = 'open' THEN 1 ELSE 0 END) FROM issues WHERE plugin_name = :plugin_name)  as 'open issues count',
                   (SELECT SUM (CASE WHEN issues.state = 'closed' THEN 1 ELSE 0 END) FROM issues WHERE plugin_name = :plugin_name)  as 'closed issues count',
                   (SELECT COUNT(*) from contributors WHERE contributors.plugin_name = :plugin_name) AS 'contributors count',
                   (SELECT SUM(CASE WHEN issues.created_at >= DATE('now', '-2 year') THEN 1 ELSE 0 END) FROM issues WHERE plugin_name = :plugin_name) AS 'issues opened in the last 2 years',
                   (SELECT SUM(CASE WHEN issues.closed_at >= DATE('now', '-2 year') THEN 1 ELSE 0 END) FROM issues WHERE plugin_name = :plugin_name) AS 'issues closed in the last 2 years'
				   FROM plugins p 
                   WHERE p.plugin_name =  :plugin_name;
            ";
        $clean = array();

        if (isset($report_subject)) {
            $this->pdo->beginTransaction();

            try {
                if (ctype_alnum($report_subject)) {
                    $clean['report_subject'] = $report_subject;
                }

                $stmt = $this->pdo->prepare($query);
                $stmt->bindParam(":plugin_name", $clean['report_subject'], PDO::PARAM_STR, 32);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (\Exception $e) {
                $this->pdo->rollBack();
                echo "Failed: " . $e->getMessage();
            }
        }
    }


    // FIXME: MOST STARRED, FORKED, WATCHED queries are inaccurate
    public function getAggregatePluginReportData()
    {
        $query = "
            SELECT ROUND (avg (avg_commits_per_year), 2) as 'commits/year', 
            COUNT (DISTINCT plugin_name) as 'plugin count',
            (SELECT SUM (CASE WHEN pulls.state = 'open' THEN 1 ELSE 0 END) FROM pulls) as 'open pulls',
            (SELECT SUM (CASE WHEN pulls.state = 'closed' THEN 1 ELSE 0 END) FROM pulls) as 'closed pulls',
            (SELECT COUNT (DISTINCT contributor_login) FROM contributors) AS 'contributors count',
            SUM (stars_count) as 'stars count',
            SUM (watchers_count) as 'watchers count',
            SUM (forks_count) as 'forks count',
			(SELECT SUM (CASE WHEN issues.state = 'open' THEN 1 ELSE 0 END) FROM issues  ) as 'open issues count',
            (SELECT SUM (CASE WHEN issues.state = 'closed' THEN 1 ELSE 0 END) FROM issues  )  as 'closed issues count',
			(SELECT SUM (CASE WHEN issues.created_at >= DATE('now', '-2 year') THEN 1 ELSE 0 END) FROM issues) AS 'issues opened in the last 2 years',
            (SELECT SUM (CASE WHEN issues.closed_at >= DATE('now', '-2 year') THEN 1 ELSE 0 END) FROM issues) AS 'issues closed in the last 2 years',
            (SELECT plugin_name FROM plugins GROUP BY plugin_name HAVING MAX (stars_count) ORDER BY stars_count DESC ) as 'most starred',
            (SELECT plugin_name FROM plugins GROUP BY plugin_name HAVING MAX (watchers_count) ORDER BY watchers_count DESC) as 'most watched',
            (SELECT plugin_name FROM plugins GROUP BY plugin_name HAVING MAX (forks_count) ORDER BY forks_count DESC) as 'most forked'   
            FROM plugins
            ";


        return $this->queryDB($query);
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
