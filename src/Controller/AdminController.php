<?php

namespace Mon\Oversight\Controller;

use Mon\Oversight\inc\DB;
use Mon\Oversight\Model\ContributorCollection;
use Mon\Oversight\Model\PluginCollection;
use Mon\Oversight\Model\IssuesCollection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;


class AdminController
{
    private $twig;

    public function __construct(Twig $twig)
    {
        $this->twig = $twig;
    }

    public function renderRowCount(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $db = new DB();
        $db->connect();
        $count = $db->countRows('plugins');
        $db->closeConnection();

        return $this->twig->render($response, 'admin.twig', ['count' => $count, 'table_name' => 'plugins']);
    }


    public function insertData(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        // connect to DB
        $db = new DB();
        $db->connect();
        $db->createTable();

        // get plugin repos in array form
        $pluginCollection = new PluginCollection();
        $plugins = $pluginCollection->getPlugins(2);

        // loop array of plugin objects and insert into database's plugins table
        if (isset($plugins)) {
            foreach ($plugins as $plugin) {
                $query = "
                INSERT INTO plugins (
                plugin_name, 
                owner_login,
                repo_link,
                description,
                date_created,
                date_pushed,
                date_updated,
                contributors_count,
                all_issues_count,
                open_issues_count,
                oldest_issue,
                newest_issue,
                forks_count,
                commits_count)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?,?,?,?)
                ON CONFLICT (plugin_name) DO UPDATE SET
                    owner_login=excluded.owner_login,
                    repo_link=excluded.repo_link,
                    description=excluded.description,                                                       
                    date_created=excluded.date_created,
                    date_pushed=excluded.date_pushed,
                    date_updated=excluded.date_updated,
                    contributors_count=excluded.contributors_count,
                    all_issues_count=excluded.all_issues_count,
                    oldest_issue=excluded.oldest_issue,                                 
                    newest_issue=excluded.newest_issue,                                                        
                    open_issues_count=excluded.open_issues_count,
                    forks_count=excluded.forks_count,
                    commits_count=excluded.commits_count
                ";
                $data = [
                    $plugin->name,
                    $plugin->owner_login,
                    $plugin->repo_link,
                    $plugin->description,
                    $plugin->date_created,
                    $plugin->date_pushed,
                    $plugin->date_updated,
                    $plugin->contributors_count,
                    $plugin->all_issues,
                    $plugin->open_issues_count,
                    $plugin->oldest_issue,
                    $plugin->newest_issue,
                    $plugin->forks_count,
                    $plugin->commits_count
                ];

                // insert plugin data
                $db->insertData($query, $data);


                $contributors = null;
                // insert contributors data
                if (isset($_GET) && isset($_GET['get'])) {
                    $contributors = preg_match('/^contributors$/', $_GET['get']);
                }
                if ($contributors) {
                    // get contributors in array form
                    $contributorCollection = new ContributorCollection($plugin->contributors_api_url, $plugin->name);
                    $contributors = $contributorCollection->getContributorCollection();
                    foreach ($contributors as $contributor) {
                        $query = "
                        INSERT INTO contributors (plugin_name, contributor_login, name, email, company)
                        VALUES (?, ?, ?, ?, ?)";

                        $data = [
                            $contributor->plugin_name,
                            $contributor->contributor_login,
                            $contributor->name,
                            $contributor->email,
                            $contributor->company,
                        ];

                        // insert plugin data
                        $db->insertData($query, $data);
                    }
                }

                $issues = null;
                // insert contributors data
                if (isset($_GET) && isset($_GET['get'])) {
                    $issues = preg_match('/^issues$/', $_GET['get']);
                }
                if ($issues) {
                    // get contributors in array form
                    $issuesCollection = new IssuesCollection(str_replace('{/number}', '?per_page=100&state=all',
                        $plugin->issues_url), $plugin->name);
                    $issues = $issuesCollection->getIssuesCollection();
                    foreach ($issues as $issue) {
                        $query = "
                        INSERT INTO issues (plugin_name, issues_node_id, title, body, state, issue_number, user_login, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                        ON CONFLICT (issues_node_id) DO UPDATE SET
                        title=excluded.title,
                        body=excluded.body,
                        state=excluded.state,
                        issue_number=excluded.issue_number,
                        user_login=excluded.user_login, 
                        created_at=excluded.created_at
                        ";

                        $data = [
                            $issue->plugin_name,
                            $issue->node_id,
                            $issue->title,
                            $issue->body,
                            $issue->state,
                            $issue->issue_number,
                            $issue->user_login,
                            $issue->created_at

                        ];

                        // insert plugin data
                        $db->insertData($query, $data);
                    }
                }
            }
        }
        $count = $db->countRows('plugins');
        $db->closeConnection();
        return $this->twig->render($response, 'update.twig', ['count' => $count, 'table_name' => 'plugins']);
    }
}