#!/bin/bash

git pull

ps aux | grep prflr | grep -v grep | awk '{print $2}' | xargs -i kill {}

go build ./prflr.go

nohup ./prflr > /dev/null 2>&1 &

