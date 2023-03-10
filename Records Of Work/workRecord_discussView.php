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

use Gibbon\Tables\DataTable;
use Gibbon\Tables\Action;
use Gibbon\Forms\Form;
use Gibbon\Services\Format;
use Gibbon\Module\RecordsOfWork\Domain\ReplyTemplateGateway;
use Gibbon\Module\RecordsOfWork\Domain\IssueDiscussGateway;
use Gibbon\Module\RecordsOfWork\Domain\IssueGateway;
use Gibbon\Module\RecordsOfWork\Domain\IssueNoteGateway;
use Gibbon\Module\RecordsOfWork\Domain\TechGroupGateway;
use Gibbon\Module\RecordsOfWork\Domain\TechnicianGateway;
use Gibbon\Domain\DataSet;
use Gibbon\Domain\System\DiscussionGateway;
use Gibbon\Domain\System\SettingGateway;
use Gibbon\Domain\User\UserGateway;
use Gibbon\Domain\School\FacilityGateway;
use Gibbon\Domain\Timetable\CourseGateway;
use Gibbon\View\View;
use Google\Service\FirebaseRules\Issue;

$page->breadcrumbs->add(__('Discuss Record Of Work'));

if (!isModuleAccessible($guid, $connection2)) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    $workrecordID = $_GET['workrecordID'] ?? '';

    $issueGateway = $container->get(IssueGateway::class);
    $issue = $issueGateway->getIssueByID($workrecordID);

    if (empty($workrecordID) || empty($issue)) {
        $page->addError(__('No Record Of Work Selected.'));
    } else {
        //Set up gateways
        $techGroupGateway = $container->get(TechGroupGateway::class);
        $technicianGateway = $container->get(TechnicianGateway::class);

        //Information about the current user
        $gibbonPersonID = $session->get('gibbonPersonID');
        $isPersonsIssue = ($issue['gibbonPersonID'] == $gibbonPersonID);
        $isTechnician = $technicianGateway->getTechnicianByPersonID($gibbonPersonID)->isNotEmpty();
        $isRelated = $issueGateway->isRelated($workrecordID, $gibbonPersonID);
        $hasViewAccess = $techGroupGateway->getPermissionValue($gibbonPersonID, 'viewRecords');
        $hasFullAccess = $techGroupGateway->getPermissionValue($gibbonPersonID, 'fullAccess');

        //Information about the issue's technician
        $technician = $technicianGateway->getTechnician($issue['qualityassuaranceID']);
        $technician = $technician->isNotEmpty() ? $technician->fetch() : [];
        $hasTechAssigned = !empty($technician);
        $isResolved = ($issue['status'] == 'completed');

        $allowed = $isRelated
            || (!$hasTechAssigned && $isTechnician)
            || $hasViewAccess;


        if ($allowed) {
            $createdByShow = ($issue['createdByID'] != $issue['gibbonPersonID']);

            $userGateway = $container->get(UserGateway::class);
            $owner = $userGateway->getByID($issue['gibbonPersonID']);
            if ($owner['gibbonRoleIDPrimary'] == '003' ) {
                $ownerRole = 'Student';
            } else {
                $ownerRole = 'Staff';
            }
            $courseGateway = $container->get(CourseGateway::class);
            $classes= $courseGateway->getCourseClassById($issue['gibbonCourseClassID']);
            $classrecord = Format::courseClassName($classes['courseNameShort'], $classes['name']);
            $detailsData = [
                'workrecordID' => $workrecordID,
                'owner' => Format::nameLinked($owner['gibbonPersonID'], $owner['title'] , $owner['preferredName'] , $owner['surname'] , $ownerRole),
                'technician' => $hasTechAssigned ? Format::name($technician['title'] , $technician['preferredName'] , $technician['surname'] , 'Student') : __('Submitted'),
                'date' => Format::date($issue['date']),
                'classes'=> $classrecord
            ];

            $table = DataTable::createDetails('details');
            $table->setTitle($issue['weekNumber']);
            $table->addMetaData('allowHTML', ['description']);

            if ($isResolved) {
                if ($isPersonsIssue || ($isRelated && $techGroupGateway->getPermissionValue($gibbonPersonID, 'undoRecordsChecked')) || $hasFullAccess) {
                    $table->addHeaderAction('reincarnate', __('Mark as Incomplete'))
                            ->setIcon('reincarnate')
                            ->directLink()
                            ->setURL('/modules/' . $session->get('module') . '/workRecord_reincarnateProcess.php')
                            ->addParam('workrecordID', $workrecordID);
                }
            } else {
                if (!$hasTechAssigned) {
                     if ($techGroupGateway->getPermissionValue($gibbonPersonID, 'acceptRecords') && !$isPersonsIssue) {
                        $table->addHeaderAction('accept', __('Review | Comment'))
                                ->setIcon('page_new')
                                ->directLink()
                                ->setURL('/modules/' . $session->get('module') . '/workRecord_acceptProcess.php')
                                ->addParam('workrecordID', $workrecordID);
                    }
                    if (($techGroupGateway->getPermissionValue($gibbonPersonID, 'assignRecords') && !$isPersonsIssue) || $hasFullAccess) {
                        $table->addHeaderAction('assign', __('Assign for Further check'))
                                ->setIcon('attendance')
                                ->modalWindow()
                                ->setURL('/modules/' . $session->get('module') . '/workRecord_assign.php')
                                ->addParam('workrecordID', $workrecordID);
                    }
                } else {
                    $table->addHeaderAction('refresh', __('Refresh'))
                            ->setIcon('refresh')
                            ->setURL('/modules/' . $session->get('module') . '/workRecord_discussView.php')
                            ->addParam('workrecordID', $workrecordID);

                    if (($techGroupGateway->getPermissionValue($gibbonPersonID, 'reassignRecords') && !$isPersonsIssue) || $hasFullAccess) {
                        $table->addHeaderAction('reassign', __('Let another HOD review'))
                                ->setIcon('attendance')
                                ->modalWindow()
                                ->setURL('/modules/' . $session->get('module') . '/workRecord_assign.php')
                                ->addParam('workrecordID', $workrecordID);
                    }
                }

                if ($isPersonsIssue || ($isRelated && $techGroupGateway->getPermissionValue($gibbonPersonID, 'recordsChecked')) || $hasFullAccess) {
                    $table->addHeaderAction('resolve', __('Mark as Complete'))
                            ->setIcon('iconTick')
                            ->directLink()
                            ->setURL('/modules/' . $session->get('module') . '/workRecord_resolveProcess.php')
                            ->addParam('workrecordID', $workrecordID);
                }
            }

            //$table->addColumn('workrecordID', __('ID'))
            //        ->format(Format::using('number', ['workrecordID', 0]));

            $table->addColumn('owner', __("Teacher's Name"));

            $table->addColumn('classes',__('Classes'));

            $table->addColumn('technician', __('Status'));

            $table->addColumn('date', __('Date'));

            if (!empty($issue['facility'])) {
                $detailsData['facility'] = $issue['facility'];
                $table->addColumn('facility', __('Facility'));
            }
            if ($createdByShow) {
                $createdBy = $userGateway->getByID($issue['createdByID']);
                $detailsData['createdBy'] = Format::name($createdBy['title'] , $createdBy['preferredName'] , $createdBy['surname'] , 'Student');
                $table->addColumn('createdBy', __('Created By'));
            }

            $table->addMetaData('gridClass', 'grid-cols-' . count($detailsData));

            $detailsData['contentCovered'] = $issue['contentCovered'];
            $table->addColumn('contentCovered', __('Content Covered'))->addClass('col-span-10');

            echo $table->render([$detailsData]);

            $settingGateway = $container->get(SettingGateway::class);

            if ($isTechnician && !$isPersonsIssue && $settingGateway->getSettingByScope('Records Of Work', 'qaNotes')) {
                $form = Form::create('qaNotes',  $session->get('absoluteURL') . '/modules/' . $session->get('module') . '/workRecord_discussNoteProccess.php', 'post');
                $form->addHiddenValue('workrecordID', $workrecordID);
                $form->addHiddenValue('address', $session->get('address'));

                $row = $form->addRow();
                    $col = $row->addColumn();
                        $col->addHeading(__('QA Notes'))->addClass('inline-block');

                    $col->addWebLink('<img title="'.__('Add QA Note').'" src="./themes/'.$session->get('gibbonThemeName').'/img/plus.png" />')
                        ->addData('toggle', '.techNote')
                        ->addClass('floatRight');

                $row = $form->addRow()->setClass('techNote hidden flex flex-col sm:flex-row items-stretch sm:items-center');
                    $col = $row->addColumn();
                        $col->addLabel('techNote', __('QA Note'));
                        $col->addEditor('techNote', $guid)
                            ->setRows(5)
                            ->showMedia()
                            ->required();

                $row = $form->addRow()->setClass('techNote hidden flex flex-col sm:flex-row items-stretch sm:items-center');;
                    $row->addFooter();
                    $row->addSubmit();

                $issueNoteGateway = $container->get(IssueNoteGateway::class);
                $notes = $issueNoteGateway->getIssueNotesByID($workrecordID)->fetchAll();

                if (count($notes) > 0) {
                    $form->addRow()
                        ->addContent('comments')
                        ->setContent($page->fetchFromTemplate('ui/discussion.twig.html', [
                            'title' => __(''),
                            'discussion' => $notes
                        ]));
                }

                echo $form->getOutput();
            }


            $form = Form::create('issueDiscuss',  $session->get('absoluteURL') . '/modules/' . $session->get('module') . '/workRecord_discussPostProccess.php?workrecordID=' . $workrecordID, 'post');
            $form->addHiddenValue('address', $session->get('address'));
            $row = $form->addRow();
            $col = $row->addColumn();
                $col->addHeading(__('Comments'))->addClass('inline-block');

            if ($issue['status'] == 'InReview' && ($isRelated || $hasFullAccess)) {
                $col->addWebLink('<img title="'.__('Add Comment').'" src="./themes/'.$session->get('gibbonThemeName').'/img/plus.png" />')->addData('toggle', '.comment')->addClass('floatRight');
                
                if ($isTechnician) {
                    $replyTemplateGateway = $container->get(ReplyTemplateGateway::class);
                    $criteria = $replyTemplateGateway->newQueryCriteria()
                        ->sortBy(['name', 'recordsOfWporkReplyTemplateID']);
                    $templateNames = NULL;
                    $templates = NULL;
                    $replyTemplates = $replyTemplateGateway->queryTemplates($criteria);
                    foreach ($replyTemplates as $replyTemplate) {
                        $templateNames[$replyTemplate['recordsOfWporkReplyTemplateID']] = $replyTemplate['name'];
                        $templates[$replyTemplate['recordsOfWporkReplyTemplateID']] = $replyTemplate['body'];
                    }
                    if ($templates != NULL) {
                        $row = $form->addRow()->setClass('comment hidden flex flex-col sm:flex-row items-stretch sm:items-center');
                            $row->addLabel('replyTemplates', __('Reply Templates'));
                            $row->addSelect('replyTemplates')
                                ->fromArray($templateNames)->placeholder('Select a Reply Template');
                    }
                }
                $row = $form->addRow()->setClass('comment hidden flex flex-col sm:flex-row items-stretch sm:items-center');
                    $column = $row->addColumn();
                    $column->addLabel('comment', __('Comment'));
                    $column->addEditor('comment', $guid)
                        ->setRows(5)
                        ->showMedia()
                        ->required();

                $row = $form->addRow()->setClass('comment hidden flex flex-col sm:flex-row items-stretch sm:items-center');
                    $row->addFooter();
                    $row->addSubmit();
                
                
               
            }

            $issueDiscussGateway = $container->get(IssueDiscussGateway::class);
            $logs = $issueDiscussGateway->getIssueDiscussionByID($workrecordID)->fetchAll();

            if (count($logs) > 0) {
                array_walk($logs, function (&$discussion, $key) use ($issue) {
                    if ($discussion['gibbonPersonID'] == $issue['gibbonPersonID']) {
                        $discussion['type'] = 'Owner';
                    } else {
                        $discussion['type'] = 'QA';
                    }
                });

                $form->addRow()
                    ->addContent('comments')
                    ->setContent($page->fetchFromTemplate('ui/discussion.twig.html', [
                        'title' => __(''),
                        'discussion' => $logs
                    ]));
            }

            if (count($form->getRows()) > 1) {
                echo $form->getOutput();
            }
            if ($isTechnician) {
                ?>
                <script>
                //Javascript to change reply when template selector is changed.
                    <?php echo 'var templates = ' . json_encode($templates) . ';'; ?>
                    $("select[name=replyTemplates]").on('change', function(){
                        var templateID = $(this).val();
                        if (templateID != '' && templateID >= 0) {
                            if(confirm('Are you sure you want to use this template. Warning: This will overwrite any thing currently written.')) {
                                tinyMCE.get('comment').setContent(templates[templateID]);
                            }
                        }
                    });
                </script>
                <?php
            } 
        } else {
            $page->addError(__('You do not have access to this action.'));
        }
    }
        
}
