# 兼职刷单项目接口文档

## 前言

### 请求头
所有接口，未作特殊说明时，`ContentType`请求头均为`application/json`, 同时参数内容以`JSON`格式进行传递


传递Token时，请将Token放入`Authorization`请求头，并在Token前加上Token格式`Bearer`

例如：`Authorization:Bearer xxx`

注意格式与Token之间的空格不能省略


所有的请求都应该是Ajax请求，因此如果遇到请求返回不正常的情况，可以尝试设置请求头`X-Requested-With`为`XMLHttpRequest` 

固定的返回格式为
```json
{
    "status": "success",
    "code": 200,
    "message": "",
    "data": {},
    "error": {}
}
```
***文档仅描述data中的部分***

## 通用公共接口

### 工具

#### 获取验证码图片
URL: /api/tools/captcha   
Method: GET   
是否需要Token: 否

参数:  
* 无

返回：
* sensitive: 是否严格模式(区分大小写)，布尔
* key: 验证码key，用于验证时识别，字符串
* img: 验证码图片，base64，字符串

#### 上传图片到本地
URL: /api/tools/upload/local   
Method: POST   
是否需要Token: 是  
Content-Type: multipart/form-data   

参数:  
* file: 需要上传的图片

返回：
* url：地址，字符串

#### 查询汇率
URL: /api/tools/rate   
Method: GET   
是否需要Token: 否      

参数:  
* scur: 来源货币代码，字符串，默认USD
* tcur：目标货币代码，字符串，默认ZAR

返回：
* 与三方接口返回一致

#### 发送短信
URL: /api/tools/sms/send   
Method: POST   
是否需要Token: 否  

参数:  
* mobile: 手机号，字符串   
* captcha: 用户输入的验证码，字符串  
* key: 验证码的Key，用于识别验证码，字符串  

返回：
* 无

## 业务接口

### 认证

#### 手机号登录
URL: /api/auth/login   
Method: POST   
是否需要Token: 否

参数:  
* mobile: 手机号，字符串
* password: 密码，字符串

返回：
* access_token: Token, 字符串
* token_type: Token类型，字符串
* expires_in: 过期时间，秒，整型


#### 手机号注册
URL: /api/auth/register   
Method: POST   
是否需要Token: 否

参数:  
* mobile: 手机号，字符串
* password: 密码，字符串
* captcha: 用户输入的验证码，字符串
* key: 验证码的Key，用于识别验证码，字符串
* invite_code: 上级CODE，字符串
* sms_code: 短信验证码，字符串

返回：
* access_token: Token, 字符串
* token_type: Token类型，字符串
* expires_in: 过期时间，秒，整型

#### 邮箱登录
URL: /api/auth/email/login   
Method: POST   
是否需要Token: 否

参数:  
* email: 邮箱，字符串
* password: 密码，字符串

返回：
* access_token: Token, 字符串
* token_type: Token类型，字符串
* expires_in: 过期时间，秒，整型


#### 邮箱注册
URL: /api/auth/email/register   
Method: POST   
是否需要Token: 否

参数:  
* email: 邮箱，字符串
* password: 密码，字符串
* invite_code: 上级CODE，字符串
* verify_code: 邮箱验证码，字符串

返回：
* access_token: Token, 字符串
* token_type: Token类型，字符串
* expires_in: 过期时间，秒，整型

#### 发送邮箱验证码
URL: /api/auth/email/code/send     
Method: POST   
是否需要Token: 否

参数:  
* email: 邮箱，字符串
* captcha: 用户输入的验证码，字符串
* key: 验证码的Key，用于识别验证码，字符串

返回：
* 无

#### 登出
URL: /api/auth/logout   
Method: POST   
是否需要Token: 是

参数:  
* 无

返回：
* ok：登出是否成功，布尔

### 轮播图

#### 获取所有轮播图
URL: /api/banner/list   
Method: GET   
是否需要Token: 否

参数:  
* 无

返回：
* name: 名称，字符串
* image: 图片地址，字符串

### 弹出公告

#### 获取最新弹出公告
URL: /api/notice/newest   
Method: GET   
是否需要Token: 否

参数:  
* 无

返回：
* name: 名称，字符串
* content: 内容，字符串

### 商品

#### 商品列表
URL: /api/item/list   
Method: GET   
是否需要Token: 否

