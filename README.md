# Research and Safety Management System (RSMS)
The Research and Safety Management System (RSMS) is comprised of a PHP Server Application and an AngularJS frontend application. The source files are contained in a Git repository in Bitbucket, currently located at:

* https://bitbucket.org/mitch_ehs/erasmus

### Foreward
This document is intended to be a quick-and-dirty overview of the RSMS project and the processes in place to manage it. Older documents are availalbe within the repository which attempted to describe parts of this, though they may be outdated. This project already has a long history, and has gone through several iterations of typical legacy refactorings. During my time here, I have tried to implement and keep to best-practices wherever possible.

I encourage you to read and understand the inner workings of these processes, as well as the application code, and always strive to make them better.

---- Mitch Martin

## Environments
There are two DoIT-managed virtual-server environments in which the RSMS project is deployed:

* Test RSMS | `safety-compliance-test-web1.sc.edu`
* Prod RSMS | `safety-compliance-prod-web1.sc.edu`

These linux servers have had PHP 7 installed, which deviates from the standard RedHat installations. PHP upgrades must me managed manually to remain up-to-date with current PHP versions.

### Key Directories:
* Application root: `/var/www/html/rsms`
* Application management: `/var/rsms`
* Scripts: `/var/rsms/scripts`
* Deployment Staging: `/var/rsms/deployments`
* Backpus: `/var/rsms/backups`

## Scheduled Jobs
DoIT manages long-term backups of virtual environments. EHS has also scheduled nightly backups of both Test and Prod instances, with the Prod backups additionally copied to the Test server.

The RSMS `MessagingModule` is intended to conduct regular scans of messages to be sent, and is triggered regularly via cron.

## Project Management
Tasks are defined as tickets in the EHS Jira project for RSMS: https://uscehs.atlassian.net/browse/RSMS

### Ticket Types
Common ticket types and practices are used:
* `New Feature`
* `Bug`
* `Task`
* `Epic`

### Worklow

The Jira ticket workflow was expanded to involve several steps between Developer and Manager roles

* `Backlog`: This task is ready to be selected and implemented
* `On Hold`: This task may require additional information or investigation
* `In Progress`: This task is currently in-development
* `Development Complete`: Development is complete, task is not deployed
* `Review in Test`: Task is deployed to the `Test` server and ready for review
* `Verified in Test`: Task has been verified in `Test` server
* `Approved for Prod`: Task is selected for Prod deployment
* `Review in Prod`: Task is deployed to Prod and ready for final review
* `Verified in Prod`: Task has been verified in `Prod` server
* `Closed`: No further work is required
* `Cancelled`: Task no longer needs to be completed

## Branching Strategy

The code is branched using the following strategy:

| Branch 	| Purpose 	|
|-	|-	|
| `master` 	| Main branch; tagged production releases 	|
| `develop` 	| Development branch; production staging 	|
| `stage/snapshot` 	| Snapshot branch intended to be deleted and recreated for test-server deployments 	|
| `RSMS-*` 	| Task branch for ticket-specific development 	|

Development occurs in a task branch, prefixed with the relevant ticket number (_For example: `RSMS-910-mylab` was created for the `RSMS-910` ticket, related to My Lab_).

If that branch requires user testing during development, the task branch is merged into `staging/snapshot` and deployed from there along with other in-progress development.

Once development is completed for a task and approved for production deployment, it is merged into `develop`.

Once a production deployment is scheduled, a Release is created in Jira, the `develop` branch is merged into `master`, and a tag is created:

```
git checkout master
git merge origin/develop
git tag -a 20200928_2119 -m "RSMS v20200928_2119"
git push --follow-tags
```

## Deployment
When a deployment is to be made, the code is packaged into a `.tar.gz` file, uploaded to the target environment(s), and deployed. This process is supported by several utility scripts, available and described further in the [/bin directory](/bin/README.md) directory.

Additionally, these processes are described in more detail [here](/devops/rsms-devops.md).

The following example goes through the process of building and deploying the `develop` branch to the Test server.

### 0. Push code to be deployed
The RSMS utility scripts do not package from the local working directory, and instead clone the `erasmus` project to ensure a clean package.

```
git checkout develop
git merge RSMS-123-example
git push
```

### 1. Package the project
Using the `build` command, generate a deployable version of the `develop` branch. This copies the relevent source files, database migrations, and deployment scripts into a `.tar.gz` file.

```
rsms build develop
```

This process creates a package in a local temp directory and outputs the name of the package. For example: `build-develop-2020911_2258-148-gaa4c09ff.tar.gz`

### 2. Upload the package to the `Test` server
Using the `stage remote` command, upload the package to the `test` enviroment:

```
rsms stage remote test build-develop-2020911_2258-148-gaa4c09ff.tar.gz
```

This process uploads the named package into the environment's `/var/rsms/deployments` directory

### 3. Connect to the `Test` environment
Using the `ssh` command, connect to the `test` environment:

```
rsms ssh test
```

### 4. Unpack the deployment
Unpack the `.tar.gz` file and navigate into the new directory

```
cd /var/rsms/deployments
tar zxf build-develop-2020911_2258-148-gaa4c09ff.tar.gz
cd build-develop-2020911_2258-148-gaa4c09ff
```

### 5. Execute the deployment
The following steps are described separately as a demonstration of the whole process. However, the included `deploy.sh` script executes all 3 steps in sequence.

#### 5.a Backup the Database and current Docroot
```
./backup.sh
```

#### 5.b Verify and execute migrations
```
./migrate.sh
```

#### 5.c Copy source files to docroot
```
./deploy.sh
```

# RSMS Development
Development environment setup is a presonal preference, but I have had great success in using [Devilbox](https://devilbox.readthedocs.io/en/latest/) to manage and containerize a local LAMP stack for development. Devilbox setup is beyond the scope of this document, but their documentation is comprehensive. An added advantage here is that it can be easily maintained to mimic the `test` and `prod` environments.

At the time of writing, there is unfortunately no simple DDL/DML scripts for initializing a new database. Backups can be easily obtained from the `Test RSMS` server.
