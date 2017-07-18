<?php

namespace Command\Service;

use Command\Model\Ib;
use Command\Model\IbLevelValues;

class UpService
{
    protected $ib;
    protected $ib_level_values;
    protected $public;

    public function __construct(Ib $ib, IbLevelValues $ib_level_values, PublicService $public)
    {
        $this->ib = $ib;
        $this->ib_level_values = $ib_level_values;
        $this->public = $public;
    }

    public function up($value, $ib_id, $item, $aid, $account_children, $account)
    {
        $ib_id = $this->public->ib_id($ib_id);

        if (empty($ib_id) || $ib_id <= 0) {
            return false;
        }

        if ($item['gid'] <= 0) {
            return false;
        }

        if ($item['type'] != 0) {
            return false;
        }

        $level = $this->public->ib($ib_id)->level;

        $rule_value = $this->rule_value($level, $item);

        if (empty($rule_value)) {
            if ($item['level'] == 0) {
                $this->up($value, $ib_id, $item, $aid, $account_children, $account);
            }
        } else {
            $money = $this->public->money($rule_value, $value, $item);

            $ib = $this->public->ib($ib_id);

            if ($money > 0) {
                $this->public->create($value, $ib, $aid, $account_children, $ib_id, $item, $money, $account);
            }

            $this->up($value, $ib_id, $item, $aid, $account_children, $account);
        }
    }

    public function rule_value($level, $item)
    {
        return $this->ib_level_values
            ->select('value')
            ->join('ib_group_rules', 'ib_group_rules.id', '=', 'ib_level_values.gid')
            ->where('lid', $level)
            ->where('gid', $item['gid'])
            ->first()
            ->value;
    }
}
