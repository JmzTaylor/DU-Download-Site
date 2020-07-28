# DU Download Site
Example PHP-FPM 7.3 & Nginx 1.18 setup for Docker, build on [Alpine Linux](http://www.alpinelinux.org/).
The image is only +/- 35MB large.

* Built on the lightweight and secure Alpine Linux distribution
* Very small Docker image size (+/-35MB)
* Uses PHP 7.3 for better performance, lower CPU usage & memory footprint
* Optimized to only use resources when there's traffic (by using PHP-FPM's on-demand PM)
* The servers Nginx, PHP-FPM and supervisord run under a non-privileged user (nobody) to make it more secure
* The logs of all the services are redirected to the output of the Docker container (visible with `docker logs -f <container name>`)
* Follows the KISS principle (Keep It Simple, Stupid) to make it easy to understand and adjust the image to your needs
* Slack slash commands integration

[![Docker Pulls](https://img.shields.io/docker/pulls/jmzsoftware/du_download_site.svg)](https://hub.docker.com/r/jmzsoftware/du_download_site/)
![nginx 1.18.0](https://img.shields.io/badge/nginx-1.18-brightgreen.svg)
![php 7.3](https://img.shields.io/badge/php-7.3-brightgreen.svg)
![License MIT](https://img.shields.io/badge/license-MIT-blue.svg)
![License APACHE](https://img.shields.io/badge/license-APACHE-blue.svg)

## Usage

Most configuration is done in the .env and the docker-compose.yml.
You can clone this repo and rename .env.sample to .env and change all the parameters.  
You will need to change the docker-compose.yml volume entry to point the devices folder to the proper location on the filesystem.
Then just run docker-compose up.  

## Slack Integration
This is configured to accept Slack slash commands.  Follow the directions here [Slack Slash Commands API]([https://api.slack.com/interactivity/slash-commands](https://api.slack.com/interactivity/slash-commands)).  Put the webhook and the Slack token in the .env file.  When setting up the slash command you will need to point it to yourdomain.com/update.php
