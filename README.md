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


If there is a need to get inside running container simply issue 

```bash
$ docker-compose exec db /bin/bash
``` 

to get into mysql container, and 

```bash
$ docker-compose exec web /bin/bash
```

to get to the web container, respectively.
