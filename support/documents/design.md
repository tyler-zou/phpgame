# Framework Design

---

## 1. System

### 1.1 Autoload
* 建议使用build脚本收集类文件的方式自动生成注册：
	* 简单易用，仅仅多占用一点内存而已，在使用相对路径的情况下，问题不大
* 使用`spl_autoload_register`进行全局的autoload注册

### 1.2 Config
* 统一的入口，静态实例，全局统一访问
* 使用内存进行已读配置的缓存
* 约定的固定配置文件文件夹

### 1.3 Context
* 为什么需要context：
	* 第三方模块或者应用需求数据库连接的时候，到哪里拿？
	* 假设我有一个应用 **Game**，我的DB资源工厂类叫 `GameDbFactory`
	* 公用的逻辑模块是不知道这个 `GameDbFactory` 的
	* 所以需要在框架级别有一个获取资源的管理器
	* `Context`就一个**中心BUS**，所有资源都会流经，并在这里受到管理
* 使用context来进行抽象管理所有的`资源`
* 抽象：
	* `ContextFactory::init()` 在框架初始化的时候根据配置进行不同实现的实例化
	* 然后通过 `ContextFactory::get()` 获取Context实例
	* Context是一个抽象，仅提供 `getDbHandle()` 之类的接口描述，由应用自行实现内容

### 1.4 Exception
* 使用一套统一的错误处理机制
* 定义好所有的错误信息号边界（框架错误1开头、应用错误2开头、etc）
* 框架统一捕获错误信息，并进行处理（日志、报警、etc）

### 1.5 Log
* 统一的入口，静态实例，全局统一访问
* 抽象出统一的使用接口类
* 根据配置实例化具体的日志实现方式
	* file
	* syslog
	* remote log server
	* etc

### 1.6 Utility
* 系列的工具类：
	* 文件操作
	* 时间计算
	* 发送HTTP请求
	* etc
	
### 1.7 Throttle & Multi Versions & Gray Publishment
* 在程序的入口添加throttle功能，进行高流量玩家分流
* 在同一个应用部署中添加多版本的部署，并根据配置进行版本检测功能
* 在程序的入口根据配置，进行灰度发布

---

## 2. MVC

### 2.1 View
* 使用smarty作为模板引擎，尽量不使用smarty的复杂功能，使用其模板作用

### 2.2 Router
* 实现parse函数，解析当前应用环境，并获得请求`REQUEST`细节
* REQUEST包含：
	* controller名
	* action名
	* 请求参数

### 2.3 Dispatcher
* 将Router解析出来的`REQUEST`分发到对应的处理action中

### 2.4 Action
* 所有action实现一个抽象出来的基类，必须实现：
	* `execute`函数，逻辑的处理函数
	* `validate`函数，验证当前action中输入的参数

### 2.5 Model

#### 2.5.1 ORM Concept
* `ORM`机制：开发者只需要编写ORM配置文件，代码将自动生成
* 开发者对存储对象的操作将对象化：
	* `不再涉及`任何SQL语句、REDIS操作、DAO编写，等等传统的概念
	* 举例来说：操作玩家
		* 获取玩家Model（基本同DAO概念）
		* 从Model中获取玩家对象
		* 修改、创建玩家对象内容
		* 存储、创建玩家对象
	* 举例来说：操作玩家背包
		* 获取玩家背包Model
		* 从Model中获取玩家背包对象
		* 获取背包中元素对象
		* 修改、创建对象元素内容
		* 将元素对象放回玩家背包对象
		* 存储玩家背包对象
		
#### 2.5.2 Design

##### 2.5.2.1 开发者层
* `业务逻辑对象`（唯一由开发者直接操作的对象）：
	* 代码根据配置自动生成
	* 业务逻辑对象（用户、背包里的道具、好友，等）
		* 内建了add、update、delete、save等接口
	* 业务逻辑对象列表（背包、好友列表，等）
		* 内建了addElement、getElement、deleteElement等接口
	* `Context`对象中需要实现：获取所有业务逻辑对象，并进行缓存的代理函数

##### 2.5.2.2 中间结构层
* `Model`对象
	* 代码根据配置自动生成
	* 基本同现在框架中的DAO概念
	* 建立在Db对象之上的高级存储操作对象
	* 内部整合了缓存的存取工作
	* `Context`对象中需要实现：获取所有Model对象，并进行缓存的代理函数

##### 2.5.2.3 底层数据层
* `Db`对象
	* 实际的db操作对象
	* 抽象出来Db基本对象（`BaseDb`）：提供基本的Db操作函数接口，由实际的Db操作对象来实现
	* 根据Db基本对象而实现的系列Db操作对象：
		* MySqlDb
		* MsSqlDb
		* OracleDb
		* MongoDb
		* RedisDb
		* etc
* `DbFactory`对象
	* 根据不同的输入条件提供不同的Db操作对象
	* 提供基本的`sharding`策略
* `QueryBuilder`对象
	* 将提供的参数转化为标准的SQL语句
	* 提供escape函数来防止SQL注入攻击

##### 2.5.1.4 底层缓存层
* 默认使用redis作为缓存层：
	* redis的hash结构对于缓存存储、更新来说，粒度非常合适
* `Cache`对象
	* 实际的cache操作对象
	* 抽象出Cache基本对象（`BaseCache`）：提供基本的Cache操作函数接口，由实际Cache操作对象来实现
	* 根据Cache基本对象而实现的系列Cache操作对象：
		* MemCache
		* RedisCache
		* etc
* `CacheFactory`对象
	* 根据不同的输入条件提供不同的Cache操作对象
	* 提供基本的`sharding`策略

---

## 3. Build

### 3.1 技术实现
* 全部使用PHP来实现，不借助第三方语言或是bash脚本，方便PHP程序员自己维护

### 3.2 脚本列表

#### 3.2.1 创建应用
* 该脚本很少使用，在项目开始的时候会执行一次
* 从无到有创建一个新的应用
* 为开发者创建出最基本的配置文件
* 为开发者创建出最基本的需要重载的类

#### 3.2.2 部署应用
* 该脚本很少使用，在使用的框架、模块版本更新的时候需要用到
* 拷贝框架代码
* 拷贝应用代码
* 拷贝模块代码
* 执行应用刷新脚本

#### 3.2.3 刷新应用
* 在orm配置更新，及新的类编写后，需要执行
* 确定当前ROOT文件夹，并写入配置文件
* 检查系统中几个必须有写权限的文件夹的权限
* 检查orm配置文件，生成：
	* Model类
	* 业务逻辑对象
	* 业务逻辑对象列表
* 收集autoload类信息，生成自动类加载配置文件

#### 3.2.4 测试应用
* 运行代码指定文件夹中的单元测试脚本，并生成报告

---

## 4. Test
* 框架集成PHPUnit，不依赖于系统安装的PHPUnit
* 框架拥有一套自动化测试脚本，查找指定位置的测试用例代码，并进行自动化测试

---

## 5. Web

### 5.1 Web UI
* 集成bootstrap作为web的界面工具

### 5.2 JavaScripts
* 集成javascript模块化加载工具，RequireJs或SeaJs
* 定义一份前端js模块化开发的规范