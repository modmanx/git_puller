cd $1
"$GIT_BIN_PATH"git.exe reset --hard HEAD
"$GIT_BIN_PATH"git.exe pull origin $2
echo "done"
