#/usr/bin/env bash

files=$(ls public/)

for f in ${files[@]}; do
    dist="dist/M26/${f}"
    pub="public/${f}"
    # echo "diff ${dist} ${pub}"
    diff "${dist}" "${pub}"
    STATUS=$?
    if [ $STATUS -ne 0 ]; then
        echo "=> ↑↑ diff \"${dist}\" \"${pub}\""
        echo "cp \"${dist}\" \"${pub}\""
        echo "cp \"${pub}\" \"${dist}\""
        echo ""
    else
        echo "no difference: \"${dist}\" \"${pub}\""
    fi
done
