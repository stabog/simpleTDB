<?php

namespace SimpleTdb;

use SimpleTdb\TextDataModel;

class TextDataModelSessions extends TextDataModel
{
    protected $schemItems = [
        'id' => ['id', [], 0, 'Id колонки', true, true, 'text', '', '', [], [], []],
        'ausData' => ['ausData', [], 1, 'Create/Edit/Sync информация', true, false, 'list', '', '', [], [], []],
        'userId' => ['userId', [], 2, 'User id', true, false, 'text', '', '', [], [], []],
        'browser' => ['browser', [], 3, 'Browser ', true, false, 'text', '', '', [], [], []],
        'ipAddress' => ['ipAddress', [], 4, 'IP адресс', true, false, 'text', '', '', [], [], []],
        'end' => ['end', [], 5, 'Время окончания сессии', true, false, 'time', '', '', [], [], []],
        'count' => ['count', [], 6, 'Счетчик проверки сессий', true, false, 'nump', '', '', [], [], []],
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