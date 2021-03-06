<?php

namespace Command\Model;

use Illuminate\Database\Eloquent\Model;

class IbDeposit extends Model
{
    //定义数据表名称
    protected $table = 'ib_deposit';

    //关闭自动更新时间
    public $timestamps = false;

    //关闭字段白名单
    protected $guarded = [];
}
