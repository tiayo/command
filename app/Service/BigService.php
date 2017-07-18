<?php

namespace Command\Service;

use Command\Model\Account;
use Command\Model\AccountChildren;
use Command\Model\IbChildren;
use Command\Model\IbRulesMt4group;
use Command\Model\IbRulesRelation;
use Command\Model\IbRulesSymbolgroupDetail;
use Command\Model\IbSymbolgroupDetail;

class BigService
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
    protected $ib_children;

    public function __construct(Account $account,
                                AccountChildren $account_children,
                                IbRulesRelation $ib_rules_relation,
                                IbRulesMt4group $ib_rules_mt4group,
                                IbSymbolgroupDetail $ib_symbolgroup_detail,
                                IbRulesSymbolgroupDetail $ib_rules_symbolgroup_detail,
                                PublicService $public,
                                UpService $up,
                                IbChildren $ib_children)
    {
        $this->account = $account;
        $this->account_children = $account_children;
        $this->ib_rules_relation = $ib_rules_relation;
        $this->ib_rules_mt4group = $ib_rules_mt4group;
        $this->ib_symbolgroup_detail = $ib_symbolgroup_detail;
        $this->ib_rules_symbolgroup_detail = $ib_rules_symbolgroup_detail;
        $this->public = $public;
        $this->up = $up;
        $this->ib_children = $ib_children;
    }

    /**
     * 执行策略.
     *
     * @param $value //订单信息
     */
    public function work($value)
    {
        $account_children = $this->account_children->select('aid', 'name')->where('login', $value['login'])->first();

        $aid = $account_children->aid;

        $account = $this->account->select('user_type', 'name')->where('id', $aid)->first();

        $loop_aid = $aid;

        while (($ib_id = $this->public->ib_id($loop_aid)) > 0) {
            $cur = $this->cur($ib_id);

            $this->loop($cur, $value, $account, $ib_id, $loop_aid, $account_children);

            $loop_aid = $ib_id;
        }

        return true;
    }

    /**
     * 策略主体.
     *
     * @param $cur
     * @param $value
     * @param $account
     * @param $ib_id
     * @param $aid
     * @param $account_children
     *
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

            if ($mt4groupcount <= 0 || $symbolgroupcount <= 0 || strpos($item['usergroup'], (string) $account->user_type) < 0) {
                continue;
            }

            $money = $this->public->money($item['value'], $value, $item);

            $ib = $this->public->ib($ib_id);

            $sub_value = $this->sub_value($item, $aid);

            if ($money - $sub_value <= 0) {
                continue;
            }

            $item['mt4group'] = $this->mt4group($value, $item);

            $item['symbolgroup'] = $this->symbolgroup($value, $item);

            //创建返佣记录
            $this->public->create($value, $ib, $aid, $account_children, $ib_id, $item, $money - $sub_value, $account);
        }

        return true;
    }

    /**
     * 下级需要扣掉打钱.
     *
     * @param $item
     *
     * @return int
     */
    public function sub_value($item, $aid)
    {
        $info = $this->ib_rules_relation
            ->select('value')
            ->where('ib_id', $aid)
            ->where('rules_id', $item['rules_id'])
            ->where('gid', $item['gid'])
            ->first();

        if (empty($info)) {
            return 0;
        }

        return $info->value;
    }

    /**
     * 获取返佣规则.
     *
     * @param $ib_id //上级代理id
     *
     * @return mixed
     */
    public function cur($ib_id)
    {
        return $this->ib_rules_relation
            ->join('ib_rules', 'ib_rules_relation.rules_id', '=', 'ib_rules.id')
            ->where('ib_rules_relation.ib_id', $ib_id)
            ->where('ib_rules.level', '>', 0)
            ->get()
            ->toArray();
    }

    /**
     * mt4分组数量.
     *
     * @param $value
     * @param $item
     *
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
     * 品种组数量.
     *
     * @param $value
     * @param $item
     *
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
     * 返回用','分割打字符串.
     *
     * @param $value
     * @param $item
     *
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
     * 返回用','分割打字符串.
     *
     * @param $value
     * @param $item
     *
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