参数:  
* min_price: 最低价格范围，字符串
* max_price: 最高价格范围，字符串
* is_group: 是否拼团，不提供时默认为全部，0=非拼团商品，1=拼团商品
* search_name：名称搜索关键字，字符串，根据商品名称进行模糊搜索
* category_id：商品类型ID，字符串

返回：
* id: ID，整型
* name: 名称，字符串
* image: 主图，字符串
* secondary_image: 次图，字符串
* price: 价格，字符串
* gain_per_day: 日收益金额，字符串
* gain_day_num: 可收益天数，整型
* cashback: 返现金额，字符串
* purchase_limit: 购买数量限制，整型
* stock: 库存，整型
* is_group_purchase: 是否拼团，整型，0=否，1=是
* group_people_count: 拼团人数，整型
* logistics_hours: 物流周期(小时)，整型
* group_purchase_end_hours: 拼团结束时间，整型
* is_sell: 是否上架，整型，0=否，1=是
* description: 商品描述，字符串(HTML)
* location: 地区，字符串
* characteristic: 商品特点，字符串(HTML)

#### 商品详情
URL: /api/item/detail   
Method: GET   
是否需要Token: 否

参数:  
* id: 商品ID，整型

返回：
* id: ID，整型
* name: 名称，字符串
* image: 主图，字符串
* secondary_image: 次图，字符串
* price: 价格，字符串
* gain_per_day: 日收益金额，字符串
* gain_day_num: 可收益天数，整型
* cashback: 返现金额，字符串
* purchase_limit: 购买数量限制，整型
* gp_start_time: 拼团开始时间，字符串
* gp_end_time: 拼团开始时间，字符串
* joined_count: （字段已过期）已参与拼团人数，整型
* description: 商品描述，字符串(HTML)
* location: 地区，字符串
* characteristic: 商品特点，字符串(HTML)

#### 商品类型列表
URL: /api/item/category/list   
Method: GET   
是否需要Token: 否

参数:  
* 无

返回：
* id: ID，整型
* name: 名称，字符串

#### 商品价格变动记录
URL: /api/item/price/log   
Method: GET   
是否需要Token: 否

参数:  
* id: 商品ID，整型

返回：
* id: ID，整型
* before_price: 变动前价格，字符串
* after_price: 变动后价格，字符串
* amount: 变动幅度，字符串
* created_at: 变动时间，字符串

#### 商品购买
URL: /api/item/buy   
Method: POST   
是否需要Token: 是

参数:  
* id: 商品ID，整型
* amount: 希望购买数量，整型
* trade_password: 交易密码，可选
* user_coupon_id: 我的优惠券ID，整型，可选

返回：
* buy_amount: 实际购买数量，整型

#### 商品售卖
URL: /api/item/sell   
Method: POST   
是否需要Token: 是

参数:  
* id: 商品ID，整型
* amount: 希望售卖数量，整型
* trade_password: 交易密码，可选

返回：
* sell_amount: 实际售卖数量，整型
* sell_price: 售卖价格，字符串

#### 领取商品收益
URL: /api/item/earning/gain   
Method: POST   
是否需要Token: 是

参数:  
* user_item_id: 用户商品ID，整型

返回：
* 无

#### 随机获取评价模板
URL: /api/item/review/tmpl/random   
Method: GET   
是否需要Token: 否

参数:  
* item_id: 商品ID，整型

返回：
* id: 模板ID，整型
* item_id: 商品ID，整型
* content: 评价内容，字符串
* image: 图片，字符串

#### 获取商品评价列表
URL: /api/item/review/list   
Method: GET   
是否需要Token: 否

参数:  
* item_id: 商品ID，整型

返回：
* tmpl: 评价模板内容

#### 评价商品
URL: /api/item/review/new   
Method: POST   
是否需要Token: 是

参数:  
* user_item_id: 我的商品ID，整型
* tmpl_id: 评价模板ID，整型
* image: 图片地址，字符串，可多张，以半角逗号进行分割
* content: 完成情况文本，字符串

返回：
* 无

### 签到

#### 签到记录
URL: /api/signin/log     
Method: GET   
是否需要Token: 是

参数:  
* year: 年, 整型
* month: 月, 整型

返回：
* id: ID，整型
* user_id: 用户ID，整型
* reward: 获得X金，字符串
* signed_at: 签到时间，字符串
* duration_day: 连续签到时长，整型

#### 用户签到
URL: /api/signin/signin     
Method: POST   
是否需要Token: 是

