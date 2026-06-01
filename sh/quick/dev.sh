#/usr/bin/env bash

name="amule-m26-dev"
image="ghcr.nju.edu.cn/jjling2011/amule-m26:v0.1.0"

echo "Remove container: ${name}"
sudo docker rm -f "${name}"
echo ""

# 4711 webui
# 4712 cmdport
# 4662 ed2k tcp
# 4665 ed2k search udp
# 4672 ed2k udp required

#    -p 127.0.0.1:4712:4712 \
#    -p 127.0.0.1:4662:4662 \
#    -p 127.0.0.1:4665:4665/udp \
#    -p 127.0.0.1:4672:4672/udp \

echo "Start container: ${name}"

#    -v ./dist:/usr/share/amule/webserver \

sudo docker run \
    --name="${name}" \
    --rm \
    -p 127.0.0.1:4401:4711 \
    -e PUID=1000 \
    -e PGID=1000 \
    -e TZ=Asia/Shanghai \
    -e GUI_PWD=dev \
    -e WEBUI_PWD=dev \
    -e MOD_AUTO_RESTART_ENABLED=true \
    -e 'MOD_AUTO_RESTART_CRON=0 6 * * *' \
    -e MOD_AUTO_SHARE_ENABLED=false \
    -e MOD_FIX_KAD_GRAPH_ENABLED=false \
    -e MOD_FIX_KAD_BOOTSTRAP_ENABLED=true \
    -e TEMP_DIR=/downloads/temp \
    -v ./dist:/usr/share/amule/webserver \
    -v ./debug/devfs/conf/amule:/home/amule/.aMule \
    -v ./debug/devfs/downloads:/downloads \
    "${image}"
