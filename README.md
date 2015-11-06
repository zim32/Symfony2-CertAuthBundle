# Symfony2-CertAuthBundle
Integrate two-factor certificate authentication in you project

With this bundle it is possible to easily integrate two-factor x509 based browser authentication into your project.

Basic features:
 - Tightly integrated into Symfony2 firewall system (less code more secutiry)
 - Integrate it on top of any authentication method - http-basic, form-login, pre-auth etc...
 - Automatic x509 certificate generation, storage and restoring
 - Pluggable certificate storage system based on Persisters, Filters and Formatters (store your certificates the way you like)
 - Flexible access rules: store ip, email or any custom field into client certificate and then filter by Symfony Expression Language 
 - Outstanding security for your customers: hacker must store user certificate, know his credentials and event then it is almost impossible to login if you will denie access by ip stored in certificate.
 - Since x509 ValidFrom and ValidTo fields are automaticly validated by nginx or any similar server, it will automaticly reduce probability of inactive accounts being yout headache