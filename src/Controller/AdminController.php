<?php

namespace Mon\Oversight\Controller;

use Mon\Oversight\inc\DB;
use Mon\Oversight\Model\ContributorCollection;
use Mon\Oversight\Model\PluginCollection;
use Mon\Oversight\Model\IssuesCollection;
use Mon\Oversight\Model\PullsCollection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;

/**
 * TODO: Write logic to update plugin repos data
 * create array of plugin repo names
 * loop array and fetch repo data like in the form of repos.json and save in Plugin class object
 * refactor dependencies of Plugin class
 * write logic to update at intervals or when repos have been modified or both?
 */
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
        $db->createTable();
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
        preg_match('/^\d+$/', $_REQUEST['plugins-number'], $pluginsRequestAmount);
        if (!isset($pluginsRequestAmount[0]) || $pluginsRequestAmount[0] === '0') {
            $message = 'Please only enter numbers that are 1 or greater';
            return $this->twig->render($response, 'error.twig', ['pageName' => 'Error', 'message' => $message]);
        }

        // get plugin repos in array form
        $pluginCollection = new PluginCollection();
        $plugins = $pluginCollection->getPlugins($pluginsRequestAmount[0]);

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
                all_issues_count,
                open_issues_count,
                closed_issues_count,
                newest_issue,
                oldest_issue,
                forks_count,
                commits_count,
                stars_count,
                watchers_count,
                age,
                avg_commits_per_year
           )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON CONFLICT (plugin_name) DO UPDATE SET
                    owner_login=excluded.owner_login,
                    repo_link=excluded.repo_link,
                    description=excluded.description,                                                       
                    date_created=excluded.date_created,
                    date_pushed=excluded.date_pushed,
                    date_updated=excluded.date_updated,
                    all_issues_count=excluded.all_issues_count,                                                    
                    open_issues_count=excluded.open_issues_count,
                    closed_issues_count=excluded.closed_issues_count,
                    newest_issue=excluded.newest_issue,    
                    oldest_issue=excluded.oldest_issue,                                 
                    forks_count=excluded.forks_count,
                    commits_count=excluded.commits_count,
                    stars_count=excluded.stars_count,
                    watchers_count=excluded.watchers_count,
                    age=excluded.age,
                    avg_commits_per_year=excluded.avg_commits_per_year                                    
                ";
                $data = [
                    $plugin->name,
                    $plugin->owner_login,
                    $plugin->repo_link,
                    $plugin->description,
                    $plugin->date_created,
                    $plugin->date_pushed,
                    $plugin->date_updated,
                    $plugin->all_issues,
                    $plugin->open_issues_count,
                    $plugin->closed_issues_count,
                    $plugin->newest_issue,
                    $plugin->oldest_issue,
                    $plugin->forks_count,
                    $plugin->commits_count,
                    $plugin->stars_count,
                    $plugin->watchers_count,
                    $plugin->age,
                    $plugin->avg_commits_per_year
                ];

                // insert plugin data
                $db->insertData($query, $data);

                if (isset($plugin->pulls) && is_array($plugin->pulls)) {
                    $pullsCollection = new PullsCollection($plugin->pulls, $plugin->name);
                    $pullsArray = $pullsCollection->getPullsCollection();
                    foreach ($pullsArray as $pull) {
                        $query = "
                        INSERT INTO pulls (pull_node_id, plugin_name, title, state, user_login, body, created_at, closed_at, merged_at, pull_url)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ON CONFLICT (pull_node_id) DO UPDATE SET
                        plugin_name=excluded.plugin_name,
                        title=excluded.title,
                        state=excluded.state,
                        user_login=excluded.user_login,
                        body=excluded.body, 
                        created_at=excluded.created_at,
                        closed_at=excluded.closed_at,
                        merged_at=excluded.merged_at,
                        pull_url=excluded.pull_url
                        ";

                        $data = [
                            $pull->node_id,
                            $pull->plugin_name,
                            $pull->title,
                            $pull->state,
                            $pull->user_login,
                            $pull->body,
                            $pull->created_at,
                            $pull->closed_at,
                            $pull->merged_at,
                            $pull->html_url
                        ];

                        // insert pulls data
                        $db->insertData($query, $data);
                    }
                }

                // TODO: Add checks for conflicts to avoid duplicate data when updating.
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

                    // insert contributors data
                    $db->insertData($query, $data);
                }


                // get issues in array form
                $issuesCollection = new IssuesCollection(str_replace('{/number}', '?per_page=100&state=all',
                    $plugin->issues_url), $plugin->name);
                $issues = $issuesCollection->getIssuesCollection();
                foreach ($issues as $issue) {
                    $query = "
                        INSERT INTO issues (plugin_name, issues_node_id, title, body, state, issue_number, user_login, created_at, closed_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ON CONFLICT (issues_node_id) DO UPDATE SET
                        title=excluded.title,
                        body=excluded.body,
                        state=excluded.state,
                        issue_number=excluded.issue_number,
                        user_login=excluded.user_login, 
                        created_at=excluded.created_at,
                        closed_at=excluded.closed_at
                        ";

                    $data = [
                        $issue->plugin_name,
                        $issue->node_id,
                        $issue->title,
                        $issue->body,
                        $issue->state,
                        $issue->issue_number,
                        $issue->user_login,
                        $issue->created_at,
                        $issue->closed_at

                    ];

                    // insert issues  data
                    $db->insertData($query, $data);
                }
            }
        }
        $count = $db->countRows('plugins');
        $db->closeConnection();
        return $this->twig->render($response, 'update.twig',
            ['pluginsRequested' => $pluginsRequestAmount[0], 'count' => $count, 'table_name' => 'plugins']);
    }
}