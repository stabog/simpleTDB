<?php

namespace SimpleTdb;

use SimpleTdb\TextDataModel as TDBM;

class ModelsFactory
{
    public static function createModels(string $rootBaseName='root') {
        $models = [];
        $namespace = 'FileSearcher\\';
        
        $bases = new \SimpleTdb\TextDataModel($rootBaseName, '', 'guid');
        foreach ($bases->all() as $bInfo){
            $basesNames[] = $bInfo[2];
        }

        // Создание экземпляров всех классов
        foreach ($basesNames as $baseName) {

            $className = $namespace.ucfirst($baseName);

            if (class_exists($className)) {
                $models[$baseName] = new $className($baseName);
            } else {
                $models[$baseName] = new TDBM($baseName, '', 'guid');
            }
        }

        // Установка зависимостей
        foreach ($models as $baseName => $model) {
            $dependencies = array_filter($models, fn($key) => $key !== $baseName, ARRAY_FILTER_USE_KEY);
            $model->setDependencies($dependencies);
        }

        return $models;
    }
}
