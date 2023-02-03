<?php
namespace Gibbon\Module\RecordsOfWork\Domain;

use Gibbon\Domain\Traits\TableAware;
use Gibbon\Domain\QueryCriteria;
use Gibbon\Domain\QueryableGateway;

/**
 * Reply Template Gateway
 */
class ReplyTemplateGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'recordsOfWporkReplyTemplate';
    private static $primaryKey = 'recordsOfWporkReplyTemplateID';
    private static $searchableColumns = [];

    public function queryTemplates($critera) {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'recordsOfWporkReplyTemplateID', 'name', 'body'
            ]);

        return $this->runQuery($query, $critera);
    }

}
