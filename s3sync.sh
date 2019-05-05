s3cmd -P -r --no-check-certificate sync "themes/bsbuidling/css/" "s3://s3.buidl.guide/themes/bsbuidling/css/"
s3cmd -P -r --no-check-certificate sync "themes/bsbuidling/js/" "s3://s3.buidl.guide/themes/bsbuidling/js/"
s3cmd -P -r --no-check-certificate sync "sites/default/files/web/" "s3://s3.buidl.guide/sites/default/files/web/"
s3cmd -P -r --no-check-certificate sync "sites/default/files/styles/" "s3://s3.buidl.guide/sites/default/files/styles/"
s3cmd -P -r --no-check-certificate sync "sites/default/files/media-icons/" "s3://s3.buidl.guide/sites/default/files/media-icons/"

