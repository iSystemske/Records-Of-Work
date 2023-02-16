<?php
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

//Basic variables
$name           = 'Records Of Work';
$description    = 'Allows Teachers to keep weekly records.';
$entryURL       = 'workRecord_view.php';
$type           = 'Additional';
$category       = 'Learn';
$version        = '2.2.00';
$author         = 'Kelvin';
$url            = 'https://github.com/kelvinmw';

//Module tables & gibbonSettings entries
$moduleTables[] = "CREATE TABLE `recordsOfWork` (
    `workrecordID` int(12) unsigned zerofill NOT NULL AUTO_INCREMENT,
    `qualityassuaranceID` int(4) unsigned zerofill DEFAULT NULL,
    `gibbonPersonID` int(10) unsigned zerofill NOT NULL,
    `weekNumber` varchar(55) NOT NULL,
    `contentCovered` text NOT NULL,
    `date` date NOT NULL,
    `status` ENUM('completed','InReview','Submitted') DEFAULT 'Submitted',
    `category` varchar(100) DEFAULT NULL,
    `priority` varchar(100) DEFAULT NULL,
    `gibbonSchoolYearID` int(3) unsigned zerofill NOT NULL,
    `createdByID` int(12) unsigned zerofill NOT NULL,
    `subcategoryID` int(4) UNSIGNED ZEROFILL DEFAULT NULL,
    `gibbonSpaceID` int(5) UNSIGNED ZEROFILL DEFAULT NULL,
    `gibbonCourseClassID` varchar(100) NOT NULL,
    `gibbonCourseID` int(10) unsigned zerofill DEFAULT NULL,
    `gibbonYearGroupID` int(10) unsigned zerofill DEFAULT NULL,
    PRIMARY KEY (`workrecordID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

$moduleTables[] = "CREATE TABLE `recordsOfWorkDiscuss` (
    `recordsOfWorkDiscussID` int(12) unsigned zerofill NOT NULL AUTO_INCREMENT,
    `workrecordID` int(12) unsigned zerofill NOT NULL,
    `comment` text NOT NULL,
    `timestamp` timestamp DEFAULT CURRENT_TIMESTAMP,
    `gibbonPersonID` int(10) unsigned zerofill NOT NULL,
    PRIMARY KEY (`recordsOfWorkDiscussID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

$moduleTables[] = "CREATE TABLE `recordsOfWorkNotes` (
    `issueNoteID` int(12) unsigned zerofill NOT NULL AUTO_INCREMENT,
    `workrecordID` int(12) unsigned zerofill NOT NULL,
    `note` text NOT NULL,
    `timestamp` timestamp DEFAULT CURRENT_TIMESTAMP,
    `gibbonPersonID` int(10) unsigned zerofill NOT NULL,
    PRIMARY KEY (`issueNoteID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";


$moduleTables[] = "CREATE TABLE `schoolQA` (
    `qualityassuaranceID` int(4) unsigned zerofill NOT NULL AUTO_INCREMENT,
    `gibbonPersonID` int(10) unsigned zerofill NOT NULL,
    `groupID` int(4) unsigned zerofill NOT NULL,
    PRIMARY KEY (`qualityassuaranceID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

$moduleTables[] = "CREATE TABLE `qualityAssuaranceGroups` (
    `groupID` int(4) unsigned zerofill NOT NULL AUTO_INCREMENT,
    `groupName` varchar(55) NOT NULL,
    `viewRecords` boolean DEFAULT 1,
    `viewRecordsStatus` ENUM('All', 'UP', 'PR', 'InReview') DEFAULT 'All',
    `assignRecords` boolean DEFAULT 0,
    `acceptRecords` boolean DEFAULT 1,
    `recordsChecked` boolean DEFAULT 1,
    `createRecordsForOther` boolean DEFAULT 1,
    `fullAccess` boolean DEFAULT 0,
    `reassignRecords` boolean DEFAULT 0,
    `undoRecordsChecked` boolean DEFAULT 1,
    PRIMARY KEY (`groupID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

$moduleTables[] = "CREATE TABLE `qualityAssuaranceDepartments` (
    `departmentID` int(4) unsigned zerofill NOT NULL AUTO_INCREMENT,
    `schoolYearGroup` varchar(55) NOT NULL,
    `departmentDesc` varchar(128) NOT NULL,
    `gibbonSchoolYearID` varchar(128),
    PRIMARY KEY (`departmentID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

$moduleTables[] = "CREATE TABLE `qualityAssuaranceDepartmentPermissions` (
    `departmentPermissionsID` int(4) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
    `departmentID` int(4) UNSIGNED ZEROFILL NOT NULL,
    `gibbonRoleID` int(3) UNSIGNED ZEROFILL NOT NULL,
    PRIMARY KEY (`departmentPermissionsID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

$moduleTables[] = "CREATE TABLE `qualityAssuaranceGroupDepartment` (
    `groupDepartmentID` int(4) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
    `groupID` int(4) UNSIGNED ZEROFILL NOT NULL,
    `departmentID` int(4) UNSIGNED ZEROFILL NOT NULL,
    PRIMARY KEY (`groupDepartmentID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

$moduleTables[] = "CREATE TABLE `recordsOfWorkclasses` (
    `classID` int(12) unsigned zerofill NOT NULL AUTO_INCREMENT,
    `workrecordID` int(12) unsigned zerofill NOT NULL,
    `gibbonCourseClassID` int(12) unsigned zerofill NOT NULL,
    `className` varchar(55),
    PRIMARY KEY (`classID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

$moduleTables[] = "CREATE TABLE `recordsOfWporkReplyTemplate` (
    `recordsOfWporkReplyTemplateID` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
    `name` varchar(30) NOT NULL,
    `body` text NOT NULL,
    PRIMARY KEY (`recordsOfWporkReplyTemplateID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

$moduleTables[] = "INSERT INTO `qualityAssuaranceGroups` (`groupID`, `groupName`, `viewRecords`, `viewRecordsStatus`, `assignRecords`, `acceptRecords`, `recordsChecked`, `createRecordsForOther`, `fullAccess`, `reassignRecords`, `undoRecordsChecked`)
    VALUES
    (NULL, 'Head QA', 1, 'All', 1, 1, 1, 1, 1, 1, 1),
    (NULL, 'QA', 1, 'All', 0, 1, 1, 1, 0, 0, 1)";

$moduleTables[] = "INSERT INTO `gibbonSetting` (`gibbonSettingID`, `scope`, `name`, `nameDisplay`, `description`, `value`)
    VALUES
    (NULL, 'Records Of Work', 'recordsPriority', 'Record Of Work Priority', 'Different priority levels for the records.', ''),
    (NULL, 'Records Of Work', 'recordsPriorityName', 'Record Of Work Priority Name', 'Different name for the Record Of Work Priority', 'Priority'),
    (NULL, 'Records Of Work', 'records0fWorkCategory', 'Record Of Work Category', 'Different categories for the records.', 'Pre-School,Lower Primary,Upper Primary,Secondary'),
    (NULL, 'Records Of Work', 'simpleCategories', 'Simple Categories', 'Whether to use Simple Categories or Not.', TRUE),
    (NULL, 'Records Of Work', 'qaNotes', 'QA Notes', 'Whether quality assuarance can leave notes on Records Of Work that only other technicians can see.', FALSE)";

//Action rows
//One array per action
$actionRows[] = [
    'name'                      => 'Create Record Of Work', //The name of the action (appears to user in the right hand side module menu)
    'precedence'                => '0', //If it is a grouped action, the precedence controls which is highest action in group
    'category'                  => 'Records Of Work', //Optional: subgroups for the right hand side module menu
    'description'               => 'Allows the user to submit records of work to be checked by the quality assuarance.', //Text description
    'URLList'                   => 'workRecord_create.php',
    'entryURL'                  => 'workRecord_create.php',
    'defaultPermissionAdmin'    => 'Y', //Default permission for built in role Admin
    'defaultPermissionTeacher'  => 'Y', //Default permission for built in role Teacher
    'defaultPermissionStudent'  => 'Y', //Default permission for built in role Student
    'defaultPermissionParent'   => 'N', //Default permission for built in role Parent
    'defaultPermissionSupport'  => 'Y', //Default permission for built in role Support
    'categoryPermissionStaff'   => 'Y', //Should this action be available to user roles in the Staff category?
    'categoryPermissionStudent' => 'Y', //Should this action be available to user roles in the Student category?
    'categoryPermissionParent'  => 'Y', //Should this action be available to user roles in the Parent category?
    'categoryPermissionOther'   => 'Y', //Should this action be available to user roles in the Other category?
];

$actionRows[] = [
    'name'                      => 'Records Of Work',
    'precedence'                => '0',
    'category'                  => 'Records Of Work',
    'description'               =>  'Gives the user access to the Records Of Work section.',
    'URLList'                   => 'workRecord_view.php',
    'entryURL'                  => 'workRecord_view.php',
    'defaultPermissionAdmin'    => 'Y',
    'defaultPermissionTeacher'  => 'Y',
    'defaultPermissionStudent'  => 'Y',
    'defaultPermissionParent'   => 'N',
    'defaultPermissionSupport'  => 'Y',
    'categoryPermissionStaff'   => 'Y',
    'categoryPermissionStudent' => 'Y',
    'categoryPermissionParent'  => 'Y',
    'categoryPermissionOther'   => 'Y',
];

$actionRows[] = [
    'name'                      => 'Records Of Work Settings',
    'precedence'                => '0',
    'category'                  => 'Admin',
    'description'               => 'Allows the user to edit the settings for the module.',
    'URLList'                   => 'recordsOfWork_settings.php',
    'entryURL'                  => 'recordsOfWork_settings.php',
    'defaultPermissionAdmin'    => 'Y',
    'defaultPermissionTeacher'  => 'N',
    'defaultPermissionStudent'  => 'N',
    'defaultPermissionParent'   => 'N',
    'defaultPermissionSupport'  => 'N',
    'categoryPermissionStaff'   => 'Y',
    'categoryPermissionStudent' => 'N',
    'categoryPermissionParent'  => 'N',
    'categoryPermissionOther'   => 'Y',
];

$actionRows[] = [
    'name'                      => 'Quality Assuarance',
    'precedence'                => '0',
    'category'                  => 'Quality Assuarance',
    'description'               => 'Allows the user to manage the Quality Assuarance.',
    'URLList'                   => 'recordsOfWork_manageTechnicians.php',
    'entryURL'                  => 'recordsOfWork_manageTechnicians.php',
    'defaultPermissionAdmin'    => 'Y',
    'defaultPermissionTeacher'  => 'N',
    'defaultPermissionStudent'  => 'N',
    'defaultPermissionParent'   => 'N',
    'defaultPermissionSupport'  => 'N',
    'categoryPermissionStaff'   => 'Y',
    'categoryPermissionStudent' => 'N',
    'categoryPermissionParent'  => 'N',
    'categoryPermissionOther'   => 'Y',
];

$actionRows[] = [
    'name'                      => 'Manage QA Groups',
    'precedence'                => '0',
    'category'                  => 'Quality Assuarance',
    'description'               => 'Allows the user to manage the QA Groups.',
    'URLList'                   => 'recordsOfWork_manageTechnicianGroup.php',
    'entryURL'                  => 'recordsOfWork_manageTechnicianGroup.php',
    'defaultPermissionAdmin'    => 'Y',
    'defaultPermissionTeacher'  => 'N',
    'defaultPermissionStudent'  => 'N',
    'defaultPermissionParent'   => 'N',
    'defaultPermissionSupport'  => 'N',
    'categoryPermissionStaff'   => 'Y',
    'categoryPermissionStudent' => 'N',
    'categoryPermissionParent'  => 'N',
    'categoryPermissionOther'   => 'Y',
];

$actionRows[] = [
    'name'                      => 'Records Of Work Statistics',
    'precedence'                => '0',
    'category'                  => 'Admin',
    'description'               => 'Statistics for the Records Of Work.',
    'URLList'                   => 'recordsOfWork_statistics.php',
    'entryURL'                  => 'recordsOfWork_statistics.php',
    'defaultPermissionAdmin'    => 'Y',
    'defaultPermissionTeacher'  => 'N',
    'defaultPermissionStudent'  => 'N',
    'defaultPermissionParent'   => 'N',
    'defaultPermissionSupport'  => 'N',
    'categoryPermissionStaff'   => 'Y',
    'categoryPermissionStudent' => 'N',
    'categoryPermissionParent'  => 'N',
    'categoryPermissionOther'   => 'Y',
];

$actionRows[] = [
    'name'                      => 'Manage Departments',
    'precedence'                => '0',
    'category'                  => 'Quality Assuarance',
    'description'               => 'Allows the user to manage the Records Of Work Departments.',
    'URLList'                   => 'recordsOfWork_manageDepartments.php',
    'entryURL'                  => 'recordsOfWork_manageDepartments.php',
    'defaultPermissionAdmin'    => 'Y',
    'defaultPermissionTeacher'  => 'N',
    'defaultPermissionStudent'  => 'N',
    'defaultPermissionParent'   => 'N',
    'defaultPermissionSupport'  => 'N',
    'categoryPermissionStaff'   => 'Y',
    'categoryPermissionStudent' => 'N',
    'categoryPermissionParent'  => 'N',
    'categoryPermissionOther'   => 'Y',
];

$actionRows[] = [
    'name'                      => 'Records Of Work Reply Templates',
    'precedence'                => '0',
    'category'                  => 'Settings',
    'description'               => 'Manage Records Of Work Reply Templates.',
    'URLList'                   => 'recordsOfWork_manageReplyTemplates.php',
    'entryURL'                  => 'recordsOfWork_manageReplyTemplates.php',
    'defaultPermissionAdmin'    => 'Y',
    'defaultPermissionTeacher'  => 'N',
    'defaultPermissionStudent'  => 'N',
    'defaultPermissionParent'   => 'N',
    'defaultPermissionSupport'  => 'N',
    'categoryPermissionStaff'   => 'Y',
    'categoryPermissionStudent' => 'N',
    'categoryPermissionParent'  => 'N',
    'categoryPermissionOther'   => 'N',
];
?>
