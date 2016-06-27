#!/bin/bash

# This script constantly tries an endpoint

HOSTNAME=demo-for-autoscaling-707880566.eu-west-1.elb.amazonaws.com

while true; do 
    curl --silent -m 2 http://$HOSTNAME/database.php | grep -i "rows" | awk '{print $(NF-5),"\t",$NF}'
    if [ $? -ne 0 ]; then
        echo "ERROR"
    fi

    sleep 0.2
done