<?php

namespace Command\Service;

use Command\Model\Account;
use Command\Model\AccountChildren;
use Command\Model\IbRulesMt4group;
use Command\Model\IbRulesRelation;
use Command\Model\IbRulesSymbolgroupDetail;
use Command\Model\IbSymbolgroupDetail;

class ManyService
{

    protected $color;
    protected $account;
    protected $ib_orders;
    protected $ib_deposit;
    protected $account_children;
    protected $ib;
    protected $ib_rules_relation;
    protected $ib_rules;
    protected $ib_rules_mt4group;
    protected $ib_symbolgroup_detail;
    protected $ib_rules_symbolgroup_detail;
    protected $ib_group_rules;
    protected $ib_level_values;
    protected $public;
    protected $up;

    public function __construct()
    {
        $this->account = app(Account::class);
        $this->account_children = app(AccountChildren::class);
        $this->ib_rules_relation = app(IbRulesRelation::class);
        $this->ib_rules_mt4group = app(IbRulesMt4group::class);
        $this->ib_symbolgroup_detail = app(IbSymbolgroupDetail::class);
        $this->ib_rules_symbolgroup_detail = app(IbRulesSymbolgroupDetail::class);
        $this->public = app(PublicService::class);
        $this->up = app(UpService::class);
    }

    /**
     * 执行策略
     *
     * @param $value //订单信息
     */
    public function work($value)
    {
        $account_children = $this->account_children->select('aid', 'name')->where('login', $value['login'])->first();

        $aid = $account_children->aid;

        $ib_id = $this->public->ib_id($aid);

        $account = $this->account->select('user_type', 'name')->where('id', $aid)->first();

        $cur = $this->cur($ib_id);

        return $this->loop($cur, $value, $account, $ib_id, $aid, $account_children);
    }

    /**
     * 策略主体
     *
     * @param $cur
     * @param $value
     * @param $account
     * @param $ib_id
     * @param $aid
     * @param $account_children
     * @return bool
     */
    public function loop($cur, $value, $account, $ib_id, $aid, $account_children)
    {
        foreach ($cur as $item) {
            //类型
            if ($item['type'] != 0) {
                continue;
            }

            $mt4groupcount = $this->mt4groupcount($value, $item);

            $symbolgroupcount = $this->symbolgroupcount($value, $item);

            if ($mt4groupcount <= 0 || $symbolgroupcount <= 0 || strpos($item['usergroup'], (string)$account->user_type) < 0) {
                continue;
            }

            $money = $this->public->money($item['value'], $value, $item);

            $ib = $this->public->ib($ib_id);

            if ($money <= 0) {
                continue;
            }

            $item['mt4group'] = $this->mt4group($value, $item);

            $item['symbolgroup'] = $this->symbolgroup($value, $item);

            //创建返佣记录
            $this->public->create($value, $ib, $aid, $account_children, $ib_id, $item, $money, $account);

            //创建上级返佣记录
            $this->up->up($value, $ib_id, $item, $money, $aid, $account_children, $account);
        }

        return true;
    }

    /**
     * 获取返佣规则
     *
     * @param $ib_id //上级代理id
     * @return mixed
     */
    public function cur($ib_id)
    {
        return $this->ib_rules_relation
            ->join('ib_rules', 'ib_rules_relation.rules_id', '=', 'ib_rules.id')
            ->where('ib_rules_relation.ib_id', $ib_id)
            ->where('ib_rules.level', '=', 0)
            ->get()
            ->toArray();
    }

    /**
     * mt4分组数量
     *
     * @param $value
     * @param $item
     * @return mixed
     */
    public function mt4groupcount($value, $item)
    {
        return $this->ib_rules_mt4group
            ->where('rule_id', $item['rules_id'])
            ->where('mt4group', $value['mt4group'])
            ->count();
    }

    /**
     * 品种组数量
     *
     * @param $value
     * @param $item
     * @return mixed
     */
    public function symbolgroupcount($value, $item)
    {
        $array = [];

        $in = $this->ib_rules_symbolgroup_detail
            ->select('symbolgroup_id')
            ->where('rule_id', $item['rules_id'])
            ->get();

        foreach ($in as $inn) {
            $array[] = $inn['symbolgroup_id'];
        }

        return $this->ib_symbolgroup_detail
            ->where('symbol', $value['symbol'])
            ->whereIn('symbolgroup_id', $array)
            ->count();
    }

    /**
     * 获取mt4分组
     * 返回用','分割打字符串
     *
     * @param $value
     * @param $item
     * @return string
     */
    public function mt4group($value, $item)
    {
        $array_r = [];

        $array = $this->ib_rules_mt4group
            ->select('mt4group')
            ->where('rule_id', $item['rules_id'])
            ->get();

        foreach ($array as $array_item) {
            $array_r[] = $array_item['mt4group'];
        }

        return implode(',', $array_r);
    }

    /**
     * 获取品种组
     * 返回用','分割打字符串
     *
     * @param $value
     * @param $item
     * @return string
     */
    public function symbolgroup($value, $item)
    {
        $array_r = $array = [];

        $in = $this->ib_rules_symbolgroup_detail
            ->where('rule_id', $item['rules_id'])
            ->get();

        foreach ($in as $inn) {
            $array[] = $inn['symbolgroup_id'];
        }

        $array = $this->ib_symbolgroup_detail
            ->whereIn('symbolgroup_id', $array)
            ->get();

        foreach ($array as $array_item) {
            $array_r[] = $array_item['symbol'];
        }

        return implode(',', $array_r);
    }
}