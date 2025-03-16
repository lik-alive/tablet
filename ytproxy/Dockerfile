FROM owlsoul/ytproxy:1.0.2

USER root:root
ADD yandex_transport_core.py /home/transport_proxy/yandex_transport_core/
RUN chown -R transport_proxy:transport_proxy /home/transport_proxy
USER transport_proxy:transport_proxy

CMD /usr/bin/python3 /home/transport_proxy/transport_proxy.py