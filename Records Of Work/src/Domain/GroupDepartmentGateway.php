<?php
namespace Gibbon\Module\RecordsOfWork\Domain;

use Gibbon\Domain\Traits\TableAware;
use Gibbon\Domain\QueryCriteria;
use Gibbon\Domain\QueryableGateway;

/**
 * Tech Group Department Gateway
 *
 * @version v22
 * @since   v22
 */
class GroupDepartmentGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'qualityAssuaranceGroupDepartment';
    private static $primaryKey = 'groupDepartmentID';
    private static $searchableColumns = [];

    public function selectGroupDepartments($groupID) {
        $select = $this
            ->newSelect()
            ->from('qualityAssuaranceGroupDepartment')
            ->cols(['groupDepartmentID, groupID, qualityAssuaranceGroupDepartment.departmentID, schoolYearGroup'])
            ->leftJoin('qualityAssuaranceDepartments', 'qualityAssuaranceGroupDepartment.departmentID = qualityAssuaranceDepartments.departmentID')
            ->where('groupID = :groupID')
            ->bindValue('groupID', $groupID);

        return $this->runSelect($select);
    }

}
