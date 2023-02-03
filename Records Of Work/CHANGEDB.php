<?php
//USE;end TO SEPERATE SQL STATEMENTS. DON'T USE;end IN ANY OTHER PLACES!

$sql=array();
$count=0;

//v0.0.01
$sql[$count][0]="0.0.01";
$sql[$count][1]="-- First version, nothing to update";

//v0.0.02
$count++;
$sql[$count][0]="0.0.02";
$sql[$count][1]="";

//v0.1.00
$count++;
$sql[$count][0]="0.1.00";
$sql[$count][1]="
UPDATE gibbonAction SET name='Create Record Of Work', URLList='workRecord_create.php', entryURL='workRecord_create.php' WHERE name='Submit Record Of Work' AND gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Records Of Work');end
INSERT INTO gibbonAction SET name='Create Issue_forOther', precedence='1', category='', description='Submits your records of work to be checked by the quality assuarance with an optional feature to create on the behalf of others.', URLList='workRecord_create.php', entryURL='workRecord_create.php', defaultPermissionAdmin='Y', defaultPermissionTeacher='Y', defaultPermissionStudent='Y', defaultPermissionParent='N', defaultPermissionSupport='Y', categoryPermissionStaff='Y', categoryPermissionStudent='Y', categoryPermissionParent='Y', categoryPermissionOther='N' WHERE name='Submit Record Of Work' AND gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Records Of Work');end
ALTER TABLE recordsOfWork ADD createdByID int(12) unsigned zerofill NOT NULL;end";

//v0.1.01
$count++;
$sql[$count][0]="0.1.01";
$sql[$count][1]="
INSERT INTO gibbonAction SET name='Create Issue_forOther', precedence='1', category='', description='Submits your records of work to be checked by the quality assuarance with an optional feature to create on the behalf of others.', URLList='workRecord_create.php', entryURL='workRecord_create.php', defaultPermissionAdmin='Y', defaultPermissionTeacher='Y', defaultPermissionStudent='Y', defaultPermissionParent='N', defaultPermissionSupport='Y', categoryPermissionStaff='Y', categoryPermissionStudent='Y', categoryPermissionParent='Y', categoryPermissionOther='N', gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Records Of Work');end";

//v0.1.02
$count++;
$sql[$count][0]="0.1.02";
$sql[$count][1]="
UPDATE gibbonAction SET URLList='workRecord_view.php, workRecord_discuss_view.php' WHERE name='View workRecord_All' AND gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Records Of Work');end
UPDATE gibbonAction SET URLList='workRecord_view.php, workRecord_assign.php, workRecord_discuss_view.php' WHERE name='View workRecord_All&Assign' AND gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Records Of Work');end
ALTER TABLE recordsOfWorkDiscuss DROP COLUMN technicianPosted;end
ALTER TABLE recordsOfWorkDiscuss ADD gibbonPersonID int(10) unsigned zerofill NOT NULL;end";

//v0.1.03
$count++;
$sql[$count][0]="0.1.03";
$sql[$count][1]="";

//v0.1.04
$count++;
$sql[$count][0]="0.1.04";
$sql[$count][1]="";

//v0.1.05
$count++;
$sql[$count][0]="0.1.05";
$sql[$count][1]="";

//v0.2.00
$count++;
$sql[$count][0]="0.2.00";
$sql[$count][1]="";

//v0.2.01
$count++;
$sql[$count][0]="0.2.01";
$sql[$count][1]="";

//v0.2.02
$count++;
$sql[$count][0]="0.2.02";
$sql[$count][1]="";

