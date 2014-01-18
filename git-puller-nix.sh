cd $1
if [ ! -d .git ]; then
        git init
        git remote add origin $3
        git pull
        git checkout $2
else
        git reset --hard HEAD
        git pull origin $2
fi
echo "done"
