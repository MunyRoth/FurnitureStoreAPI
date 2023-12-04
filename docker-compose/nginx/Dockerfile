FROM nginx:1.23.1

RUN rm -f /etc/nginx/conf.d/*

RUN mkdir /nginx
COPY ./ /nginx
COPY furniture.conf /etc/nginx/nginx.conf

CMD /usr/sbin/nginx -g "daemon off;" -c /etc/nginx/nginx.conf