参数:  
* 无

返回：
* id: ID，整型
* user_id: 用户ID，整型
* reward: 获得X金，字符串
* signed_at: 签到时间，字符串
* duration_day: 连续签到时长，整型

### 返现

#### 返现首页数据
URL: /api/award/list   
Method: GET   
是否需要Token: 是

参数:  
* 无

返回：
* total_invite: 总邀请人数，整型
* received_invite: 已领取邀请返现人数，整型
* unreceive_invite: 未领取邀请返现人数，整型
* invite_limit: 总计能邀请多少用户获得邀请金额，整型
* invite_reward: 邀请用户获得金额，整型
* rewards: 商品返现列表，数组
    * id: ID，整型
    * name: 名称，字符串
    * image: 主图，字符串
    * secondary_image: 次图，字符串
    * price: 价格，字符串
    * gain_per_day: 日收益金额，字符串
    * gain_day_num: 可收益天数，整型
    * cashback: 返现金额，字符串
    * purchase_limit: 购买数量限制，整型
    * unreceive: 未领取返现数量，整型
    * received: 已领取返现数量，整型

#### 领取邀请返现
URL: /api/award/receive/invite  
Method: POST   
是否需要Token: 是

参数:  
* 无

返回：
* total_invite: 总邀请人数，整型
* received_invite: 已领取邀请返现人数，整型
* unreceive_invite: 未领取邀请返现人数，整型

#### 领取商品返现
URL: /api/award/receive/item  
Method: POST   
是否需要Token: 是

参数:  
* item_id: 商品ID，整型

返回：
* item_id: 商品ID，整型
* unreceive: 未领取返现数量，整型
* received: 已领取返现数量，整型

#### 查询充值返现金额
URL: /api/award/stat/recharge    
Method: GET   
是否需要Token: 是

参数:  
* 无

返回：
* back_amount: 待领取数额，字符串

#### 领取充值返现
URL: /api/award/receive/recharge    
Method: POST   
是否需要Token: 是

参数:  
* 无

返回：
* receive_amount: 本次领取数额，字符串

#### 查询充值返现记录
URL: /api/award/list/recharge    
Method: GET   
是否需要Token: 是

参数:  
* 无

返回：
* id: 记录ID，整型
* pay_amount: 充值金额，字符串
* back_amount: 返现金额，字符串
* status：是否领取，整型，0=未领取，1=已领取
* pay_user: 充值用户信息，对象

### 团队

#### 团队统计接口
URL: /api/user/team/state     
Method: GET   
是否需要Token: 是

参数:  
* 无

返回：
* lv1_count: 一级用户数量，整型
* lv1_earning: 一级收益，字符串
* lv1_recharge: 一级充值，字符串
* lv2_count: 二级用户数量，整型
* lv2_earning: 二级收益，字符串
* lv2_recharge: 二级充值，字符串
* lv3_count: 三级用户数量，整型
* lv3_earning: 三级收益，字符串
* lv3_recharge: 三级充值，字符串
* lv1_mb_cost: 一级用户日任务金消费，整型
* lv2_mb_cost: 二级用户日任务金消费，整型
* lv3_mb_cost: 三级用户日任务金消费，整型

#### 团队下级用户列表
URL: /api/user/team/inferior     
Method: GET   
是否需要Token: 是

参数:  
* lv: 等级, 字符串

返回：
* name: 名称，字符串
* avatar: 头像，字符串
* mobile: 手机号，字符串
* recharge_money: 充值金额，字符串

#### 团队收益记录
URL: /api/user/team/commission     
Method: GET   
是否需要Token: 是

参数:  
* 无

#### 团队流水奖励设置
URL: /api/team/statementbonus/setting    
Method: GET   
是否需要Token：是

参数：   
* 无

返回：
* bonus4：四级奖励
    * limit：限额，字符串
    * reward：奖励比例，字符串
* bonus3：三级奖励
    * limit：限额，字符串
    * reward：奖励比例，字符串
* bonus2：二级奖励
    * limit：限额，字符串
    * reward：奖励比例，字符串
* bonus1：一级奖励
    * limit：限额，字符串
    * reward：奖励比例，字符串

### 收入页面

#### 收入统计
URL: /api/user/my/income/state     
Method: GET   
是否需要Token: 是

参数:  
* 无

返回：
* total_income: 总收益，整型
* settled_income: 已领取收益，整型
* unsettled_income: 未领取收益，整型

