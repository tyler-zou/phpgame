<?php
return array(
    // 10000: File loading & Class loading
    10000 => 'Unable to register SzAutoload::autoload as an autoloading method.',
    10001 => 'Class definition not found in autoload config file, className: %s.',
    10002 => 'Class file specified cannot be found, path: %s.',
    // 10100: Config
    10100 => 'Config %s cannot be found in file: %s.config.php.',
    10101 => 'Config file specified cannot be found, path: %s.',
    // 10200: Utility
    10200 => 'Curl error, No: %s, Msg: %s.',
    10201 => 'Socket connection failed, errno: %s, errstr: %s.',
    10202 => 'Get Random Int from range, config is invalid %s.',
    10203 => 'Get Random Int from range, min is bigger than max %s.',
    10204 => 'Error in opening file, path: %s.',
    10205 => 'Error in closing file handler!',
    10206 => 'No opened file handle found for handling!',
    10207 => 'Error in writing data into file!',
    10208 => 'Target file to be downloaded does not exist or not a file or not readable, path: %s.',
    10209 => 'Error in uploading the file, error code: %s.',
    10210 => 'Size of the file uploaded is empty!',
    10211 => 'Type of the file uploaded is invalid, %s wanted, and %s given.',
    10212 => '%s does not exist, or not readable.',
    10213 => '%s is not writable.',
    10214 => 'Error in writing file: %s.',
    10215 => 'Error in object instanceof validation, given: %s, wished: %s.',
    10216 => 'Error in array keys validation, not array or empty array!',
    10217 => 'Error in array keys validation, key lost: %s.',
    10218 => 'Error in number validation, not numeric, input: %s.',
    10219 => 'Error in int validation, input: %s.',
    10220 => 'Error in float validation, input: %s.',
    10221 => 'Error in number or null validation, not numeric, input: %s.',
    10222 => 'Error in boolean validation, input: %s.',
    10223 => 'Error in string validation, input: %s.',
    10224 => 'Error in string or numeric validation, input: %s.',
    10225 => 'Error in string or null validation, input: %s.',
    10226 => 'Error in json validation, input: %s.',
    10227 => 'Error in time string validation, input: %s.',
    10228 => 'Error in array validation, input: %s',
    10229 => 'Error in strict time string validation, input: %s.',
    10230 => 'Error in ip address validation, input: %s.',
    // 10300: Log
    10300 => 'Log type provided invalid, type: %s.',
    // 10400: Controller
    10400 => 'Wrong request format in request registration!',
    10401 => 'Invalid Action class, no "execute" method implemented, name: %s.',
    10402 => 'Invalid Action class, no "paramTypes" definition!',
    10403 => 'Invalid Action class, wrong input param type defined, type: %s.',
    10404 => 'Invalid Action class, wrong input param count!',
    10405 => 'Invalid Api Sign!',
    10406 => 'request count overflow limit!',

    // 10500: Model
    10500 => 'SzMySqlDb: Error in connecting to %s:%s, errno: %s, errmsg: %s.',
    10502 => 'SzMySqlDb: SQL Error: No: %s, Msg: %s, Sql: %s.',
    10503 => 'SzRedisDb: Error in connecting to %s:%s.',
    10504 => 'CommonModel: Column id specified is invalid, model: %s, id: %s.',
    10505 => 'CommonDb: Invalid config given when initializing SzAbstractDb: %s.',
    10506 => 'CommonDbFactory: Invalid database type specified: %s.',
    10507 => 'SzMySqlModel: Shard key is not found in select query.',
    10508 => 'SzMySqlModel: Input object type invalid for update query, wished: %s, given: %s.',
    10509 => 'SzAbstractMySqlVoList: Shard key is empty in batch insert process with auto increment column exists, ormName: %s.',
    10510 => 'SzAbstractRedisVoList: Auto incr ids generated are invalid: %s.',
    10511 => 'CommonVoList: Element %s cannot be found in %s.',
    10512 => 'CommonDbFactory: Invalid factory type specified: %s.',
    10513 => 'SzPersister: Model Class invalid, name: %s.',
    10514 => 'SzPersister: Retrieve list object in non-list model, name :%s.',
    10515 => 'CommonVoList: Random element get from list is not valid vo object, maybe the list structure has been reformatted.',
    10516 => 'CommonModel: Model has no list mode, name: %s.',
    10517 => 'CommonModel: The $this->list of SzAbstractVoList of this model has too many layers.',
    10518 => 'CommonVoList: Invalid element type, shall be the instanceof SzAbstractVo.',
    10519 => 'SzPersister: Vo given shall be the typeof SzAbstractVo in func setVo.',
    10520 => 'SzPersister: VoList given shall be the typeof SzAbstractVoList in func setVoList.',
    10521 => 'SzPersister: Retrieve vo object in list model, name: %s.',
    10522 => 'SzAbstractVo: Json column value length exceed the limit, name: %s, limit: %s, length: %s.',
    10523 => 'SzAbstractModel: Wrong usage of setListOfVoCache, $fullListOfObjects have to be array when cache type is memcached.',
    10524 => 'SzMySqlModel: Invalid table shard key, have to be numeric param: %s.',
    // 10600: SQL Builder
    10600 => 'Insert: Column list does not match the value list.',
    10601 => 'Insert: The type of values inputted for batch insert shall be array.',
    10602 => 'Update: Column list does not match the value list.',
    // 10700: Cache
    10700 => 'Redis: Error in connecting to %s:%s.',
    10701 => 'Common: Invalid cache type: %s.',
    10702 => 'Memcached: Error in connecting to %s.',
    10703 => 'Common: Invalid cache server type: %s.',
    10704 => 'Memcached: Invalid value type to set into memcached, type: %s.',
    // 10800: IP
    10801 => 'IP: Invalid ip address ip: %s.',
);