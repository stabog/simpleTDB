<?php

namespace SimpleTdb;

use SimpleTdb\TextDataModel;

class TextDataModelSessions extends TextDataModel
{
    protected $schemItems = [
        0 => [0, [], 'id', 'Id колонки', true, true, 'text', '', '', [], [], []],
        1 => [1, [], 'ausData', 'Create/Edit/Sync информация', true, false, 'list', '', '', [], [], []],
        2 => [2, [], 'userId', 'User id', true, false, 'text', '', '', [], [], []],
        3 => [3, [], 'browser', 'Browser ', true, false, 'text', '', '', [], [], []],
        4 => [4, [], 'ipAddress', 'IP адресс', true, false, 'text', '', '', [], [], []],
        5 => [5, [], 'end', 'Время окончания сессии', true, false, 'time', '', '', [], [], []],
        6 => [6, [], 'count', 'Счетчик проверки сессий', true, false, 'nump', '', '', [], [], []],
    ];
    
    public function __construct(string $dbName = 'sess', string $dbPath = '', string $indexType = 'guid')
    {
        parent::__construct($dbName, $dbPath, $indexType);
    }

    public function createSession(string $userId)
    {
        $browserInfo = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'; // Получение информации о браузере
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown'; // Получение IP-адреса
        $currentTime = time();
        $twoWeeksAgoTime = $currentTime + (7 * 24 * 60 * 60); // Две недели в секундах

        $sessionInfo = [
            'userId' => $userId,
            'browser' => $browserInfo,
            'ipAddress' => $ipAddress,
            'end' => $twoWeeksAgoTime,
        ];
        return $this->add($sessionInfo, "sys");
    }

    public function getSession(string $sessionId)
    {        
        $sessionInfo = $this->get($sessionId);
        if (!$sessionInfo){
            return false;
        }
        $sessionInfo['end'] = time() + (7 * 24 * 60 * 60);
        $sessionInfo['count'] = isset($sessionInfo['count']) ? $sessionInfo['count'] : 0;
        $sessionInfo['count'] ++;

        $this->upd($sessionId, $sessionInfo, "sys");
        return $sessionInfo;

    }

    public function delSession(string $sessionId)
    {
        return $this->del($sessionId);
    }
}