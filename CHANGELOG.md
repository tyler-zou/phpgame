Version: 1.0.9.4

Release date: 2016-10-11
* OGZ平台接入相关:
    * 实现 SzPublishPlatformOgz.class.php

Version: 1.0.9.3

Release date: 2016-10-10

* build/lib/Builder.class.php:
    * 构建模块时，处理orm.config.php中的tableShardCount字段值为-1时，优先读取自定义常量
* build/templates/model/model.tpl
    * 加载模块相关的自定义配置及tableShardCount字段修改为优先读取自定义常量
* config/SzConfig.class.php:
    * 修改loadAppConfig功能，增加参数判断当文件不存在，不报错，并返回一个空Array

---

Version: 1.0.9.2

Release date: 2016-09-21

* SzAbstractLogger.class.php
	* 修改日志格式，将原来 SYS LOG Program 通道中的日志的 JSON string 部分还原成 JSON 格式，方便架构部门解析

---

Version: 1.0.9.1

Release date: 2016-07-25

* SzPersister.class.php
	* 修复SzPersister::addUpdateResponse和SzPersister::addDeleteResponse中键值未做SzUtility::checkArrayKey判断, 会产生一条warnning日志的bug

---

Version: 1.0.9.0

Release date: 2016-05-25

* SzAbstractRedisVoList.class.php
	* 修改SzAbstractRedisVoList::persistDeleteList方法, 添加response数据
* SzAbstractMySqlVoList.class.php
	* SzAbstractMySqlVoList::persistDeleteList方法, 添加response数据
* SzAbstractVo.class.php
	* SzAbstractVo::buildResponse, 新增一个参数, 区分是insert/update还是delete
* SzPersister.class.php
	* SzPersister::addResponse, 根据$changeValue的值是否为`null`, 来区分是insert/update类型的还是delete类型的
	* 当changeValue类型为`array`时, 调用SzPersister::addUpdateResponse方法, 添加insert/update类型的response
	* 当changeValue为null时, 调用SzPersister::addDeleteResponse方法, 添加delete类型的response
* SzResponseManager.class.php
	* SzResponseManager::mergePersistUpdateResponse方法名修改成SzResponseManager::mergePersistResponse
	* SzResponseManager::mergePersistResponse会将Szpersister::$responseList中的`UPDATE`和`DELETE`数据, 合并至$this->body中
* DELETE中的数据添加方法:
    * 如果orm是list结构的, 框架会在deleteElement时, 自动将数据放入到`DELETE`中
    * 如果orm是非list结构的, 需要在执行$model->delete($vo)后, 调用代码`SzPersister::get()->addResponse($unionVo->buildResponse($model, false, true));`将数据添加到`DELETE`中

---

Version: 1.0.8.1

Release date: 2016-05-25

* 为了ELK系统能正确的解析日志, 日志系统中程序日志需要做以下处理:
    * 任意英文格式的时间字符串会被转换成unix timestamp
    * `level`, `time`, `channel`, `filter`四个字段会被添加一个前缀`'_'`
* SzAbstractLogger.class.php
    * 增加const属性`KEY_PREFIX`, 作为冲突键名的前缀
    * 新增SzAbstractLogger::formatParams方法, 实现参数替换和unix timestamp转换

---

Version: 1.0.8.0

Release date: 2016-05-09

* vendor
	* 新增paypay支付的SDK。
* 新增文件SzPublishPlatformArmorgame.class.php
* SzPublish.class.php
	* 新增AG平台的支持。

---

Version: 1.0.7.1

Release date: 2016-03-31

* logger.config.php
	* 新增一个`LOG_FILTER`配置，用于区分项目产生日志的环境情况。默认值为："UNLABELED"。
	* `LOG_FILTER`配置格式："项目名_运行环境_*运行环境"，字母均为大写。其中"运行环境"为可选项，供相同项目环境下，区分运行平台使用。e.g.:
	* FV 开发环境:
      * Develop   //FV_DEV
      * QA 环境
        * test    //FV_QA
        * test-sina  //FV_QA_SINA
        * staging      //FV_QA_STAGING
      * 线上环境
        * master     //FV_ONLINE_FB
        * master_sina  //FV_ONLINE_SINA
        * test_vk      //FV_ONLINE_VK