#### 收入记录
URL: /api/user/my/income/record     
Method: GET   
是否需要Token: 是

参数:  
* 无

#### 我的商品列表
URL: /api/user/my/device/list     
Method: GET   
是否需要Token: 是

参数:  
* is_gp: 是否仅过滤团购商品，整型，0=否，1=是
* id: 我的商品ID，整型，user_item_id

返回：
* id: 我的商品ID，整型，user_item_id
* user_id: 用户ID，整型
* item_id: 商品ID，整型
* amount: 数量，整型
* total_income: 该商品总收益，字符串
* earning_status: 收益领取状态，整型，0=未激活，1=待领取，2=已领取，3=已结束

### 用户信息

#### 修改密码
URL: /api/user/password/change     
Method: POST    
是否需要Token: 是

参数:  
* old_password: 旧密码，字符串
* new_password: 新密码，字符串
* confirm_password: 确认密码，字符串

返回：
* 无

#### 修改交易密码
URL: /api/user/password/trade/change     
Method: POST    
是否需要Token: 是

参数:  
* old_password: 旧交易密码，字符串，可选，如果不传此字段时表示第一次设置密码
* new_password: 新交易密码，字符串
* confirm_password: 确认新交易密码，字符串

返回：
* 无

#### 我的个人信息
URL: /api/user/my/info      
Method: GET    
是否需要Token: 是

参数:  
* 无

返回：
* id: 用户ID，整型
* name: 名称，字符串 
* avatar: 头像，字符串 
* mobile: 手机号，字符串 
* code: 我的CODE，字符串 
* parent_code: 上级CODE，字符串 
* balance: 余额，字符串 
* freeze_balance: 刷单金，字符串 
* available_balance: 可提现余额，字符串   
* is_salesman: 是否业务员，整型，0=否，1=是
* is_has_trade_password: 是否设置交易密码，布尔
* is_remember_trade_password: 是否记住了交易密码，布尔

#### 修改我的个人信息
URL: /api/user/my/info      
Method: POST    
是否需要Token: 是

参数:  
* name: 名称，字符串 
* avatar: 头像，字符串 

返回：
* 无

#### 获取指定用户个人信息
URL: /api/user/info      
Method: GET    
是否需要Token: 是

参数:  
* user_id: 用户ID，整型

返回：
* id: 用户ID，整型
* name: 名称，字符串 
* avatar: 头像，字符串 
* mobile: 手机号，字符串 
* code: 我的CODE，字符串 
* parent_code: 上级CODE，字符串 
* balance: 余额，字符串 
* freeze_balance: 刷单金，字符串 
* available_balance: 可提现余额，字符串   
* is_salesman: 是否业务员，整型，0=否，1=是

#### 我的流水
URL: /api/user/my/moneylog      
Method: GET    
是否需要Token: 是

参数:  
* 无 

#### 我的提现记录
URL: /api/user/my/withdrawallog      
Method: GET    
是否需要Token: 是

参数:  
* 无 

返回：
* status: 状态，0=未审核，1=审核通过，2=审核拒绝
* amount: 提现金额，整型
* bankcard: 银行卡信息

#### 我的充值记录
URL: /api/user/my/rechargelog      
Method: GET    
是否需要Token: 是

参数:  
* 无 

返回：
* order_no: 订单号，字符串
* status: 状态，0=未支付，1=已支付
* price: 充值金额，字符串
* pay_type: 支付方式，字符串，bank=银行，usdt=USDT
* pay_time: 支付时间，字符串

#### 我的个人统计
URL: /api/user/my/state/personal      
Method: GET    
是否需要Token: 是

参数:  
* 无 

返回：
* income: 总收益，字符串
* today_income: 今日收益，字符串
* product: 总产品数，整型

#### 我的详细统计
URL: /api/user/my/state/detail          
Method: GET    
是否需要Token: 是

参数:  
* 无 

返回：
* product_income: 产品收益，字符串
* total_recharge: 充值统计，字符串
* team_income: 团队佣金，字符串
* today_recharge: 今日充值，字符串
* mission_balance: 任务金余额，字符串

#### 我的提现统计
URL: /api/user/my/state/withdrawal          
Method: GET    
是否需要Token: 是

参数:  
* 无 

返回：
* total_withdrawal: 产品收益，字符串
* today_withdrawal: 充值统计，字符串
* balance: 可提现余额，字符串

