class {$LIST_NAME} extends SzAbstract{$DB_TYPE}VoList
{

    /**
     * Initialize {$LIST_NAME}.
     *
     * @param array $list array of {$VO_NAME}
     * @return {$LIST_NAME}
     */
    public function __construct($list)
    {
        parent::__construct($list);

        $this->listClassName = '{$LIST_NAME}';
        $this->ormName = '{$ORM_NAME}';
    }

}