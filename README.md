## local start
```sh
s local start Auto

# or
docker tag registry.cn-beijing.aliyuncs.com/aliyunfc/runtime-custom:1.10.9 runtime-custom
docker run --rm -v $(pwd):/code -it --entrypoint="" -p 9000:9000 runtime-custom bash
```

## deploy
```sh
s deploy -y --use-local
```

## 安装其他扩展
https://help.aliyun.com/document_detail/89032.html

## ToDO
- [*] 二维码
- [*] 代理
- [*] 图片压缩
- [*] 微信签名