//v0.3.00
$count++;
$sql[$count][0]="0.3.00";
$sql[$count][1]="
DELETE FROM gibbonAction WHERE name='View workRecord_All' AND gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Records Of Work');end
DELETE FROM gibbonAction WHERE name='View workRecord_All&Assign' AND gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Records Of Work');end
UPDATE gibbonAction SET name='Records', description='Shows records dePending on role/permissions.' WHERE name='View workRecord_Mine'AND gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Records Of Work');end
DELETE FROM gibbonAction WHERE name='Create Issue_forOther' AND gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Records Of Work');end
CREATE TABLE `qualityAssuaranceGroups` (`groupID` int(4) unsigned zerofill NOT NULL AUTO_INCREMENT, `groupName` varchar(55) NOT NULL, `viewRecords` boolean DEFAULT 1, `viewRecordsStatus` ENUM('All', 'UP', 'PR', 'InReview') DEFAULT 'All', `assignRecords` boolean DEFAULT 0, `acceptRecords` boolean DEFAULT 1, `recordsChecked` boolean DEFAULT 1, `createRecordsForOther` boolean DEFAULT 1, `fullAccess` boolean DEFAULT 0, PRIMARY KEY (`groupID`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;end
UPDATE gibbonAction SET URLList='recordsOfWork_settings.php', entryURL='recordsOfWork_settings.php' WHERE name='Records Of Work Settings'AND gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Records Of Work');end
UPDATE gibbonAction SET URLList='recordsOfWork_manageTechnicians.php', entryURL='recordsOfWork_manageTechnicians.php' WHERE name='Manage Technicians'AND gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Records Of Work');end
INSERT INTO gibbonAction SET name='Manage QA Groups', precedence='0', category='', description='Manage QA Groups.', URLList='recordsOfWork_manageTechnicianGroup.php', entryURL='recordsOfWork_manageTechnicianGroup.php', defaultPermissionAdmin='Y', defaultPermissionTeacher='N', defaultPermissionStudent='N', defaultPermissionParent='N', defaultPermissionSupport='N', categoryPermissionStaff='Y', categoryPermissionStudent='N', categoryPermissionParent='N', categoryPermissionOther='N', gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Records Of Work');end
ALTER TABLE schoolQA ADD groupID int(4) unsigned zerofill NOT NULL;end
";

//v0.3.01
$count++;
$sql[$count][0]="0.3.01";
$sql[$count][1]="
INSERT INTO `gibbonPermission` (`permissionID` ,`gibbonRoleID` ,`gibbonActionID`) VALUES (NULL , '1', (SELECT gibbonActionID FROM gibbonAction JOIN gibbonModule ON (gibbonAction.gibbonModuleID=gibbonModule.gibbonModuleID) WHERE gibbonModule.name='Records Of Work' AND gibbonAction.name='Manage QA Groups'));end
UPDATE gibbonAction SET URLList='recordsOfWork_manageTechnicians.php', entryURL='recordsOfWork_manageTechnicians.php' WHERE name='Manage QA Groups' AND gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Records Of Work');end
";

//v0.3.02
$count++;
$sql[$count][0]="0.3.02";
$sql[$count][1]="
UPDATE gibbonAction SET URLList='recordsOfWork_manageTechnicianGroup.php', entryURL='recordsOfWork_manageTechnicianGroup.php' WHERE name='Manage QA Groups' AND gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Records Of Work');end
UPDATE gibbonAction SET URLList='recordsOfWork_manageTechnicians.php', entryURL='recordsOfWork_manageTechnicians.php' WHERE name='Manage QA' AND gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Records Of Work');end
";

//v0.3.03
$count++;
$sql[$count][0]="0.3.03";
$sql[$count][1]="
UPDATE gibbonAction SET URLList='recordsOfWork_manageTechnicians.php', entryURL='recordsOfWork_manageTechnicians.php' WHERE name='Manage Technicians' AND gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Records Of Work');end
";

//v0.3.04
$count++;
$sql[$count][0]="0.3.04";
$sql[$count][1]="
UPDATE gibbonAction SET description='Allows the user to submit records of work to be checked by the quality assuarance.' WHERE name='Create Record Of Work' AND gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Records Of Work');end
UPDATE gibbonAction SET description='Gives the user access to the Records section' WHERE name='Records' AND gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Records Of Work');end
UPDATE gibbonAction SET description='Allows the user to manage the Technicians.' WHERE name='Manage Technicians' AND gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Records Of Work');end
UPDATE gibbonAction SET description='Allows the user to manage the Technicians Groups.' WHERE name='Manage QA Groups' AND gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Records Of Work');end
UPDATE gibbonAction SET description='Allows the user to edit the settings for the module.' WHERE name='Records Of Work Settings' AND gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Records Of Work');end
UPDATE gibbonModule SET url='https://github.com/raynichc/helpdesk' WHERE name='Records Of Work';end
";

//v0.3.05
$count++;
$sql[$count][0]="0.3.05";
$sql[$count][1]="";

//v0.3.10
$count++;
$sql[$count][0]="0.3.10";
$sql[$count][1]="
ALTER TABLE qualityAssuaranceGroups ADD reassignRecords boolean DEFAULT 0;end
";

//v0.3.11
$count++;
$sql[$count][0]="0.3.11";
$sql[$count][1]="";

//v0.3.12
$count++;
$sql[$count][0]="0.3.12";
$sql[$count][1]="";

//v0.3.13
$count++;
$sql[$count][0]="0.3.13";
$sql[$count][1]="";

//v0.3.14
$count++;
$sql[$count][0]="0.3.14";
$sql[$count][1]="";

//v0.3.15
$count++;
$sql[$count][0]="0.3.15";
$sql[$count][1]="";

//v0.3.16
$count++;
$sql[$count][0]="0.3.16";
$sql[$count][1]="
UPDATE gibbonAction SET categoryPermissionOther='Y' WHERE name='Create Record Of Work' AND gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Records Of Work');end
UPDATE gibbonAction SET categoryPermissionOther='Y' WHERE name='Records' AND gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Records Of Work');end
UPDATE gibbonAction SET categoryPermissionOther='Y' WHERE name='Manage Technicians' AND gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Records Of Work');end
UPDATE gibbonAction SET categoryPermissionOther='Y' WHERE name='Manage QA Groups' AND gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Records Of Work');end
UPDATE gibbonAction SET categoryPermissionOther='Y' WHERE name='Records Of Work Settings' AND gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Records Of Work');end
";

//v0.3.17
$count++;
$sql[$count][0]="0.3.17";
$sql[$count][1]="";

//v0.4.00
$count++;
$sql[$count][0]="0.4.00";
$sql[$count][1]="
INSERT INTO `gibbonSetting` (`gibbonSystemSettingsID`, `scope`, `name`, `nameDisplay`, `description`, `value`)
VALUES
(NULL, 'Records Of Work', 'resolvedIssuePrivacy', 'Default completed Record Of Work Privacy', 'Default privacy setting for completed records.', 'Everyone');end
ALTER TABLE recordsOfWork ADD `privacySetting` ENUM('Everyone', 'Related', 'Owner', 'No one') DEFAULT 'Everyone';end
UPDATE gibbonAction SET entrySidebar='N' WHERE name='Records' AND gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Records Of Work');end
ALTER TABLE qualityAssuaranceGroups ADD undoRecordsChecked boolean DEFAULT 1;end
";

//v0.4.10
$count++;
$sql[$count][0]="0.4.10";
$sql[$count][1]="
ALTER TABLE recordsOfWork ALTER privacySetting SET DEFAULT 'Related';end
UPDATE gibbonAction SET category='Records' WHERE name='Create Record Of Work' AND gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Records Of Work');end
UPDATE gibbonAction SET category='Records' WHERE name='Records' AND gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Records Of Work');end
UPDATE gibbonAction SET category='Settings' WHERE name='Manage Technicians' AND gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Records Of Work');end
UPDATE gibbonAction SET category='Settings' WHERE name='Manage QA Groups' AND gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Records Of Work');end
UPDATE gibbonAction SET category='Settings' WHERE name='Records Of Work Settings' AND gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Records Of Work');end
";

//v0.4.20
$count++;
$sql[$count][0]="0.4.20";
$sql[$count][1]="
INSERT INTO gibbonAction SET name='Records Of Work Statistics', precedence='0', category='Admin', description='Statistics for the Records Of Work.', URLList='recordsOfWork_statistics.php', entryURL='recordsOfWork_statistics.php', defaultPermissionAdmin='Y', defaultPermissionTeacher='N', defaultPermissionStudent='N', defaultPermissionParent='N', defaultPermissionSupport='N', categoryPermissionStaff='Y', categoryPermissionStudent='N', categoryPermissionParent='N', categoryPermissionOther='N', gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Records Of Work');end
INSERT INTO `gibbonPermission` (`permissionID` ,`gibbonRoleID` ,`gibbonActionID`) VALUES (NULL , '1', (SELECT gibbonActionID FROM gibbonAction JOIN gibbonModule ON (gibbonAction.gibbonModuleID=gibbonModule.gibbonModuleID) WHERE gibbonModule.name='Records Of Work' AND gibbonAction.name='Records Of Work Statistics'));end
UPDATE gibbonAction SET category='Admin' WHERE name='Records Of Work Settings' AND gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Records Of Work');end
UPDATE gibbonAction SET category='QA' WHERE name='Manage Technicians' AND gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Records Of Work');end
UPDATE gibbonAction SET category='QA' WHERE name='Manage QA Groups' AND gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Records Of Work');end
UPDATE gibbonModule SET description='A virtual help desk module for Gibbon.' WHERE name='Records Of Work';end
";

//v1.0.00
$count++;
$sql[$count][0]="1.0.00";
$sql[$count][1]="
";

//v1.0.01
$count++;
$sql[$count][0]="1.0.01";
$sql[$count][1]="
";

//v1.0.02
$count++;
$sql[$count][0]="2.2.00";
$sql[$count][1]="
";

//v1.1.00
$count++;
$sql[$count][0]="1.1.00";
$sql[$count][1]="
";

//v1.1.01
$count++;
$sql[$count][0]="1.1.01";
$sql[$count][1]="
";

//v1.1.02
$count++;
$sql[$count][0]="1.1.02";
$sql[$count][1]="
";

//v1.1.03
$count++;
$sql[$count][0]="1.1.03";
$sql[$count][1]="
";

//v1.1.04
$count++;
$sql[$count][0]="1.1.04";
$sql[$count][1]="
";

//v1.1.05
$count++;
$sql[$count][0]="1.1.05";
$sql[$count][1]="
";

//v1.2.00
$count++;
$sql[$count][0]="1.2.00";
$sql[$count][1]="
";

//v1.2.01
$count++;
$sql[$count][0]="1.2.01";
$sql[$count][1]="
UPDATE `gibbonModule` SET `author`='Ray Clark, Ashton Power & Adrien Tremblay' WHERE `name` = 'Records Of Work';end
";

//v1.2.02
$count++;
$sql[$count][0]="1.2.02";
$sql[$count][1]="
UPDATE `gibbonModule` SET `author`='Ray Clark, Ashton Power & Adrien Tremblay' WHERE `name` = 'Records Of Work';end
";

//v1.2.03
$count++;
$sql[$count][0]="1.2.03";
$sql[$count][1]="
";

//v1.2.04
$count++;
$sql[$count][0]="1.2.04";
$sql[$count][1]="
";

//v1.2.05
$count++;
$sql[$count][0]="1.2.05";
$sql[$count][1]="
";

//v1.2.06
$count++;
$sql[$count][0]="1.2.06";
$sql[$count][1]="
";

//v1.3.00
$count++;
$sql[$count][0]="1.3.00";
$sql[$count][1]="
CREATE TABLE `qualityAssuaranceDepartments` (`departmentID` int(4) unsigned zerofill NOT NULL AUTO_INCREMENT, `schoolYearGroup` varchar(55) NOT NULL, `departmentDesc` varchar(128) NOT NULL, PRIMARY KEY (`departmentID`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;end
CREATE TABLE `recordsOfWorkclasses` (`subcategoryID` int(4) unsigned zerofill NOT NULL AUTO_INCREMENT, `departmentID` int(4) unsigned zerofill NOT NULL, `className` varchar(55) NOT NULL, PRIMARY KEY (`subcategoryID`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;end
ALTER TABLE `recordsOfWork` ADD `gibbonSpaceID` int(5) UNSIGNED ZEROFILL DEFAULT NULL;end
ALTER TABLE `recordsOfWork` ADD `subcategoryID` int(4) UNSIGNED ZEROFILL DEFAULT NULL;end
ALTER TABLE `qualityAssuaranceGroups` ADD `departmentID` int(4) UNSIGNED ZEROFILL DEFAULT NULL;end
INSERT INTO `gibbonSetting` (`gibbonSettingID`, `scope`, `name`, `nameDisplay`, `description`, `value`) VALUES (NULL, 'Records Of Work', 'simpleCategories', 'Simple Categories', 'Whether to use Simple Categories or Not.', TRUE);end
INSERT INTO gibbonAction SET name='Manage Departments', precedence='0', category='QA', description='Allows the user to manage the Records Of Work Departments.', URLList='recordsOfWork_manageDepartments.php', entryURL='recordsOfWork_manageDepartments.php', defaultPermissionAdmin='Y', defaultPermissionTeacher='N', defaultPermissionStudent='N', defaultPermissionParent='N', defaultPermissionSupport='N', categoryPermissionStaff='Y', categoryPermissionStudent='N', categoryPermissionParent='N', categoryPermissionOther='Y', gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Records Of Work');end
INSERT INTO `gibbonPermission` (`permissionID` ,`gibbonRoleID` ,`gibbonActionID`) VALUES (NULL , '1', (SELECT gibbonActionID FROM gibbonAction JOIN gibbonModule ON (gibbonAction.gibbonModuleID=gibbonModule.gibbonModuleID) WHERE gibbonModule.name='Records Of Work' AND gibbonAction.name='Manage Departments'));end
";

//v1.4.00
$count++;
$sql[$count][0]="1.4.00";
$sql[$count][1]="
ALTER TABLE `recordsOfWork` DROP COLUMN `privacySetting`;end
DELETE FROM `gibbonSetting` WHERE name='resolvedIssuePrivacy' AND scope='Records Of Work';end
";

//v1.4.01
$count++;
$sql[$count][0]="1.4.01";
$sql[$count][1]="
";

//v1.4.10
$count++;
$sql[$count][0]="1.4.10";
$sql[$count][1]="
CREATE TABLE `qualityAssuaranceDepartmentPermissions` (`departmentPermissionsID` int(4) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT, `departmentID` int(4) UNSIGNED ZEROFILL NOT NULL, `gibbonRoleID` int(3) UNSIGNED ZEROFILL NOT NULL, PRIMARY KEY (`departmentPermissionsID`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;end
INSERT INTO `qualityAssuaranceDepartmentPermissions` (`departmentPermissionsID`,`departmentID`, `gibbonRoleID`) SELECT NULL, qualityAssuaranceDepartments.departmentID, '001' FROM qualityAssuaranceDepartments;end
INSERT INTO `qualityAssuaranceDepartmentPermissions` (`departmentPermissionsID`,`departmentID`, `gibbonRoleID`) SELECT NULL, qualityAssuaranceDepartments.departmentID, '002' FROM qualityAssuaranceDepartments;end
INSERT INTO `qualityAssuaranceDepartmentPermissions` (`departmentPermissionsID`,`departmentID`, `gibbonRoleID`) SELECT NULL, qualityAssuaranceDepartments.departmentID, '003' FROM qualityAssuaranceDepartments;end
";

//v1.4.11
$count++;
$sql[$count][0]="1.4.11";
$sql[$count][1]="
";

//v1.4.20
$count++;
$sql[$count][0]="1.4.20";
$sql[$count][1]="
INSERT INTO `gibbonSetting` (`gibbonSettingID`, `scope`, `name`, `nameDisplay`, `description`, `value`)
VALUES (NULL, 'Records Of Work', 'qaNotes', 'QA Notes', 'Whether technicians can leave notes on records that only other technicians can see.', FALSE);end
CREATE TABLE `recordsOfWorkNotes` (`issueNoteID` int(12) unsigned zerofill NOT NULL AUTO_INCREMENT, `workrecordID` int(12) unsigned zerofill NOT NULL, `note` text NOT NULL, `timestamp` timestamp DEFAULT CURRENT_TIMESTAMP, `gibbonPersonID` int(10) unsigned zerofill NOT NULL, PRIMARY KEY (`issueNoteID`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;end
";

//v1.5.00
$count++;
$sql[$count][0]="1.5.00";
$sql[$count][1]="
CREATE TABLE `qualityAssuaranceGroupDepartment` (`groupDepartmentID` int(4) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT, `groupID` int(4) UNSIGNED ZEROFILL NOT NULL, `departmentID` int(4) UNSIGNED ZEROFILL NOT NULL, PRIMARY KEY (`groupDepartmentID`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;end
INSERT INTO `qualityAssuaranceGroupDepartment` (`groupDepartmentID`, `groupID`, `departmentID`) SELECT NULL, qualityAssuaranceGroups.groupID, qualityAssuaranceGroups.departmentID FROM qualityAssuaranceGroups WHERE qualityAssuaranceGroups.departmentID IS NOT NULL;end
ALTER TABLE `qualityAssuaranceGroups` DROP COLUMN `departmentID`;end
";

//v1.5.01
$count++;
$sql[$count][0]="1.5.01";
$sql[$count][1]="
";

//v1.5.02
$count++;
$sql[$count][0]="1.5.02";
$sql[$count][1]="
";

//v2.0.00
$count++;
$sql[$count][0]="2.0.00";
$sql[$count][1]="
";

//v2.0.01
$count++;
$sql[$count][0]="2.0.01";
$sql[$count][1]="
";

//v2.0.02
$count++;
$sql[$count][0]="2.0.02";
$sql[$count][1]="
";

//v2.1.00
$count++;
$sql[$count][0]="2.1.00";
$sql[$count][1]="
CREATE TABLE `recordsOfWporkReplyTemplate` (`recordsOfWporkReplyTemplateID` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT, `name` varchar(30) NOT NULL, `body` text NOT NULL, PRIMARY KEY (`recordsOfWporkReplyTemplateID`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;end
INSERT INTO gibbonAction SET name='Records Of Work Reply Templates', precedence=0, category='Settings', description='Manage Records Of Work Reply Templates.', URLList='recordsOfWork_manageReplyTemplates.php', entryURL='recordsOfWork_manageReplyTemplates.php', defaultPermissionAdmin='Y', defaultPermissionTeacher='N', defaultPermissionStudent='N', defaultPermissionParent='N', defaultPermissionSupport='N', categoryPermissionStaff='Y', categoryPermissionStudent='N', categoryPermissionParent='N', categoryPermissionOther='N', gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Records Of Work');end
INSERT INTO `gibbonPermission` (`permissionID` ,`gibbonRoleID` ,`gibbonActionID`) VALUES (NULL , '1', (SELECT gibbonActionID FROM gibbonAction JOIN gibbonModule ON (gibbonAction.gibbonModuleID=gibbonModule.gibbonModuleID) WHERE gibbonModule.name='Records Of Work' AND gibbonAction.name='Records Of Work Reply Templates'));end
";

//v2.1.01
$count++;
$sql[$count][0]="2.1.01";
$sql[$count][1]="
";

//v2.1.02
$count++;
$sql[$count][0]="2.2.00";
$sql[$count][1]="
";
