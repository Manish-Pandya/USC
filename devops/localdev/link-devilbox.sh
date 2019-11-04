# Link project source to docroot
ln -sTf /shared/httpd/rsms.graysail.com/htdocs/rsms/src /var/www/html/rsms

# Link and expose devops
sudo ln -sTf /shared/httpd/rsms.graysail.com/htdocs/devops/ /var/rsms
sudo chmod 2775 /var/rsms
