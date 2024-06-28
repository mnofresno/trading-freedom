#!/bin/bash
docker build -f docker/Dockerfile --build-arg user=www-data --build-arg uid=1000  -t ghcr.io/mnofresno/trading_freedom_php7.4:0.0.1 .
