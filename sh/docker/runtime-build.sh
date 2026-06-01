#!/usr/bin/env bash

source ./sh/docker/env.txt

name="${RUNTIME_IMAGE_NAME}:${RUNTIME_VER}"

echo "remove image: ${name}"
sudo docker image rm "${name}"

echo "building: ${name}"

sudo docker build \
    -t "${name}" \
    -f docker/Dockerfile.runtime \
    .
