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
    public $forks_count;
    public $contributors_count;
    public $all_issues;
    public $oldest_issue;
    public $newest_issue;
    public $commits_count;
    public $average_commits_per_year;
    public $contributors_api_url;

    function __construct($pluginData)
    {
        $this->name = $pluginData['name'];
        $this->owner_login = $pluginData['owner']['login'];
        $this->repo_link = $pluginData['html_url'];
        $this->description = $pluginData['description'];
        $this->date_created = date('Y-m', strtotime($pluginData['created_at']));
        $this->date_pushed = date('Y-m', strtotime($pluginData['pushed_at']));
        $this->date_updated = date('Y-m-d', strtotime($pluginData['updated_at']));
        $this->open_issues_count = $pluginData['open_issues_count'];
        $this->forks_count = $pluginData['forks_count'];
        $this->contributors_count = Services::getApiData($pluginData['contributors_url'], 'count');
        $this->all_issues = Services::getApiData(str_replace('{/number}', '?per_page=100&state=all',
            $pluginData['issues_url']), 'count');
        $this->oldest_issue = $pluginData['open_issues'] ? $this->getPluginIssueByAge('oldest',
            str_replace('{/number}', '?per_page=100&state=all', $pluginData['issues_url'])) : 'No Open Issues';
        $this->newest_issue = $pluginData['open_issues'] ? $this->getPluginIssueByAge('newest',
            str_replace('{/number}', '?per_page=100&state=all', $pluginData['issues_url'])) : 'No Open Issues';
        $this->commits_count = $this->getPluginCommitsCount(str_replace('{/sha}', '?per_page=100&state=all',
            $pluginData['commits_url']));
        $this->average_commits_per_year = $this->calcAvgCommits(str_replace('{/sha}', '?per_page=100&state=all',
            $pluginData['commits_url']), $this->date_created);
        $this->contributors_api_url = $pluginData['contributors_url'];
    }

    /**
     * @param string $age specifies whether to get the newest or oldest issue's title
     * @param string $url github REST API url with parameters
     * @return string the title of the issue
     */

    // TODO: Add check if state is open
    // FIXME: Not all the oldest issues are displayed

    private function getPluginIssueByAge($age, $url)
    {
        $issueTitle = '';
        $issuesArray = Services::getApiData($url, '');

        if ($age === 'newest') {
            $tempNewest = 0;
            foreach ($issuesArray as $key => $issue) {
                $currentIssueTimestamp = strtotime($issue['created_at']);
                if ($issue['state'] === 'closed') {
                    continue;
                }

                $tempNewest = $currentIssueTimestamp >= $tempNewest ? $currentIssueTimestamp : $tempNewest;

                if ($tempNewest <= $currentIssueTimestamp) {
                    $issueTitle = "'" . $issue['title'] . "'" . ' created at ' . date('Y-m-d H:m',
                            strtotime($issue['created_at']));
                }
            }
        }

        if ($age === 'oldest') {
            $tempOldest = 0;
            $oldestFirst = array_reverse($issuesArray);
            foreach ($oldestFirst as $key => $issue) {
                $currentIssueTimestamp = strtotime($issue['created_at']);
                if ($issue['state'] === 'closed') {
                    continue;
                }
                if ($key === 0) {
                    $tempOldest = $currentIssueTimestamp;
                } else {
                    $tempOldest = $currentIssueTimestamp <= $tempOldest ? $currentIssueTimestamp : $tempOldest;
                }


                if ($tempOldest >= $currentIssueTimestamp) {
                    $issueTitle = "'" . $issue['title'] . "'" . ' created at ' . date('Y-m-d H:m',
                            strtotime($issue['created_at']));
                }
            }
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