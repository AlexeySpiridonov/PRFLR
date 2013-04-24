#!/bin/bash

git pull

go build ./prflr.go

ps aux | grep prflr | grep -v grep | awk '{print $2}' | xargs -i kill {}

nohup ./prflr > /dev/null 2>&1 &