#### 用户激活
URL: /api/user/activite          
Method: POST    
是否需要Token: 是

参数:  
* code: 激活码，字符串 

返回：
* 无

#### 查询我是否激活
URL: /api/user/activite/status          
Method: GET    
是否需要Token: 是

参数:  
* 无 

返回：
* is_activite: 是否激活，布尔

### 用户银行卡

#### 新增或修改银行卡信息
URL: /api/user/bankcard/cu   
Method: POST    
是否需要Token: 是

参数:  
* id: 银行卡表ID，整型，如果提供ID，则表示修改，如果未提供，则为新建
* bank_name: 银行名称，字符串
* card_no: 银行卡卡号，字符串
* name: 提现人姓名，字符串
* mobile: 提现人手机号，字符串
* email: 提现人邮箱，字符串
* ifsc_code: IFSC码，字符串，可选
* subbranch：分行支行，字符串
* wallet_chain: 钱包所属链，字符串
* wallet_address: 钱包地址，字符串
* bankcard_no: 银行编码，字符串

返回：
* 无

#### 查询银行卡信息
URL: /api/user/bankcard/list   
Method: GET    
是否需要Token: 是

参数:  
* 无

返回：
* id: 银行卡ID，整型
* bank_name: 银行名称，字符串
* card_no: 银行卡卡号，字符串
* name: 提现人姓名，字符串
* mobile: 提现人手机号，字符串
* email: 提现人邮箱，字符串
* ifsc_code: IFSC码，字符串
* wallet_chain: 钱包所属链，字符串
* wallet_address: 钱包地址，字符串
* bankcard_no: 银行编码，字符串

### 用户实名

#### 新增或修改用户实名信息
URL: /api/user/realname   
Method: POST    
是否需要Token: 是

参数:  
* image1: 图片1地址，字符串
* image2: 图片2地址，字符串
* paper_type: 证件类型，整型，1=身份证，2=护照，3=驾照
* paper_code: 证件号，字符串

返回：
* 无

#### 用户实名信息
URL: /api/user/realname   
Method: GET    
是否需要Token: 是

参数:  
* 无

返回：
* image1: 图片1地址，字符串
* image2: 图片2地址，字符串
* paper_type: 证件类型，整型，1=身份证，2=护照，3=驾照
* paper_code: 证件号，字符串

### 支付

#### 充值
URL: /api/payment/recharge        
Method: POST   
是否需要Token: 是

参数:  
* pay_type: 支付方式, 整型, 1=CSPAY, 2=USDT, 3=YTPAY, 4=GSPAY, 5=WEPAY, 6=DFPAY, 7=SharkPAY, 8=GTPAY, 9=PPAY, 10=MPay, 11=FFPay, 12=XDPay-X1, 13=XDPay-DGM, 14=XDPay-X2, 15=WOWPay, 16=PTMPay
* amount: 金额, 整型, 最小10
* image: 交易截图图片地址, 字符串, 当pay_type=2时必填

返回(pay_type=1)：
* pay_url: 支付地址, 字符串

返回(pay_type=2)：
* 无

返回(pay_type=3)：
* pay_url: 支付地址, 字符串

返回(pay_type=4)：
* (支付渠道响应中的payParams参数)
* payMethod: formJump
* payUrl: 自动提交的form表单内容

返回(pay_type=5)：
* pay_url: 支付地址, 字符串

返回(pay_type=6)：
* pay_url: 支付地址, 字符串

返回(pay_type=7)：
* pay_url: 支付地址, 字符串

返回(pay_type=8)：
* pay_url: 支付地址, 字符串

返回(pay_type=9)：
* pay_url: 支付地址, 字符串

返回(pay_type=10)：
* pay_url: 支付地址, 字符串

返回(pay_type=11)：
* pay_url: 支付地址, 字符串

返回(pay_type=12)：
* pay_url: 支付地址, 字符串

返回(pay_type=13)：
* pay_url: 支付地址, 字符串

返回(pay_type=14)：
* pay_url: 支付地址, 字符串

返回(pay_type=15)：
* pay_url: 支付地址, 字符串

#### 获取USDT支付地址
URL: /api/payment/usdt/channel
Method: GET   
是否需要Token: 否

参数:  
* 无

返回：
* channel: 渠道名称, 字符串
* address: 地址, 字符串

#### 申请提现
URL: /api/payment/withdrawal    
Method: POST   
是否需要Token: 是

