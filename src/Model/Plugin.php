<?php

namespace Mon\Oversight\Model;

use Mon\Oversight\inc\Services;

// TODO: Add a plugin owner property

class Plugin
{
    public $name;
    public $link;
    public $description;
    public $created;
    public $pushed;
    public $open_issues_count;
    public $fork;
    public $contributors;
    public $all_issues;
    public $oldest_issue;
    public $newest_issue;
    public $commits;
    public $average_commits_per_year;
    public $contributors_api_url;

    function __construct($pluginData)
    {
        $this->name = $pluginData['name'];
        $this->link = sprintf('<a href="%s">%s</a>', $pluginData['html_url'], $pluginData['name']);
        $this->description = $pluginData['description'];
        $this->created = date('Y-m', strtotime($pluginData['created_at']));
        $this->pushed = date('Y-m', strtotime($pluginData['pushed_at']));
        $this->open_issues_count = $pluginData['open_issues_count'];
        $this->fork = $pluginData['fork'] ? 'fork' : '';
        $this->contributors = Services::getApiData($pluginData['contributors_url'], 'count');
        $this->all_issues = Services::getApiData(str_replace('{/number}', '?per_page=100&state=all',
            $pluginData['issues_url']), 'count');
        $this->oldest_issue = $this->getPluginIssueByAge('oldest',
            str_replace('{/number}', '?per_page=100&state=all', $pluginData['issues_url']));
        $this->newest_issue = $this->getPluginIssueByAge('newest',
            str_replace('{/number}', '?per_page=100&state=all', $pluginData['issues_url']));
        $this->commits = $this->getPluginCommitsCount(str_replace('{/sha}', '?per_page=100&state=all',
            $pluginData['commits_url']));
        $this->average_commits_per_year = $this->calcAvgCommits(str_replace('{/sha}', '?per_page=100&state=all',
            $pluginData['commits_url']), $this->created);
        $this->contributors_api_url = $pluginData['contributors_url'];
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
        if (!$issuesArray) {
            return 'No issues';
        }
        if ($age === 'newest') {
            $issueTitle = $issuesArray[0]['title'];
        }

        if ($age === 'oldest') {
            $issueTitle = $issuesArray[count($issuesArray) - 1]['title'];
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

    /**
     * @param string $url github REST API url with parameters
     * @param string $repoCreatedDate the YYYY-MM the repo was created
     * @return int the average commits per year
     */


    private function calcAvgCommits($url, $repoCreatedDate)
    {
        $commitsCount = $this->getPluginCommitsCount($url);

        return $commitsCount === 0 ? 'No commits' : (date('Y') - date('Y',
                strtotime($repoCreatedDate)));
    }
}