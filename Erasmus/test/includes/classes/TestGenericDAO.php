<?php
require_once(dirname(__FILE__) . '/../../simpletest/autorun.php');

//Require Application.php to set up autoloading & database connection info
require_once(dirname(__FILE__) . '/../../../src/Application.php');

class TestGenericDAO extends UnitTestCase {
	
	public function assertAutomaticFields_new( GenericCrud $genericCrud ){
		$classname = get_class($genericCrud);
		
		//Assert that auto-set fields are set
		$this->assertNotNull( $genericCrud->getKeyId(), "$classname KeyId was not automatically set");
		$this->assertNotNull( $genericCrud->getDateCreated(), "$classname Date Created was not automatically set");
		$this->assertNotNull( $genericCrud->getIsActive(), "$classname IsActive was not automatically set");
	}
	
	public function assertAutomaticFields_update( $oldGenericCrud, $updatedGenericCrud ){
		$classname = get_class($oldGenericCrud);
		
		$this->assertNotNull($updatedGenericCrud->getDateLastModified(), "$classname Date Last Modified was not automatically set");
		$this->assertNotEqual($updatedGenericCrud->getDateLastModified(), $oldGenericCrud->getDateLastModified(), "$classname Date Last Modified was not automatically changed");
	}
	
	public function test_userCrud(){
		$userDao = new GenericDAO(new User());
		
		//Create
		$newUser = new User();
		$newUser->setEmail('savetest@mitch.com');
		$newUser->setName('Save Test');
		$newUser->setUsername('savetest');
		
		$savedUser = $userDao->save($newUser);
		
		//Assert that correct data was saved
		$this->assertEqual($newUser->getEmail(), $savedUser->getEmail(), "Email was not saved");
		$this->assertEqual($newUser->getName(), $savedUser->getName(), "Name was not saved");
		$this->assertEqual($newUser->getUsername(), $savedUser->getUsername(), "Username was not saved");
		
		//Assert that auto-set fields are set
		$this->assertAutomaticFields_new( $savedUser );
		
		//Retrieve
		$retrievedUser = $userDao->getById( $savedUser->getKeyId());
		$this->assertEqual($savedUser, $retrievedUser, "Retrieved User is not equal to saved User");
		
		//Update
		$retrievedUser->setName('Updated Save Test');
		$updatedUser = $userDao->save($retrievedUser);

		//Assert that changed data was saved
		$this->assertEqual($updatedUser->getName(), $retrievedUser->getName(), "Name was not updated");
		
		//Assert that unchanged data is the same
		$this->assertEqual($updatedUser->getEmail(), $retrievedUser->getEmail(), "Email was changed");
		$this->assertEqual($updatedUser->getUsername(), $retrievedUser->getUsername(), "Username was changed");
		
		//Assert that auto-set fields are set
		$this->assertAutomaticFields_update($retrievedUser, $updatedUser);
		
		//TODO: Delete
	}
	
	// Role
	
	public function test_roleCrud(){
		$roleDao = new GenericDAO( new Role() );
		
		//Create
		$newRole = new Role();
		$newRole->setName("TestRole");
		$savedRole = $roleDao->save($newRole);
		
		//Assert that correct data was saved
		$this->assertEqual($newRole->getName(), $savedRole->getName(), "Name was not saved");
		
		//Assert that auto-set fields are set
		$this->assertAutomaticFields_new( $savedRole );
		
		//Retrieve
		$retrievedRole = $roleDao->getById( $savedRole->getKeyId());
		$this->assertEqual($savedRole, $retrievedRole, "Retrieved Role is not equal to saved Role");
		
		//Update
		$retrievedRole->setName('Updated TestRole');
		$updatedRole = $roleDao->save($retrievedRole);
		
		//Assert that changed data was saved
		$this->assertEqual($updatedRole->getName(), $retrievedRole->getName(), "Name was not updated");
		
		//TODO: Assert that unchanged data is the same?
		
		//Assert that auto-set fields are set
		$this->assertAutomaticFields_update($retrievedRole, $updatedRole);
		
		//TODO: Delete
	}
	
	/**
	 * Asserts tha all listed GenericCrud subclasses have associated Tables,
	 * and that those tables that exist return the correct types via GenericDAO
	 */
	public function testGenericDAO_TablesAndTypes(){
		$classnames = array(
			'Building',
			'Checklist',
			'CorrectiveAction',
			'Deficiency',
			'DeficiencyRootCause',
			'DeficiencySelection',
			'Department',
			'Equipment',
			'Hazard',
			'Inspection',
			'Inspector',
			'LabSafetyManager',
			'Observation',
			'PrincipalInvestigator',
			'Question',
			'Recommendation',
			'Response',
			'Role',
			'Room',
			'User'
		);
		
		foreach($classnames as $classname){
			$dao = new GenericDAO( new $classname() );
			
			$tableExists = $dao->doesTableExist();
			
			$this->assertTrue($tableExists, "$classname Table does not exist");
			
			// Skip data test if no table exists
			if( !$tableExists ){
				continue;
			}
			
			$all = $dao->getAll();
			if( !empty($all) ){
				//Check that the first returned item is of the correct type
				//TODO: Should we check all?
				$this->assertIsA($all[0], $classname);
			}
		}
	}
}

?>