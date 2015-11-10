## Configuration Reference

#### ca_path
**type**: string, **required**: true , **detault**: null

Path to your CA certificate. Used to generate client certificates.

#### ca_key_path
**type**: string, **required**: true , **detault**: null

Path to private key of your CA certificate. Used to generate client certificates.

#### ca_key_password
**type**: string, **required**: false , **detault**: null

Private key password. User for loading password protected CA private key.

#### disable_cert_restore
**type**: bool, **required**: false , **detault**: false

Disable automatic client certificate recovery. When this option is set to true, clients will not be able to
recover their certificates by themselves. The will be redirected to deny:block page. You can customize this template and controller to provide your own logic for restoring client certificate (f.e. ask him some questions, contact by phone or other method you choose)

#### client_csr_options
**type**: array, **required**: false , **detault**: []

Additional options, passed to openssl_csr_new() as first argument. You can use it to customize such fields as
countryName, organizationName and others. Look [here](http://php.net/manual/ru/function.openssl-csr-new.php) for more details.

#### bit_strength
**type**: integer, **required**: false , **detault**: 4096, **possible values**: 1024, 2048 or 4096

Private key strength used for clients private keys.

#### cert_content_server_var
**type**: string, **required**: false , **detault**: CLIENT_CERT

Name of SERVER VARIABLE which is used to pass user x509 certificate from server to php script.

#### cert_validation_expression
**type**: string, **required**: false , **detault**: cert["subject"]["CN"] == 'token.getUserName() && request.server.get("CLIENT_CERT_OK") === "SUCCESS"'

Expression used to validate request and make decision to grant or deny access. You can use it if you want validation based on your custom fields (f.e. validation by IP, time of day, etc.) This option uses Symfony Expression Language. Three variables exists in expression scope:
 - cert - array of x509 options in form of openssl_x509_parse() return value
 - request - current Request object
 - token - current user token

#### cert_storage_formatter
```
cert_storage_formatter:
    id: {service_id}
    options: []
```
Service used for formatting raw x509 resources into format suitable for persistance.
Currently there is only one formatter *zim_cert_auth.certificate_storage.formatter.pkcs12* which formats x509 resource to pkcs12 encrypted format. You can write your own Formatter. See [source code](https://github.com/zim32/Symfony2-CertAuthBundle/blob/master/Storage/Formatter/CertificateFormatterInterface.php).

Options which will be passed as first argument to your formatter constructor. Use it to provide configurable options to formatter.

#### cert_storage_persister
```
cert_storage_persister:
    id: {service_id}
    options: []
```
Service used for persisting formatted certificate. Currently there is two persisters:
```
cert_storage_persister:
    id: zim_cert_auth.certificate_storage.persister.localfs
    options:
        rootDir: %kernel.root_dir%/cert/clients # pass to root dir
```
```
cert_storage_persister:
    id: zim_cert_auth.certificate_storage.persister.orm
    options: []
```
You can implement your own persister. See [source code](https://github.com/zim32/Symfony2-CertAuthBundle/blob/master/Storage/Persister/CertificatePersisterInterface.php).