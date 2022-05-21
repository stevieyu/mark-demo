

apt update && \
apt-get install axel build-essential && \
axel -k https://github.com/ImageMagick/ImageMagick/archive/refs/tags/7.1.0-35.tar.gz -o ImageMagick.tar.gz
tar xvzf ImageMagick.tar.gz && \
cd ImageMagick-7.1.0-35 && \
./configure && \
make && \
make install && \
ldconfig /usr/local/lib && \
pecl install imagick