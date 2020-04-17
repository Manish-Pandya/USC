<?php

/**
 * Define a set of rules for any given Role. Each Rule states a Required, Optional, or Prohibited User-related field
 *
 * A 'Role Requirement'
 */
class UserCategoryRule {

    public static function required( $rolename, $property, $value = null ){
        return new UserCategoryRule($rolename, $property, UserCategoryRule::REQUIRED, $value);
    }
    public static function optional( $rolename, $property, $value = null ){
        return new UserCategoryRule($rolename, $property, UserCategoryRule::OPTIONAL, $value);
    }
    public static function prohibited( $rolename, $property, $value = null ){
        return new UserCategoryRule($rolename, $property, UserCategoryRule::PROHIBITED, $value);
    }

    public const REQUIRED = 'required';
    public const OPTIONAL = 'optional';
    public const PROHIBITED = 'prohibited';

    private $roleName;
    private $operator;
    private $property;
    private $value;

    private function __construct( $roleName, $property, $operator, $value ){
        $this->roleName = $roleName;
        $this->property = $property;
        $this->operator = $operator;
        $this->value = $value;
    }

    public function getRoleName(){ return $this->roleName; }
    public function getOperator(){ return $this->operator; }
    public function getProperty(){ return $this->property; }
    public function getValue(){ return $this->value; }
}


/**
 * Note on Types of Requirements
 * 
 * Most requirements are that a property is populated
 *   (has at least one Department, has a username, has a phone number)
 *
 * Some requirements are more specific, such as requiring a specific Role
 * 
 */


interface I_UserCategoryRuleProvider {
    public function getUserCategoryRules();
}

class UserCategoryRules implements I_UserCategoryRuleProvider {
    // TODO: per-module rules

    // TODO: All roles except for Lab Contact/Personnel should prohibit Supervisor_id
    public function getUserCategoryRules(){
        return [
            // PI must have a Department
            //UserCategoryRule::required(LabInspectionModule::ROLE_PI, 'Department'),
            UserCategoryRule::prohibited(LabInspectionModule::ROLE_PI, 'Supervisor_id'),

            // Lab Personnel may or may not have a PI supervisor
            UserCategoryRule::optional(LabInspectionModule::ROLE_PERSONNEL, 'Supervisor_id'),

            // Lab Contacts must have a PI supervisor
            // Lab Contacts must have the LAB_PERSONNEL role
            UserCategoryRule::required(LabInspectionModule::ROLE_CONTACT, 'Supervisor_id'),
            UserCategoryRule::required(LabInspectionModule::ROLE_CONTACT, 'Role.Name', LabInspectionModule::ROLE_PERSONNEL),

            UserCategoryRule::required(ChairReportModule::ROLE_CHAIR, 'Department'),
            UserCategoryRule::prohibited(ChairReportModule::ROLE_CHAIR, 'Supervisor_id'),

            UserCategoryRule::required(ChairReportModule::ROLE_COORDINATOR, 'Department'),
            UserCategoryRule::prohibited(ChairReportModule::ROLE_COORDINATOR, 'Supervisor_id'),

            //LabInspectionModule::ROLE_INSPECTOR => [],
            //LabInspectionModule::ROLE_TEACHING_LAB_CONTACT => [],
        ];
    }
}

?>
