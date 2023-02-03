<?php
namespace Gibbon\Module\RecordsOfWork\Domain;

use Gibbon\Domain\Traits\TableAware;
use Gibbon\Domain\QueryCriteria;
use Gibbon\Domain\QueryableGateway;

/**
 * QA Gateway
 *
 * @version v20
 * @since   v20
 */
class TechGroupGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'qualityAssuaranceGroups';
    private static $primaryKey = 'groupID';
    private static $searchableColumns = [];

    public function selectTechGroups() {
        $query = $this
            ->newSelect()
            ->cols([
                'groupID','groupName',
                'viewRecords', 'viewRecordsStatus',
                'assignRecords', 'acceptRecords', 'recordsChecked', 'createRecordsForOther', 'fullAccess', 'reassignRecords', 'undoRecordsChecked',
            ])
            ->from('qualityAssuaranceGroups')
            ->orderBy(['groupID']);

        return $this->runSelect($query);
    }

    public function getPermissionValue($gibbonPersonID, $permission)
    {
        $query = $this
            ->newSelect()
            ->distinct()
            ->cols(['viewRecords, viewRecordsStatus, assignRecords, acceptRecords, recordsChecked, createRecordsForOther, fullAccess, reassignRecords, undoRecordsChecked'])
            ->from($this->getTableName())
            ->leftJoin('schoolQA', 'qualityAssuaranceGroups.groupID=schoolQA.groupID')
            ->where('schoolQA.gibbonPersonID = :gibbonPersonID')
            ->bindValue('gibbonPersonID', $gibbonPersonID);

        $result = $this->runSelect($query);

        //If there isn't one unique row, deny all permissions
        if ($result->rowCount() != 1) {
            return false;
        }

        $row = $result->fetch();

        //Check for fullAccess permissions
        if ($row['fullAccess'] == true) {
            if ($permission == 'viewRecordsStatus') {
                return 'All';
            } else {
                return true;
            }
        }

        //Return permission that was asked for
        return $row[$permission];
    }

    public function deleteTechGroup($groupID, $newGroupID) {
        $this->db()->beginTransaction();

        $query = $this
            ->newUpdate()
            ->table('schoolQA')
            ->set('groupID', $newGroupID)
            ->where('groupID = :groupID')
            ->bindValue('groupID', $groupID);

        $this->runUpdate($query);

        if (!$this->db()->getQuerySuccess()) {
            $this->db()->rollback();
            return false;
        }

        $this->delete($groupID);

        if (!$this->db()->getQuerySuccess()) {
            $this->db()->rollback();
            return false;
        }

        $this->db()->commit();
        return true;
    }

}
