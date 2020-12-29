#!/bin/bash
curl -c ./tmp/cookie -s -L "https://drive.google.com/uc?export=download&id=$1" > /dev/null
curl -Lb ./tmp/cookie "https://drive.google.com/uc?export=download&confirm=`awk '/download/ {print $NF}' ./tmp/cookie`&id=$1" -o $2
