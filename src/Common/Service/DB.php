<?php

namespace Common\Service;


use Silex\Application;

class DB
{
    private static $instance;
    public $PDO;

    private function __construct(Application $app)
    {
        $this->PDO = new \PDO('sqlite:'.$app['my.config']['db']['db_path']);
        $this->PDO->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        if (!$this->checkIfExist()) {
            $this->generateTable();
        }
    }

    public static function get(Application $app)
    {
        if (!self::$instance) {
            self::$instance = new self($app);
        }
        return self::$instance;
    }

    public function getPresets()
    {
        $st = $this->PDO->query("SELECT * FROM presets");
        $result = [];
        while ($row = $st->fetch(\PDO::FETCH_ASSOC)) {
            $row['presetJson'] = json_decode($row['presetJson']);
            $result[$row['id']] = $row;
        }
        return $result;
    }

    public function savePreset($name, $dataArray)
    {
        $sql_q = '
          INSERT INTO presets (presetName, presetJson) VALUES(\''.$name.'\', \''.json_encode($dataArray).'\');
        ';
        $this->PDO->exec($sql_q);
        return $this->PDO->lastInsertId();
    }

    public function deletePreset($presetId)
    {
        $presetId = (int)$presetId;
        return $this->PDO->exec("DELETE FROM presets WHERE id=".$presetId);
    }

    private function checkIfExist()
    {
        try {
            $this->PDO->exec("SELECT * FROM presets LIMIT 1");
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function generateTable()
    {
        $this->PDO->exec("
            CREATE TABLE presets (
                id integer PRIMARY KEY,
                presetName TEXT,
                presetJson TEXT
            )
        ");
    }
}
