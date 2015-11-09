# Symfony2-CertAuthBundle
Integrate two-factor certificate authentication in you project.

With this bundle it is possible to easily integrate two-factor x509 based browser authentication into your project.

Basic features:
 - Tightly integrated into Symfony2 firewall system (less code more security)
 - Integrate it on top of any authentication method - http-basic, form-login, pre-auth etc...
 - Automatic x509 certificate generation, storage and restoring
 - Pluggable certificate storage system based on Persisters, Filters and Formatters (store your certificates the way you like)
 - Flexible access rules: store ip, email or any custom field into client certificate and then filter by Symfony Expression Language 
 - Outstanding security for your customers: hacker must store user certificate, know his credentials and event then it is almost impossible to login if you will deny access by ip stored in certificate
 - Since x509 ValidFrom and ValidTo fields are automatically validated by nginx or any similar server, it will automatically reduce probability of inactive accounts being your headache
 
How it works?
-------------
  - User is logged in as usual
  - User is redirected to DENY page
  - User enters his secure word and click generate certificate
  - Generated certificate is stored on server (localfs, db, {your_custom_storage})
  - User downloads certificate and installs it into the browser
  - User refreshes page and get into admin interface
  - Your frontend server stack validates user certificate and pass several SERVER variables to backend
  - Bundle checks this variables to grant or deny access
  
What if user go to another computer?
------------------------------------
 - User is logged in as usual
 - System detects that he has certificate locally
 - User is redirected to RESTORE page
 - User enters his secure word and download certificate again

Certificates are protected by password and even your admin can not view it's content.

Installation
------------

**Download via composer**
```bash
composer require zim32/cert-auth-bundle
```
**Add bundle to your Kernel**
```php
# app/AppKernel.php
$bundles = array(
...
    new Zim\CertAuthBundle\ZimCertAuthBundle(),
...
 );
```
**Import routes**
```yml
# app/config/routing.yml
zim_cert_auth:
    resource: "@ZimCertAuthBundle/Resources/config/routing.yml"
    prefix:   /cert
```
**Generate youe CA certificate (if you don't have one)**
```bash
mkdir /{your_app_root}/cert
cd /{your_app_root}/cert
openssl genrsa -des3 -out private.pem 4096
openssl req -new -x509 -key private.pem -out CA.crt -days 365
chmod a-rwx * && chmod u+r *
```
**Modify nginx config. Add lines in your server section:**
```
# /etc/nginx/sites-available/yoursite.conf
server {
...
ssl_verify_client optional;
ssl_trusted_certificate /{your_app_root}/cert/CA.crt;
...
}
```
Depenging on version of nginx you should add **ssl_client_certificate /{your_app_root}/cert/CA.crt;** instead of ssl_trusted_certificate, but this is not recommended option, consider updating your nginx server.

**Modify your openssl config file**

This bundle uses custom openssl extension section called **zim_usr_cert** to prevent different conflicts with your existing configurations. So add this to the end of your openss config file (on Debian /etc/ssl/openssl.cnf):
```
[zim_usr_cert]
basicConstraints=critical,CA:FALSE
keyUsage = nonRepudiation, digitalSignature, keyEncipherment
```

**For PHP-FPM use:**
```
fastcgi_param   CLIENT_CERT         $ssl_client_raw_cert;
fastcgi_param   CLIENT_CERT_OK      $ssl_client_verify;

```
**For proxy pass use:**
```
proxy_set_header    CLIENT_CERT         $ssl_client_raw_cert;
proxy_set_header    CLIENT_CERT_OK      $ssl_client_verify;
```
**Add directory to store generated client's certificates**
```bash
mkdir /{your_app_root}/cert/clients
```
**Add bundle configuration**
```yml
# app/config/config.yml
...
zim_cert_auth:
    ca_path: %kernel.root_dir%/cert/CA.crt
    ca_key_path: %kernel.root_dir%/cert/private.pem
    ca_key_password: my_des3_password
    client_csr_options:
        countryName: UA
        organizationName: YOUR ORGANIZATION NAME Limited
    cert_content_server_var: CLIENT_CERT
    cert_storage_formatter:
        id: zim_cert_auth.certificate_storage.formatter.pkcs12
        options: []
    cert_storage_persister:
        id: zim_cert_auth.certificate_storage.persister.localfs
        options:
            rootDir: %kernel.root_dir%/cert/clients
```
**Modify security config**
```yml
# app/config/security.yml
firewalls:
    firewallname:
        ...
        pattern: ^/admin
        access_denied_handler: zim_cert_auth.access_denied_handler
        zim_cert: ~
        ...
    access_control:
        ...
        - { path: ^/cert/denied, role: IS_AUTHENTICATED_FULLY }
        - { path: ^/admin, allow_if: "'ROLE_ADMIN' in roles and 'ROLE_CERT_AUTHENTICATED_FULLY' in roles" }
```
Customize
=========
This bundle has only three templates 
 - Resources/views/Denied/layout.html.twig
 - Resources/views/Denied/index.html.twig
 - Resources/views/Denied/restore.html.twig

Override them using [Symfony Override Templates](http://symfony.com/doc/current/book/templating.html#overriding-bundle-templates) technique to add f.e. instructions how to install certificate into the browser.

Override *Controller/AccessDeniedController* if you need some custom logic.

Change persister to store client certificates other way then localfs (f.e. in database).

Run Tests
=========
```bash
cd /{your_project_root} && KERNEL_DIR=./app ./bin/phpunit vendor/zim32/cert-auth-bundle/Tests/
```