#!/usr/bin/env bash
/usr/sbin/service slapd start
/opt/project/vendor/bin/phpunit "$@"