* SzAbstractLogger.class.php
    * 修改了formatMessage函数，增加日志字段`filter`，对应`LOG_FILTER`的配置。

---

Version: 1.0.7.0

Release date: 2016-3-10

* SzResponseManager:
	* 修复SzResponseManager::adaptResponseBody方法中，未对$body参数进行判断的bug
* 新增API重发包功能：
    * 客户端的每个协议中, 都需要在url中携带一个`sign`参数
    * 服务端会将协议的返回结果, 存储到cache中
    * 如果客户端重复发起请求, 服务端会绕过逻辑处理, 直接从cache中读取之前协议的返回数据, 返回给客户端
    * 重发次数以及重发有效期, 由各项目根据自己的项目要求去配置
	* app.config.php中增加了四个配置项:
		* API_REPEAT_CHECK：boolean, 是否开启api重发包功能.如果有些项目不需要该特性或者项目处于开发期,可以将这个常量值置为false来关闭框架对该特性的支持,默认值为false.
		* API_REPEAT_LIMIT：int, 用来限制指定api可重发次数上限.API_REPEAT_CHECK为true时该值才生效
		* API_REPEAT_EXPIRE：int, 用来限制指定api的缓存数据的缓存有效期,单位为秒,API_REPEAT_CHECK为true时该值才生效
		* API_SIGN_SECRET：string, 指定api签名密钥,API_REPEAT_CHECK为true时该值才生效

Version: 1.0.6.18

Release date: 2016-1-29

* SzResponseManager：
	* 增加 SzResponseManager adaptResponseBody方法，用来根据各项目情况适配响应输出内容, 目前主要的目的是将app配置中指定ORM NAME的包体转换成OBJECT.支持MSG及UPDATE包体内容转换.
	* 需要在 app.config.php 中指定 PERSIST_RESULT_FILTER，类型为 array，内部为数组结构（不是K/V的Map结构），所有的值都是 orm.config.php 中的Key，也就是ORM名字，表示这些 ORM 数据将会在 UPDATE 字段中被过滤掉，默认值为false.

	该常量是一个数组, eg: 'PERSIST_RESULT_FORCE_OBJECT' => array('ModuleMissionFinished','UnlockFunction'),

Version: 1.0.6.17

Release date: 2016-01-26

* logger.config.php
	* 新增一个`LOG_RETAIN_FIELD`配置，用于配置business日志，在超过最大上限的时候，需要保留的字段
* SzAbstractLogger.class.php
    * 修改handleLoggerSize函数，分别根据channel字段，执行清除message方法，如果是business字段，则将除了LOG_RETAIN_FIELD内字段外，全部清空，保持结构不变
---

Version: 1.0.6.16

Release date: 2016-01-22

* logger.config.php
	* 新增一个`LOG_MAX_SIZE`配置，用于配置输出日志时，日志最大字节数
* SzAbstractLogger.class.php
    * 新增handleLoggerSize函数，当超过最大字节数的时候，破坏message的结构，给出一个经过截断的message，并输出一条warning报错。
---

Version: 1.0.6.15

Release date: 2016-01-21

* app.config.php
	* 新增一个`LOG_EXCEPTION_TRACE`配置，用于配置是否在set_exception_handler设定的exception错误处理的时候，打印出debug_backtrace的堆栈
* SzErrorHandler：
	* 修改SzErrorHandler::handleException方法，通过获取app.config.php的LOG_EXCEPTION_TRACE参数，判断是否需要打印trace的堆栈

---

Version: 1.0.6.14

Release date: 2016-1-18

* SzResponseManager：
	* 增加 SzResponseManager 公共属性responseCount, 用来描述响应计数.
	
* SzParam:
    * SzParam::parseRawInputs 增加api签名合法校验.
    
