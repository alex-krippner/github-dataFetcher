<?php

namespace Mon\Oversight\Model;

use Mon\Oversight\inc\Services;

// TODO: Add a plugin owner property

class Plugin
{
    public $name;
    public $owner_login;
    public $repo_link;
    public $description;
    public $date_created;
    public $date_pushed;
    public $date_updated;
    public $open_issues_count;
    public $closed_issues_count;
    public $forks_count;
    public $all_issues;
    public $oldest_issue;
    public $newest_issue;
    public $commits_count;
    public $contributors_api_url;
    public $issues_url;
    public $pulls;
    public $stars_count;
    public $watchers_count;
    public $age;
    public $avg_commits_per_year;

    function __construct($pluginData)
    {
        $this->name = $pluginData['name'];
        $this->owner_login = $pluginData['owner']['login'];
        $this->repo_link = $pluginData['html_url'];
        $this->description = $pluginData['description'];
        $this->date_created = date('Y-m', strtotime($pluginData['created_at']));
        $this->date_pushed = date('Y-m', strtotime($pluginData['pushed_at']));
        $this->date_updated = date('Y-m-d', strtotime($pluginData['updated_at']));
        $this->all_issues = Services::getApiData(str_replace('{/number}', '?per_page=100&state=all',
            $pluginData['issues_url']), 'count');
        $this->open_issues_count = $pluginData['open_issues_count'];
        $this->closed_issues_count = $this->all_issues - $this->open_issues_count;
        $this->forks_count = $pluginData['forks_count'];
        $this->oldest_issue = $pluginData['open_issues'] ? $this->getPluginIssueByAge('oldest',
            str_replace('{/number}', '?per_page=100&state=open&sort=created&direction=asc',
                $pluginData['issues_url'])) : 'No Open Issues';
        $this->newest_issue = $pluginData['open_issues'] ? $this->getPluginIssueByAge('newest',
            str_replace('{/number}', '?per_page=100&state=open&sort=created&direction=desc',
                $pluginData['issues_url'])) : 'No Open Issues';
        $this->commits_count = $this->getPluginCommitsCount(str_replace('{/sha}', '?per_page=100&state=all',
            $pluginData['commits_url']));
        $this->contributors_api_url = $pluginData['contributors_url'];
        $this->issues_url = $pluginData['issues_url'];
        $this->pulls = Services::getApiData(str_replace('{/number}', '?per_page=100&state=all',
            $pluginData['pulls_url']), '');
        $this->stars_count = $pluginData['stargazers_count'];
        $this->watchers_count = $pluginData['watchers_count'];
        $this->age = date('Y') - date('Y', strtotime($this->date_created));
        $this->avg_commits_per_year = $this->commits_count / $this->age;
    }

    /**
     * @param string $age specifies whether to get the newest or oldest issue's title
     * @param string $url github REST API url with parameters
     * @return string the title of the issue
     */

    private function getPluginIssueByAge($age, $url)
    {
        $issueTitle = '';
        $issuesArray = Services::getApiData($url, '');

        if ($age === 'newest') {
            $issueTitle = "'" . $issuesArray[0]['title'] . "'" . ' created at ' . date('Y-m-d H:m',
                    strtotime($issuesArray[0]['created_at']));
        }

        if ($age === 'oldest') {
            $issueTitle = "'" . $issuesArray[0]['title'] . "'" . ' created at ' . date('Y-m-d H:m',
                    strtotime($issuesArray[0]['created_at']));
        }

        return $issueTitle;
    }

    /**
     * @param string $url github REST API url with parameters
     * @return int the total number of commits
     */

    private function getPluginCommitsCount($url)
    {
        $commitsArray = Services::getApiData($url, '');

        return !$commitsArray ? 0 : count($commitsArray);
    }

}