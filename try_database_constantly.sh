#!/bin/bash

# This script constantly tries an endpoint

HOSTNAME=demo-for-autoscaling-707880566.eu-west-1.elb.amazonaws.com

while true; do 
    curl http://$HOSTNAME/database.php
    sleep 0.2
done