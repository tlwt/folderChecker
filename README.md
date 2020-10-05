# folderChecker

![consider it GmbH](https://img.shields.io/badge/project%20by-CIT-orange)

When managing multiple projects, a coherent folder structure is essential, so that in case of an emergency anybody can take over. This is what the folderChecker does. It compares project directories against a template directory.

Some additional checks it does:
* that each specified folder contains at least one file. Only the relevant contract etc. should be in the main directory.
* Drafts, versions etc need to go into an `archive` folder. Folders with that name will not be checked
* In case there is no file for the corresponding template directory put a `not applicable *.txt` into the directory.
* a `due yyyy-mm-dd.txt` will be checked wrt. how many days are left and "overdue" warning will be visible.


## how to set it up?

In the docker-compose.yml map the `/foldercheck` folder to your projects and map the `/template/` folder to your template directory.

```
- /Users/tillwitt/.../42_Running_Projects/:/foldercheck/:ro
- /Users/tillwitt/.../99 Templates/new project folders (admin)/:/template/:ro
```

then run

```
docker-compose build && docker-compose up
```

### it doesnt work

please check the `debug.php` if it shows the right folder content.

access http://localhost:4081
