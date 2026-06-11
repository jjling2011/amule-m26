#/usr/bin/env bash

name="amule"
src="./dist/M26/"
dest="/usr/share/amule/webserver/"

echo "copy files"
sudo docker cp "${src}" "${name}:${dest}"

echo ""
echo "md5sum: ${src}"
md5sum "${src}"*

echo ""
echo "md5sum: ${dest}"
sudo docker exec -it "${name}" /bin/sh -c "md5sum ${dest}M26/*"

echo ""
echo "sum: ${src}"
cat "${src}"* | md5sum

echo ""
echo "sum: ${dest}"
sudo docker exec -it "${name}" /bin/sh -c "cat ${dest}M26/* | md5sum"
