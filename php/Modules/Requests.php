<?php


use \SimpleTdb\TextDataModel as TDM;
use \SimpleTdb\TextDataModelUsers as TDUsers;
use \SimpleTdb\TextDataModelException;

class Requests extends TDM {
    protected $dbName = 'requests';
    protected $indexType = "num";
    protected $usersModel;
    protected $usersRolesModel;
    protected $usersCountsModel;
    
    protected $schemItems = [
        0 => [0, [], 'id', 'id запроса', true, true, 'text', '', '', [], [], []],
        1 => [1, [], 'ausData', 'Create/Edit/Sync информация', true, false, 'list', '', '', [], [], []],
    ];

    public function __construct(string $dbName='', string $dbPath='', string $indexType='') {
        // Вызов родительского конструктора
        parent::__construct($dbName, $dbPath, $indexType);

        $this->usersModel = new TDUsers('users', $dbPath, 'guid');
        $this->usersRolesModel = new TDM('roles', $dbPath);
        //$this->usersCountsModel = new TDM('counts', $dbPath);
        
    }


    public function add($info, $surce="user")
    {        
        if (!$info ) {
            throw new TextDataModelException("Не корректные данные для add.");
        }
        

        $userId = $info["userId"];
        $equipId = $info["equipmentId"];
        $type = $info["type"];
        $source = $info["source"];

        $userInfo = $this->usersModel->get($userId);

        $userRequest = $this->countRequests($userId);
        //print_r($userRequest);


        $roleId = $userInfo["role"];
        $roleInfo = $this->usersRolesModel->get($roleId);
        
        $limits ["equip"] = [
            "today" => $userInfo ["equip_today"] ?? $roleInfo ["equip_today"],
            "this_month" => $userInfo ["equip_this_month"] ?? $roleInfo ["equip_this_month"],
            "total" => $userInfo ["equip_total"] ?? $roleInfo ["equip_total"],
        ];        


        $info = $this->schem->validateAndConvertItemValues ($info, $this->schem->getSchem(), "dict", "data");
        $newId = $this->data->add($info);

        if ($newId) {
            //$this->updateLinkedBasesNew($newId, $info);
            return $newId;
        }
        

        return false;
    }

    // Universal method to count user requests by source and equipmentId
    public function countRequests($userId = null): array
    {
        $requests = $this->all(); // Assuming `all()` method returns all requests
        $counts = [];

        $today = strtotime('today');
        $this_month_start = strtotime('first day of this month');
        
        /*
        if ($userId){
            $counts[$userId]["equip"] = [
                "total" => [],
                "today" => [],
                "this_month" => [],
            ];
        }
        */

        foreach ($requests as $request) {
            $requestUserId = $request['userId'] ?? null;
            $requestType = $request['type'] ?? null;
            $requestSource = $request['source'] ?? null;
            $requestEquipId = $request['equipmentId'] ?? null;
            $requestTime = $request["ausData"][0];

            if ($userId && $requestUserId !== $userId) {
                continue;
            }

            if ($requestType !== 'response') {
                continue;
            }

            if (!isset($counts[$requestUserId])) {
                $counts[$requestUserId]["equip"] = [                    
                    "total" => [],
                    "today" => [],
                    "this_month" => [],
                ];
            }

            if (!isset($counts[$requestUserId][$requestSource])) {
                $counts[$requestUserId][$requestSource] = [
                    "total" => 0,
                    "today" => 0,
                    "this_month" => 0,
                ];
            }

            $counts[$requestUserId][$requestSource]["total"]++;

            if ($requestTime >= $today) {
                $counts[$requestUserId][$requestSource]["today"]++;
            }

            if ($requestTime >= $this_month_start) {
                $counts[$requestUserId][$requestSource]["this_month"]++;
            }


            // Count unique equipment IDs
            if ($requestSource === 'LLM'){
                $this->addUniqueEquipment($counts[$requestUserId]["equip"], $requestEquipId, $requestTime, $today, $this_month_start);
            }            
        }

        // Convert unique equipment arrays to counts
        foreach ($counts as &$userCounts) {
            $userCounts["equip"]["total"] = count($userCounts["equip"]["total"]);
            $userCounts["equip"]["today"] = count($userCounts["equip"]["today"]);
            $userCounts["equip"]["this_month"] = count($userCounts["equip"]["this_month"]);
        }        

        return $counts;
    }

    private function addUniqueEquipment(array &$equipments, string $equipId, int $requestTime, int $today, int $this_month_start): void
    {
        if (!in_array($equipId, $equipments["total"])) {
            $equipments["total"][] = $equipId;
        }

        if ($requestTime >= $today && !in_array($equipId, $equipments["today"])) {
            $equipments["today"][] = $equipId;
        }

        if ($requestTime >= $this_month_start && !in_array($equipId, $equipments["this_month"])) {
            $equipments["this_month"][] = $equipId;
        }
    }
}