# Frontal CNDT : site statique + proxy /api/ et /storage/ vers l'API.
# Le site public et le dashboard n'utilisent plus que des adresses
# relatives (/api/v1) : en production, un seul domaine suffit.
FROM httpd:2.4-alpine

# Activation des modules proxy
RUN sed -i \
    -e 's/^#LoadModule proxy_module /LoadModule proxy_module /' \
    -e 's/^#LoadModule proxy_http_module /LoadModule proxy_http_module /' \
    -e 's/^#LoadModule headers_module /LoadModule headers_module /' \
    -e 's/^#LoadModule rewrite_module /LoadModule rewrite_module /' \
    /usr/local/apache2/conf/httpd.conf

# Configuration du proxy (cible résolue par Docker DNS : http://api:80)
COPY proxy-cndt.conf /usr/local/apache2/conf/extra/proxy-cndt.conf
RUN echo 'Include conf/extra/proxy-cndt.conf' >> /usr/local/apache2/conf/httpd.conf
