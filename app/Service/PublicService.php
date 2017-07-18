<?php

namespace Command\Service;

use Command\Model\Ib;
use Command\Model\IbChildren;

class PublicService
{
    protected $ib;
    protected $ib_children;

    public function __construct(Ib $ib, IbChildren $ib_children)
    {
        $this->ib = $ib;
        $this->ib_children = $ib_children;
    }

    public function money($rule_value, $value, $item)
    {
        if ($item['way'] = '$') {
            $item = $rule_value * $value['volume'];
        } elseif ($item['way'] = '%') {
            $item = $rule_value * $value['volume'] * 0.01;
        }

        return $item;
    }

    public function create($value, $ib, $aid, $account_children, $ib_id, $item, $money, $account)
    {
        $map['ticket'] = $value['ticket'];
        $map['LEVEL'] = $ib->level;
        $map['from_aid'] = $aid;
        $map['from_account'] = $value['login'];
        $map['from_name'] = $account_children->name;
        $map['to_account'] = $ib->mt4;
        $map['to_name'] = $ib->name;
        $map['to_aid'] = $ib_id;
        $map['money'] = sprintf('%.2f', $money);
        $map['volume'] = $value['volume'];
        $map['create_at'] = $value['close_time'];
        $map['rid'] = $item['rules_id'];
        $map['period'] = $item['date_type'];
        $map['status'] = 0;
        $map['mt4group'] = $value['mt4group'];
        $map['symbol'] = $value['symbol'];
        $map['remark'] = $this->remark($item, $account, $value);

        write_log($map);
    }

    public function ib($ib_id)
    {
        return $this->ib->select('level', 'mt4', 'name')->where('aid', $ib_id)->first();
    }

    public function remark($item, $account, $value)
    {
        return '记录返佣规则(id|名字|用户组号|品种|MT4组|结算方式)为:'.$item['rules_id'].'|'.$item['title'].'|'.$item['usergroup'].'|'.$item['symbolgroup'].'|'.$item['mt4group'].'|'.$item['way'].';记录交易账户(用户组号|品种|MT4组)为:'.(string) $account->user_type.'|'.$value['symbol'].'|'.$value['mt4group'].'';
    }

    /**
     * 获得用户上一级代理.
     *
     * @param $ib_id
     *
     * @return int
     */
    public function ib_id($ib_id)
    {
        $info = $this->ib_children
            ->select('ib_id')
            ->where('aid', $ib_id)
            ->first();

        if (empty($info->ib_id)) {
            return 0;
        }

        return $info->ib_id;
    }
}
