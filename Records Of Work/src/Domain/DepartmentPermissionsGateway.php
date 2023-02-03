<?php
namespace Gibbon\Module\RecordsOfWork\Domain;

use Gibbon\Domain\Traits\TableAware;
use Gibbon\Domain\QueryCriteria;
use Gibbon\Domain\QueryableGateway;

/**
 * Department Permissions Gateway
 *
 * @version v20
 * @since   v20
 */
class DepartmentPermissionsGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'qualityAssuaranceDepartmentPermissions';
    private static $primaryKey = 'departmentPermissionsID';
    private static $searchableColumns = [];
    
    public function queryDeptPerms($criteria) {
        $query = $this
            ->newQuery()
            ->from('qualityAssuaranceDepartmentPermissions')
            ->cols(['departmentPermissionsID', 'qualityAssuaranceDepartmentPermissions.departmentID', 'schoolYearGroup', 'gibbonRoleID'])
            ->leftJoin('qualityAssuaranceDepartments', 'qualityAssuaranceDepartmentPermissions.departmentID=qualityAssuaranceDepartments.departmentID');

        $criteria->addFilterRules([
            'departmentID' => function ($query, $departmentID) {
                return $query
                    ->where('qualityAssuaranceDepartmentPermissions.departmentID = :departmentID')
                    ->bindValue('departmentID', $departmentID);
            },
            'gibbonRoleID' => function($query, $gibbonRoleID) {
                return $query
                    ->where('qualityAssuaranceDepartmentPermissions.gibbonRoleID = :gibbonRoleID')
                    ->bindValue('gibbonRoleID', $gibbonRoleID);
            }
        ]);

        return $this->runQuery($query, $criteria);
    }


}
