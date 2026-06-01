#!/usr/bin/env bash

IMAGE_NAME="amule-m26-r"
IMAGE_VER="v0.1"

name="${IMAGE_NAME}:${IMAGE_VER}"

# echo "remove image: ${name}"
# sudo docker image rm -f "${name}"

echo "build image: ${name}"
sudo docker build \
    -t "${name}" \
    -f docker/Dockerfile.release \
    .
