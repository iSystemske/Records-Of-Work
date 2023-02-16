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
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

use Gibbon\UI\Chart\Chart;
use Gibbon\Tables\DataTable;
use Gibbon\Forms\Form;
use Gibbon\Services\Format;
use Gibbon\Domain\System\LogGateway;
use Gibbon\Module\RecordsOfWork\Domain\TechGroupGateway;
use Gibbon\Module\RecordsOfWork\Domain\TechnicianGateway;
use Gibbon\Module\RecordsOfWork\Domain\IssueDiscussGateway;
use Gibbon\Module\RecordsOfWork\Domain\IssueGateway;
use Gibbon\Domain\User\UserGateway;

$page->breadcrumbs
    ->add(__('Statistics'), 'recordsOfWork_statistics.php')
    ->add(__('Detailed Statistics'));

if (!isActionAccessible($guid, $connection2, "/modules/Records Of Work/recordsOfWork_statistics.php")) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    //Proceed!
    $title = $_GET["title"] ?? '';
    if (empty($title)) {
        $page->addError(__('No statistics selected.'));
    } else {
        $URL = $session->get('absoluteURL') . "/index.php?q=/modules/" . $session->get('module');

        //Default Data
        $d = new DateTime('first day of this month');
        $startDate = isset($_GET['startDate']) ? Format::dateConvert($_GET['startDate']) : $d->format('Y-m-d');
        $endDate = isset($_GET['endDate']) ? Format::dateConvert($_GET['endDate']) : date('Y-m-d');

        //Filter
        $form = Form::create('helpDeskStatistics', $session->get('absoluteURL') . '/index.php', 'get');

        $form->setTitle('Filter');
        $form->addHiddenValue('q', '/modules/' . $session->get('module') . '/recordsOfWork_statisticsDetail.php');
        $form->addHiddenValue('title', $title);

        $row = $form->addRow();
            $row->addLabel('startDate', __('Start Date Filter'));
            $row->addDate('startDate')
                ->setDateFromValue($startDate)
                ->chainedTo('endDate')
                ->required();

        $row = $form->addRow();
            $row->addLabel('endDate', __('End Date Filter'));
            $row->addDate('endDate')
                ->setDateFromValue($endDate)
                ->chainedFrom('startDate')
                ->required();

        $row = $form->addRow();
            $row->addFooter();
            $row->addSubmit();

        echo $form->getOutput();

        $logGateway = $container->get(LogGateway::class);
        $criteria = $logGateway->newQueryCriteria()
            ->sortBy('timestamp', 'DESC')
            ->filterBy('module', 'Records Of Work')
            ->filterBy('title', $title)
            ->filterBy('startDate', $startDate)
            ->filterBy('endDate', date('Y-m-d 23:59:59', strtotime($endDate)))
            ->fromPOST();

        $logs = $logGateway->queryLogs($criteria, $session->get('gibbonSchoolYearID'));
        
        $table = DataTable::createPaginated('detailedStats', $criteria);
        $table->setTitle(__($title));

        $table->addColumn('timestamp', __('Timestamp'))
                ->format(Format::using('dateTime', ['timestamp']));

        $table->addColumn('person', __('Person'))
                ->format(Format::using('name', ['title', 'preferredName', 'surname', 'Student']));


        function issueColumn(&$table, $container) {
            $issueGateway = $container->get(IssueGateway::class);
            $table->addColumn('workrecordID', __('Record Of Work'))
                ->format(function ($log) use ($issueGateway) {
                    $array = unserialize($log['serialisedArray']);

                    $issue = $issueGateway->getByID($array['workrecordID']);
                    if (!empty($issue)) {
                        return Format::link('./index.php?q=/modules/Records Of Work/workRecord_discussView.php&workrecordID=' . $issue['workrecordID'], $issue['weekNumber']);
                    }

                    return __('Could not find Record Of Work.');
                });
        }

        function techColumn(&$table, $container) {
            $technicianGateway = $container->get(TechnicianGateway::class);
            $table->addColumn('qualityassuaranceID', __('QA'))
                ->format(function ($log) use ($technicianGateway) {
                    $array = unserialize($log['serialisedArray']);

                    $technician = $technicianGateway->getTechnician($array['qualityassuaranceID']);
                    if ($technician->isNotEmpty()) {
                        $technician = $technician->fetch();
                        return Format::name($technician['title'], $technician['preferredName'], $technician['surname'], 'Student');
                    }

                    return __('Could not find QA.');
                });
        }

        function groupColumn(&$table, $container, $new = false) {
            $techGroupGateway = $container->get(TechGroupGateway::class);
            $table->addColumn('qualityassuaranceID', __($new ? 'New Group' : 'Group'))
                ->format(function ($log) use ($techGroupGateway, $new) {
                    $array = unserialize($log['serialisedArray']);

                    $group = $techGroupGateway->getByID($array[$new ? 'newGroupID' : 'groupID']);
                    if (!empty($group)) {
                        return Format::link('./index.php?q=/modules/Records Of Work/recordsOfWork_manageTechnicianGroup.php&groupID=' . $group['groupID'], $group['groupName']);
                    }

                    return __('Could not find Group.');
                });
        }

        if ($title == "Record Of Work Created" || $title == "Record Of Work Accepted" || $title == "Record Of Work Reincarnated" || $title == "Record Of Work completed") {
            issueColumn($table, $container);
            
            //Stat Person Chart
            $page->scripts->add('chart');
            $chartDataArray = $logs->toArray();
            
            $userGateway = $container->get(UserGateway::class);
            $chartData = array_count_values(array_column($chartDataArray, 'username'));
            
            $chart = Chart::create('issueChart', 'pie');
            $chart->setLegend(false);
            foreach (array_keys($chartData) as $username){
                $person = $userGateway->selectBy(['username'=>$username])->fetch();
                $people[] = Format::name($person['title'], $person['preferredName'], $person['surname'], 'Student');
            }
            $chart->setLabels($people);
            
            $chart->addDataset('data')
                ->setData($chartData);
            
            echo $chart->render();
            
                
        } else if ($title == "Record Of Work Created (for Another Person)") {
            issueColumn($table, $container);
            techColumn($table);
        } else if ($title == "QA Assigned") {
            issueColumn($table, $container);
            techColumn($table, $container);
        } else if ($title == "Discussion Posted") {
            $issueDisucssGateway = $container->get(IssueDiscussGateway::class);
            $table->addColumn('recordsOfWorkDiscussID', __('Record Of Work Discss'))
                ->format(function ($log) use ($issueDisucssGateway) {
                    $array = unserialize($log['serialisedArray']);

                    $issueDiscuss = $issueDisucssGateway->getByID($array['recordsOfWorkDiscussID']);
                    if (!empty($issueDiscuss)) {
                        return Format::link('./index.php?q=/modules/Records Of Work/workRecord_discussView.php&workrecordID=' . $issueDiscuss['workrecordID'] . '&recordsOfWorkDiscussID=' . $issueDiscuss['recordsOfWorkDiscussID'], __('Discussion Post'));
                    }

                    return __('Could not find discussion.');
                });
        } else if ($title == "QA Group Added" || $title == "QA Group Edited") {
            groupColumn($table, $container);
        } else if ($title == "QA Added") {
            techColumn($table, $container);
        } else if ($title == "QA Group Set") {
            groupColumn($table, $container);
            techColumn($table, $container);
        } else if ($title == "QA Removed") {
            $userGateway = $container->get(UserGateway::class);
            $table->addColumn('person', __('Person'))
                ->format(function ($log) use ($userGateway) {
                    $array = unserialize($log['serialisedArray']);

                    $person = $userGateway->getByID($array['gibbonPersonID']);
                    if (!empty($person)) {
                        return Format::name($person['title'], $person['preferredName'], $person['surname'], 'Student');
                    }

                    return __('Could not find person');
                });
        } else if ($title == "QA Group Removed") {
            groupColumn($table, $container, true);
        }
       
        echo $table->render($logs);
    }
}
?>
