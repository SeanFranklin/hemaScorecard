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
In order to persist data after the first, initial run, comment out first volume in db service (`- ./includes:/docker-entrypoint-initdb.d`) and uncomment the second one (`./data:/var/lib/mysql`)
Data is persisted in ./data folder.

### Troubleshooting
In case of issues with running web container (`xdebug install failed`) issue

```bash
$ docker-compose down
$ docker composer rm -fsv
```

If there is a need to get inside running container simply issue 

```bash
$ docker-compose exec db /bin/bash
``` 

to get into mysql container, and 

```bash
$ docker-compose exec web /bin/bash
```

to get to the web container, respectively.
