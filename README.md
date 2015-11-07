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
  
What if user go to another computer?
------------------------------------
 - User is logged in as usual
 - System detects that he has certificate locally
 - User is redirected to RESTORE page
 - User enters his secure word and download certificate again

Certificates are protected by password and even your admin can not view it's content.