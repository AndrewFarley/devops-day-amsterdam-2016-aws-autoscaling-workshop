#!/bin/bash

# This script constantly tries an endpoint

HOSTNAME=demo-for-autoscaling-707880566.eu-west-1.elb.amazonaws.com

while true; do 
    curl --silent http://$HOSTNAME/database.php | grep -i "rows" | awk '{print $(NF-5),"\t",$NF}'

    sleep 0.5
done