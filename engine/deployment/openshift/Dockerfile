FROM openshift/origin-cli

RUN yum update -y && yum install -y yum-utils \
    device-mapper-persistent-data \
    lvm2

RUN yum-config-manager \
        --add-repo \
        https://download.docker.com/linux/centos/docker-ce.repo

RUN yum install docker-ce -y