FROM alpine:3.8

COPY run.sh /var/run/
RUN apk add --no-cache openssh bash
RUN adduser tunnel -u 1000 -D
USER tunnel

CMD ["/var/run/run.sh"]
