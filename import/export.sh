#!/bin/bash
curl -c ./cookie -s -L "https://drive.google.com/uc?export=download&id=$1" > /dev/null
curl -Lb ./cookie "https://drive.google.com/uc?export=download&confirm=`awk '/download/ {print $NF}' ./cookie`&id=$1" -o $2
