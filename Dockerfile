FROM alpine:3.7

#pdf basis
RUN apk add --no-cache php7

#adding source code
add ./src /www/
