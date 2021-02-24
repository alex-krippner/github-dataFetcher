<?php

namespace githubDataFetcher\model;

use \githubDataFetcher\inc\Services as Services;


class Plugin
{

    /**
     * @var Services instance for connecting with github REST API
     */
    private $services;


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

    function __construct($pluginData)
    {
        $this->services = new Services();

        $this->name = $pluginData['name'];
        $this->link = sprintf('<a href="%s">%s</a>', $pluginData['html_url'], $pluginData['name']);
        $this->description = $pluginData['description'];
        $this->created = date('Y-m', strtotime($pluginData['created_at']));
        $this->pushed = date('Y-m', strtotime($pluginData['pushed_at']));
        $this->open_issues_count = $pluginData['open_issues_count'];
        $this->fork = $pluginData['fork'] ? 'fork' : '';
        $this->contributors = $this->services->getApiData($pluginData['contributors_url'], 'count');
        $this->all_issues = $this->services->getApiData(str_replace('{/number}', '?per_page=100&state=all',
            $pluginData['issues_url']), 'count');
        $this->oldest_issue = $this->getPluginIssueByAge('oldest',
            str_replace('{/number}', '?per_page=100&state=all', $pluginData['issues_url']));
        $this->newest_issue = $this->getPluginIssueByAge('newest',
            str_replace('{/number}', '?per_page=100&state=all', $pluginData['issues_url']));
        $this->commits = $this->getPluginCommitsCount(str_replace('{/sha}', '?per_page=100&state=all',
            $pluginData['commits_url']));
        $this->average_commits_per_year = $this->calcAvgCommits(str_replace('{/sha}', '?per_page=100&state=all',
            $pluginData['commits_url']), $this->created);
    }

    /**
     * @param string $age specifies whether to get the newest or oldest issue's title
     * @param string $url github REST API url with parameters
     * @return string the title of the issue
     */

    private function getPluginIssueByAge($age, $url)
    {
        $issueTitle = '';
        $issuesArray = $this->services->getApiData($url, '');
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
        $commitsArray = $this->services->getApiData($url, '');

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