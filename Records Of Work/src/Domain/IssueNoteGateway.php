<?php
namespace Gibbon\Module\RecordsOfWork\Domain;

use Gibbon\Domain\Traits\TableAware;
use Gibbon\Domain\QueryCriteria;
use Gibbon\Domain\QueryableGateway;

/**
 * Record Of Work Note Gateway
 *
 * @version v22
 * @since   v22
 */
class IssueNoteGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'recordsOfWorkNotes';
    private static $primaryKey = 'issueNoteID';
    private static $searchableColumns = [];

    public function getIssueNotesByID($workrecordID) {
        $query = $this
            ->newSelect()
            ->cols(['recordsOfWorkNotes.note as comment', 'recordsOfWorkNotes.timestamp', 'recordsOfWorkNotes.gibbonPersonID', 'gibbonPerson.title', 'gibbonPerson.surname', 'gibbonPerson.preferredName', 'gibbonPerson.image_240', 'gibbonPerson.username', 'gibbonPerson.email', '"Commented " AS action'])
            ->from('recordsOfWorkNotes')
            ->innerJoin('gibbonPerson', 'recordsOfWorkNotes.gibbonPersonID=gibbonPerson.gibbonPersonID')
            ->where('recordsOfWorkNotes.workrecordID = :workrecordID')
            ->bindValue('workrecordID', $workrecordID);

        $result = $this->runSelect($query);

        return $result;
    }
}
