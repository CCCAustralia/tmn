TMN - Total Monthly Needs
===
A budget calculation form created by CCCA

Requirements
---
Runs well with MAMP

Contributing
---
To contriubte our team uses the [Feature Branch Method](https://www.atlassian.com/git/tutorials/comparing-workflows/feature-branch-workflow) or you can just submit a pull request.

Publishing Code
---
To deploy run
```
. deploy.sh {user_name} {password} {config_path} {deployment_type=["stage", "production"]} {full_refresh=[true, false]} {version_number}
```
production pulls code from master branch and puts it on the production server
stage pulls code from the dev branch and puts it on the staging server

e.g.
```
. deploy.sh user_name password full/path/to/config.stage.json stage true 2.5
```
Help
---
For help email tech.team@ccca.org.au