* SzController:
    * 调整了 SzController 的 process()逻辑.
        * 由于网络或其他原因导致服务器阻塞严重时,客户端可能会接收不到服务端的响应数据, 所以通过api签名将响应管理器缓存.
        * 当缓存中存在已被指定api签名缓存的响应管理器时, 跳过派发,直接调用响应管理器的send()方法,将缓存的响应数据发送给客户端,
        * 通过在app.config.php中配置API_REPEAT_CHECK的值为true来开启api重发检测功能,如果有些项目不需要该特性或者项目处于开发期,可以将这个常量值置为false来关闭框架对该特性的支持.
        * 如果API_REPEAT_CHECK值为true,框架会读取app.config.php中配置的API_REPEAT_LIMIT来限制指定api签名的响应次数上限.
        * 如果API_REPEAT_CHECK值为true,框架会读取app.config.php中配置的API_REPEAT_EXPIRE来限制指定api签名的缓存数据的缓存有效期.
        * 如果API_REPEAT_CHECK值为true,框架会读取app.config.php中配置的API_SIGN_SECRET来指定api签名密钥.
        
* SzUtility:
    * 增加 SzUtility::makeSign 方法. 
        * 根据参数及api签名密钥创建签名,用来校验外部传进来的api签名是否合法.

Version: FV1.0.7.0

Release date:2015-09-24

* VK平台接入相关:
    * SzPublishPlatformVk.class.php
        * 修改parsePlatformId方法，通过VK平台传入参数获取用户平台id

---

Version: 1.0.6.13

Release date: 2016-01-21

