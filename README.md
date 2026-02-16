# hemaScorecard
HEMA Scorecard - Tournament Management Software

Software to run and manage tournaments.

Developed by Sean Franklin
A HEMA Alliance supported project

## Running in docker
Install [docker](https://docs.docker.com/install/) and [docker compose] (https://docs.docker.com/compose/install/).
After that simply issue docker-compose up in the source root directory. The application will be avaliable on http://localhost:8000
PHP docker image contains xdebug, and project contains sample VS Code config which allows user to debug application via "Listen for XDebug" command.

## Data persistence
MySql data will be persisted to the ./data folder in the repository directory as per the docker compose. This can be altered if needed.

### Troubleshooting


If there is a need to get inside running container simply issue the following specifying the db or web container.

```bash
$ docker compose exec [db/web] /bin/bash
``` 

To login to the mysql database inside of the running db container. You'll be prompted for the password which you can find in the docker compose file.

```bash
$ docker compose exec db /bin/bash
$ mysql -uroot -p ScorecardV5
```

