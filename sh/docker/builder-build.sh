#!/usr/bin/env bash

source ./sh/docker/env.txt

name="${BUILDER_IMAGE_NAME}:${BUILDER_VAR}"

# echo "remove image: ${name}"
# sudo docker image rm "${name}"

echo "building: ${name}"

sudo docker build \
    -t "${name}" \
    -f docker/Dockerfile.builder \
    .