参数:  
* pay_type: 支付方式, 整型
    * 7=SharkPAY
    * 11=FFPay   
    * 以下方式不可用
    * 1=CSPAY(银行卡)
    * 2=USDT
    * 3=YTPAY 
    * 4=GSPAY 
    * 5=WEPAY
    * 6=DFPAY
    * 8=GTPAY
    * 9=PPAY(银行卡)
    * 10=MPay 
    * 12=XDPay-X1 
    * 13=XDPay-DGM
    * 14=XDPay-X2
    * 15=WOWPay
    * 16=PTMPay
* amount: 金额, 整型
* bankcard_id: 银行卡ID
* trade_password: 交易密码，可选

返回：
* 无

#### 余额转换为任务金
URL: /api/payment/convert    
Method: POST   
是否需要Token: 是

参数:  
* amount: 金额, 整型

返回：
* 无

#### 最新的提现记录
URL: /api/payment/withdrawal/newest    
Method: GET   
是否需要Token: 否

参数:  
* 无

### 设置

#### 获取某个设置
URL: /api/setting/info   
Method: GET    
是否需要Token: 否    

参数:
* key: 设置的key, 字符串

返回
* id: 设置ID, 整型
* key: 设置Key, 字符串
* value: 设置值, 字符串
* comment: 备注, 字符串

### 滚动广播

#### 获取列表数据
URL: /api/scroll/list    
Method: GET    
是否需要Token: 否    

参数:
* 无

返回
* money: 变动金额，字符串
* user: 用户信息
    * mobile: 手机号，字符串

### 客服

#### 获取所有客服信息
URL: /api/cs/list   
Method: GET   
是否需要Token: 否

参数:  
* salesman_code: 业务员码，字符串
* service_type: 服务类型，字符串

返回：
* icon: 图标，字符串
* address: 地址，字符串
* account: 账号，字符串
* service_type: 服务类型，字符串

### 文本

#### 获取所有文本内容
URL: /api/text/list   
Method: GET   
是否需要Token: 否

参数:  
* type: 类型，字符串

返回：
* types: 类型，字符串
* content: 内容，字符串
* sort: 排序，整型

### 红包

#### 开启红包
URL: /api/redpack/open   
Method: POST   
是否需要Token: 是

参数:  
* 无

返回：
* amount: 红包奖励金额数量，字符串

#### 邀请红包记录
URL: /api/redpack/log/invite   
Method: GET   
是否需要Token: 是

参数:  
* 无

#### 消费红包记录
URL: /api/redpack/log/freeze   
Method: GET   
是否需要Token: 是

参数:  
* 无

#### 当前红包总金额
URL: /api/redpack/total/amount   
Method: GET   
是否需要Token: 是

参数:  
* 无

返回：
* amount: 当前红包总金额，字符串

#### 将当前红包金转换为任务金
URL: /api/redpack/receive   
Method: POST     
是否需要Token: 是

参数:  
* 无

返回：
* redpacket_balance: 当前红包金数量，字符串
* mission_balance: 当前任务金数量，字符串


### 聊天室

#### 我的聊天室列表
URL: /api/chat/myroom   
Method: GET   
是否需要Token: 是

参数:  
* 无

返回：
* id: 聊天室ID，整型
* name: 名称，字符串
* record: 最新消息内容，对象，可选
    * user: 发言人信息，对象
    * content：消息内容，可选
    * type：消息类型，0=文字，1=图片，2=红包
    * created_at：消息发送时间，字符串

#### 聊天室用户列表
URL: /api/chat/room/user   
Method: GET   
是否需要Token: 是

参数:  
* room_id: 聊天室ID，字符串
* username: 搜索用户名关键字，字符串

返回：
* user: 用户信息
    * id：用户ID，整型
    * avatar：头像，字符串
    * name：名称，字符串
* is_mute: 是否被禁言，整型

#### 禁言用户(再次调用解除)
URL: /api/chat/mute   
Method: POST   
是否需要Token: 是

参数:  
* room_id: 聊天室ID，字符串
* user_id: 用户ID，字符串

返回：
* 无

#### 聊天历史记录
URL: /api/chat/records   
Method: GET   
是否需要Token: 是

参数:  
* room_id: 聊天室ID，字符串

返回：
* content: 消息内容，对象
* record_type: 消息类型，0=文本，1=图片，2=红包
* redpacket_amount: 红包总金额，字符串
* redpacket_count: 红包数量，数量，整型
* user: 用户信息，对象

