#!/usr/bin/env bash

source ./sh/docker/env.txt

name="${DEV_IMAGE_NAME}:${DEV_VER}"

# echo "remove image: ${name}"
# sudo docker image rm -f "${name}"

echo "building: ${name}"

sudo docker build \
    -t "${name}" \
    -f docker/Dockerfile.dev \
    .
