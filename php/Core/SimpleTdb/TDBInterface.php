<?php

namespace SimpleTdb;


interface TDBInterface
{
    public function all(array $sort = []) : array ;
    public function flt(array $params, array $sort = []) : array ;

    public function add(array $item); // int|string|array
    public function get($id); // bool|array
    public function upd($id, array $item) : bool ;
    public function rpl($id, array $item) : bool ;
    public function del($id) : bool ;    
}