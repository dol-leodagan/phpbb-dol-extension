# phpbb-dol-extension
PhpBB Extensions for displaying Dawn of Light status

## Backend
PhpBB DOL Extension need an HTTP(S) backend server to query DOL Server status.

This backend need to be installed near the DOL Server to retrieve runtime data.

For the development of this exentension I used an Nginx server as Backend: 

```nginx
        server {
                listen "${PUBLIC}":443;
                server_name "${PUBLIC}";

                ssl on;

                ssl_protocols TLSv1 TLSv1.1 TLSv1.2;
                ssl_ciphers "EECDH+AESGCM:EDH+AESGCM:ECDHE-RSA-AES128-GCM-SHA256:AES256+EECDH:DHE-RSA-AES128-GCM-SHA256:AES256+EDH:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-SHA384:ECDHE-RSA-AES128-SHA256:ECDHE-RSA-AES256-SHA:ECDHE-RSA-AES128-SHA:DHE-RSA-AES256-SHA256:DHE-RSA-AES128-SHA256:DHE-RSA-AES256-SHA:DHE-RSA-AES128-SHA:ECDHE-RSA-DES-CBC3-SHA:EDH-RSA-DES-CBC3-SHA:AES256-GCM-SHA384:AES128-GCM-SHA256:AES256-SHA256:AES128-SHA256:AES256-SHA:AES128-SHA:DES-CBC3-SHA:HIGH:!aNULL:!eNULL:!EXPORT:!DES:!MD5:!PSK:!RC4";
                ssl_dhparam /home/dolserver/ssl/dhparam.pem;
                ssl_prefer_server_ciphers on;
                ssl_session_cache shared:SSL:10m;

                ssl_certificate /home/dolserver/ssl/server.cert;
                ssl_client_certificate /home/dolserver/ssl/server.cert;
                ssl_certificate_key /home/dolserver/ssl/server.key;

                access_log /home/dolserver/nginx/ssl_access_log main;
                error_log /home/dolserver/nginx/ssl_error_log info;

                root /home/dolserver/ws;

                add_header Strict-Transport-Security "max-age=63072000; includeSubdomains; preload";

                # only authorized client
                ssl_verify_client on;
        }
```

For testing Purpose I used LUA Scripts to fake Web Service Reply through a Shell Script:

```nginx
                location /serverstatus {
                        #add_header Content-Type text/html;
                        default_type text/html;
                        gzip off;
                        content_by_lua '
                                local f = io.popen("/home/dolserver/scripts/serverstatus.sh", "r")
                                local l = f:read("*a")
                                f:close()
                                ngx.say(l)
                        ';
                }
```

This Configuration needs a Client SSL Certificate for Authentication, here are the needed steps :

```bash
# Go to SSL Dir
cd /home/dolserver/ssl/

# Generate Server Self-Signed Certificate
openssl genrsa -out server.key 4096
openssl req -new -x509 -days 3650 -key server.key -out server.cert

# Sign a "Client" Certificate for Frontend
openssl genrsa -out client.key 4096
openssl req -new -key client.key -out client.csr
openssl x509 -req -days 3650 -in client.csr -CA server.cert -CAkey server.key -set_serial 01 -out client.cert
# Export Client Certificate in PKCS12 (Browser Import) and PEM Format (for Curl)
# !! BE CAREFUL the private key is not password protected in this configuration !!
openssl pkcs12 -export -clcerts -in client.cert -inkey client.key -out client.p12 -passout pass:
openssl pkcs12 -in client.p12 -out client.pem -clcerts -nodes -passout pass:

# Sign an Administrator Certificate for human access (Web Browser)
openssl genrsa -out admin.key 4096
openssl req -new -key admin.key -out admin.csr
openssl x509 -req -days 3650 -in admin.csr -CA server.cert -CAkey server.key -set_serial 01 -out admin.cert
# Export as PKCS12 with Password to be imported on desktop / smartphone Browser
openssl pkcs12 -export -clcerts -in admin.cert -inkey admin.key -out admin.p12

# Generate DH Params (can be long)
openssl dhparam -out dhparam.pem 4096

# (Optional) Secure Files
chmod o-rwx,g+rx ./ && sudo chgrp nginx ./
chmod o-rwx ./*.key ./*.p12 ./*.pem
chmod g+r ./server.key && sudo chown root:nginx ./server.key
```

## Configure Extension to query Backend

_TODO_