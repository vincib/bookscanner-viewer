#!/bin/bash

# parameters to add to convert 
SCRIPT="-resample 150" 

files=""
allpdf=""
i=0
j=0
for file in *.tif
do
    files="$files $file"
    i=$(($i + 1))
    if [ "$i" -eq "10" ]
    then
	echo "Converting $files to PDF $j"
	convert $files $SCRIPT ../${j}.pdf
	files=""
	i=0
	allpdf="$allpdf ${j}.pdf"
	j=$(($j + 1))
    fi
done
convert $files $SCRIPT ../${j}.pdf
allpdf="$allpdf ${j}.pdf"
j=$(($j + 1))
cd ..
echo "Now concatenating all PDF into one:"
echo "$allpdf into book.pdf"
pdftk $allpdf cat output book.pdf
echo "Cleanup"
rm -f $allpdf
echo "Done"
