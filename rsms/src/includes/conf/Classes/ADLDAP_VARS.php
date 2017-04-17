<?php
/*
	
	CREATED BY Eric Patterson 02.25.2009 v 1.0
	
	###################################################################################	
*/
	//RESOURCE ACCOUNTS FOR VIEWING ALL USERS (INC. PRIVATE)
	define('AD_USER', 'CN=UTSWAD2,OU=032,OU=USCUsers,DC=ds,DC=sc,DC=edu');
	define('AD_PASSWORD', 'a2wjn11#6');
	
	//USC's BASE DN FOR USERS
	define('USC_BASE_DN', 'ou=uscusers,dc=ds,dc=sc,dc=edu');

	//USC's AD SERVERS
	define('USC_AD_1', 'ldaps://cae145adcp03.ds.sc.edu');
    define('USC_AD_2', 'ldaps://cae145adcp04.ds.sc.edu');
	
	
	//define('USC_AD_1', 'ldaps://10.49.18.5'); //DUO LDAP server
     //   define('USC_AD_2', 'ldaps://10.49.18.4'); //DUO LDAP server
	
	//define('USC_AD_1', 'ldaps://spa970adcp01.ds.sc.edu');
     //   define('USC_AD_2', 'ldaps://spa970adcp01.ds.sc.edu');
?>
