<?php

namespace Command\Service;

use Command\Console\PrintColor;
use Command\Model\IbOrders;

class QueueService
{
    protected $color;
    protected $ib_orders;
    protected $many;
    protected $big;

    public function __construct(PrintColor $color, IbOrders $ib_orders, ManyService $many, BigService $big)
    {
        $this->color = $color;
        $this->ib_orders = $ib_orders;
        $this->many = $many;
        $this->big = $big;
    }

    public function queue($array)
    {
        $usleep = empty($array['s']) ? 100000 : $array['s'] * 1000; //单位：微秒

        //开始提示
        print_r($this->color->getColoredString('监控启动成功！', null, 'green')."\r\n");

        while (true) {
            $all = $this->ib_orders
                ->where('status', 0)
                ->get();

            $this->loop($all);

            usleep($usleep);
        }

        return true;
    }

    public function loop($all)
    {
        foreach ($all as $value) {
            //记录开始时间
            $start = microtime(true);

            //输出订单信息
            print_r("\r\n\r\n".$this->color->getColoredString('监控到ticket:'.$value['ticket'], 'red', 'yellow').' ');

            //日志
            write_log('监控到ticket:'.$value['ticket']);

            //执行多代理逻辑
            $this->many->work($value);

            //执行大代理逻辑
            $this->big->work($value);

            //更新状态为1
            $this->ib_orders
                ->where('id', $value['id'])
                ->update(['status' => 1]);

            //记录结束时间
            $end = microtime(true) - $start;

            //输出结束时间
            print_r($this->color->getColoredString('执行完毕，耗时时间：'.round($end, 6).'秒', null, 'magenta'));

            //日志
            write_log($value['ticket'].'执行完毕，耗时时间：'.round($end, 6).'秒');
        }
    }
}
