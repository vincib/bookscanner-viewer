#!/bin/bash

cd `dirname $0`
HERE=`pwd`

cd /tr2/bookscanner/bookscanner
for project in `find -mindepth 1 -maxdepth 1 -type d` 
do
    if [ -f "$project/genocr" ]
    then
	echo "$(date '+%Y%m%d %H:%M:%S') Building OCR for $project"
	pushd "$project/booktif"
	$HERE/pocr
	rm ../genocr
	popd
	echo "$(date '+%Y%m%d %H:%M:%S') DONE"
    fi
done
