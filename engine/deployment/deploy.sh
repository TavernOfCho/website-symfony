#!/bin/sh

docker run -v $PWD:/app -w /app openshift/origin oc get services $APP 2> /dev/null || oc new-app . --name=$APP --strategy=docker
docker run -v $PWD:/app -w /app openshift/origin oc new-build . --name=$APP --strategy=docker --output=yaml | oc apply -f -
docker run -v $PWD:/app -w /app openshift/origin oc cancel-build bc/$APP && oc start-build $APP --from-dir=. --follow
docker run -v $PWD:/app -w /app openshift/origin oc get routes $APP 2> /dev/null || oc expose service $APP --hostname=$APP_HOST
