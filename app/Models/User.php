<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "id",
        'name',
        'avatar',
        'mobile',
        'password',
        'trade_password',
        'parent_code',
        'code',
        'balance',
        'lv1_superior_id',
        'lv2_superior_id',
        'lv3_superior_id',
        "redpacket_balance",
        "mission_balance",
        "salesman_code",
        "email",
        "is_recharged_or_buyed",
        "is_open_v2",
        "task_id",
        "is_openwallet"
    ];

    // protected $appends = ['available_balance'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        // 'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
    
    public function taskIndex()
    {
        return $this->belongsTo(TaskIndex::class, 'task_id');
    }
    public function parent() {
        return $this->belongsTo(self::class, "parent_code", "code");
    }

    public function bankcard() {
        return $this->hasOne(UserBankcard::class);
    }

    public function own_item() {
        return $this->hasMany(UserItem::class);
    }

    // 可提现金额
    protected function availableBalance(): Attribute {
        return Attribute::make(function(){
      
            // 20230831: 
            $amount =  $this->balance;
            // 20230804: 调整可提现金额的算法
            // 我想了下，提现的时候  排除掉充值金额，去统计所有收益 。
            // 那么你给我一个可提现金额的统计接口
            // （一定要做排除效率更高，就是不等于1 ，然后所有money >0 ）。
            // 提现的时候也要查询控制一下。 确定这个方案实现
            //  SELECT SUM(money) FROM `money_log` where money > 0 and log_type <> 1
            
            
            //统计充值金额
            $recharge_amount = MoneyLog::where("user_id", $this->id)
                ->where("log_type", 1)
                ->sum("money");
            //统计购买金额
            $buy_amount = MoneyLog::where("user_id", $this->id)
                ->where("log_type", 6)
                ->sum("money");
            //充值金额减去购买产品的金额，如果小于0 说明充值金额已经消耗完，如果大于0,说明还有剩余未消耗完，未消耗的就不可以提，直接余额减。
            $cjamount = $recharge_amount + $buy_amount;
            if($cjamount>0){
                $amount=$amount-$cjamount;
            } 
            
            if ($amount < 0) {
                return strval(0);
            } else {
                return strval($amount);
            }
        });
        
    }

    // 检查交易密码
    public function checkTradePassword($preflight=false) {
        $trade_password = request("trade_password", "");
        $remember_key = "TRADE_PASSWORD_IS_PASSED:" . $this->id;

        if ($preflight) {
            return Cache::has($remember_key);
        }

        if (Cache::has($remember_key)) {
            return true;
        }

        $isPass = Hash::check($trade_password, $this->trade_password);
        if (!$isPass) {
            throw new \Exception("trade password is error");
        }

        Cache::put($remember_key, 1, 60 * 60 * 2);

        return true;
    }

    // 是否拥有交易密码
    protected function isHasTradePassword(): Attribute {
        return Attribute::make(function(){
            return !empty($this->trade_password);
        });

    }

    public function realname(): HasOne
    {
        return $this->hasOne(UserRealname::class);
    }

    // 是否实名
    protected function isRealname(): Attribute {
        return Attribute::make(function(){
            return !empty($this->realname);
        });
    }

    // 是否记住了交易密码
    protected function isRememberTradePassword(): Attribute {
        return Attribute::make(function(){
            return $this->checkTradePassword(true);
        });

    }

    // 充值金额
    protected function rechargeAmount(): Attribute {
        return Attribute::make(function(){
            return MoneyLog::where("user_id", $this->id)
                ->where("log_type", "1")
                ->sum("money");
        });
        
    }

    // 拥有商品价格
    protected function assetValue(): Attribute {
        return Attribute::make(function(){
            return $this->own_item()
                ->where("earning_end_at", ">", now())
                ->withSum("item", "price")
                ->get()
                ->sum("item_sum_price");
        });
        
    }
}
