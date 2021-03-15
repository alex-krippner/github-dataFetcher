-- ************************************** plugins

CREATE TABLE IF NOT EXISTS plugins
(
    plugin_name          TEXT PRIMARY KEY,
    owner_login          TEXT,
    repo_link            TEXT,
    description          TEXT,
    date_created         TEXT,
    date_pushed          TEXT,
    date_updated         TEXT,
    all_issues_count     INTEGER,
    open_issues_count    INTEGER,
    closed_issues_count  INTEGER,
    newest_issue         TEXT,
    oldest_issue         TEXT,
    commits_count        INTEGER,
    forks_count          INTEGER,
    stars_count          INTEGER,
    watchers_count       INTEGER,
    age                  INTEGER,
    avg_commits_per_year INTEGER
);

CREATE TABLE IF NOT EXISTS contributors
(
    plugin_name       TEXT,
    contributor_login TEXT,
    name              TEXT,
    email             TEXT,
    company           TEXT,
    FOREIGN KEY (plugin_name) REFERENCES plugins (plugin_name)
);


-- ************************************** issues

CREATE TABLE IF NOT EXISTS issues
(
    issues_node_id TEXT PRIMARY KEY,
    title          TEXT,
    body           TEXT,
    state          TEXT,
    issue_number   INTEGER,
    user_login     TEXT,
    plugin_name    TEXT,
    created_at     TEXT,
    closed_at      TEXT,
    FOREIGN KEY (plugin_name) REFERENCES plugins (plugin_name)
);

-- ************************************** owner

CREATE TABLE IF NOT EXISTS owners
(
    owner_login TEXT PRIMARY KEY,
    plugin_name TEXT,
    name        TEXT,
    email       TEXT,
    company     TEXT,
    FOREIGN KEY (owner_login) REFERENCES plugins (owner_login)
);

CREATE TABLE IF NOT EXISTS pulls
(
    pull_node_id TEXT PRIMARY KEY,
    plugin_name  TEXT,
    title        TEXT,
    state        TEXT,
    user_login   TEXT,
    body         TEXT,
    created_at   TEXT,
    closed_at    TEXT,
    merged_at    TEXT,
    pull_url     TEXT,
    FOREIGN KEY (plugin_name) REFERENCES plugins (plugin_name)
)