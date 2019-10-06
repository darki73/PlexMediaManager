FROM nginx:1.17.4-alpine

RUN apk update && apk add dos2unix

COPY ./etc/fastcgi_params /etc/nginx/fastcgi_params
COPY ./etc/html /usr/share/nginx/html
COPY ./etc/html-errorpages /usr/share/nginx/html-errorpages
COPY ./etc/errorpages.conf /etc/nginx/errorpages.conf
COPY ./etc/nginx.conf /etc/nginx/nginx.conf
COPY ./nginx-entrypoint.sh /nginx-entrypoint.sh

RUN dos2unix /nginx-entrypoint.sh
RUN chmod +x /nginx-entrypoint.sh

ENTRYPOINT ["/nginx-entrypoint.sh"]

CMD ["nginx"]
