class {$VO_NAME} extends SzAbstract{$DB_TYPE}Vo
{

{if $COLUMNS && is_array($COLUMNS)}
{foreach $COLUMNS as $COLUMN_NAME}
    private ${$COLUMN_NAME};
{/foreach}
{/if}

    /**
     * Initialize {$VO_NAME}.
     *
{if $COLUMNS && is_array($COLUMNS)}
{foreach $COLUMNS as $COLUMN_NAME}
     * @param ${$COLUMN_NAME}
{/foreach}
{/if}
     * @param boolean $isInsert default false, means for now this vo is initialized not for insert process
     * @return {$VO_NAME}
     */
    public function __construct({if $COLUMNS && is_array($COLUMNS)}{foreach $COLUMNS as $COLUMN_NAME}
${$COLUMN_NAME}, {/foreach}{/if}$isInsert = false) {
{if $COLUMNS && is_array($COLUMNS)}
{foreach $COLUMNS as $COLUMN_ID => $COLUMN_NAME}
{if in_array($COLUMN_ID, $JSON_COLUMNS)}
        $this->{$COLUMN_NAME} = json_decode(${$COLUMN_NAME}, true);
{else}
        $this->{$COLUMN_NAME} = ${$COLUMN_NAME};
{/if}
{/foreach}
{/if}

        $this->isInsert = $isInsert;
        $this->voClassName = '{$VO_NAME}';
        $this->ormName = '{$ORM_NAME}';
    }

    /**
     * @see SzAbstractVo::toArray
     */
    public function toArray($needEncode = false)
    {
        return array(
{if $TOARR_COLUMNS && is_array($TOARR_COLUMNS)}
{foreach $TOARR_COLUMNS as $COLUMN_ID => $COLUMN_NAME}
{if in_array($COLUMN_ID, $JSON_COLUMNS)}
            '{$COLUMN_NAME}' => ($needEncode) ? $this->get{$COLUMN_NAME|ucfirst}() : $this->getRaw{$COLUMN_NAME|ucfirst}(),
{else}
            '{$COLUMN_NAME}' => $this->get{$COLUMN_NAME|ucfirst}(),
{/if}
{/foreach}
{/if}
        );
    }

    /**
     * @see SzAbstractVo::toEntireArray
     */
    public function toEntireArray($needEncode = false)
    {
        return array(
{if $COLUMNS && is_array($COLUMNS)}
{foreach $COLUMNS as $COLUMN_ID => $COLUMN_NAME}
{if in_array($COLUMN_ID, $JSON_COLUMNS)}
            '{$COLUMN_NAME}' => ($needEncode) ? $this->get{$COLUMN_NAME|ucfirst}() : $this->getRaw{$COLUMN_NAME|ucfirst}(),
{else}
            '{$COLUMN_NAME}' => $this->get{$COLUMN_NAME|ucfirst}(),
{/if}
{/foreach}
{/if}
        );
    }

    /**
     * @see SzAbstractVo::toPureArray
     */
    public function toPureArray()
    {
        return array(
{if $COLUMNS && is_array($COLUMNS)}
{foreach $COLUMNS as $COLUMN_NAME}
            $this->get{$COLUMN_NAME|ucfirst}(),
{/foreach}
{/if}
        );
    }

{if $COLUMNS && is_array($COLUMNS)}
{foreach $COLUMNS as $COLUMN_ID => $COLUMN_NAME}
    public function get{$COLUMN_NAME|ucfirst}()
    {
{if in_array($COLUMN_ID, $JSON_COLUMNS)}
        return json_encode($this->{$COLUMN_NAME});
{else}
        return $this->{$COLUMN_NAME};
{/if}
    }
{if in_array($COLUMN_ID, $JSON_COLUMNS)}

    public function getRaw{$COLUMN_NAME|ucfirst}()
    {
        return $this->{$COLUMN_NAME};
    }
{/if}

    public function set{$COLUMN_NAME|ucfirst}($val)
    {
{if in_array($COLUMN_ID, $JSON_COLUMNS)}
        $encodedLength = SzUtility::strLen(json_encode($val));
        if ($encodedLength > {$JSON_COLUMNS_LENGTH_LIMIT[$COLUMN_ID]}) {
            SzLogger::get()->error('[{$VO_NAME}] The length of column {$COLUMN_NAME} exceed the limit, length / limit: ' . $encodedLength . ' / {$JSON_COLUMNS_LENGTH_LIMIT[$COLUMN_ID]}');
            throw new SzException(10522, array('{$VO_NAME}', '{$JSON_COLUMNS_LENGTH_LIMIT[$COLUMN_ID]}', $encodedLength));
        } else if ($encodedLength > {$JSON_COLUMNS_LENGTH_WARN[$COLUMN_ID]}) {
            SzLogger::get()->warn('[{$VO_NAME}] The length of column {$COLUMN_NAME} exceed the warning level, length / limit: ' . $encodedLength . ' / {$JSON_COLUMNS_LENGTH_LIMIT[$COLUMN_ID]}');
        }
{/if}
        $this->saveColumnStatus({$COLUMN_NAME@key}, $this->{$COLUMN_NAME});
        $this->{$COLUMN_NAME} = $val;
    }

{/foreach}
{/if}
}