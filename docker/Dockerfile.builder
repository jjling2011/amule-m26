FROM alpine:3.23 

COPY docker/repositories /etc/apk/

# Install build tools
RUN apk add --no-cache \
        gcc g++ cmake make \
        boost-dev crypto++-dev libmaxminddb-dev libupnp-dev readline-dev wxwidgets-dev zlib-dev

CMD ["/bin/bash"]