#### 发消息
URL: /api/chat/send      
Method: POST   
是否需要Token: 是

参数:  
* room_id: 聊天室ID，字符串
* content: 消息内容，对象，根据需要自定义
* record_type: 消息类型，0=文本，1=图片，2=红包
* redpacket_amount: 红包总金额，字符串，非红包为0
* redpacket_count: 红包数量，数量，整型，非红包为0

返回：
* id: 消息ID，整型

#### 开红包
URL: /api/chat/redpacket/open         
Method: POST   
是否需要Token: 是

参数:  
* record_id: 消息ID，整型

返回：
* amount：红包开出的金额，字符串
* is_taked：是否领到，布尔
* taked_count：已领走数量，整型
* taked_amount: 已领走金额，字符串
* total_count：总数量，整型
* total_amount: 总金额，字符串
* users: 已领取用户排行列表，数组
    * amount：领取金额，字符串
    * created_at： 领取时间，字符串
    * user：领取用户信息，对象
* content：红包消息内容，对象
* speaker：发送红包用户信息，对象

#### 新建聊天室
URL: /api/chat/room/create         
Method: POST   
是否需要Token: 是

参数:  
* name: 名称，字符串
* uplimit: 最多邀请人数，整型
* content: 公告，字符串

返回：  
* id: 聊天室ID，整型
* user_id: 聊天室所属用户ID，整型
* name: 名称，字符串
* uplimit: 最多邀请人数，整型
* content: 公告，字符串

#### 邀请聊天
URL: /api/chat/invite         
Method: POST   
是否需要Token: 是

参数:  
* mobile: 邀请用户手机号，字符串
* room_id: 聊天室ID，整型，可选，未提供时新建

返回：  
* room_id: 聊天室ID，整型

#### 移除聊天
URL: /api/chat/remove         
Method: POST   
是否需要Token: 是

参数:  
* user_id: 目标用户ID，整型
* room_id: 聊天室ID，整型

返回：  
* 无

#### 修改聊天室信息
URL: /api/chat/room/info/change         
Method: POST   
是否需要Token: 是

参数:  
* room_id: 聊天室ID，整型
* avatar: 头像，字符串，不提供不更改
* content: 公告，字符串，不提供不更改

返回：  
* 无

#### 批量移除聊天记录
URL: /api/chat/room/record/remove/batch           
Method: POST   
是否需要Token: 是

参数:  
* room_id: 聊天室ID，整型
* records: 聊天记录ID，数组

返回：  
* 无

#### 聊天室详情
URL: /api/chat/room/info           
Method: GET   
是否需要Token: 是

参数:  
* room_id: 聊天室ID，整型

返回：  
* id: 聊天室ID，整型
* name: 聊天室名称，字符串，
* user_id: 聊天室拥有者用户ID，整型
* uplimit: 聊天室用户数量上限，整型
* content: 公告，整型
* avatar: 头像，整型

### 新闻

#### 获取新闻列表
URL: /api/news/list   
Method: GET   
是否需要Token: 否

参数:  
* page：页数，整型
* item_category_id: 商品分类ID，整型，可选

返回：
* id：新闻ID，整型
* title：标题，字符串
* image：图片地址，字符串
* description: 简介，字符串
* content：详情内容，字符串(HTML)
* item_category_id: 商品分类ID，整型
* created_at: 创建时间，字符串
* updated_at: 更新时间，字符串

### 优惠券

#### 领取优惠券
URL: /api/coupon/receive   
Method: POST   
是否需要Token: 是

参数:  
* 无

返回：
* id：优惠券ID，整型
* discount：折扣，字符串
* expire_time：过期时长，整数，小时
* weight：权重，字符串
* created_at: 创建时间，字符串
* updated_at: 更新时间，字符串

#### 我的优惠券列表
URL: /api/user/my/coupon/list   
Method: GET   
是否需要Token: 是

参数:  
* 无

返回：
* id：我的优惠券ID，整型
* user_id：用户ID，整型
* coupon_id：优惠券ID，整型
* status：状态，0=未使用，1=已使用
* item_id: 使用商品ID，整型
* expire_at: 过期时间，字符串
* coupon：优惠券信息，对象

### 统计

#### 平台统计信息
URL: /api/state/platform   
Method: GET   
是否需要Token: 否

参数:  
* 无

