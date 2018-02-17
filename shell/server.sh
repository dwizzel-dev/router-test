#!/bin/bash
command=$1
if [ "$command" == "start" ] || [ "$command" == "" ];then
	sudo systemctl restart nginx.service
	sudo systemctl restart php-fpm.service
	sudo ps -aux | grep "nginx\|php-fpm"
	sudo tail -f /var/log/nginx/router-test.dwizzel.local-error.log
elif [ "$command" == "stop" ];then
	sudo systemctl stop nginx.service
	sudo systemctl stop php-fpm.service
else
	echo "start|stop"
fi	
exit 0
