<?php
/*

解析国内ip地址列表，以ip地址的第一段为索引，
保存到redis中的一个hash中

by 刘宏缔
2020.04.02

*/
//------------------------------------------------settings
ini_set("display_errors","On");
error_reporting(E_ALL);
//------------------------------------------------constant
define("REDIS_SERVER", "127.0.0.1");
define("REDIS_PORT", "6379");
define("IP_FILE", "/data/data/ipdata/china_ip.txt");
define("IP_HASH_NAME", "china_ip_hash");
//------------------------------------------------link 4 redis
$redis_link = new \Redis();
$redis_link->connect(REDIS_SERVER,REDIS_PORT);

//------------------------------------------------main
set_ip_list(IP_FILE);

//------------------------------------------------function
//处理所有的ip范围到redis
function set_ip_list($ip_file) {
    //从文件中得到所有的国内ip
    $arr_all = file($ip_file);

    //遍历，得到所有的第一段
    $arr_first = array();
    foreach ($arr_all as $k => $rangeone) {
	      $rangeone = trim($rangeone);
        if ($rangeone == "") {
            continue;
        }
        $first = explode(".", $rangeone);
        if (isset($first[0]) && $first[0]!='') {
    	      $arr_first[] = $first[0];
        }
    }

    //对所有的第一段去除重复
    $arr_first = array_unique($arr_first);

    //得到线上hash的所有key
    $arr_hkeys = hash_keys(IP_HASH_NAME);

    //如果一个线上已存在的key不再存在于新ip的第一段的数组中
    //需要从线上hash中删除
    if (is_array($arr_hkeys) && sizeof($arr_hkeys)>0) {
        foreach($arr_hkeys as $k => $hkey_one) {
           if (!in_array($hkey_one, $arr_first)) {
                echo "will delete :".$hkey_one."\n";
                hash_delete_hkey(IP_HASH_NAME,$hkey_one);
            }
        }
    }

    //得到每个第一段下面对应的所有ip地址段,保存到redis
    foreach ($arr_first as $k => $first) {
	  	  add_a_list_by_first($first,$arr_all);  
    }

}


//把所有的第一段为指定数字的ip,添加到redis
function add_a_list_by_first($first,$arr) {

	   $arr_line = array();
     foreach ($arr as $k => $rangeone) {
	        $rangeone = trim($rangeone);
          $first_a = explode(".", $rangeone);
          if (!isset($first_a[0]) || $first_a[0] == "") {
          	  continue;
          }
          $cur_first = $first_a[0];
          if ($cur_first == $first) {

              $line = get_line_by_rangeone($rangeone);
              //echo "line:".$line."\n";
              $arr_line[] = $line;
          } else {
          	  continue;
          }
      }


      if (sizeof($arr_line) >0) {
      	   $key_name = $first;
      	   hash_set(IP_HASH_NAME,$key_name,$arr_line);
      }
}


//得到一个ip地址段的起始范围
function get_line_by_rangeone($networkRange) {

        $s = explode('/', $networkRange);
        $network_start = (double) (sprintf("%u", ip2long($s[0])));
        $network_len = pow(2, 32 - $s[1]);
        $network_end = $network_start + $network_len - 1;

        $line = $network_start."--".$network_end;
        return $line;
}


//redis set 一个数组到hash
function hash_set($hash_name,$key_name,$arr_value){
	    global $redis_link;
      $str_value = json_encode($arr_value);
      $b = $redis_link->hset($hash_name, $key_name, $str_value);
}

//返回redis hash中
function hash_keys($hash_name) {
      global $redis_link;
      $arr = $redis_link->hKeys($hash_name);
      return $arr;
}

//删除一个hash的hkey
function hash_delete_hkey($hash_name,$key_name) {
      global $redis_link;
      $redis_link->hdel($hash_name, $key_name);
}

?>