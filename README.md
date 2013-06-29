git_puller
==========

git puller (win &amp; nix)

Set up:
1) copy all files to web-server
2) make it accessible fe: deploy.my-server.com/pull_changes.php
3) copy config.sample.php to config.php
4) edit pull_changes.php set $server_name to your server name used in config.php
5) add service URL hook at github

NOTES for Windows users:
install msysgit
set SYS ENV VAR "HOME=c:/users/administrator"
set SYS ENV VAR "GIT_BIN_PATH=c:/Program Files (x86)/Git/bin/"
and make sure that c:/Users/Administrator//.gitconfig have permission for IUSR
make deploykey and put it to github
