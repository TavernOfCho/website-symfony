#!/bin/sh

oc login "$OPENSHIFT_SERVER" --token="$OPENSHIFT_TOKEN" 2> /dev/null
DOCKER_IMAGE_NAME=$OPENSHIFT_PROJECT/$APP

docker login -u `oc whoami` -p `oc whoami -t` $OPENSHIFT_REGISTRY
docker build -t $DOCKER_IMAGE_NAME --cache-from $OPENSHIFT_REGISTRY/$DOCKER_IMAGE_NAME .
docker tag $DOCKER_IMAGE_NAME $OPENSHIFT_REGISTRY/$DOCKER_IMAGE_NAME
docker push $OPENSHIFT_REGISTRY/$DOCKER_IMAGE_NAME

oc get services $APP 2> /dev/null || oc new-app . --name=$APP
