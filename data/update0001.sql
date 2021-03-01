
-- ************************************** plugins

CREATE TABLE plugins
(
    plugin_name       TEXT PRIMARY KEY,
    owner_login       TEXT,
    contributor_login TEXT,
    open_issues_count INTEGER,
    commits_count     INTEGER,
    forks_count       INTEGER
);

CREATE TABLE contributors
(
    plugin_name       TEXT PRIMARY KEY,
    contributor_login TEXT,
    name              TEXT,
    email             TEXT,
    company           TEXT,
    FOREIGN  KEY (contributor_login) REFERENCES plugins (contributor_login)
);


-- ************************************** issues

CREATE TABLE issues
(
    issues_node_id    TEXT PRIMARY KEY,
    title             TEXT,
    body              TEXT,
    state             TEXT,
    issue_number      INTEGER,
    issue_login       TEXT,
    plugin_name       TEXT,
    FOREIGN KEY (plugin_name) REFERENCES plugins (plugin_name)
);

-- ************************************** owner

CREATE TABLE owners
(
    owner_login       TEXT PRIMARY KEY,
    plugin_name       TEXT,
    name              TEXT,
    email             TEXT,
    company           TEXT,
    FOREIGN KEY (owner_login) REFERENCES plugins (owner_login)
);