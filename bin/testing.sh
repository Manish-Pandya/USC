echo "HI"

#echo $VAL | perl -ne '{ /(TEST-\d+)/ && print "$1\n" }'
echo 'TEST-1, TEST-2' \
 | perl -ne 'while(/(TEST-\d+)/g){print "$&\n";}'