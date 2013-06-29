git_puller
==========

git puller (win &amp; nix)

Set up:
------
* copy all files to web-server
* make it accessible fe: deploy.my-server.com/pull_changes.php
* copy config.sample.php to config.php
* edit config.php ... use deploy.my-server.com for your server_name in repositories
* add service URL hook at github

NOTES (win):
------------
* install msysgit
* set SYS ENV VAR "HOME=c:/users/administrator"
* set SYS ENV VAR "GIT_BIN_PATH=c:/Program Files (x86)/Git/bin/"
* and make sure that c:/Users/Administrator//.gitconfig have permission for IUSR
* make deploykey and put it to github

NOTES (nix):
* chmod 0777 git-puller-nix.sh first time
* git config --global core.filemode false
* add following post-merge hook
  #!/bin/sh
  echo "setting persmissions"
  echo "curr dir"
  echo $(pwd)
  env -i chmod 0777 git-puller-nix.sh

TODO:
-----

- [ ] rename files
- [ ] do more checks
