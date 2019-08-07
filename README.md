Panel Server Video
=================

Panel for upload, administration and auto off server if not use (with Emby)

add in cron root:
```
* * * * * /usr/bin/php /<you_dir>/bin/console app:cron >/dev/null 2>&1 && /<you_dir>/src/CronBundle/Sbin/checkreboot.sh >/dev/null 2>&1 #cron panel video
```
A Symfony project created on July 27, 2019, 6:58 am.
