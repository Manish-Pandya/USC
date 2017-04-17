<?php
/*
	
	CREATED BY Eric Patterson 02.25.2009 v 1.0
	
	###################################################################################	
	
	LDAP PHP Class
	
	EXAMPLES
			**	ALWAYS CALLED #######################################################
			**
			**	$pClassLDAP = new LDAP();
			**
			**	TO RETRIEVE ATTRIBUTES #######################################################
			**	
			**	$pFieldsAuth = array("department","cn","sn","description","telephoneNumber","givenName","employeeID","uscEduCarolinaCode","uscEduUSCID");
			**
			**	if ($pArrayOfFields = $pClassLDAP->GetAttr($pUsername, $pFieldsAuth))
			**	{
			**		echo $pArrayOfFields["department"] . "<br>";
			**		echo $pArrayOfFields["cn"] . "<br>";
			**		echo $pArrayOfFields["sn"] . "<br>";
			**		echo $pArrayOfFields["description"] . "<br>";
			**		echo $pArrayOfFields["telephoneNumber"] . "<br>";
			**		echo $pArrayOfFields["givenName"] . "<br>";
			**		echo $pArrayOfFields["employeeID"] . "<br>";
			**		echo $pArrayOfFields["uscEduCarolinaCode"] . "<br>";
			**		echo $pArrayOfFields["uscEduUSCID"] . "<br>";
			**		exit();
			**	}
			**
			**	DETERMINE IF USER ACCT EXISTS #######################################################
			**
			**  if ($pClassLDAP->IsUserInBase($pUsername))	
			**		echo "Success";
			**
			**	AUTHENTICATE A USER ACCOUNT #######################################################
			**
			**  if ($pClassLDAP->IsAuthenticated($pUsername, $pPassword))
			**		echo "Success";
			**
			**	AUTHENTICATE A USER ACCOUNT IN A PASSED BASE DN #####################################
			**
			**  if ($pClassLDAP->IsAuthenticated($pUsername, $pPassword, null, null, array("myBaseDn")))
			**		echo "Success";
			**
			**	AUTHENTICATE A USER ACCOUNT VIA A DIFF RESOURCE ACCT #################################
			**
			**  if ($pClassLDAP->IsAuthenticated($pUsername, $pPassword, <resUsername>, <resPassword>))
			**		echo "Success";
			**
			**	AUTHENTICATE A USER ACCOUNT AND LOOKUP GROUP #################################
			**
			**  if ($pClassLDAP->IsAuthenticated($pUsername, $pPassword, null, null, null, array("myFullDnToGroup"), "GROUP"))
			**		echo "Success";
			**
			****************************************************************************************************
*/

	//INCLUDE FILE FOR RESOURCE ACCOUNTS AND BASE DNS
	include("ADLDAP_VARS.php");
	
	class LDAP
	{
		var $pConnection;
		var $pErrorMessage;
		var $pDN;
		var $pServers;
		var $pPort;

		/**************************************************************************************
		**	LDAP Constructor (class that connects to LDAP server, based on array parameter of servers or default array paramnter)
		**				input param:	array("ldaps://cae145dnsp01.ds.sc.edu"):	Server address
		**								$port(636):	Port for connecting to Server (default 389 optional)
		**				
		**************************************************************************************/
		function LDAP($server = array(USC_AD_1, USC_AD_2), $port = "636") 
		{
			$this->pServers = $server;
			$this->pPort = $port;
		}

		/**************************************************************************************
		**	IsAuthenticated (returns true is authenticated by baseDN and/or group.  If resource account is not passed, 
		**					 pull in resource account from ADLDAP_VARS.php file.  If base dn is not passed, 
		**					 use default array paramter base dn.  If base dn is passed, pass null for resource accounts
		**					 if you need to use default parameters.  If authenticating (or looking up a group member), you 
		**					 must pass null for the resource accts parameters and the base dn if you need to use the default 
		**					 parameters)
		**				input param:	$username:			Username (required)
		**								$password:			Password (required)
		**								$resUsername:		AD RECOURCE ACCT DN (optional)
		**								$resPassword:		AD RECOURCE ACCT PASSWORD (optional)
		**								$baseDNArr:			Base DN Array of lookup containers (optional)
		**								$groupsArr(""):		Groups Array of lookup groups (optional)
		**								$groupORbase(BASE):	Setting if Authenticate by base or group (optional)
		**
		**				output:			boolean:			Is user authenticated in baseDNs or groups	
		**************************************************************************************/
		function IsAuthenticated($username, $password, $resUsername = AD_USER, $resPassword = AD_PASSWORD, $baseDNArr = array(USC_BASE_DN), $groupsArr = "", $groupORbase = "BASE")
		{	
			/**************************************************************************************
			**  SPECIAL CASE FOR DEFAULT PARAMETER NULL CHECK, THIS IS ONLY NEEDED IF GROUP ARRAY IS PASSED W/O THE RESOURCE AND BASEDNs
			/**************************************************************************************/
			$resUsername = is_null($resUsername) ? AD_USER : $resUsername;
			$resPassword = is_null($resPassword) ? AD_PASSWORD : $resPassword;
			$baseDNArr = is_null($baseDNArr) ? array(USC_BASE_DN) : $baseDNArr;
			/**************************************************************************************/
			
			if ($password == "")
                                return false;
			
			//FIRST AUTHENTICATE VIA BASE DN
			for ($iCounter = 0; $iCounter < count($this->pServers); $iCounter++)
			{
				$this->pConnection = @ldap_connect($this->pServers[$iCounter], $this->pPort);
				
				// BIND TO SERVER
				if (@ldap_bind($this->pConnection, $resUsername, $resPassword))
				{
					$pFilter = "(sAMAccountName=$username)";
					
					//LOOP THROUGH BASEDNS FOR SEARCH
					for ($jCounter = 0; $jCounter < count($baseDNArr); $jCounter++)
					{
						//SEARCH AD ACCOUNT TO GET DN
						if ($pResult = @ldap_search($this->pConnection, $baseDNArr[$jCounter], $pFilter, array("cn")))
						{   
							// RETURNS TRUE IF USER WAS FOUND AND LOOKING IN BASE ONLY
							if ($pEntryID = @ldap_first_entry($this->pConnection, $pResult))
							{
								// LOOPS THRU PROPERTIES AND STORES IN ARRAY FOR RETURN
								$newDN = ldap_get_dn($this->pConnection, $pEntryID);
								
								// BIND TO SERVER tHEN LOOK UP GROUPS IF GROUP AUTHENTICATION IS NEEDED
								if (@ldap_bind($this->pConnection, $newDN, $password))
								{
									if ($groupORbase != "GROUP")
										return true;
									else
										return $this->IsInGroup($newDN, $groupsArr);
								}
							}
						}
					}
				}
			}
		
			return false;
		}
		
		/**************************************************************************************
		**	IsInGroup (returns true if in any group in passed array)
		**				input param:	$groupsArr:			array("CN=COUTSD-Contractual-Services-ALL,OU=Distribution,OU=Groups,OU=UTS,OU=DesktopSLA,OU=DOIT,OU=Columbia,DC=ds,DC=sc,DC=edu")
		*************************************************************************************/
		function IsInGroup($userDN, $groupsArr)
		{
			$groupFilter = "(member:1.2.840.113556.1.4.1941:=" . $userDN . ")";
		
			//LOOP THROUGH GROUP BASEDNS FOR SEARCH
			for ($jCounter = 0; $jCounter < count($groupsArr); $jCounter++)
			{
				//SEARCH AD ACCOUNT TO GET DN
				if ($pResult = @ldap_search($this->pConnection, $groupsArr[$jCounter], $groupFilter, array("dn")))
				{   
					// RETURNS TRUE IF USER WAS FOUND AND LOOKING IN BASE ONLY
					if ($pEntryID = @ldap_first_entry($this->pConnection, $pResult))
						return true;
				}
			}
			
			return false;
		}
		
		/**************************************************************************************
		**	IsUserInBase (returns true if in username is in baseDN)
		**				input param:	$username
		**								$baseDNArr:			Base DN Array of lookup containers (optional)
		**								$resUsername:		AD RECOURCE ACCT DN (optional)
		**								$resPassword:		AD RECOURCE ACCT PASSWORD (optional)
		*************************************************************************************/
		function IsUserInBase($username, $baseDNArr = array(USC_BASE_DN), $resUsername = AD_USER, $resPassword = AD_PASSWORD)
		{
			/**************************************************************************************
			**  SPECIAL CASE FOR DEFAULT PARAMETER NULL CHECK, THIS IS ONLY NEEDED IF GROUP ARRAY IS PASSED W/O THE RESOURCE AND BASEDNs
			/**************************************************************************************/
			$resUsername = is_null($resUsername) ? AD_USER : $resUsername;
			$resPassword = is_null($resPassword) ? AD_PASSWORD : $resPassword;
			$baseDNArr = is_null($baseDNArr) ? USC_BASE_DN : $baseDNArr;
			/**************************************************************************************/
			
			//FIRST AUTHENTICATE VIA BASE DN
			for ($iCounter = 0; $iCounter < count($this->pServers); $iCounter++)
			{
				$this->pConnection = @ldap_connect($this->pServers[$iCounter], $this->pPort);
				
				// BIND TO SERVER
				if (@ldap_bind($this->pConnection, $resUsername, $resPassword))
				{
					$pFilter = "(sAMAccountName=$username)";
					
					//LOOP THROUGH BASEDNS FOR SEARCH
					for ($jCounter = 0; $jCounter < count($baseDNArr); $jCounter++)
					{
						//SEARCH AD ACCOUNT TO GET DN
						if ($pResult = @ldap_search($this->pConnection, $baseDNArr[$jCounter], $pFilter, array("cn")))
						{   
							// RETURNS TRUE IF USER WAS FOUND AND LOOKING IN BASE ONLY
							if ($pEntryID = @ldap_first_entry($this->pConnection, $pResult))
								return true;
						}
					}
				}
			}
		
			return false;
		}
		
		
		/**************************************************************************************
		**	GetAttr (returns array of properties of user logged
		**				input param:	$username:	Username
		**								$propsArr:	Array of fields needed: array("department","cn","sn","description","telephoneNumber","givenName","employeeID","uscEduCarolinaCode","uscEduUSCID");
		**								$baseDNArr:	Base DN Array of lookup containers (optional)	
		**								$resUsername:		AD RECOURCE ACCT DN (optional)
		**								$resPassword:		AD RECOURCE ACCT PASSWORD (optional)
		**
		**				output:			false on error:  otherwise array of properties:					
		**************************************************************************************/
		function GetAttr($username, $propsArr, $baseDNArr = array(USC_BASE_DN), $resUsername = AD_USER, $resPassword = AD_PASSWORD)
		{
			/**************************************************************************************
			**  SPECIAL CASE FOR DEFAULT PARAMETER NULL CHECK, THIS IS ONLY NEEDED IF GROUP ARRAY IS PASSED W/O THE RESOURCE AND BASEDNs
			/**************************************************************************************/
			$resUsername = is_null($resUsername) ? AD_USER : $resUsername;
			$resPassword = is_null($resPassword) ? AD_PASSWORD : $resPassword;
			$baseDNArr = is_null($baseDNArr) ? array(USC_BASE_DN) : $baseDNArr;
			/**************************************************************************************/
			
			$pReturnArr = array();
			$pFilter = "(sAMAccountName=$username)";
			
			//FIRST AUTHENTICATE VIA BASE DN
			for ($iCounter = 0; $iCounter < count($this->pServers); $iCounter++)
			{
				$this->pConnection = @ldap_connect($this->pServers[$iCounter], $this->pPort);
				
				// BIND TO SERVER
				if (@ldap_bind($this->pConnection, $resUsername, $resPassword))
				{
					//LOOP THROUGH BASEDNS FOR SEARCH
					for ($jCounter = 0; $jCounter < count($baseDNArr); $jCounter++)
					{
						//SEARCH AD ACCOUNT TO GET ATTRIBUTES
						if ($pResult = @ldap_search($this->pConnection, $baseDNArr[$jCounter], $pFilter, $propsArr))
						{   
							// RETURNS TRUE IF USER WAS FOUND AND LOOKING IN BASE ONLY
							if ($pEntryID = @ldap_first_entry($this->pConnection, $pResult))
							{
								// LOOPS THRU PROPERTIES AND STORES IN ARRAY FOR RETURN
								$attr = @ldap_get_attributes($this->pConnection, $pEntryID);
							
								for ($kCounter = 0; $kCounter < $attr["count"]; $kCounter++)
									$pReturnArr[$attr[$kCounter]] = $attr[$attr[$kCounter]][0]; 
								
								return $pReturnArr;
							}
						}
					}
				}
			}
			
			return false;
		}

		/**************************************************************************************
                **      GetUsernameByVIPID (returns user logged via vipid
                **                              input param:    $vipid:      vipid
		** 						 $propsArr:      Array of fields needed: array("department","cn","sn","description","telephoneNumber","givenName","employeeID","uscEduCarolinaCode","uscEduUSCID");

                **                                                              $baseDNArr:     Base DN Array of lookup containers (optional)
                **                                                              $resUsername:           AD RECOURCE ACCT DN (optional)
                **                                                              $resPassword:           AD RECOURCE ACCT PASSWORD (optional)
                **
                **                              output:                 false on error:  otherwise array of properties:
                **************************************************************************************/
                function GetUsernameByVIPID($vipid, $propsArr, $baseDNArr = array(USC_BASE_DN), $resUsername = AD_USER, $resPassword = AD_PASSWORD)
                {
                        /**************************************************************************************
                        **  SPECIAL CASE FOR DEFAULT PARAMETER NULL CHECK, THIS IS ONLY NEEDED IF GROUP ARRAY IS PASSED W/O THE RESOURCE AND BASEDNs
                        /**************************************************************************************/
                        $resUsername = is_null($resUsername) ? AD_USER : $resUsername;
                        $resPassword = is_null($resPassword) ? AD_PASSWORD : $resPassword;
                        $baseDNArr = is_null($baseDNArr) ? array(USC_BASE_DN) : $baseDNArr;
                        /**************************************************************************************/

                        $pFilter = "(&(uscEduCarolinaCode=$vipid)(uscEduPreferredId=Y))";

                        //FIRST AUTHENTICATE VIA BASE DN
                        for ($iCounter = 0; $iCounter < count($this->pServers); $iCounter++)
                        {
                                $this->pConnection = ldap_connect($this->pServers[$iCounter], $this->pPort);

                                // BIND TO SERVER
                                if (ldap_bind($this->pConnection, $resUsername, $resPassword))
                                {
                                        //LOOP THROUGH BASEDNS FOR SEARCH
                                        for ($jCounter = 0; $jCounter < count($baseDNArr); $jCounter++)
                                        {
                                                //SEARCH AD ACCOUNT TO GET ATTRIBUTES
                                                if ($pResult = ldap_search($this->pConnection, $baseDNArr[$jCounter], $pFilter, $propsArr))
                                                {
					                // RETURNS TRUE IF USER WAS FOUND AND LOOKING IN BASE ONLY
                                                        if ($pEntryID = ldap_first_entry($this->pConnection, $pResult))
                                                        {
								// LOOPS THRU PROPERTIES AND STORES IN ARRAY FOR RETURN
                                                                $attr = ldap_get_attributes($this->pConnection, $pEntryID);

                                                                for ($kCounter = 0; $kCounter < $attr["count"]; $kCounter++)
                                                                        $pReturnArr[$attr[$kCounter]] = $attr[$attr[$kCounter]][0];

                                                                return $pReturnArr;
                                                        }
                                                }
                                        }
                                }
                        }

                        return false;
                }


	}
?>
