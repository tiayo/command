<?php

namespace Command\Model;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    //定义数据表名称
    protected $table = 'account';

    //关闭自动更新时间
    public $timestamps = false;

    //关闭字段白名单
    protected $guarded = [];
}
