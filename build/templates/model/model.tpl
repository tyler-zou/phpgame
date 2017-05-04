class {$MODEL_NAME} extends Sz{$DB_TYPE}Model
{

    /**
     * @see SzAbstractModel::$ORM_NAME
     * @var string
     */
    public static $ORM_NAME = '{$ORM_NAME}';

    /**
     * Initialize the {$MODEL_NAME}.
     *
     * @return {$MODEL_NAME}
     */
    public function __construct()
    {
{if $CONFIG.tableShardCount === -1 and $CONFIG.moduleName}
        $moduleAppConfig = SzConfig::get()->loadAppConfig("{$CONFIG.moduleName}/app", null, false, false);

{/if};
        $this->table = '{$CONFIG.table}';
        $this->columns = array({if $CONFIG.columns}{foreach $CONFIG.columns as $COLUMN_NAME}'{$COLUMN_NAME}', {/foreach}{/if});
        $this->autoIncrColumn = {if !is_null($CONFIG.autoIncrColumn)}{$CONFIG.autoIncrColumn}{else}null{/if};
        $this->diffUpColumns = array({if $CONFIG.diffUpColumns}{foreach $CONFIG.diffUpColumns as $COLUMN_ID}{$COLUMN_ID}, {/foreach}{/if});
        $this->updateFilter = array({if $CONFIG.updateFilter}{foreach $CONFIG.updateFilter as $COLUMN_ID}{$COLUMN_ID}, {/foreach}{/if});
        $this->toArrayFilter = array({if $CONFIG.toArrayFilter}{foreach $CONFIG.toArrayFilter as $COLUMN_ID}{$COLUMN_ID}, {/foreach}{/if});
        $this->searchColumns = array({if $CONFIG.searchColumns}{foreach $CONFIG.searchColumns as $COLUMN_ID}{$COLUMN_ID}, {/foreach}{/if});
        $this->updateColumns = array({if $CONFIG.updateColumns}{foreach $CONFIG.updateColumns as $COLUMN_ID}{$COLUMN_ID}, {/foreach}{/if});
        $this->jsonColumns = array({if $CONFIG.jsonColumns}{foreach $CONFIG.jsonColumns as $COLUMN_ID => $COLUMN_LENGTH}{$COLUMN_ID}, {/foreach}{/if});
        $this->cacheColumn = {if !is_null($CONFIG.cacheColumn)}{$CONFIG.cacheColumn}{else}null{/if};
        $this->shardColumn = {if !is_null($CONFIG.shardColumn)}{$CONFIG.shardColumn}{else}null{/if};
        $this->pkColumn = {if !is_null($CONFIG.pkColumn)}{$CONFIG.pkColumn}{else}null{/if};
        $this->deleteColumns = array({if $CONFIG.deleteColumns}{foreach $CONFIG.deleteColumns as $COLUMN_ID}{$COLUMN_ID}, {/foreach}{/if});
{if $CONFIG.tableShardCount === -1 and $CONFIG.moduleName}
        $this->tableShardCount = {$CONFIG.tableShardCountValue};
{else}
        $this->tableShardCount = {if !is_null($CONFIG.tableShardCount)}{$CONFIG.tableShardCount}{else}null{/if};
{/if}
        $this->cacheTime = {if !is_null($CONFIG.cacheTime)}{$CONFIG.cacheTime}{else}null{/if};

        $this->ormName = '{$ORM_NAME}';
        $this->columnCount = {$COL_COUNT};
        $this->isList = {if $CONFIG.isList}true{else}false{/if};
        $this->dbType = '{$DB_TYPE}';
        $this->voClassName = '{$VO_NAME}';
        $this->voListClassName = '{$LIST_NAME}';

        parent::__construct();
    }

}