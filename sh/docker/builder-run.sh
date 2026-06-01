#!/usr/bin/env bash

. ./sh/docker/env.txt

echo "press ctrl+p and ctrl+q to exit -it mode"

sudo docker run \
    --network host \
    --rm -it \
    --name "${BUILDER_IMAGE_NAME}" \
    -v ./debug/wrk:/home/amule \
    "${BUILDER_IMAGE_NAME}:${BUILDER_VAR}" /bin/sh
