#!/bin/bash

cd `dirname $0`
HERE=`pwd`

cd /tr2/bookscanner/bookscanner
for project in `find -mindepth 1 -maxdepth 1 -type d` 
do
    if [ -f "$project/genpdf" ]
    then
	echo "$(date '+%Y%m%d %H:%M:%S') Building PDF for $project"
	pushd "$project/booktif"
	$HERE/tif2pdf.sh
	rm ../genpdf
	popd
	echo "$(date '+%Y%m%d %H:%M:%S') DONE"
    fi
done