返回：
* today_recharge: 今日充值金额，字符串
* today_withdrawal: 今日提现金额，字符串
* total_recharge: 总充值金额，字符串
* total_withdrawal: 总提现金额，字符串

#### 所有用户成功的提现记录
URL: /api/state/withdrawal/list      
Method: GET    
是否需要Token: 否

参数:  
* 无 

返回：
* status: 状态，0=未审核，1=审核通过，2=审核拒绝
* amount: 提现金额，整型
* user: 用户信息

#### 所有用户成功的充值记录
URL: /api/state/recharge/list      
Method: GET    
是否需要Token: 否

参数:  
* 无 

返回：
* order_no: 订单号，字符串
* status: 状态，0=未支付，1=已支付
* price: 充值金额，字符串
* pay_type: 支付方式，字符串，bank=银行，usdt=USDT
* user: 用户信息

#### 邀请充值排行榜
URL: /api/state/recharge/invite/lb      
Method: GET    
是否需要Token: 否

参数:  
* 无 

返回：
* id: 用户ID，字符串
* name: 用户昵称，字符串
* avatar: 头像图片地址，字符串
* recharge_amount: 充值金额，字符串
* invite_count: 总邀请人数，整型

### Youtube链接

#### 所有审核通过的列表
URL: /api/youtubelink/list   
Method: GET   
是否需要Token: 否

参数:  
* 无

返回：
* id：链接ID，整型
* name：名称，字符串
* link：链接，字符串
* image：封面，字符串
* user: 用户信息，对象

#### 我的提交列表
URL: /api/youtubelink/my/list   
Method: GET   
是否需要Token: 是

参数:  
* 无

返回：
* id：链接ID，整型
* name：名称，字符串
* link：链接，字符串
* image：封面，字符串

#### 用户添加数据
URL: /api/youtubelink/new   
Method: POST   
是否需要Token: 是

参数:  
* name：名称，字符串
* link：链接，字符串
* image：封面，字符串

返回：
* 无

### 大转盘

#### 抽奖
URL: /api/prize/turn   
Method: POST   
是否需要Token: 是

参数:  
* 无

返回：
* id：奖品ID，整型
* name：名称，字符串
* reward_type：奖品类型，整型，1=优惠券，2=产品，3=现金
* coupon：优惠券信息，对象
* item: 产品信息，对象
* cash_amount: 现金数量，字符串

#### 奖品列表
URL: /api/prize/reward/list   
Method: GET   
是否需要Token: 否

参数:  
* 无

返回：
* id：奖品ID，整型
* name：名称，字符串
* reward_type：奖品类型，整型，1=优惠券，2=产品，3=现金
* coupon：优惠券信息，对象
* item: 产品信息，对象
* cash_amount: 现金数量，字符串
* rate: 中奖概率(百分比)，字符串

#### 我抽中记录
URL: /api/prize/my/log   
Method: GET   
是否需要Token: 是

参数:  
* 无

返回：
* id：奖品记录ID，整型
* reward_id：奖品ID，整型
* user_id：用户ID，整型
* name：奖品名称，字符串
* reward_type：奖品类型，整型，1=优惠券，2=产品，3=现金
* coupon：优惠券信息，对象
* item: 产品信息，对象
* cash_amount: 现金数量，字符串

### 红包V2

#### 开红包
URL: /api/redpacketv2/open         
Method: POST   
是否需要Token: 是

参数:  
* id: 红包ID，整型

返回：
* amount：红包开出的金额，字符串
* is_taked：是否领到，布尔
* taked_count：已领走数量，整型
* taked_amount: 已领走金额，字符串
* total_count：总数量，整型
* total_amount: 总金额，字符串
* logs: 已领取用户排行列表，数组
    * amount：领取金额，字符串
    * created_at： 领取时间，字符串
    * user：领取用户信息，对象
* remark: 红包备注，字符串

#### 红包列表
URL: /api/redpacketv2/list         
Method: GET   
是否需要Token: 是

参数:  
* 无

返回：
* remark: 红包备注，字符串
* count：总数量，整型
* amount: 总金额，字符串
* status: 状态，整型，0=已领完，1=未领完

#### 单个红包领取记录
URL: /api/redpacketv2/log         
Method: GET   
是否需要Token: 是

参数:  
* id: 红包ID，整型

返回：
* amount：领取金额，字符串
* created_at： 领取时间，字符串
* user：领取用户信息，对象