* app.config.php
	* 删除原来的`RESPONSE_JSON_NUMERIC_CHECK`配置
	* 新增一个`RESPONSE_JSON_OPTIONS`配置，用于配置在返回response时，json_encode的参数，默认是0，具体配置参考：[options](http://php.net/manual/en/json.constants.php)
* SzResponseManager：
	* 修改SzResponseManager::formatContent方法，在构建response时，json_encode读取app.config.php中`RESPONSE_JSON_OPTIONS`中配置的值

---

Version: 1.0.6.13

Release date: 2015-12-04

* SzResponseManager：
	* 修正 SzResponseManager::formatContent方法中'gmt'变量的取值有问题，需要加上`app.config.php`设置的时间偏移量，与`game.init`中返回的值保持一致
* SzSystemCache：
	* 新增SzSystemCache::remove()方法，支持删除SzSystemCache::$caches中已缓存的数据
		* 因为SzSystemCache::$caches是一个常住内存的常量，在daemon进程中，会因为这里面存储的数据，无法同步数据源的改变而产生bug，所以需要手动移除并重新获取数据
		* 方法提供一个参数$type，删除指定类型的缓存数据，也可以传入`null`，表示删除所有缓存数据

---

Version: 1.0.6.12

Release date: 2015-11-23

* SzConfig：
	* 修复SzConfig::loadConfig在读取多条数据时（即$key传入的是一个数组）时的bug
	* 修改SzConfig::loadAppConfig方法，使之支持读取按照id来分割的配置文件
		* 配置文件分割规则：
			* 假如配置文件的文件名位module_item_def.config.php
			* 生成一个跟配置文件同名的文件夹，即module_item_def
			* 将module_item_def的每一条记录，都生成一个配置文件，如: module_item_def/1.config.php, module_item_def/2.config.php
		* 配置文件读取规则：
			* 若参数$key传入null，原逻辑保持不变，还是读取整个配置文件（在配置文件输出的时候，分割后的单条配置文件和整个一份的配置文件都会输出到之前描述的文件夹，所以整个一个的配置文件也可以加载得到）
			* 若参数$key传入string|int|array，会根据传入值，找到对应的子配置文件，读取内容

---

Version: 1.0.6.11

Release date: 2015-11-17

* SzTime：
	* 删除 SzTime::getGmtTime方法，取unix时间戳统一使用SzTime::getTime方法
* SzResponseManager：
	* 修正 SzResponseManager::formatContent方法中'gmt'变量的取值bug，变量值应该是unix时间戳

---

Version: 1.0.6.10

Release date: 2015-11-12

* SzMySqlMode：
	* 修正 SzMySqlMode::convertVoToAssocArray，将 toEntireArray 改为 toPureArray，所有和数据库相关的模型转换函数统一为 toPureArray

---

Version: 1.0.6.9

Release date: 2015-11-12

* SzResponse：
	* 修复构造函数，入参$body判断。使用is_null进行非空判断，避免影响空字符串或者数字0的返回值body
* README.md：
	* 添加公会和socket模块的错误编号边界定义

---

Version: 1.0.6.8

Release date: 2015-11-06

* SzAbstractRedisVoList：
	* 修复SzAbstractRedisVoList::persistDeleteList方法的错误：
		* 不再将删除的数据，通过response返回
		* 错误的使用了$model->save方法，修正成$model->delete
* SzRedisModel：
	修复SzRedisModel::save、SzRedisModel::delete中genObjectCacheKey、getDb的bug

---

Version: 1.0.6.7

Release date: 2015-11-04

* SzPersister：
	* 修改SzPersister::persist方法，支持应该调用此方法的结束时，将SzPersister::$responseList清空
	* 主要应用场景：一些有大量写入操作的脚本，程序执行过程中，可以手动调用此方法，将数据写入DB、缓存

---

Version: 1.0.6.6

Release date: 2015-11-02

* SzRedisCache：
	* 修复SzRedisCache::hMSet函数中缓存键值$key被程序逻辑中的同名变量覆盖的bug

---

Version: 1.0.6.5

Release date: 2015-10-28

* SzAbstractLogger:
    * formatMessage方法修改，当channel为business时，直接使用传入message作为formatMessage
* README:
    * app.config.php，增加GAME_ID参数

---

Version: 1.0.6.4

Release date: 2015-10-19

* SzValidator：
	* SzValidator::validateInt、SzValidator::validateFloat两个方法在执行过程中，有一个类型转换，所以必须改成引用传递，才会改变原参数数组的变量值

---

Version: 1.0.6.3

Release date: 2015-09-17

* SzRedisDb：
	* 方法中支持多传入参数的函数，包括：lPush、rPush、sAdd、sDiff、sDiffStore、sInter、sInterStore、sRem、sUnion、sUnionStore，实现方法有问题，改成支持不定参数
    
* SzAbstractAction：
	* SzAbstractAction::validateParam 若接受到的参数为空，则给此参数赋予此类型的默认值，程序继续执行

---

Version: 1.0.6.2

Release date:2015-09-16

* SzAbstractLogger.class.php
    * formatMessage方法增加更改channel的方法

---

Version: 1.0.6.1

Release date:2015-09-07

* SzSystem.class.php
    * 增加getReqTime方法，保存与读取请求时间
    * 增加getSysTime方法，读取带有时间偏移量的系统服务器时间
    * getSysTime的时间偏移量在app.config.php中设置timeOffset字段
* SzUtility.class.php
    * consistentHash不再使用cache保存上一次的计算结果

---

Version: 1.0.6.0

Release date:2015-09-02

* 新浪平台接入相关:
    * SzPublishPlatformSina.class.php
        * 修改parsePlatformId方法，通过新浪平台传入参数获取用户平台id
    * WeiyouxiApi.php
        * 继承WeiyouxiClient.php，并增加重置sessionkey，signature的方法
    * WeiyouxiClient.php
        * 新浪微游戏SDK
---

Version: 1.0.5.4

Release date:2015-08-05

* SzMemcachedCache:
	* SzMemcachedCache::incr 方法中原来直接使用 Memcached::touch 方法调整键值的过期时间，现改为使用 SzMemcachedCache::expire 方法

---

Version: 1.0.5.3

Release date: 2015-07-30

* SzRedisDb:
	* 修正 zRevRange 实现代码错误，真实使用的函数调用出错

---

Version: 1.0.5.2

Release date: 2015-07-27

* SzPublishPlatformFacebook:
	* 修复js跳转，引起GET参数被复写的问题

---

Version: 1.0.5.1

Release date: 2015-07-16

* SzPublishPlatformFacebook:
	* 修复js跳转，由顶层页面跳转改为框体frame内跳转

---

Version: 1.0.5.0

Release date: 2015-07-15

* SzPublishPlatformFacebook:
	* 解析platformId逻辑修改，当无法解析platformId的时候，将会通过JS跳转到应用的注册页面
	* 修改后，注意游戏入口PageIndexAction，需要增加一个对REQUEST参数act=register的Page页显示
* publish.config.php:
	* 增加WEB_HOST参数，web服务器域名

---

Version: 1.0.4.2

Release date: 2015-07-02

* SzTime:
	* 添加函数 SzTime::getGmtTime，获取GMT时区的timestamp
* SzResponseManager:
	* 在 SzResponseManager::formatContent 的默认API返回格式中添加 "gmt" 字段，存放GMT时区的timestamp数据

---

Version: 1.0.4.1

Release date: 2015-06-29

* SzAbstractRedisVo:
	* 修正persist函数里对于vo是否有改动的判断，添加了是否插入操作的判断

---

Version: 1.0.4.0

Release date: 2015-06-19

* SzSystem:
	* 将日志关闭事件注册在init过程的最后，防止在程序还未退出之前日志关闭事件已经执行导致最后一部分日志没有记录
* SzAutoload:
	* 调整了类未找到的日志格式
* SzController:
	* 调整了请求耗时日志，统一记录单个多包中的所有请求时长及整个多包的请求时长
	* 调整了 logExceptionExitReqStatus 里的日志格式
* SzDispatcher:
	* 调整了 dispatch 的时间记录逻辑，现在统一使用 SzController::logActionStartTime 和 SzController::logActionEndTime
* SzErrorHandler:
	* 调整了 Error 和 Exception 的日志输出格式
	* 将 E_ERROR 添加到 SzErrorHandler::$errnos 的名字mapping表内
* SzAbstractLogger:
	* 重命名 CHANNEL_* 系列常量，添加LOG前缀
	* 添加常量 LOG_TAG_SESSION
	* 所有对外使用的日志接口添加第二位参数 $params，类型为数组，默认值为null
	* 删除工具函数 getAdditionalParams
	* 修正 formatMessage 函数，session数据将从第二位的 $params 里提供，数组的KEY为常量 LOG_TAG_SESSION
* SzFileLogger:
	* 根据当前日志系统的变化，调整了doLog的实现
* SzSysLogger:
	* 根据当前日志系统的变化，调整了doLog的实现
* SzDbQueryBuilder:
	* 添加多处查询条件为空的情况下的告警日志
* SzAbstractModel:
	* 调整了 setVoCache 里的日志格式
* SzMySqlModel:
	* 调整了所有记录SQL的日志格式
* SzRedisModel:
	* 修正了对象在序列化和反序列化时候的一个bug，之前的序列化和反序列化并没有最终将对象转换成json字符串，由于之前SzVoSerializer做过一次改版

---

Version: 1.0.3.6

Release date: 2015-06-17

* SzRedisDb:
	* 增加 Sorted Set 的基础操作方法
* SzMemcachedCache:
	* 修改 incr 方法
		* 原来的 incr 方法调用了 Memcached::increment 函数时默认传了初始化值 0，这个参数只有在 Memcached 实例使用二进制协议时有效
		* 使用了 Memcached::touch 来设置存入 key 的过期时间
		* incr 方法中的 Memcached::increment 不传入初始化值和过期时间

---

Version: 1.0.3.5

Release date: 2015-06-15

* SzUtility:
	* getClientHost 修正获取IP的方式，对 $_SERVER['HTTP_X_FORWARDED_FOR'] 进行解析，获取最原始的IP地址

---

Version: 1.0.3.4

Release date: 2015-06-14

* SzResponseManager:
	* formatContent 添加一处遗漏的encode开关

---

Version: 1.0.3.3

Release date: 2015-06-09

* SzResponseManager:
	* formatContent 通过app.config.php的RESPONSE_JSON_NUMERIC_CHECK参数，对json_encode的JSON_NUMERIC_CHECK的format方式进行控制

* SzPublishPlatformFacebook:
	* $facebookLoginUrl URL增加facebook版本号

---

Version: 1.0.3.2

Release date: 2015-06-02

* SzUtility:
	* compress: 增加是否需要trim参数，部分情况不需要trim
	* decompress: 增加是否需要trim参数，部分情况不需要trim
	* base64Encode: 增加是否需要trim参数，部分情况不需要trim
	* base64Decode: 增加是否需要trim参数，部分情况不需要trim

* SzResponseManager:
	* 压缩的response数据，trim后base64会引起无法解压缩的报错

* SzParam:
	* base64的数据decode后，如果经过trim再解压缩会引起无法解压缩的报错

---

Version: 1.0.3.1

Release date: 2015-06-02

* SzSysLogger:
	* 修正 openlog 函数调用，不再将日志同时输出到 PHP 的 stderr，否则这些日志也会被输出到 PHP-FPM 的错误日志里

---

Version: 1.0.3.0

Release date: 2015-06-01

* app.config.php:
	* 添加配置项：BASE64_ENCODE 类型为 boolean，表示是否需要在压缩之后再进行 base64_encode，同时是否需要在解压缩之前 base64_decode 
	* 添加配置项：PERSIST_RESULT_FILTER 类型为 array，内部为数组结构（不是K/V的Map结构），所有的值都是 orm.config.php 中的Key，也就是ORM名字，表示这些 ORM 数据将会在 UPDATE 字段中被过滤掉

* SzUtility:
	* 添加函数 base64Encode 和 base64Decode

* SzParam:
	* parseRawInputs 的时候分离压缩和base64成为两个单独的步骤，读取配置判断是否进行 base64_decode

* SzResponseManager:
	* formatContent 的时候分离压缩和base64成为两个单独的步骤，读取配置判断是否进行 base64_encode
	* formatContent 在进行 json_encode 转换输出JSON字符串的时候，添加配置项：JSON_NUMERIC_CHECK，将输出的数字类型字符串转化为数字

* SzAbstractAction:
	* validateParam:
		* 在验证 int 之后，将传入参数转为整型
		* 在验证 float 之后，将传入参数转为浮点型
		* 在验证 boolean 之后，如果传入值是字符串的话，将其转为布尔型

* SzAbstractMySqlVo:
	* persist 修正 insert 后的 response 结构，进行完整数据输出，保持和 SzAbstractMySqlVoList 一致的行为

---

Version: 1.0.2.4

Release date: 2015-05-27

* SzPublish:
	* 修复节流闸细节bug

* SzRedisLogger:
	* 功能整体删除，不再存在存放log到redis的可能性

* SzAbstractLogger:
	* log 函数，调整日志格式，额外输入参数缩减为2个
	* 详细细节参见函数 formatMessage

* SzSysLogger:
	* 调整doLog函数，适应新的变化

* SzSystem:
	* 添加 handleIdentify 函数，作为系统级别用户唯一标识信息处理函数

---

Version: 1.0.2.3

Release date: 2015-05-19

* SzUtility:
	* 增加获取玩家真实IP地址的方法

---

Version: 1.0.2.2

Release date: 2015-05-07

* SzPublish:
	* DEBUG 修复必须开启preview节流阀，导致部分玩家可以进preview版本的问题

---

Version: 1.0.2.1

Release date: 2015-04-03

* SzPublishPlatformFacebook:
	* 修改接口 SzPublishPlatformFacebook::parsePlatformId, 增加玩家浏览器语言的设置

---

Version: 1.0.2.0

Release date: 2015-04-01

* SzAbstractModel:
	* 添加接口 SzAbstractModel::getTable

* 修正 NOTIFY\_PERSIST\_RESULT 参数在部分逻辑中没有起效的问题

* SzConfig:
	* 添加接口 SzConfig::setLang

---

Version: 1.0.1.0

Released date: 2015-03-13

* SzPublish:
	* 修正 SzPublishPlatformFacebook::parsePlatformId 的重定向链接，在里面添加 $_GET 参数

* SzTime:
	* 添加接口 SzTime::isDst

* SzUtility:
	* 删除 url encoding 逻辑， SzUtility::compress & SzUtility::decompress

* SzAbstractModel:
	* 新增分表功能，ORM配置中每个ORM多一项`tableShardCount`，该值为null表示该ORM在库内不需要分表，反之则需要分成n份
